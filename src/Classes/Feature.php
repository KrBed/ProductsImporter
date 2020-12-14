<?php

namespace ProductsImporter\Classes;

class Feature
{

  private $childs = [];

  private $id;
  private $name;
  private $position;
  private $parentId;

    /**
     * @return mixed
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param  mixed  $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }


  public function __construct($name)
  {
    $this->name = $name;
  }

  /**
   * @return mixed
   */
  public function getParentId()
  {
    return $this->parentId;
  }

  /**
   * @param int $parentId
   */
  public function setParentId($parentId)
  {
    $this->parentId = $parentId;
  }

  /**
   * @return array
   */
  public function getChilds()
  {
    return $this->childs;
  }

  /**
   * @param array $childs
   */
  public function setChilds($childs)
  {
    $this->childs = $childs;
  }

  /**
   * @param $feature
   */
  public function addChild($feature)
  {
    $this->childs[] = $feature;
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
  public function getName()
  {
    return $this->name;
  }

  /**
   * @param string $name
   */
  public function setName($name)
  {
    $this->name = $name;
  }
}

