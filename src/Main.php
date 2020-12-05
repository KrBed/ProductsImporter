<?php

namespace ProductsImporter;

use Exception;
use PrestaShop\PrestaShop\Adapter\Entity\Image;
use PrestaShop\PrestaShop\Adapter\Entity\Tools;

use ProductsImporter\Db\DatabaseConnection;
use ProductsImporter\Db\QueryBuilder;
use ProductsImporter\Repositories\AttributesRepository;
use ProductsImporter\Repositories\CategoryRepository;
use ProductsImporter\Repositories\FeatureRepository;
use ProductsImporter\Repositories\ImageRepository;
use ProductsImporter\Repositories\ProductRepository;
use ProductsImporter\Repositories\RepositoryCore;
use ProductsImporter\Services\AttributeService;
use ProductsImporter\Services\CategoryService;
use ProductsImporter\Services\FeatureService;
use ProductsImporter\Services\ImageService;
use ProductsImporter\Services\ProductService;
use ProductsImporter\Services\SpreadsheetService;
use ProductsImporter\Utils\AttributesHelper;
use ProductsImporter\Utils\CategoryHelper;
use ProductsImporter\Utils\FeaturesHelper;
use ProductsImporter\Utils\Registry;

class Main
{
  /**
   * @param array $argv
   * @throws \PhpOffice\PhpSpreadsheet\Exception
   * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
   * @throws Exception
   */
  public static function execute($argv = [])
  {
//    self::checkIfFileOk($argv);

  DatabaseConnection::checkConnection();

    $repositoryCore = RepositoryCore::getConnection();
    $queryBuilder = new QueryBuilder($repositoryCore);
    $queryBuilder->createTableInternalOrderIdIfNotExists();
    $queryBuilder->createTableProductImageIfNotExists();

    SpreadsheetService::loadRequiredData(self::getFileName($argv));


    $categoryRepository = new CategoryRepository();
    $categories = $categoryRepository->addCategoriesIfNotExists(CategoryHelper::CreateCategoryObjects(CategoryService::getCategories()));
    Registry::bind('categories', $categories);
    $attributesRepository = new AttributesRepository();
    $attributes = $attributesRepository->addAttributesIfNotExists(AttributesHelper::CreateAttributeObjects(AttributeService::getAttributes()));
    Registry::bind('attributes', $attributes);
    $featuresRepository = new FeatureRepository();
    $features = $featuresRepository->addFeaturesIfNotExists(FeaturesHelper::CreateFeaturesObjects(FeatureService::getFeatures()));
    Registry::bind('features', $features);

    $products = ProductService::createProducts();

//    $productsRepository = new ProductRepository(new FeatureRepository(), new AttributesRepository(), new ImageRepository());
//      $productsRepository->importProductsToDb($products);
    $imageRepository = new ImageRepository();

    $imagesArray = [];
    $highestRow = ImageService::getWorksheet()->getHighestRow();
    for ($row = 2; $row <= $highestRow; ++$row) {
      $imagesArray [] = ImageService::getImageData($row,ImageService::IMAGE_ADDRESS_COLUMNS);
    }

    foreach ($imagesArray as $images){
      /**@var \ProductsImporter\Classes\ProductImage */
      foreach ($images as $image){

       $exist =  $imageRepository->checkExist('ml_product_image',['id_global' => $image->getGlobalId(),'name'=>$image->getImageName()]);

       if($exist){
         $imageId = $exist->data['id_image'];
         $productId = $exist->data['id_product'];
         $url = $exist->data['fullpath'];

         $prestaImage = new Image($image->getId(),$_ENV['LANG_ID']);
         $path = $prestaImage->getExistingImgPath();
         if(!$path){
           ImageService::copyImg($imageId,$productId,$url);
         }

       }
      }
    }
  }

  /**
   * @param array $argv
   * @return string
   * @throws \Exception
   */
  private static function getFileName($argv = [])
  {
//    if (count($argv) !== 1) {
//      throw new \Exception('Only one file can be added');
//    }

    return $argv[1];
  }

  /**
   * @param array $argv
   * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
   */
  public static function checkIfFileOk(array $argv)
  {
    if (!file_exists($argv[1])) {
      throw new Exception('File does not exists');
    }
    $filename = self::getFileName($argv);

    if (!SpreadsheetService::identifyFileExtension($filename)) {
      throw new Exception('Loaded file' . pathinfo($filename, PATHINFO_BASENAME) . 'has wrong format ');
    } else {
      $info = pathinfo($filename);
      echo 'File ' . pathinfo($filename)['basename'] . ' is correctly identified ' . "\n";
    }
  }
}
