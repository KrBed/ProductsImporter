<?php

namespace ProductsImporter\Classes;

use ProductsImporter\Utils\AppHelper;

class Product
{

  private $categories = [];
  private $attributes = [];
  private $features = [];
  private $images = [];
  private $attachments = [];

  private $id;
  private $attributeId;
  private $globalId;
  private $defaultCategoryId;
  private $indexLupus;
  private $lupusPictureCode;
  private $fittingStandard;
  private $productName;
  private $linkRewrite;
  private $longDiscription;
  private $upc;
  private $EAN;
  private $dimensionAfterFoldX;
  private $dimensionAfterFoldY;
  private $dimensionAfterFoldZ;
  private $nettoPriceEXWPLN;
  private $cartonDimensionX;
  private $cartonDimensionY;
  private $cartonDimensionZ;
  private $weight;
  private $inOnlineShop;
  private $quantity;

  /**
   * @return mixed
   */
  public function getQuantity()
  {
    return $this->quantity;
  }

  /**
   * @param mixed $quantity
   */
  public function setQuantity($quantity)
  {
    $this->quantity = $quantity;
  }

  /**
   * @return mixed
   */
  public function getAttributeId()
  {
    return $this->attributeId;
  }

  /**
   * @param mixed $attributeId
   */
  public function setAttributeId($attributeId)
  {
    $this->attributeId = $attributeId;
  }

  /**
   * @return mixed
   */
  public function getGlobalId()
  {
    return $this->globalId;
  }

  /**
   * @param mixed $globalId
   */
  public function setGlobalId($globalId)
  {
    $this->globalId = $globalId;
  }
    /**
     * @return mixed
     */
    public function getDefaultCategoryId()
    {
        return $this->defaultCategoryId;
    }

    /**
     * @param  mixed  $defaultCategoryId
     */
    public function setDefaultCategoryId($defaultCategoryId)
    {
        $this->defaultCategoryId = $defaultCategoryId;
    }

  /**
   * @return mixed
   */
  public function getUpc()
  {
    return $this->upc;
  }

  /**
   * @param mixed $upc
   */
  public function setUpc($upc)
  {
    $cutUpc = substr($upc, 0, 2);
    $this->upc = $cutUpc;
  }

  /**
   * @return mixed
   */
  public function getFittingStandard()
  {
    return $this->fittingStandard;
  }

  /**
   * @param mixed $fittingStandard
   */
  public function setFittingStandard($fittingStandard)
  {
    $this->fittingStandard = $fittingStandard;
  }

  /**
   * @return mixed
   */
  public function getLupusPictureCode()
  {
    return $this->lupusPictureCode;
  }

  /**
   * @param mixed $lupusPictureCode
   */
  public function setLupusPictureCode($lupusPictureCode)
  {
    $this->lupusPictureCode = $lupusPictureCode;
  }

  private $series;

  /**
   * @return mixed
   */
  public function getSeries()
  {
    return $this->series;
  }

  /**
   * @param mixed $series
   */
  public function setSeries($series)
  {
    $this->series = $series;
  }

  /**
   * @return string
   */
  public function getIndexLupus()
  {
    return $this->indexLupus;
  }

  /**
   * @param string $indexLupus
   */
  public function setIndexLupus($indexLupus)
  {
    $this->indexLupus = $indexLupus;
  }

  /**
   * @return array
   */
  public function getAttributes()
  {
    return $this->attributes;
  }

  /**
   * @param array $attributes
   */
  public function setAttributes(array $attributes)
  {
    $this->attributes = $attributes;
  }

  /**
   * @return array
   */
  public function getFeatures()
  {
    return $this->features;
  }

  /**
   * @param array $feature
   */
  public function setFeatures(array $feature)
  {
    $this->features = $feature;
  }

  /**
   * @return int
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * @param int $id
   */
  public function setId($id)
  {
    $this->id = $id;
  }

  /**
   * @return string
   */
  public function getProductName()
  {
    return $this->productName;
  }

  /**
   * @param string $productName
   */
  public function setProductName($productName)
  {
    $this->productName = $productName;
  }

  /**
   * @return string $linkRewrite
   */
  public function getLinkRewrite()
  {
    return $this->linkRewrite;
  }

  /**
   * @param string $productName
   */
  public function setLinkRewrite($productName)
  {
    $linkRewrite = AppHelper::slug($productName, '-');
    $this->linkRewrite = $linkRewrite;
  }

  /**
   * @return string
   */
  public function getLongDiscription()
  {
    return $this->longDiscription;
  }

