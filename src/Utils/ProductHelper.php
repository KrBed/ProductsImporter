<?php

namespace ProductsImporter\Utils;

use ProductsImporter\Classes\Category;
use ProductsImporter\Classes\Product;

class ProductHelper {
    /**
     * Sets witch product should be main by lowest price filtering
     * @param  Product  $product
     * @return Product
     */
    public static function getMainProductByAttribute($product)
    {
        $mainProduct = $product;
        $attributes = [];
        foreach ($product->getAttributes() as $attribute) {
            if ($mainProduct->getNettoPriceEXWPLN() > $attribute->getNettoPriceEXWPLN()) {
                $attributes[] = $mainProduct;
                $mainProduct = $attribute;
            } else {
                $attributes[] = $attribute;
            }
        }
        if (!is_null($attributes)) {
            $mainProduct->setAttributes($attributes);
        }
        if (!is_null($product->getCategories())) {
            $mainProduct->setCategories($product->getCategories());
        }
        if (!is_null($product->getFeatures())) {
            $mainProduct->setFeatures($product->getFeatures());
        }

        return $mainProduct;
    }

    /**
     * checks if product is attribute of any other earlier created products based on
     * product series lupus picture code and fitting standard
     * when series and lupus picture code are the same and fitting standard id different checking product is a variant
     * @var Product[] $products
     * @var Product $product
     */
    public static function checkIfIsAttribute($mainProducts,$productToCheck)
    {
        foreach ($mainProducts as $product) {
            if (($product->getSeries() === $productToCheck->getSeries()) && ($product->getLupusPictureCode() === $productToCheck->getLupusPictureCode())) {
                if (empty($product->getAttributes()) && ($productToCheck->getFittingStandard() !== $product->getFittingStandard())) {
                    $product->addAttribute($productToCheck);

                    return true;
                } else {
                    $isAdded = false;
                    foreach ($product->getAttributes() as $attribute) {
                        if ($productToCheck->getFittingStandard() === $attribute->getFittingStandard()) {
                            $isAdded = true;
                        }
                    }
                    if (!$isAdded) {
                        $product->addAttribute($productToCheck);

                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param $productId
     * @param  Category  $category
     * @return array
     */
    public static function getMlCategoryProductData($productId, Category $category)
    {
        return [
            'id_category' => $category->getId(),
            'id_product'  => $productId,
            'position'    => $category->getPosition(),
        ];
    }
}
