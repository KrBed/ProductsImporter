<?php

namespace ProductsImporter\Classes;

class ProductAttribute
{
  private $id;
  private $name;
  private $position;

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
  private $childs = [];

  /**
   * @return array
   */
  public function getChilds(): array
  {
    return $this->childs;
  }

  /**
   * @param array $childs
   */
  public function setChilds(array $childs): void
  {
    $this->childs = $childs;
  }

  /**
   * @param ProductAttribute
   */
  public function addChild($child)
  {
    $this->childs[] = $child;
  }

  /**
   * @return int
   */
  public function getId(): int
  {
    return $this->id;
  }

  /**
   * @param int $id
   */
  public function setId(int $id)
  {
    $this->id = $id;
  }

  /**
   * @return string
   */
  public function getName(): string
  {
    return $this->name;
  }

  /**
   * @param string $name
   */
  public function setName(string $name)
  {
    $this->name = $name;
  }

}
