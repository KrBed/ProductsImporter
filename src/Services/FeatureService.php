<?php


namespace ProductsImporter\Services;


use ProductsImporter\Classes\Feature;

class FeatureService extends SpreadsheetService {

    /**
     * @return Feature[] $features
     * returns attribute  array created from spreadsheet
     */
    public static function getFeatures()
    {
        return parent::$features;
    }

    /**
     * take unique values of features from spreadsheet and assign to attributes array
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public static function getFeaturesFromWorksheet()
    {
        $featuresTypes = self::getFeatureTypes();
        return self::getFeaturesValues($featuresTypes);
    }

    /**
     * gets feature types from spreadsheet based on FEATURE_CELLS static array
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public static function getFeatureTypes()
    {
        $featureTypes = [];
        foreach (self::FEATURES_CELLS as $cell) {
            $featureType = self::$worksheet->getCell($cell . '1')->getValue();
            if (!isset($featureType, $attributes[$featureType])) {
                $featureTypes[$featureType] = [];
            }
        }
        return $featureTypes;
    }

    /**
     * takes feature values from spreadsheet
     * @param array $features
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public static function getFeaturesValues($features)
    {
        $highestRow = self::$worksheet->getHighestRow();

        foreach (self::FEATURES_CELLS as $cell) {
            $featureType = self::getCellValue($cell, 1);
            for ($row = 2; $row <= $highestRow; ++$row) {
                $value = self::returnValueIfCorrect(self::$worksheet->getCell($cell . $row)->getValue());
                if ($value !== false && !in_array($value, $features[$featureType])) {
                    $features[$featureType][] = $value;
                }
            }
        }
        return $features;
    }

    /**
     * takes product features
     * @param $row
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public static function getProductFeatures($row)
    {
        $features = [];
        foreach (self::FEATURES_CELLS as $cell) {
            $featureType = self::getCellValue($cell, 1);
            $features[$featureType] = [];
            $value = self::returnWorksheetValueIfCorrect($cell, $row);
            if ($value !== false && (!in_array($value, $features[$featureType]))) {
                $features[$featureType] = $value;
            }
        }
        return $features;
    }

}
