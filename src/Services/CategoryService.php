<?php

namespace ProductsImporter\Services;

use PrestaShop\PrestaShop\Adapter\Entity\Category as PrestaShop;
use ProductsImporter\Classes\Product;
use ProductsImporter\Utils\CategoryHelper;

class CategoryService extends SpreadsheetService
{
  
  
  /**
   * @return array
   * returns categories  array created from spreadsheet
   */
  public static function getCategories()
  {
    return parent::$categories;
  }
  
  /**
   * ake nique values of categories from spreadsheet and assign to categories array
   * @return array
   * @throws \PhpOffice\PhpSpreadsheet\Exception
   */
  public static function getCategoriesFromWorkSheet()
  {
    $categoryPaths = CategoryHelper::createCategoryPaths(self::CATEGORIES_COLUMNS);
    return CategoryHelper::createCategoryArrayFromStringPaths($categoryPaths);
    
  }
  
  /**
   * @param $categoriesColumns
   * @param int $row
   * @return array  $categories
   * @throws \PhpOffice\PhpSpreadsheet\Exception
   */
  public static function getSingleRowCategories($categoriesColumns, $row)
  {
    $categories = [];
    $categoryNames = self::returnCategoryNamesIfCorrect($categoriesColumns, $row);
    if ($categoryNames) {
      foreach ($categoryNames as $name) {
        $categories [] = $name;
      }
      return $categories;
    }
    return [];
  }
  
  /**
   * return Category names if path is correct
   * @param $columns
   * @param $row
   * @param bool $logMessages
   * @return array|bool
   * @throws \PhpOffice\PhpSpreadsheet\Exception
   */
  public static function returnCategoryNamesIfCorrect($columns, $row)
  {
    $categories = [];
    foreach ($columns as $column) {
      $categories[] = self::$worksheet->getCell($column . $row)->getValue();
    }
    if (!self::checkIfValueCorrect($categories[0])) {
      LoggerService::getLogger()->info("Main category can't be empty in {$columns[0]}{$row}");
      return false;
    }
    if (!self::checkIfValueCorrect($categories[1]) && self::checkIfValueCorrect($categories[2])) {
      LoggerService::getLogger()->info("Wrong values for Category in  {$columns[0]}{$row} column");
      return false;
    }
    if (!self::checkIfValueCorrect($categories[2]) && self::checkIfValueCorrect($categories[3])) {
      LoggerService::getLogger()->info("Wrong values for Category in  {$columns[0]}{$row} column");
      return false;
    }
    if ((!self::checkIfValueCorrect($categories[1]) && !self::checkIfValueCorrect($categories[2])) && self::checkIfValueCorrect($categories[3])) {
      LoggerService::getLogger()->info("Wrong values for Category in  {$columns[0]}{$row} column");
      return false;
    }
    return $categories;
  }
  
  /**
   * @param Product $product
   */
  public static function rebuildNtree()
  {
    PrestaShop::regenerateEntireNtree();
  }
  
}
