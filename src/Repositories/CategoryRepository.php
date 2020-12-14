<?php

namespace ProductsImporter\Repositories;

use Exception;
use ProductsImporter\Classes\Category;
use ProductsImporter\Utils\CategoryHelper;

class CategoryRepository extends RepositoryCore {

    /**
     * main function to add created categories to Db
     * @param  array  $categories
     * @return array
     * @throws Exception
     */
    public function addCategoriesIfNotExists(array $categories)
    {
        parent::$db->beginTransaction();
        try {
            $this->addCategories($categories);
        } catch (Exception $e) {
            parent::$db->rollback();
            throw $e;
        }
        parent::$db->commit();

        return $categories;
    }

    /**
     * Bases function to add or update category tree
     * @param  Category[]  $categories
     * @param  int[]  $options
     * @throws \Exception
     */
    public function addCategories(array $categories)
    {
        foreach ($categories as $category) {
            $options = CategoryHelper::setNewCategoryDisplayOptions((int)$_ENV['BASE_SHOP_CATEGORY'], (int)$_ENV['BASE_LEVEL_DEPTH']);
            $foundCategory = $this->checkIfCategoryExists($category, $options['parentId']);
            if ($foundCategory) {
                $category->createCategory($foundCategory);
                if (!empty($category->getChilds())) {
                    $options = CategoryHelper::setNewCategoryDisplayOptions($category->getId(), $options['levelDepth'] + 1);
                    $this->addOrUpdateCategory($category->getChilds(), $options);
                }
            } else {
                $options = $this->addCategory($category, $options);
                if (!empty($category->getChilds())) {
                    $this->addOrUpdateCategory($category->getChilds(), $options);
                }
            }
        }

        return $categories;
    }

    /**
     * adds single Category to Db
     * @param  \ProductsImporter\Classes\Category  $category
     * @param  array options
     * @return array
     * @throws \Exception
     */
    private function addCategory($category, $options)
    {
        //gets category id
        $id = $this->getMaxIdFromTable("{$_ENV['MYSQL_PREFIX']}category", 'id_category') + 1;
        //set category parent id
        $category->setParentId($options['parentId']);
        $category->setId($id);
        $category->setLinkRewrite($category->getName());
        if ($category->getParentId() === (int)$_ENV['BASE_SHOP_CATEGORY']) {
            $category->setPosition($this->getCategoryPositionForMainCategory());
        } else {
            $category->setPosition($this->getShopCategoryPosition($category->getParentId()));
        }

        $mlCategoryLangData = CategoryHelper::getMlCategoryLangData($category);
        $mlCategoryData = CategoryHelper::getMlCategoryData($category, $options);
        $mlCategoryShopData = CategoryHelper::getCategoryShopData($category);
        // think what to do with this groups
        $groups = [
            ['id_group' => 1],
            ['id_group' => 2],
            ['id_group' => 3],
        ];
        $this->insert("{$_ENV['MYSQL_PREFIX']}category_lang", $mlCategoryLangData);
        $this->insert("{$_ENV['MYSQL_PREFIX']}category", $mlCategoryData);
        $this->insert("{$_ENV['MYSQL_PREFIX']}category_shop", $mlCategoryShopData);
        $this->addCategoryGroups($id, $groups);

        return CategoryHelper::setNewCategoryDisplayOptions($category->getId(), $options['levelDepth'] + 1);

    }

    /**
     * adds or updates Categories based on reccurence
     * @param  Category[]  $categories
     * @param  array  $options
     * @throws \Exception
     */
    private function addOrUpdateCategory($categories, array $options)
    {
        foreach ($categories as $category) {
            $foundCategory = $this->checkIfCategoryExists($category, $options['parentId']);

            if ($foundCategory) {
                $category->createCategory($foundCategory);
                if (!empty($category->getChilds())) {
                    $newOptions = CategoryHelper::setNewCategoryDisplayOptions($foundCategory['id_category'], $options["levelDepth"] + 1);
                    $this->addOrUpdateCategory($category->getChilds(), $newOptions);
                }
            } else {
                $newOptions = $this->addCategory($category, $options);
                if (!empty($category->getChilds())) {
                    $this->addOrUpdateCategory($category->getChilds(), $newOptions);
                }
            }
        }

        return $options;
    }

