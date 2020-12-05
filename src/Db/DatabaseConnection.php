<?php

namespace ProductsImporter\Db;

use Doctrine\DBAL\Driver\PDOException;
use PDO;

class DatabaseConnection
{
  
  /**
   *check if is set connection with DB
   */
  public static function checkConnection()
  {
    
    $dsn = "mysql:host={$_ENV['MYSQL_HOST']};dbname={$_ENV['MYSQL_DB']}";
    try {
      $db = new PDO($dsn, $_ENV['MYSQL_USER'], $_ENV['MYSQL_PWD']);
      if ($db) {
        echo "Successfully connected to database \n";
      }
    } catch (PDOException $e) {
      echo $e;
    }
  }
  
  /**
   * initializes connection with Db
   * @return false|PDO
   */
  public static function initialize()
  {
    //database connection options
    $options = [
      PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES   => false,
      PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
    ];
    
    $dsn = "mysql:host={$_ENV['MYSQL_HOST']};dbname={$_ENV['MYSQL_DB']}";
    
    try {
      return new PDO($dsn, $_ENV['MYSQL_USER'], $_ENV['MYSQL_PWD'], $options);
      
    } catch (PDOException $e) {
      echo $e;
    }
    return false;
  }
}