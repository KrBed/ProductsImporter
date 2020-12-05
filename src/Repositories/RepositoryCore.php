<?php

namespace ProductsImporter\Repositories;

use PDO;
use PDOException;
use ProductsImporter\Db\DatabaseConnection;

class RepositoryCore implements IRepositoryInterface {

    /**@var PDO */
    static protected $db;

    private $errorMessage;

    public static function getConnection()
    {
        if (!self::$db) {
            self::$db = DatabaseConnection::initialize();
        }

        return self::$db;
    }


    /**
     * @param  string  $table  table name
     * @param  array  $data  associative array 'col'=>'val'
     * @return bool
     */
    public function insert($table, $data)
    {
        $values = [];
        if ($data !== null) {
            $values = array_values($data);
        }
        //grab keys
        $columns = array_keys($data);
        $joinedColumns = implode(', ', $columns);

        //grab values and change it value
        $mark = [];
        foreach ($values as $key) {
            $keys = '?';
            $mark[] = $keys;
        }
        $joinArguments = implode(', ', $mark);
        $statement = self::$db->prepare("INSERT INTO $table ($joinedColumns) values ($joinArguments)");
        try {
            $statement->execute($values);

            return true;
        } catch (PDOException $exception) {
            $this->setErrorMessage($exception->getMessage());
            throw $exception;
        }
    }


    /**
     * update record
     * @param  string  $table  table name
     * @param  array  $data  associative array 'col'=>'val'
     * @param  string  $idColumnName  primary key column name
     * @param  int  $idValue  key value
     * @return bool
     */
    public function update($table, $data, $idColumnName, $idValue)
    {
        $values = [];
        if ($data !== null) {
            $values = array_values($data);
        }
        //adds $id to $values array
        array_push($values, $idValue);
        //takes keys as columns
        $columns = array_keys($data);
        $mark = [];
        //prepares arguments
        foreach ($columns as $key) {
            $mark[] = $key."=?";
        }
        //joins arguments
        $joinArguments = implode(', ', $mark);
        //prepare statement
        $statement = self::$db->prepare("UPDATE $table SET $joinArguments where $idColumnName=?");
        try {
            //execute statement
            $statement->execute($data);

            return true;
        } catch (PDOException $exception) {
            $this->setErrorMessage($exception->getMessage());
            throw $exception;
        }
    }

    /**
     * check if there is exist data
     * @param  string  $table  table name
     * @param  array  $dat  array list of data to find
     * @return true or false
     */
    public function checkExist($table, $data)
    {
        $values = array_values($data);
        //
        $columns = array_keys($data);
        $joinColumns = implode(', ', $columns);
        foreach ($columns as $key) {
            $keys = $key."=?";
            $parameters[] = $keys;
        }
        $count = count($data);
        if ($count > 1) {
            $joinedParameters = implode(' and  ', $parameters);
            $statement = self::$db->prepare("SELECT * from $table WHERE $joinedParameters");
        } else {
            $joinedParameters = implode('', $parameters);
            $statement = self::$db->prepare("SELECT * from $table WHERE $joinedParameters");
        }
        $statement->execute($values);
        $statement->setFetchMode(PDO::FETCH_ASSOC);
        $count = $statement->rowCount();
        if ($count > 0) {
            $obj = $statement->fetch();
            $this->data = $obj;

            return $this;
        } else {
            return false;
        }
    }

    /**
     * takes max Id from specified table
     * @param $tableName
     * @param $columnName
     * @return array
     */
    public function getMaxIdFromTable($tableName, $columnName)
    {
        $statement = self::$db->query("SELECT MAX({$columnName}) FROM {$tableName}");
        $id = $statement->fetch();

        return reset($id);
    }

    /**
     * begin a transaction.
     */
    public function beginTransaction()
    {
        self::$db->setAttribute(PDO::ATTR_AUTOCOMMIT, 0);
        self::$db->beginTransaction();
    }

