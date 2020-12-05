<?php

namespace ProductsImporter\Classes;

use ProductsImporter\Utils\AppHelper;

class ProductImage
{
  private $id;
  private $globalId;
  private $productId;
  private $attributeIds =[];


  private $fullPath;
  private $imageName;
  private $position;
  private $cover;
  private $scheme;
  private $path;

  /**
   * @return mixed
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * @param mixed $id
   */
  public function setId($id): void
  {
    $this->id = $id;
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
  public function setGlobalId($globalId): void
  {
    $this->globalId = $globalId;
  }

  /**
   * @return mixed
   */
  public function getProductId()
  {
    return $this->productId;
  }

  /**
   * @param mixed $productId
   */
  public function setProductId($productId): void
  {
    $this->productId = $productId;
  }

  /**
   * @return mixed
   */
  public function getImageName()
  {
    return $this->imageName;
  }

  /**
   * @param mixed $imageName
   */
  public function setImageName($imageName): void
  {
    $this->imageName = $imageName;
  }

  /**
   * @return mixed
   */
  public function getPosition()
  {
    return $this->position;
  }

  /**
   * @param mixed $position
   */
  public function setPosition($position): void
  {
    $this->position = $position;
  }
    /**
     * @return array
     */
    public function getAttributeIds()
    {
        return $this->attributeIds;
    }

    /**
     * @param  array  $attributeIds
     */
    public function setAttributeIds($attributeIds)
    {
        $this->attributeIds = $attributeIds;
    }

    public function addAttributeId($attributeId){
        $this->attributeIds[] = $attributeId;
    }

  /**
   * @return mixed
   */
  public function getCover()
  {
    return $this->cover;
  }

  /**
   * @param mixed $cover
   */
  public function setCover($cover): void
  {
    $this->cover = $cover;
  }

  /**
   * @return mixed
   */
  public function getScheme()
  {
    return $this->scheme;
  }

  /**
   * @param mixed $scheme
   */
  public function setScheme($scheme): void
  {
    $this->scheme = $scheme;
  }

  /**
   * @return mixed
   */
  public function getPath()
  {
    return $this->path;
  }

  /**
   * @param mixed $path
   */
  public function setPath($path): void
  {
    $this->path = $path;
  }

  public function __construct($globalId, $productId = null)
  {
    $this->globalId = $globalId;
  }
  /**
   * @return mixed
   */
  public function getFullPath()
  {
    return $this->fullPath;
  }

  /**
   * @param mixed $fullPath
   */
  public function setFullPath($fullPath): void
  {
    $this->fullPath = $fullPath;
  }



  public function makeLegend($imgName)
  {
    $pathFragments = explode('.', $imgName);
    $end = end($pathFragments);
    if (($key = array_search($end, $pathFragments)) !== false) {
      unset($pathFragments[$key]);
    }
    $name = implode('', $pathFragments);
    return AppHelper::slug($name, ' ');
  }
}
