<?php

namespace ProductsImporter\Repositories;

use Exception;
use PDOException;
use ProductsImporter\Classes\Category;
use ProductsImporter\Classes\InternalProductId;
use ProductsImporter\Classes\Product;
use ProductsImporter\Services\ImageService;
use ProductsImporter\Utils\AppHelper;
use ProductsImporter\Utils\CategoryHelper;
use ProductsImporter\Utils\FeaturesHelper;
use ProductsImporter\Utils\ProductHelper;
use ProductsImporter\Utils\Registry;

class ProductRepository extends RepositoryCore
{

  private $internalProductIds = [];
  /**@var FeatureRepository */
  private $featureRepository;
  /**@var AttributesRepository */
  private $attributeRepository;
  /**@var ImageRepository */
  private $imageRepository;

  public function __construct($featureRepository, $attributeRepository, $imageRepository)
  {
    $this->featureRepository = $featureRepository;
    $this->attributeRepository = $attributeRepository;
    $this->imageRepository = $imageRepository;
  }

  /**
   * @param int $idStock
   * @param $productId
   * @param Product $product
   * @return array
   */
  public static function getSingleQuantityStockAvailableData(int $idStock, $productId, Product $product): array
  {
    $data = ['id_stock_available'   => $idStock,
             'id_product'           => $productId,
             'id_product_attribute' => $product->getAttributeId(),
             'id_shop'              => $_ENV['SHOP_ID'],
             'id_shop_group'        => 0,
             'quantity'             => $product->getQuantity() > 0 ? $product->getQuantity() : $_ENV['QUANTITY'],
             'out_of_stock'         => $_ENV['OUT_OF_STOCK']
    ];
    return $data;
  }

  /**
   * main function to add products to Db
   * @param $products
   * @throws Exception
   */
  public function importProductsToDb($products)
  {
    $this->beginTransaction();
    try {
      $this->addOrUpdateProducts($products);
    } catch (PDOException $e) {
      $this->rollback();
      throw $e;
    }
    $this->commit();
    Registry::bind('internalIds', $this->internalProductIds);
  }

  /**
   * adds or updates products to Db
   * @throws \Exception
   * @var Product [] $products
   */
  public function addOrUpdateProducts($products)
  {
    foreach ($products as $product) {
      $exists = $this->checkExist("{$_ENV['MYSQL_PREFIX']}product_lang", ['name' => $product->getProductName()]);
      if ($exists) {
        $product->setId($exists['data']['id_product']);
        $this->checkProductInInternalOrderIdTable($product);
        $this->updateProduct($product, $exists['id_product']);
      } else {
        $this->addProduct($product);
      }
    }
  }

  /**
   * Updates Product
   * @param Product $product
   * @param int $productId
   * @throws \Exception
   */
  public function updateProduct($product, $productId)
  {
    $product = ProductHelper::getMainProductByAttribute($product);
    $filteredProductCategories = CategoryHelper::filterProductCategories(Registry::get('categories'), $product->getCategories());
    $product->setCategories($filteredProductCategories);
    $defaultProductCategoryId = CategoryHelper::getDefaultCategoryId($product->getCategories()[0]);
    $product->setDefaultCategoryId($defaultProductCategoryId);

    $mlProductLangData = ProductHelper::getProductLangData($product);
    $mlProductData = ProductHelper::getProductData($product);
    $mlProductShopData = ProductHelper::getProductShopData($product);

    $this->update("{$_ENV['MYSQL_PREFIX']}product_lang", $mlProductLangData, 'id_product', $productId);
    $this->update("{$_ENV['MYSQL_PREFIX']}product", $mlProductData, 'id_product', $productId);
    $this->update("{$_ENV['MYSQL_PREFIX']}product_shop", $mlProductShopData, 'id_product', $productId);
    $this->updateOrDeleteProductCategoriesRelations($productId, $product->getCategories());

    $filteredFeatures = FeaturesHelper::filterProductFeatures($product->getFeatures());
    $product->setFeatures($filteredFeatures);
    $this->featureRepository->updateOrDeleteProductFeatures($productId, $product->getFeatures());

    if (!empty($product->getAttributes() && !is_null($product->getAttributes()))) {
      $this->attributeRepository->updateAttributes($product);
    }
    $this->updateProductQuantity($product);

        //add product internalColumnId
    $this->updateProductInternalColumnId($product);
    //insert product images
//    $images = ImageService::FilterImages($product);
//    $this->imageRepository->insertImages($images, $product->getId());
  }