    /**
     * commit the transaction.
     */
    public function commit()
    {
        self::$db->commit();
        self::$db->setAttribute(PDO::ATTR_AUTOCOMMIT, 1);
    }

    /**
     * rollback the transaction.
     */
    public function rollback()
    {
        self::$db->rollBack();
        self::$db->setAttribute(PDO::ATTR_AUTOCOMMIT, 1);
    }

    /**
     * custom query , joining multiple table, aritmathic etc
     * @param  string  $sql  custom query
     * @param  array  $data  associative array
     * @return array  recordset
     */
    public function query($sql, $data = null)
    {
        $values = [];
        if ($data !== null) {
            $values = array_values($data);
        }
        $statement = self::$db->prepare($sql);

        try {
            if ($data !== null) {
                $statement->execute($values);
            } else {
                $statement->execute();
            }
            $statement->setFetchMode(PDO::FETCH_ASSOC);

            return $statement->fetchAll();
        } catch (PDOException $exception) {
            $this->setErrorMessage($exception->getMessage());
            echo $this->getErrorMessage();
            exit();
        }

    }

    /**
     * [getErrorMessage return string throw exception
     * @return string return string error
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * [setErrorMessage set error message]
     * @param [type] $error [description]
     */
    public function setErrorMessage($error)
    {
        $this->errorMessage = $error;
    }

    /**
     * search data
     * @param  string  $table  table name
     * @param  array  $col  column name
     * @param  array  $where  where condition
     * @return array recordset
     */
    public function search($table, $where)
    {
        $data = array_values($where);
        foreach ($data as $key) {
            $val = '%'.$key.'%';
            $value[] = $val;
        }
        //grab keys
        $columns = array_keys($where);

        foreach ($columns as $key) {
            $keys = $key." LIKE ?";
            $mark[] = $keys;
        }
        $count = count($where);
        if ($count > 1) {
            $joinParameters = implode(' OR  ', $mark);
            $statement = self::$db->prepare("SELECT * from $table WHERE $joinParameters");
        } else {
            $joinParameters = implode('', $mark);
            $statement = self::$db->prepare("SELECT * from $table WHERE $joinParameters");
        }

        $statement->execute($value);
        $statement->setFetchMode(PDO::FETCH_ASSOC);

        return $statement->fetchAll();
    }

    /**
     * gets max values from selected table columns
     * @param $tableName
     * @param  array  $columnNames
     * @param  array  $where
     * @return mixed
     */
    public function getMaxColumnValuesFromTable($tableName, array $columnNames, array $where = null)
    {
        $value = [];
        $columns = [];
        $data = [];
        $count = count($columnNames);
        $sortedColumns = "";
        for ($x = 0; $x < $count; $x++) {
            if ($x === $count - 1) {
                $sortedColumns .= "MAX({$columnNames[$x]}) AS {$columnNames[$x]}";
            } else {
                $sortedColumns .= "MAX({$columnNames[$x]}) AS {$columnNames[$x]}, ";
            }
        }


        $count = 0;
        if (!is_null($where)) {
            $columns = array_keys($where);
            $count = count($where);
            $data = array_values($where);
        }

        if (!is_null($data)) {
            foreach ($data as $key) {
                $val = '%'.$key.'%';
                $value[] = $val;
            }
        }

        $mark = [];
        if (!is_null($columns)) {
            foreach ($columns as $key) {
                $keys = $key." LIKE ?";
                $mark[] = $keys;
            }
        }

        if ($count > 1) {
            $joinParameters = implode(' OR  ', $mark);
            $statement = self::$db->prepare("SELECT $sortedColumns FROM $tableName WHERE $joinParameters");
        } elseif ($count === 1) {
            $joinParameters = implode(' ', $mark);
            $statement = self::$db->prepare("SELECT $sortedColumns FROM $tableName WHERE $joinParameters");
        } else {
            $joinParameters = implode(' ', $mark);
            $statement = self::$db->prepare("SELECT $sortedColumns FROM $tableName");
        }
        $statement->execute($value);
        $result = $statement->fetch();

        return $result;
    }

}
