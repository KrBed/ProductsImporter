<?php

namespace ProductsImporter\Services;

use ProductsImporter\Classes\ProductAttribute;

class AttributeService extends SpreadsheetService
{

  /**
   * returns attribute  array created from spreadsheet
   * @return ProductAttribute [] $attributes
   */
  public static function getAttributes()
  {
    return parent::$attributes;
  }

  /**
   * takes unique values of attributes from spreadsheet and assign to attributes array
   * @return array
   * @throws \PhpOffice\PhpSpreadsheet\Exception
   */
  public static function getAttributesFromWorksheet()
  {
    $attributes = self::getAttributeTypes();

    return self::GetAttributeNames($attributes);
  }

  /**
   * adds attribute names to attributes array witch
   * contains attributeType names as keys
   * @param array $attributes
   * @return array
   * @throws \PhpOffice\PhpSpreadsheet\Exception
   */
  private static function GetAttributeNames(array $attributes)
  {
    $highestRow = self::$worksheet->getHighestRow();
    foreach (self::ATTRIBURES_ARRAY as $cell) {
      $attributeType = self::getCellValue($cell, 1);
      for ($row = 2; $row <= $highestRow; ++$row) {
        $fitting = self::$worksheet->getCell($cell . $row)->getValue();
        if (self::checkIfValueCorrect($fitting)) {
          if ($fitting !== false && !in_array($fitting, $attributes[$attributeType])) {
            $attributes[$attributeType][] = $fitting;
          }
        }
      }
    }
    return $attributes;
  }

  /**
   * gets attribute type names from spreadsheet based on ATTRIBUTES_ARRAY
   * @param $attributeName
   * @return array
   * @throws \PhpOffice\PhpSpreadsheet\Exception
   */
  private static function getAttributeTypes()
  {
    $attributes = [];
    foreach (self::ATTRIBURES_ARRAY as $attributeCell) {
      $attributeType = self::$worksheet->getCell($attributeCell . '1')->getValue();
      if (!isset($attributeType, $attributes[$attributeType])) {
        $attributes[$attributeType] = [];
      }
    }
    return $attributes;
  }
}
