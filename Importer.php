<?php

use ProductsImporter\Main;

include_once 'vendor/autoload.php';


// Load PS config and autoloader
define('PS_DIR', __DIR__.'/../meblelupus.pl');


// I use this to load compoper dependencies
require_once __DIR__.'/../../vendor/autoload.php';
require_once PS_DIR.'/../../config/config.inc.php';
//ToolsCore::clearSmartyCache();
//ToolsCore::clearXMLCache();
//MediaCore::clearCache();
//ToolsCore::generateIndex();

//\ProductsImporter\Services\CategoryService::rebuildNtree();
//die();
//PrestaShop\PrestaShop\Adapter\Entity\Category::regenerateEntireNtree();

//die();
//define('_PS_MIGRATETOOL',true);
//if (!defined('_PS_MODE_DEV_')){
//    define('_PS_MODE_DEV_', true);
//}
//ob_start();

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$fileNameArgumentIndex = array_search(basename(__FILE__), $argv);
if ($fileNameArgumentIndex !== false) {
    unset($argv[$fileNameArgumentIndex]);
}

Main::execute($argv);

