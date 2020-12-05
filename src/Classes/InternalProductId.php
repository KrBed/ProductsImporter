<?php

namespace ProductsImporter\Classes;

class InternalProductId
{
  private $globalId;
  private $productId;
  private $attributeId;
  
  public function __construct($globalId, $productId, $attributeId)
  {
    $this->$globalId = $globalId;
    $this->productId = $productId;
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
  public function getProductId()
  {
    return $this->productId;
  }
  
  /**
   * @param mixed $productId
   */
  public function setProductId($productId)
  {
    $this->productId = $productId;
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
  
}