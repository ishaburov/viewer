<?php
/**
 * User: Vanya Shaburov  * Date: 06.03.2019  * Time: 21:49
 */

namespace core\includes\classes\Grid;


class GridQueries
{

	private $linkedTables;
	private $linkedColumns;
	private $startFilter;
	private $tableName;

	public function __construct($tableName,$linkedTables,$linkedColumns,$startFilter)
	{
		$this->linkedTables = $linkedTables;
		$this->linkedColumns = $linkedColumns;
		$this->startFilter = $startFilter;
		$this->tableName = $tableName;
	}

	public function getStartFilter()
	{
		if (!empty($this->startFilter)){
			return $this->startFilter;
		}
		return false;
	}

	public function getLinkedTables()
	{
		if (!empty($this->linkedTables)){
			return $this->linkedTables;
		}
		return false;
	}

	public function getLinkedColumns()
	{
		if (!empty($this->linkedColumns)){
			return $this->linkedColumns;
		}
		return false;
	}

	public function setLinkedTables($select)
	{

		$class = $this->linkedTables['className'];
		$method = $this->linkedTables['method'];

		if (class_exists($class) && !empty($select)){
			if (method_exists($class,$method)){
				$tableTitles = $select;

				foreach ($tableTitles as $key => $value){
					$modelSelect[$key] = $this->getTable().'.'.$value;
				}
				return (new $class())->$method($modelSelect);
			}
		}
		return false;
	}

	public function setLinkedColumns($currentQuery)
	{
		$class = $this->linkedColumns['className'];
		$method = $this->linkedColumns['method'];

		if (class_exists($class) && !empty($currentQuery)) {
			if (method_exists($class, $method)) {
				return (new $class())->$method($currentQuery);
			}
		}
		return false;
	}

	public function setStartFilter($currentQuery)
	{
		$class = $this->startFilter['className'];
		$method = $this->startFilter['method'];
		$value = $this->startFilter['value'];

		if (class_exists($class) && !empty($currentQuery)) {
			if (method_exists($class, $method)) {
				return (new $class())->$method($currentQuery,$value);
			}
		}
		return false;
	}


	public function getTable()
	{
		return $this->tableName;
	}



}