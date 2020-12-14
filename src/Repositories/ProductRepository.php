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
        $this->ckechProductInInternalOrderIdTable($product);
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


    $mlProductLangData = $product->getProductLangData($productId);
    $mlProductData = [
      'id_product'                => $productId,
      'id_category_default'       => $defaultProductCategoryId,
      'id_shop_default'           => $_ENV['SHOP_DEFAULT_ID'],
      'cache_default_attribute'   => $product->getId(),
      'ean13'                     => $product->getEAN(),
      'upc'                       => $product->getUpc(),
      'quantity'                  => $product->getQuantity() > 0 ? $product->getQuantity() : $_ENV['QUANTITY'],
      'minimal_quantity'          => $_ENV['MINIMAL_QUANTITY'],
      'additional_delivery_times' => $_ENV['ADDITIONAL_DELIVERY_TIMES'],
      'active'                    => $_ENV['ACTIVE'],
      'out_of_stock'              => $_ENV['OUT_OF_STOCK'],
      'available_date'            => AppHelper::getActualDate(),
      'show_price'                => $_ENV['SHOW_PRICE'],
      'price'                     => $product->getNettoPriceEXWPLN(),
      'reference'                 => $product->getIndexLupus(),
      'width'                     => $product->getDimensionAfterFoldX(),
      'height'                    => $product->getDimensionAfterFoldY(),
      'depth'                     => $product->getDimensionAfterFoldZ(),
      'weight'                    => $product->getWeight(),
      'date_upd'                  => AppHelper::getActualDate(),
      'indexed'                   => 1,
      'pack_stock_type'           => $_ENV['PACK_STOCK_TYPE'],
      'state'                     => $_ENV['STATE'],
    ];

    $mlProductShopData = [
      'id_product'              => $product->getId(),
      'id_shop'                 => $_ENV['SHOP_ID'],
      'id_category_default'     => $product->getDefaultCategoryId(),
      'id_tax_rules_group'      => $_ENV['DEFAULT_TAX_RULE'],
      'on_sale'                 => $_ENV['ON_SALE'],
      'online_only'             => $_ENV['ONLINE_ONLY'],
      'ecotax'                  => $_ENV['ECO_TAX'],
      'minimal_quantity'        => $_ENV['MINIMAL_QUANTITY'],
      'price'                   => $product->getNettoPriceEXWPLN(),
      'active'                  => $_ENV['ACTIVE'],
      'available_for_order'     => $_ENV['AVAILABLE_FOR_ORDER'],
      'available_date'          => AppHelper::getActualDate(),
      'cache_default_attribute' => $product->getId(),
      'show_price'              => $_ENV['SHOW_PRICE'],
      'date_upd'                => AppHelper::getActualDate(),
      'pack_stock_type'         => $_ENV['PACK_STOCK_TYPE'],
    ];

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

