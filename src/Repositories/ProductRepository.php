<?php

namespace ProductsImporter\Repositories;

use Exception;
use ProductsImporter\Classes\Category;
use ProductsImporter\Classes\InternalProductId;
use ProductsImporter\Classes\Product;
use ProductsImporter\Services\ImageService;
use ProductsImporter\Utils\AppHelper;
use ProductsImporter\Utils\CategoryHelper;
use ProductsImporter\Utils\FeaturesHelper;
use ProductsImporter\Utils\ProductHelper;
use ProductsImporter\Utils\Registry;

class ProductRepository extends RepositoryCore {

    private $nternalProductIds = [];
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
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }

        $this->commit();
        Registry::bind('internalIds', $this->nternalProductIds);

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
                continue;
                $this->updateProduct($product, $exists->data['id_product']);
            } else {
                $this->addProduct($product);
            }
        }
    }

    /**
     * Updates Product
     * @param  Product  $product
     * @param  int  $productId
     * @throws \Exception
     */
    public function updateProduct($product, $productId)
    {
        $filteredProductCategories = CategoryHelper::filterProductCategories(Registry::get('categories'), $product->getCategories());
        $product->setCategories($filteredProductCategories);
        $defaultProductCategoryId = CategoryHelper::getDefaultCategoryId($product->getCategories()[0]);
        $product = ProductHelper::getMainProductByAttribute($product);

        $mlProductLangData = $product->getProductLangData($productId);
        $mlProductData = [
            'id_product'                => $productId,
            'id_category_default'       => $defaultProductCategoryId,
            'id_shop_default'           => $_ENV['SHOP_DEFAULT_ID'],
            'cache_default_attribute'   => $product->getId(),
            'ean13'                     => $product->getEAN(),
            'upc'                       => $product->getUpc(),
            'quantity'                  => 999,
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
            'date_add'                  => AppHelper::getActualDate(),
            'date_upd'                  => AppHelper::getActualDate(),
            'pack_stock_type'           => $_ENV['PACK_STOCK_TYPE'],
            'state'                     => $_ENV['STATE'],
        ];

        $this->update("{$_ENV['MYSQL_PREFIX']}product_lang", $mlProductLangData, 'id_product', $productId);
        $this->update("{$_ENV['MYSQL_PREFIX']}product", $mlProductData, 'id_product', $productId);

        $this->addProductCategoriesRelations($productId, $product->getCategories());

        $filteredFeatures = FeaturesHelper::filterProductFeatures($product->getFeatures());
        $product->setFeatures($filteredFeatures);
        $this->featureRepository->addProductFeatures($productId, $product->getFeatures());

        if (!empty($product->getAttributes() && !is_null($product->getAttributes()))) {
            $this->attributeRepository->addAttributes($product);
        }
        //add product internalColumnId
        $this->addProductInternalColumnId($product);
        //insert product images
        $this->imageRepository->insertImages($product->getImages(), $product->getId());
        //insert attributes images
        foreach ($product->getAttributes() as $attribute) {
            $this->imageRepository->insertImages($attribute->getImages(), $attribute->getId());
        }
    }

    private function addProductCategoriesRelations($productId, array $categories)
    {
        foreach ($categories as $category) {
            $data = ProductHelper::getMlCategoryProductData($productId, $category);
            $this->insert("{$_ENV['MYSQL_PREFIX']}category_product", $data);
        }
    }

    /**
     * aad data to global_internal_order_id for product and attributes
     * @param  Product  $product
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
     * @param  Product  $product
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
            'id_tax_rules_group'        => 1,
            'cache_default_attribute'   => $product->getId(),
            'ean13'                     => $product->getEAN(),
            'upc'                       => $product->getUpc(),
            'quantity'                  => $product->getQuantity() > 0 ? $product->getQuantity() : (int)$_ENV['QUANTITY'],
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
            'id_tax_rules_group' => 1,
            'on_sale'                 => 0,
            'online_only'             => 0,
            'ecotax'                  => 0.0,
            'minimal_quantity'        => $_ENV['MINIMAL_QUANTITY'],
            'price'                   => $product->getNettoPriceEXWPLN(),
            'active'                  => $_ENV['ACTIVE'],
            'available_for_order'     => 1,
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
        //add product internalColumnId
        $this->addProductInternalColumnId($product);
        //insert product images
        $images = ImageService::FilterImages($product);
        $this->imageRepository->insertImages($images, $product->getId());

    }

    /**
     *adds single Product to Db
     * @param  Product  $product
     * @throws Exception
     */

    /**
     * adds relation to Db beetween Product and Category
     * @param $productId
     * @param  Category[]  $categories
     */
    public function updateProductCategoriesRelations($productId, $categories)
    {
        $productCategoriesRelations = $this->search("{$_ENV{'MYSQL_PREFIX'}}category_product", ['id_product' => $productId]);

        foreach ($categories as $category) {
            $contains = false;
            foreach ($productCategoriesRelations as $relation) {
                if ($category->getId() === $relation['id_category']) {
                    $contains = true;
                    continue;
                }
            }
            if (!$contains) {
                $data = ProductHelper::getMlCategoryProductData($productId, $category);
                $this->insert("{$_ENV['MYSQL_PREFIX']}category_product", $data);
            }
        }
    }

    /**
     * @param  Product  $product
     * @return $this|false|true
     */
    public function checkIfProductExists(Product $product)
    {
        $data = [
            'name'    => $product->getProductName(),
            'id_lang' => $_ENV['LANG_ID'],
        ];

        return $this->checkExist("{$_ENV['MYSQL_PREFIX']}product_lang", $data);
    }

    /**
     * @param  Product  $product
     * @return array
     */

}
