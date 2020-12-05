<?php

namespace ProductsImporter\Services;



use ProductsImporter\Classes\Attachment;

class AttachmentService extends SpreadsheetService
{

    /**
     * gets product attachments from worksheet
     * @param $row
     * @param $cellsArray
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public static function getAttachments($row,$cellsArray)
    {
        $attachments = [];
        $globalId = self::returnWorksheetValueIfCorrect(self::GLOBAL_ID, $row);
        $urls = self::getUrls($row,$cellsArray);
        if (!empty($urls)) {
            foreach ($urls as $url) {
                $attachment = new Attachment($globalId);
                $attachment->setScheme(self::extractSchemeFromUrl($url));
                $attachment->setName(self::extractImgNameFromUrl($url));
                $attachment->setPath(self::extractPathFromUrl($url));
                $attachment->setHost(self::extractHostFromUrl($url));
                $attachments[] = $attachment;
            }
        }

        return $attachments;

    }
}
