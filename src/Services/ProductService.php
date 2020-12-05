<?php

namespace ProductsImporter\Services;


use ProductsImporter\Classes\Product;
use ProductsImporter\Utils\CategoryHelper;
use ProductsImporter\Utils\ProductHelper;

class ProductService extends SpreadsheetService {

    /**
     * takes values of product from worksheet
     * @return array
     * @throws \Exception
     */
    public static function createProducts()
    {
        $products = [];
        $highestRow = self::$worksheet->getHighestRow();
        for ($row = 2; $row <= $highestRow; ++$row) {
            $product = new Product();

            $categories = [];
            $categories[] = CategoryService::getSingleRowCategories(self::CATEGORIES_COLUMNS[0], $row);
            $categories[] = CategoryService::getSingleRowCategories(self::CATEGORIES_COLUMNS[1], $row);
            $categories[] = CategoryService::getSingleRowCategories(self::CATEGORIES_COLUMNS[2], $row);
            $categories = CategoryHelper::removeUnsetPaths($categories);
            if (empty($categories)) {
                LoggerService::getLogger()->error("Product from row {$row} don't have any category");
                continue;
            }
            $product->setCategories($categories);

            $product->setGlobalId(self::$worksheet->getCell(self::GLOBAL_ID.$row)->getValue());
            $product->setIndexLupus(self::$worksheet->getCell(self::INDEX_LUPUS.$row)->getValue());
            $product->setLupusPictureCode(self::$worksheet->getCell(self::LUPUS_PICTURE_CODE.$row)->getValue());
            $product->setSeries(self::$worksheet->getCell(self::SERIES.$row)->getValue());
            $product->setProductName(self::$worksheet->getCell(self::PRODUCT_NAME.$row)->getValue());
            $product->setLinkRewrite($product->getProductName());
            $product->setLongDiscription(self::$worksheet->getCell(self::DESCRYPTION_LONG_PL.$row)->getValue());
            $product->setUpc(self::$worksheet->getCell(self::UPC.$row)->getValue());
            $product->setFittingStandard(self::$worksheet->getCell(self::FITTING_STANDARD.$row)->getValue());
            $product->setEAN(self::$worksheet->getCell(self::EAN.$row)->getValue());
            $product->setDimensionAfterFoldX(self::$worksheet->getCell(self::DIMENSION_AFTER_FOLD_X.$row)->getValue());
            $product->setDimensionAfterFoldY(self::$worksheet->getCell(self::DIMENSION_AFTER_FOLD_Y.$row)->getValue());
            $product->setDimensionAfterFoldZ(self::$worksheet->getCell(self::DIMENSION_AFTER_FOLD_Z.$row)->getValue());
            $product->setNettoPriceEXWPLN(self::$worksheet->getCell(self::DETAL_NETTO_PRICE_EXW_PLN.$row)->getValue());
            $product->setCartonDimensionX(self::$worksheet->getCell(self::CARTON_DIMENSION_X.$row)->getValue());
            $product->setCartonDimensionY(self::$worksheet->getCell(self::CARTON_DIMENSION_Y.$row)->getValue());
            $product->setCartonDimensionZ(self::$worksheet->getCell(self::CARTON_DIMENSION_Z.$row)->getValue());
            $product->setWeight(self::$worksheet->getCell(self::WEIGHT.$row)->getValue());
            $product->setInOnlineShop(self::$worksheet->getCell(self::IN_ONLINE_STORE.$row)->getValue());
            $features = FeatureService::getProductFeatures($row);
            $product->setFeatures($features);

            //get images from worksheet
            $imageService = new ImageService();

            $images = $imageService->getImageData($row,self::IMAGE_ADDRESS_COLUMNS);
            $product->setImages($images);

//            $attachments = AttachmentService::getAttachments($row,self::INSTRUCTION_ADDRESS_COLUMNS);
//            $product->setAttachments($attachments);

            //checking if product is attribute
            $result = ProductHelper::checkIfIsAttribute($products, $product);
            if (!$result) {
                $products[] = $product;
            }
        }

        return $products;
    }

}