//    //add product internalColumnId
//    $this->addProductInternalColumnId($product);
//    //insert product images
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

    $mlProductLangData = $product->getProductLangData($product->getId());
    $mlProductData = [
      'id_product'                => $product->getId(),
      'id_category_default'       => $product->getDefaultCategoryId(),
      'id_shop_default'           => $_ENV['SHOP_DEFAULT_ID'],
      'id_tax_rules_group'        => $_ENV['DEFAULT_TAX_RULE'],
      'cache_default_attribute'   => $product->getId(),
      'ean13'                     => $product->getEAN(),
      'upc'                       => $product->getUpc(),
      'quantity'                  => $product->getQuantity() > 0 ? $product->getQuantity() : $_ENV['QUANTITY'],
      'minimal_quantity'          => (int)$_ENV['MINIMAL_QUANTITY'],
      'additional_delivery_times' => $_ENV['ADDITIONAL_DELIVERY_TIMES'],
      'active'                    => (int)$_ENV['ACTIVE'],
      'out_of_stock'              => (int)$_ENV['OUT_OF_STOCK'],
      'available_date'            => AppHelper::getActualDate(),
      'show_price'                => (int)$_ENV['SHOW_PRICE'],
      'price'                     => $product->getNettoPriceEXWPLN(),
      'reference'                 => $product->getIndexLupus(),
      'width'                     => $product->getDimensionAfterFoldX(),
      'height'                    => $product->getDimensionAfterFoldY(),
      'depth'                     => $product->getDimensionAfterFoldZ(),
      'weight'                    => $product->getWeight(),
      'date_add'                  => AppHelper::getActualDate(),
      'date_upd'                  => AppHelper::getActualDate(),
      'pack_stock_type'           => $_ENV['PACK_STOCK_TYPE'],
      'state'                     => $_ENV['STATE'],
    ];

    $mlProductShopData = [
      'id_product'              => $product->getId(),
      'id_shop'                 => $_ENV['SHOP_ID'],
      'id_category_default'     => $product->getDefaultCategoryId(),
      'id_tax_rules_group'      => $_ENV['DEFAULT_TAX_RULE'],
      'on_sale'                 => $_ENV['ON_SALE'],
      'online_only'             => $_ENV['ON_SALE'],
      'ecotax'                  => $_ENV['ECO_TAX'],
      'minimal_quantity'        => $_ENV['MINIMAL_QUANTITY'],
      'price'                   => $product->getNettoPriceEXWPLN(),
      'active'                  => $_ENV['ACTIVE'],
      'available_for_order'     => $_ENV['AVAILABLE_FOR_ORDER'],
      'available_date'          => AppHelper::getActualDate(),
      'cache_default_attribute' => $product->getId(),
      'show_price'              => $_ENV['SHOW_PRICE'],
      'date_add'                => AppHelper::getActualDate(),
      'date_upd'                => AppHelper::getActualDate(),
      'pack_stock_type'         => $_ENV['PACK_STOCK_TYPE'],
    ];

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
    $filteredCategories = $this->deleteExpendableProductCategoriesRelations($categories,$productId);
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
    $sumOfquantity = $product->getQuantity() > 0 ? $product->getQuantity() : $_ENV['QUANTITY'];
    foreach ($product->getAttributes() as $attribute) {
      $sumOfquantity += $attribute->getQuantity() > 0 ? $product->getQuantity() : $_ENV['QUANTITY'];
    }
    $this->addMainQuantity($product->getId(), $sumOfquantity);
    $this->addSingleQuantity($product, $product->getId());
    foreach ($product->getAttributes() as $attribute) {
      $this->addSingleQuantity($attribute, $product->getId());
    }
  }

  /**
   * @param Product $product
   * @param $productId
   */
  private function addSingleQuantity(Product $product, $productId)
  {
    $idStock = $this->getMaxIdFromTable("{$_ENV['MYSQL_PREFIX']}stock_available", 'id_stock_available') + 1;
    $data = ['id_stock_available'   => $idStock,
             'id_product'           => $productId,
             'id_product_attribute' => $product->getAttributeId(),
             'id_shop'              => $_ENV['SHOP_ID'],
             'id_shop_group'        => 0,
             'quantity'             => $product->getQuantity() > 0 ? $product->getQuantity() : $_ENV['QUANTITY'],
             'out_of_stock'         => $_ENV['OUT_OF_STOCK']
    ];
    $this->insert("{$_ENV['MYSQL_PREFIX']}stock_available", $data);
  }

  private function addMainQuantity(int $productId, $sumOfQuantity): void
  {
    $idStock = $this->getMaxIdFromTable("{$_ENV['MYSQL_PREFIX']}stock_available", 'id_stock_available') + 1;
    $data = ['id_stock_available'   => $idStock,
             'id_product'           => $productId,
             'id_product_attribute' => 0,
             'id_shop'              => $_ENV['SHOP_ID'],
             'id_shop_group'        => 0,
             'quantity'             => $sumOfQuantity,
             'out_of_stock'         => $_ENV['OUT_OF_STOCK']
    ];
    $this->insert("{$_ENV['MYSQL_PREFIX']}stock_available", $data);
  }

  /**
   * @param array $categories
   * @param array $productCategoriesRelations
   * @param $productId
   */
  public function updateProductCategoriesRelations( $productId,$filteredCategories,$productCategories): void
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
  }

  /**
   * @param Product $product
   * @return false|Product
   */
  private function ckechProductInInternalOrderIdTable(Product $product)
  {
    $exists = $this->checkExist("{$_ENV['MYSQL_PREFIX']}internal_order_id",['id_global'=>$product->getGlobalId()]);
    if($exists){
      $product->setAttributeId($exists['id_attribute']);
      return $product;
    }
    return false;
  }
}
