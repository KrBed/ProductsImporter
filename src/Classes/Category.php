<?php

namespace ProductsImporter\Classes;

use ProductsImporter\Utils\AppHelper;

class Category {
    private $id;
    private $name;
    private $linkRewrite;
    private $position;
    private $parentId;
    private $childs = [];

    public function __construct($name = null)
    {
        $this->name = $name;
        if (!$this->isNullOrEmptyString('name')) {
            $this->setLinkRewrite($this->name);
        }
    }

    public function isNullOrEmptyString($str)
    {
        return (!isset($str) || trim($str) === '');
    }

    /**
     * @param  array  $categoryPaths
     * @param  array  $categoryCollection
     * @param  int  $deep
     * @return array;
     */

    /**
     * @return mixed
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * @param  mixed  $parentId
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;
    }

    /**
     * @param $childs
     */
    public function appendChilds($childs)
    {
        $this->childs = array_merge($this->childs, $childs);
    }


    /**
     * @param  array  $categoryData
     * @param $parentId
     * @param $categoryId
     * @return Category
     */
    public function createCategory($foundCategory)
    {
        $this->setId($foundCategory['id_category']);
        $this->setParentId($foundCategory['id_parent']);
        $this->setposition($foundCategory['position']);

        return $this;
    }

    /**
     * @return array
     */
    public function getChilds()
    {
        $a = $this->childs;

        return $a;
    }

    /**
     * @param  Category[]  $childs
     * @return Category
     */
    public function setChilds($childs)
    {
        $this->childs = $childs;

        return $this;
    }


    /**
     * @param  Category  $child
     * @return Category
     */
    public function addChild($child)
    {

        $this->childs[$child->getName()] = $child;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param  string  $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param  int  $position
     */
    public function setPosition($position)
    : void
    {
        $this->position = $position;
    }

    /**
     * @return int $Id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param  int  $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getLinkRewrite()
    {
        return $this->linkRewrite;
    }

    /**
     * @param  mixed  $categoryName
     */
    public function setLinkRewrite($categoryName)
    {
        $linkRewrite = AppHelper::slug($categoryName, '-');
        $this->linkRewrite = $linkRewrite;
    }
}
