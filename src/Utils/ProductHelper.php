<?php

namespace ProductsImporter\Utils;

use ProductsImporter\Classes\Category;
use ProductsImporter\Classes\Product;

class ProductHelper
{
  /**
   * Sets witch product should be main by lowest price filtering
   * @param Product $product
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
  public static function checkIfIsAttribute($mainProducts, $productToCheck)
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
   * @param Category $category
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

  /**
   * @param Product $product
   * @return array
   */
  public static function getProductLangData($product)
  {
    return [
      'id_product'         => $product->getId(),
      'id_shop'            => $_ENV['SHOP_ID'],
      'id_lang'            => $_ENV['LANG_ID'],
      'description'        => $product->getLongDiscription(),
      'description_short'  => $product->getIndexLupus(),
      'link_rewrite'       => $product->getLinkRewrite(),
      'name'               => $product->getProductName(),
      'delivery_out_stock' => $_ENV['DELIVERY_OUT_STOCK'],
    ];
  }

  public static function getProductData(Product $product)
  {
    return [
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

  }

  public static function getProductShopData(Product $product)
  {
    return [
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
  }

  public static function getMainQuantityStockAvailableData(int $idStock, int $productId, $sumOfQuantity)
  {
    return ['id_stock_available'   => $idStock,
            'id_product'           => $productId,
            'id_product_attribute' => 0,
            'id_shop'              => $_ENV['SHOP_ID'],
            'id_shop_group'        => 0,
            'quantity'             => $sumOfQuantity,
            'out_of_stock'         => $_ENV['OUT_OF_STOCK']
    ];

  }

  public static function getSingleQuantityStockAvailableData(int $idStock, Product $product)
  {
    return ['id_stock_available'   => $idStock,
            'id_product'           => $product->getId(),
            'id_product_attribute' => $product->getAttributeId(),
            'id_shop'              => $_ENV['SHOP_ID'],
            'id_shop_group'        => 0,
            'quantity'             => $product->getQuantity() > 0 ? $product->getQuantity() : $_ENV['QUANTITY'],
            'out_of_stock'         => $_ENV['OUT_OF_STOCK']
    ];
  }
}