  private function addProductCategoriesRelations($productId, array $categories)
  {
    foreach ($categories as $category) {
      $this->addSingleProductCategoryRelation($productId, $category);
    }
  }

  /**
   * aad data to global_internal_order_id for product and attributes
   * @param Product $product
   * @return array
   */
  private function addProductInternalColumnId(Product $product)
  {
    $internalIds = [];
    $data = [
      'id_global'    => $product->getGlobalId(),
      'id_product'   => $product->getId(),
      'id_attribute' => $product->getAttributeId(),
    ];
    $this->insert("{$_ENV['MYSQL_PREFIX']}internal_order_id", $data);
    $globalId = new InternalProductId($product->getGlobalId(), $product->getId(), $product->getAttributeId());
    $internalIds[] = $globalId;
    foreach ($product->getAttributes() as $attribute) {
      $data = [
        'id_global'    => $attribute->getGlobalId(),
        'id_product'   => $product->getId(),
        'id_attribute' => $attribute->getAttributeId(),
      ];
      $this->insert("{$_ENV['MYSQL_PREFIX']}internal_order_id", $data);

      $globalId = new InternalProductId($product->getGlobalId(), $product->getId(), $product->getAttributeId());

      $internalIds[] = $globalId;
    }

    return $internalIds;
  }

  /**
   * @param Product $product
   * @throws Exception
   */
  public function addProduct($product)
  {
    //set witch product should be dafault filtering product and attributes
    $product = ProductHelper::getMainProductByAttribute($product);
    //set witch categories should be added to product
    $filteredProductCategories = CategoryHelper::filterProductCategories(Registry::get('categories'), $product->getCategories());
    $product->setCategories($filteredProductCategories);
    //search for default category (last node of first category)
    $defaultCategoryId = CategoryHelper::getDefaultCategoryId($product->getCategories()[0]);
    $product->setDefaultCategoryId($defaultCategoryId);
    //set product Id
    $productId = $this->getMaxIdFromTable("{$_ENV['MYSQL_PREFIX']}product", 'id_product') + 1;
    $product->setId($productId);

    $mlProductLangData = ProductHelper::getProductLangData($product);
    $mlProductData = ProductHelper::getProductData($product);

    $mlProductShopData = ProductHelper::getProductShopData($product);

    $this->insert("{$_ENV['MYSQL_PREFIX']}product_lang", $mlProductLangData);
    $this->insert("{$_ENV['MYSQL_PREFIX']}product", $mlProductData);
    $this->insert("{$_ENV['MYSQL_PREFIX']}product_shop", $mlProductShopData);


    $this->addProductCategoriesRelations($productId, $product->getCategories());

    $filteredFeatures = FeaturesHelper::filterProductFeatures($product->getFeatures());
    $product->setFeatures($filteredFeatures);
    $this->featureRepository->addProductFeatures($productId, $product->getFeatures());

    if (!is_null($product->getAttributes()) && !empty($product->getAttributes())) {
      $this->attributeRepository->addAttributes($product);
    }
    $this->addProductQuantity($product);

    //add product internalColumnId
    $this->addProductInternalColumnId($product);
    //insert product images
    $images = ImageService::FilterImages($product);
    $this->imageRepository->insertImages($images, $product->getId());

  }

  /**
   * adds relation to Db beetween Product and Category
   * @param $productId
   * @param Category[] $categories
   */
  public function updateOrDeleteProductCategoriesRelations($productId, $categories): void
  {
    $filteredCategories = $this->deleteExpendableProductCategoriesRelations($categories, $productId);
    $this->updateProductCategoriesRelations($categories, $filteredCategories, $productId);
  }

  /**
   * @param $productId
   * @param $category
   */
  public function addSingleProductCategoryRelation($productId, $category): void
  {
    $data = ProductHelper::getMlCategoryProductData($productId, $category);
    $this->insert("{$_ENV['MYSQL_PREFIX']}category_product", $data);
  }

  private function addProductQuantity(Product $product)
  {
    $sumOfquantity = $this->getTotalProductQuantity($product);

    $this->addMainQuantity($product->getId(), $sumOfquantity);
    $this->addSingleQuantity($product);
    foreach ($product->getAttributes() as $attribute) {
      $this->addSingleQuantity($attribute);
    }
  }

