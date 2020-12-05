<?php

namespace ProductsImporter\Classes;

class Attachment
{
  private $id;
  private $productId;
  private $globalId;
  private $scheme;
  private $host;
  private $path;
  private $name;
  
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
  public function getHost()
  {
    return $this->host;
  }
  
  /**
   * @param mixed $host
   */
  public function setHost($host): void
  {
    $this->host = $host;
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
  
  /**
   * @return mixed
   */
  public function getName()
  {
    return $this->name;
  }
  
  /**
   * @param mixed $name
   */
  public function setName($name): void
  {
    $this->name = $name;
  }
  
  
}
