<?php

namespace ProductsImporter\Repositories;

use ProductsImporter\Classes\ProductImage;
use ProductsImporter\Utils\AppHelper;

class ImageRepository extends RepositoryCore
{

  /**
   * @param ProductImage $image
   */
  public function insertImages($images, $productId)
  {
    $position = 1;
    $cover = 1;
    foreach ($images as $image) {
      $this->addImage($image, $productId, $position, $cover);
      $position++;
      $cover = null;
      $this->insertAttributesImages($image);
    }
  }

  /**
   * @param ProductImage $image
   * @param $productId
   */
  public function addImage($image, $productId, $position, $cover)
  {
    $imageId = $this->getMaxIdFromTable('ml_image', 'id_image') + 1;
    $image->setId($imageId);

    $mlImageData = [
      'id_image'   => $image->getId(),
      'id_product' => $productId,
      'position'   => $position,
      'cover'      => $cover,
//			'projection' ???
    ];
    $mlImageLangData = [
      'id_image' => $imageId,
      'id_lang'  => $_ENV['LANG_ID'],
      'legend'   => $image->makeLegend($image->getImageName()) //make something with it
    ];
    $mlImageShopData = [
      'id_product' => $productId,
      'id_image'   => $imageId,
      'id_shop'    => $_ENV['SHOP_ID'],
      'cover'      => $cover
    ];
    $mlProductImageData = [
      'id_image'   => $imageId,
      'id_product' => $productId,
      'id_global'  => $image->getGlobalId(),
      'fullpath'   => $image->getFullPath(),
      'name'       => $image->getImageName(),
      'path'       => $image->getPath(),
      'scheme'     => $image->getScheme()
    ];

    $this->insert("{$_ENV['MYSQL_PREFIX']}image", $mlImageData);
    $this->insert("{$_ENV['MYSQL_PREFIX']}image_lang", $mlImageLangData);
    $this->insert("{$_ENV['MYSQL_PREFIX']}image_shop", $mlImageShopData);
    $this->insert("{$_ENV['MYSQL_PREFIX']}product_image", $mlProductImageData);
  }

    /**
     * @param ProductImage $image
     */
    private function insertAttributesImages($image)
    {
        foreach ($image->getAttributeIds() as $attributeId){
            $this->insert("{$_ENV['MYSQL_PREFIX']}product_attribute_image",['id_product_attribute'=>$attributeId,'id_image'=>$image->getId()]);
        }
    }
}