  /**
   * @param Product $product
   * @param $productId
   */
  private function addSingleQuantity(Product $product)
  {
    $idStock = $this->getMaxIdFromTable("{$_ENV['MYSQL_PREFIX']}stock_available", 'id_stock_available') + 1;
    $data = ProductHelper::getSingleQuantityStockAvailableData($idStock, $product);
    $this->insert("{$_ENV['MYSQL_PREFIX']}stock_available", $data);
  }

  private function addMainQuantity(int $productId, $sumOfQuantity): void
  {
    $idStock = $this->getMaxIdFromTable("{$_ENV['MYSQL_PREFIX']}stock_available", 'id_stock_available') + 1;
    $data = ProductHelper::getMainQuantityStockAvailableData($idStock, $productId, $sumOfQuantity);
    $this->insert("{$_ENV['MYSQL_PREFIX']}stock_available", $data);
  }

  /**
   * @param array $categories
   * @param array $productCategoriesRelations
   * @param $productId
   */
  public function updateProductCategoriesRelations($productId, $filteredCategories, $productCategories): void
  {
    foreach ($productCategories as $category) {
      $contains = false;
      foreach ($filteredCategories as $relation) {
        if ($category->getId() === $relation['id_category']) {
          $contains = true;
          break;
        }
      }
      if (!$contains) {
        $this->addSingleProductCategoryRelation($productId, $category);
      }
    }
  }

  /**
   * @param array $categories
   * @param $productId
   */
  private function deleteExpendableProductCategoriesRelations(array $categories, $productId): array
  {
    $productCategoriesRelations = $this->search("{$_ENV['MYSQL_PREFIX']}category_product", ['id_product' => $productId]);
    foreach ($productCategoriesRelations as $key => $categoriesRelation) {
      $contains = false;
      foreach ($categories as $category) {
        if ($categoriesRelation['id_category'] === $category->getId()) {
          $contains = true;
          break;
        }
      }
      if (!$contains) {
        $this->delete("{$_ENV['MYSQL_PREFIX']}category_product", ['id_category' => $categoriesRelation['id_category'], 'id_product' => $productId]);
        unset($productCategoriesRelations[$key]);
      }
    }
    return $productCategoriesRelations;
  }

  private function updateProductQuantity(Product $product)
  {
    $sumOfQuantity = $this->getTotalProductQuantity($product);

    $this->updateMainQuantity($product->getId(), $sumOfQuantity);
    $this->updateSingleQuantity($product);
    foreach ($product->getAttributes() as $attribute) {
      $this->updateSingleQuantity($attribute);
    }
  }

  /**
   * @param Product $product
   * @return false|Product
   */
  private function checkProductInInternalOrderIdTable(Product $product)
  {
    $exists = $this->checkExist("{$_ENV['MYSQL_PREFIX']}internal_order_id", ['id_global' => $product->getGlobalId()]);
    if ($exists) {
      $product->setAttributeId($exists['id_attribute']);
      return $product;
    }
    return false;
  }

  /**
   * @param Product $product
   * @return int
   */
  private function getTotalProductQuantity(Product $product): int
  {
    $quantity = $product->getQuantity() > 0 ? $product->getQuantity() : $_ENV['QUANTITY'];
    foreach ($product->getAttributes() as $attribute) {
      $quantity += $attribute->getQuantity() > 0 ? $product->getQuantity() : $_ENV['QUANTITY'];
    }
    return $quantity;
  }

  /**
   * @param int $productId
   * @param int $sumOfQuantity
   */
  public function updateMainQuantity(int $productId, int $sumOfQuantity): void
  {
    $result = $this->search("{$_ENV['MYSQL_PREFIX']}stock_available", ['id_product' => $productId, 'id_product_attribute' => 0]);
    $idStock = $result[0]['id_stock_available'];
    $data = ProductHelper::getMainQuantityStockAvailableData($idStock, $productId, $sumOfQuantity);
    $this->update("{$_ENV['MYSQL_PREFIX']}stock_available", $data, 'id_stock_available', $idStock);
  }

  /**
   * @param Product $product
   */
  public function updateSingleQuantity(Product $product): void
  {
    $result = $this->search("{$_ENV['MYSQL_PREFIX']}stock_available", ['id_product' => $product->getId(), 'id_product_attribute' => $product->getAttributeId()]);
    $idStock = $result[0]['id_stock_available'];
    $data = ProductHelper::getSingleQuantityStockAvailableData($idStock, $product);
    $this->update("{$_ENV['MYSQL_PREFIX']}stock_available", $data,'id_stock_available',$idStock);
  }
}
