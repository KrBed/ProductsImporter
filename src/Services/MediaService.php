<?php


namespace ProductsImporter\Services;


abstract class MediaService extends SpreadsheetService {

    /**
     * @param $url
     * @return mixed
     */
    public static function extractImgNameFromUrl($url)
    {
        $url = parse_url($url);
        $path = explode('/', $url['path']);
        $name = end($path);

        return $name;
    }

    /**
     * @param $url
     * @return mixed
     */
    public static function extractHostFromUrl($url)
    {
        $url = parse_url($url);

        return $url['host'];
    }

    /**
     * @param $url
     */
    public static function extractSchemeFromUrl($url)
    {
        $url = parse_url($url);

        return $url['scheme'];
    }

    /**
     * gets url addresses from worksheet based on added cells array
     * @param $row
     * @param $cellsArray  // array with cell adresses
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public static function getUrls($row, $cellsArray)
    {
        $urls = [];
        foreach ($cellsArray as $cell) {

            $url = self::getSingleUrlAddress($cell, $row);
            if ($url !== false && !in_array($url, $urls)) {
                $urls[] = $url;
            }
        }

        return $urls;
    }

    /**
     * @param $cell
     * @param $row
     * @return false
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public static function getSingleUrlAddress($cell, $row)
    {
        return self::returnValueIfCorrect(self::$worksheet->getCell($cell.$row)->getValue());
    }
}
