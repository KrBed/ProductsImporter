<?php

namespace ProductsImporter\Db;

use ProductsImporter\Classes\Category;
use ProductsImporter\Classes\Product;
use ProductsImporter\Repositories\RepositoryCore;
use ProductsImporter\Services\LoggerService;

class QueryBuilder
{
  /**@var RepositoryCore $repositoryCore */
  private $repositoryCore;

  public function __construct($repositoryCore)
  {
    $this->repositoryCore = $repositoryCore;
  }

  /**
   * creates  internal_order_id table if not exists
   */
  public function createTableInternalOrderIdIfNotExists()
  {
    $sql = "CREATE TABLE IF NOT EXISTS {$_ENV['MYSQL_PREFIX']}internal_order_id
        (id_global INT PRIMARY KEY ,id_product INT NOT NULL ,id_attribute INT DEFAULT NULL)";
    $result = $this->repositoryCore->query($sql);
    if ($result) {
      LoggerService::getLogger()->info("Succesfully added {$_ENV['MYSQL_PREFIX']}internal_order_id table to database");
    } else {
      LoggerService::getLogger()->error("Failed to add {$_ENV['MYSQL_PREFIX']}internal_order_id table to database");
    }
  }

  public function createTableProductImageIfNotExists()
  {
    $sql = "CREATE TABLE IF NOT EXISTS {$_ENV['MYSQL_PREFIX']}product_image
        (id_image INT PRIMARY KEY ,id_global INT NOT NULL ,id_product INT NOT NULL, fullpath VARCHAR (128),name VARCHAR (128) NOT NULL,scheme VARCHAR (128) NOT NULL , path VARCHAR(128))";
    $result = $this->repositoryCore->query($sql);
    if ($result) {
      LoggerService::getLogger()->info("Succesfully added {$_ENV['MYSQL_PREFIX']}product_image table to database");
    } else {
      LoggerService::getLogger()->error("Failed to add {$_ENV['MYSQL_PREFIX']}product_image table to database");
    }
  }
}
