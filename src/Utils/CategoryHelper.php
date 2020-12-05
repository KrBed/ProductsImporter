<?php

namespace ProductsImporter\Utils;

use ProductsImporter\Classes\Category;
use ProductsImporter\Services\SpreadsheetService;

class CategoryHelper {

    public static function CreateCategoryObjects(array $categoryPaths, $categoryCollection = [], $deep = 0)
    {
        foreach ($categoryPaths as $path) {
            if (empty($path)) {
                continue;
            }
            // check if category exists
            if (!isset($categoryCollection[$path[0]])) {
                $categoryCollection[$path[0]] = new Category($path[0]);
            }

            // prepre childs for recursion, first element is always handled node
            // cut first node (we already commited him)
            $slicedArray = [array_slice($path, 1)];
            $categoryCollection[$path[0]]->appendChilds(self::CreateCategoryObjects($slicedArray, $categoryCollection[$path[0]]->getChilds(), $deep + 1));
        }

        return $categoryCollection;
    }

    /**
     * @param  Category  $category
     * @return array
     */
    public static function getCategoryShopData($category)
    {
        return [
            'id_category' => $category->getId(),
            'id_shop'     => $_ENV['SHOP_ID'],
            'position'    => $category->getPosition(),
        ];
    }

    /**
     * @return array
     */
    public static function setNewCategoryDisplayOptions($parentId, $levelDepth)
    {
        return [
            'parentId'   => $parentId,
            'levelDepth' => $levelDepth,
        ];
    }

    /**
     * @param $category
     * @return array
     */
    public static function getMlCategoryLangData($category)
    {
        return [
            'id_category'  => $category->getId(),
            'id_lang'      => $_ENV['LANG_ID'],
            'name'         => $category->getName(),
            'link_rewrite' => $category->getLinkRewrite(),
        ];
    }

    /**
     * @param  Category  $category
     * @param $options
     * @return array
     * @throws Exception
     */
    public static function getMlCategoryData($category, $options)
    {
        return [
            'id_category'     => $category->getId(),
            'id_parent'       => $category->getParentId(),
            'id_shop_default' => $_ENV['SHOP_DEFAULT_ID'],
            'level_depth'     => $options['levelDepth'],
            'active'          => $_ENV['ACTIVE'],
            'date_add'        => AppHelper::getActualDate(),
            'date_upd'        => AppHelper::getActualDate(),
            'position'        => $category->getPosition(),
        ];
    }

    public static function createCategoryArrayFromStringPaths($stringPaths)
    {
        $categories = [];
        foreach ($stringPaths as $path) {
            $categoryPath = explode('.', $path);
            $categories[] = $categoryPath;
        }

        return $categories;
    }

    /**
     * assigns tp Product category objects
     * @param $insertedCategories
     * @param $productCategories
     * @return array
     */
    public static function filterProductCategories($insertedCategories, $productCategories)
    {
        $filteredCategories = [];
        foreach ($productCategories as $category) {
            $categoriesToCheck = $insertedCategories;
            foreach ($category as $name) {
                $category = CategoryHelper::checkIfObjectExists($categoriesToCheck, $name);
                if ($category) {
                    $filteredCategories[] = $category;
                    $categoriesToCheck = $category->getChilds();
                }
            }
        }

        return $filteredCategories;
    }

    /**
     * checks if objects exists in given Category array and returns that objects
     * @param  Category []  $objects
     * @param $name
     * @return false|mixed
     */
    public static function checkIfObjectExists($objects, $name)
    {
        foreach ($objects as $object) {
            if ($object->getName() == $name) {
                return $object;
            }
        }

        return false;
    }

    /**
     * removes empty paths from array categories
     * @param  array  $categories
     * @return array
     */
    public static function removeUnsetPaths($categories)
    {
        foreach ($categories as $path) {
            if (empty($path)) {
                $path = '';
            }
        }

        return array_filter($categories);
    }

    /**
     * creates string representation category paths
     * @param $categoriesColumns
     * @return array
     */
    public static function createCategoryPaths($categoriesColumns)
    {
        $rootPaths = [];
        $highestRow = SpreadsheetService::getWorksheet()->getHighestRow();
        foreach ($categoriesColumns as $columns) {
            for ($row = 2; $row <= $highestRow; ++$row) {
                $path = [];
                foreach ($columns as $column) {
                    $value = SpreadsheetService::returnWorksheetValueIfCorrect($column, $row);
                    if ($value) {
                        $path [] = $value;
                    } else {
                        break;
                    }
                }
                if (!empty($path)) {
                    $stringPath = implode('.', $path);
                    if (!in_array($stringPath, $rootPaths, true)) {
                        $rootPaths[] = $stringPath;
                    }
                }
            }
        }

        return $rootPaths;
    }

    /**
     * sets Product defaultCategoryId
     * @param  Category  $category
     * @return int
     */
    public static function getDefaultCategoryId($category)
    {
        if (!empty($category->getChilds())) {
            $lastKey = array_key_last($category->getChilds());
            $array = $category->getChilds();
            $lastNode = $array[$lastKey];

            return self::getDefaultCategoryId($lastNode);
        }

        return $category->getId();
    }

    /**
     * @param  Category  $category
     * @return mixed
     */
    public static function getCategory($category)
    {
        return $category;
    }

    /**
     * @param $childs
     */
    public function appendChilds($childs)
    {
        $this->childs = array_merge($this->childs, $childs);
    }


}
