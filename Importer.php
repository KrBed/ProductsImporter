<?php

use ProductsImporter\Main;

include_once 'vendor/autoload.php';


// Load PS config and autoloader
define('PS_DIR', __DIR__.'/../meblelupus.pl');


// I use this to load compoper dependencies
require_once __DIR__.'/../../vendor/autoload.php';
require_once PS_DIR.'/../../config/config.inc.php';



//PrestaShop\PrestaShop\Adapter\Entity\Category::regenerateEntireNtree();


$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$fileNameArgumentIndex = array_search(basename(__FILE__), $argv);
if ($fileNameArgumentIndex !== false) {
    unset($argv[$fileNameArgumentIndex]);
}

Main::execute($argv);