  /**
   * @param string $londDiscription
   */
  public function setLongDiscription($londDiscription)
  {
    $this->londDiscription = $londDiscription;
  }

  /**
   * @return string
   */
  public function getEAN()
  {
    return $this->EAN;
  }

  /**
   * @param string $EAN
   */
  public function setEAN($EAN)
  {
    $this->EAN = $EAN;
  }

  /**
   * @return int
   */
  public function getDimensionAfterFoldX()
  {
    return $this->dimensionAfterFoldX;
  }

  /**
   * @param int $dimensionAfterFoldX
   */
  public function setDimensionAfterFoldX($dimensionAfterFoldX)
  {
    $this->dimensionAfterFoldX = $dimensionAfterFoldX;
  }

  /**
   * @return int
   */
  public function getDimensionAfterFoldY()
  {
    return $this->dimensionAfterFoldY;
  }

  /**
   * @param int $dimensionAfterFoldY
   */
  public function setDimensionAfterFoldY($dimensionAfterFoldY)
  {
    $this->dimensionAfterFoldY = $dimensionAfterFoldY;
  }

  /**
   * @return int
   */
  public function getDimensionAfterFoldZ()
  {
    return $this->dimensionAfterFoldZ;
  }

  /**
   * @param int $dimensionAfterFoldZ
   */
  public function setDimensionAfterFoldZ($dimensionAfterFoldZ)
  {
    $this->dimensionAfterFoldZ = $dimensionAfterFoldZ;
  }

  /**
   * @return mixed
   */
  public function getNettoPriceEXWPLN()
  {
    return $this->nettoPriceEXWPLN;
  }

  /**
   * @param mixed $nettoPriceEXWPLN
   */
  public function setNettoPriceEXWPLN($nettoPriceEXWPLN)
  {
    $this->nettoPriceEXWPLN = $nettoPriceEXWPLN;
  }

  /**
   * @return int
   */
  public function getCartonDimensionX()
  {
    return $this->cartonDimensionX;
  }

  /**
   * @param int $cartonDimensionX
   */
  public function setCartonDimensionX($cartonDimensionX)
  {
    $this->cartonDimensionX = $cartonDimensionX;
  }

  /**
   * @return int
   */
  public function getCartonDimensionY()
  {
    return $this->cartonDimensionY;
  }

  /**
   * @param int $cartonDimensionY
   */
  public function setCartonDimensionY($cartonDimensionY)
  {
    $this->cartonDimensionY = $cartonDimensionY;
  }

  /**
   * @return int
   */
  public function getCartonDimensionZ()
  {
    return $this->cartonDimensionZ;
  }

  /**
   * @param int $cartonDimensionZ
   */
  public function setCartonDimensionZ($cartonDimensionZ)
  {
    $this->cartonDimensionZ = $cartonDimensionZ;
  }

  /**
   * @return float
   */
  public function getWeight()
  {
    return $this->weight;
  }

  /**
   * @param float $weight
   */
  public function setWeight($weight)
  {
    $this->weight = $weight;
  }

  /**
   * @return int
   */
  public function getInOnlineShop()
  {
    return $this->inOnlineShop;
  }

  /**
   * @param int $inOnlineShop
   */
  public function setInOnlineShop($inOnlineShop)
  {
    $this->inOnlineShop = $inOnlineShop;
  }

  /**
   * @return array
   */
  public function getCategories()
  {
    return $this->categories;
  }

  /**
   * @param array $categories
   */
  public function setCategories($categories)
  {
    $this->categories = $categories;
  }

  public function addAttribute($attribute)
  {
    $this->attributes[] = $attribute;

  }

  /**
   * @return array
   */
  public function getImages()
  {
    return $this->images;
  }

  /**
   * @param array $images
   */
  public function setImages($images)
  {
    $this->images = $images;
  }

  /**
   * @return array
   */
  public function getAttachments()
  {
    return $this->attachments;
  }

  /**
   * @param array $attachments
   */
  public function setAttachments($attachments)
  {
    $this->attachments = $attachments;
  }

  /**
   * returns data for ml_product_lang table in Db
   * @param $productId
   * @return array
   */
  public function getProductLangData($productId)
  {
    return  [
      'id_product'         => $productId,
      'id_shop'            => $_ENV['SHOP_ID'],
      'id_lang'            => $_ENV['LANG_ID'],
      'description'        => $this->getLongDiscription(),
      'description_short'  => $this->getIndexLupus(),
      'link_rewrite'       => $this->getLinkRewrite(),
      'name'               => $this->getProductName(),
      'delivery_out_stock' => $_ENV['DELIVERY_OUT_STOCK'],
    ];
  }


}
