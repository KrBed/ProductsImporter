<?php

namespace ProductsImporter\Repositories;

interface IRepositoryInterface
{
	/**
	 * insert data to table
	 *
	 * @param string $table table name
	 * @param array $dat associative array 'column_name'=>'val'
	 */
	public function insert($table,$dat);

	/**
	 * update record
	 *
	 * @param string $table table name
	 * @param array $data associative array 'col'=>'val'
	 * @param string $idColumnName primary key column name
	 * @param int $idValue key value
	 */
	public function update($table, $data, $idColumnName, $idValue);

	/**
	 * check if there is exist data
	 * @param  string  $table  table name
	 * @param  array  $data  array list of data to find
	 * @return true or false
	 */
	public function checkExist($table, $data);

	/**
	 * takes max Id from specified table
	 * @param $tableName
	 * @param $columnName
	 * @return array
	 */
	public function getMaxIdFromTable($tableName, $columnName);


	/**
	 * custom query , joining multiple table, aritmathic etc
	 * @param  string  $sql  custom query
	 * @param  array  $data  associative array
	 * @return array  recordset
	 */
	public function query($sql, $data = null);

	/**
	 * search data
	 *
	 * @param  string $table table name
	 * @param  array  $where where condition
	 * @return array recordset
	 */
	public function search($table, $where);
}
