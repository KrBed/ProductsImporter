<?php

namespace ProductsImporter\Services;

use PrestaShop\PrestaShop\Adapter\Entity\Configuration;
use PrestaShop\PrestaShop\Adapter\Entity\Hook;
use PrestaShop\PrestaShop\Adapter\Entity\Image;
use PrestaShop\PrestaShop\Adapter\Entity\ImageManager;
use PrestaShop\PrestaShop\Adapter\Entity\ImageType;
use PrestaShop\PrestaShop\Adapter\Entity\Tools;
use ProductsImporter\Classes\Product;
use ProductsImporter\Classes\ProductImage;

class ImageService extends MediaService {

    const SUPPORTED_IMAGES = [
        'gif',
        'jpg',
        'jpeg',
        'png',
    ];

    public static $images = [];

    /**
     * @param  Product  $product
     * @return array
     */
    public static function FilterImages($product)
    {
        $images = [];
        foreach ($product->getImages() as $image) {
            $contains = array_filter($images, function ($obj) use ($image) {
                return ($obj->getImageName() === $image->getImageName());
            });
            if (!$contains) {
                $image->addAttributeId($product->getAttributeId());
                $images[] = $image;
            }
        }
        foreach ($product->getAttributes() as $attribute) {
            foreach ($attribute->getImages() as $image) {
                $contains = array_filter($images, function ($obj) use ($image, $attribute) {
                    if ($obj->getImageName() === $image->getImageName()) {
                        $obj->addAttributeId($attribute->getAttributeId());
                        return true;
                    }
                    return false;
                });
                if (!$contains) {
                    $image->addAttributeId($product->getAttributeId());
                    $images[] = $image;
                }
            }
        }

        return $images;
    }

    public static function copyImg($productId, $imageId, $url, $entity = 'products', $regenerate = true)
    {
        $tmpfile = tempnam(_PS_TMP_IMG_DIR_, 'ps_import');
        $watermark_types = explode(',', Configuration::get('WATERMARK_TYPES'));
        $image = new Image();
        $image->id = $imageId;

        $path = $image->getPathForCreation();

        $url = str_replace(' ', '%20', trim($url));

        // Evaluate the memory required to resize the image: if it's too much, you can't resize it.
        if (!ImageManager::checkImageMemoryLimit($url)) {
            return false;
        }
        // 'file_exists' doesn't work on distant file, and getimagesize makes the import slower.
        // Just hide the warning, the processing will be the same.
        if (Tools::copy($url, $tmpfile)) {
            ImageManager::resize($tmpfile, $path.'.jpg');
            $images_types = ImageType::getImagesTypes($entity);

            if ($regenerate) {
                foreach ($images_types as $image_type) {
                    ImageManager::resize($tmpfile, $path.'-'.stripslashes($image_type['name']).'.jpg', $image_type['width'], $image_type['height']);
                    if (in_array($image_type['id_image_type'], $watermark_types)) {
                        Hook::exec('actionWatermark', [
                            'id_image'   => $imageId,
                            'id_product' => $productId,
                        ]);
                    }
                }
            }
        } else {
            unlink($tmpfile);

            return false;
        }
        unlink($tmpfile);

        return true;
    }

    /**
     * gets imade data from worksheet
     * @param $row
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function getImageData($row, $cellsArray)
    {
        $images = [];
        $globalId = self::returnWorksheetValueIfCorrect(self::GLOBAL_ID, $row);
        $urls = self::getUrls($row, $cellsArray);
        if (!empty($urls)) {
            foreach ($urls as $url) {
                $url = str_replace('\\', '/', $url);
                $image = new ProductImage($globalId);
                $image->setImageName(self::extractImgNameFromUrl($url));
                $image->setPath(self::extractPathFromUrl($url));
                $image->setScheme(self::extractSchemeFromUrl($url));
                $image->setFullPath($url);
                $images[] = $image;
            }
        }

        return $images;
    }

    /**
     * @inheritDoc
     */
    protected static function extractPathFromUrl($url)
    {
        $url = parse_url($url);
        $pathChunks = explode("/", $url['path']);
        $lastChunk = end($pathChunks);
        $extension = strtolower(pathinfo($lastChunk, PATHINFO_EXTENSION)); // Using strtolower to overcome case sensitive
        if (in_array($extension, self::SUPPORTED_IMAGES, true)) {
            array_pop($pathChunks);

        } else {
            //return error of image
            return false;
        }

        return implode("/", $pathChunks);
    }
}
