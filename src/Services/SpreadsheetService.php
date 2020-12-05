<?php

namespace ProductsImporter\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SpreadsheetService {
    const GLOBAL_ID = "A";
    const INDEX_LUPUS = "B";
    const LUPUS_PICTURE_CODE = "C";
    const SERIES = "D";
    const PRODUCT_NAME = "E";
    const DESCRYPTION_LONG_PL = "F";
    const UPC = "H";
    const FITTING_STANDARD = "I";
    const EAN = "J";
    const DIMENSION_AFTER_FOLD_X = "K";
    const DIMENSION_AFTER_FOLD_Y = "L";
    const DIMENSION_AFTER_FOLD_Z = "M";
    const DETAL_NETTO_PRICE_EXW_PLN = "N";
    const CARTON_DIMENSION_X = "O";
    const CARTON_DIMENSION_Y = "P";
    const CARTON_DIMENSION_Z = "Q";
    const WEIGHT = "R";
    const MOVIE_URL = "AG";
    const IN_ONLINE_STORE = "AH";

    const STYLE = "AN";
    const STRUCTURE = "AR";
    const DIMENSION_KIND = "AS";
    const FRONT_PATTERN = "AT";
    const FRONT_PLY = "AU";
    const BODY_BOARD = "AV";


    const INSTRUCTION_ADDRESS_COLUMNS = [
        'AD',
        'AE',
        'AF',
    ];
    const VALUE_BREAK_CONDITIONS = [
        null,
        "BRAK",
        "BRAKBRAK",
    ];
    const CATEGORIES_COLUMNS = [
        [
            'AI',
            'AJ',
            'AK',
            'AL',
        ],
        [
            'AM',
            'AN',
            'AO',
            'AP',
        ],
        [
            'AQ',
            'AR',
            'AS',
            'AT',
        ],
    ];
    const ACCEPTED_FILE_EXTENSIONS = [
        "xlsx",
        "xls",
    ];
    const FEATURES_CELLS = [
        'AN',
        'AR',
        'AS',
        'AT',
        'AU',
        'AV',
    ];

    const IMAGE_ADDRESS_COLUMNS = [
        'T',
        'U',
        'V',
        'W',
        'X',
        'Y',
        'Z',
        'AA',
        'AB',
        'AC',
    ];
    const ATTRIBURES_ARRAY = ['I'];
    /**
     * @var Worksheet $worksheet
     */
    protected static $worksheet;
    /**
     * @var Spreadsheet $spreadsheet ;
     */
    protected static $spreadsheet;
    protected static $features = [];
    protected static $attributes = [];
    protected static $categories = [];
    protected static $products = [];


    /**
     * @return Worksheet
     */
    public static function getWorksheet()
    {
        return self::$worksheet;
    }

    /**
     * loads data from spreadsheet
     * @param  string  $filename
     * @return void
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public static function loadRequiredData($filename)
    {
        $reader = IOFactory::createReaderForFile($filename);

        self::$spreadsheet = $reader->load($filename);
        self::$worksheet = self::$spreadsheet->getActiveSheet();
        self::$attributes = AttributeService::getAttributesFromWorksheet();
        self::$features = FeatureService::getFeaturesFromWorksheet();
        self::$categories = CategoryService::getCategoriesFromWorkSheet();
    }

    /**
     * checks if value taken from spreadsheet is correct
     * base on VALUE_BREAK_CONDITION array
     * @param $value
     * @return bool
     */
    public static function checkIfValueCorrect($value)
    {
        foreach (self::VALUE_BREAK_CONDITIONS as $condition) {
            if ($value === $condition) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $column
     * @param $row
     * @return bool|mixed
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public static function returnWorksheetValueIfCorrect($column, $row)
    {

        $value = self::$worksheet->getCell($column.$row)->getValue();
        foreach (self::VALUE_BREAK_CONDITIONS as $condition) {
            if ($value === $condition) {
                return false;
            }
        }

        return $value;
    }

    /**
     * identifies if file has correct extension
     * @param  string  $filename
     * @return bool
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public static function identifyFileExtension($filename)
    {
        $inputFileType = strtolower(IOFactory::identify($filename));
        if (in_array($inputFileType, self::ACCEPTED_FILE_EXTENSIONS)) {
            return true;
        }

        return false;
    }

    public static function getCellValue($cell, $row)
    {
        return self::$worksheet->getCell($cell.$row)->getValue();
    }



    /**
     * returns value if correct
     * @param $value
     * @return false
     */
    public static function returnValueIfCorrect($value)
    {
        foreach (self::VALUE_BREAK_CONDITIONS as $condition) {
            if ($value === $condition) {
                return false;
            }
        }

        return $value;
    }


}