    /**
     * checks if category exists in Dd
     * @param $category
     * @param  int  $parentId
     * @return mixed
     */
    private function checkIfCategoryExists($category, $parentId)
    {
        $categories = $this->searchCategoriesByNameAndLang($category);

        foreach ($categories as $foundCategory) {
            $result = $this->getCategoryData($foundCategory);
            if ($result['id_parent'] === $parentId) {
                return $result;
            }
        }

        return false;
    }

    /**
     * @param  Category  $category
     * @return array
     */
    private function searchCategoriesByNameAndLang($category)
    {
        $data = [
            'name'    => $category->getName(),
            'id_lang' => $_ENV['LANG_ID'],
        ];
        $statement = self::$db->prepare("SELECT * FROM {$_ENV['MYSQL_PREFIX']}category_lang WHERE name=:name AND id_lang=:id_lang");
        $statement->execute($data);

        return $statement->fetchAll();
    }

    /**
     * takes Category parentId from Db
     * @param  array  $category
     * @return array $data/bool
     */
    private function getCategoryData($category)
    {
        $statement = self::$db->prepare("SELECT id_category,id_parent,position FROM {$_ENV['MYSQL_PREFIX']}category WHERE id_category=:id_category");
        $statement->execute(['id_category' => $category['id_category']]);
        $result = $statement->fetch();
        if (!empty($result)) {
            return $result;
        }

        return false;
    }

    /**
     * takes Category position from Db
     * @param $categoryId
     * @return int $position
     */
    private function getCategoryPositionForMainCategory()
    {
        //check for main categories with position 0 and check it's position
        // in ml_category_shop table to set position in ml_category table
        $positionInMlCategoryShopTable = $this->getMaxMainCategoryPositionInMlCategoryShopTable();
        $positionInMlCategoryTable = $this->getMaxPositionForNewMainCategoryInMlCategoryTable();
        if ($positionInMlCategoryShopTable >= $positionInMlCategoryTable) {
            return $positionInMlCategoryShopTable + 1;
        }

        return $positionInMlCategoryTable;
    }

    /**
     * //check for main categories with position 0 and check it's position
     * // in ml_category_shop table to set position in ml_category table
     * @return mixed
     */
    public function getMaxMainCategoryPositionInMlCategoryShopTable()
    {
        $MaxMainCategoryPositionOnMlCategoryShop = self::$db->query("SELECT MAX(c2.position) FROM {$_ENV['MYSQL_PREFIX']}category c1
        INNER JOIN  {$_ENV['MYSQL_PREFIX']}category_shop c2 USING(id_category)  WHERE c1.id_parent={$_ENV['BASE_SHOP_CATEGORY']} AND c1.position =0");
        $MaxMainCategoryPositionOnMlCategoryShop->execute();
        $MaxMainCategoryOnMlCategoryShopResult = $MaxMainCategoryPositionOnMlCategoryShop->fetch();

        return reset($MaxMainCategoryOnMlCategoryShopResult);
    }

    /**
     * @return int|mixed
     */
    private function getMaxPositionForNewMainCategoryInMlCategoryTable()
    {
        $statement = self::$db->query("SELECT MAX(position) FROM {$_ENV['MYSQL_PREFIX']}category WHERE id_parent={$_ENV['BASE_SHOP_CATEGORY']}");
        $statement->execute();
        $position = $statement->fetch();
        if (!$position) {
            return 0;
        }

        return reset($position) + 1;
    }


    /**
     * gets category position to insert into ml_category_shop table
     * @param $parentId
     * @return array|int
     */
    private function getShopCategoryPosition($parentId)
    {
        $data = ['id_parent' => $parentId];
        $statement = self::$db->prepare("SELECT COUNT(position) AS position FROM {$_ENV['MYSQL_PREFIX']}category  WHERE id_parent=:id_parent");
        $statement->execute($data);
        $result = $statement->fetch();
        if (!$result) {
            return 0;
        }

        return $result['position'];
    }

    /**
     * Adds Category groups to Db
     * @param  int  $id
     * @param  array  $groups
     */
    private function addCategoryGroups($id, array $groups)
    {
        foreach ($groups as $group) {
            $data = [
                'id_category' => $id,
                'id_group'    => $group['id_group'],
            ];
            $this->insert("{$_ENV['MYSQL_PREFIX']}category_group", $data);
        }
    }
}
