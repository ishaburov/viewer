<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 05.03.2019
 * Time: 12:52
 */

namespace core\includes\classes\Grid;


use web\Model;

class GridSearch
{

	private $query;
	private $tableName;
	private $columnsTypes;

	/**
	 * GridSearch constructor.
	 * @param $model Model
	 */
	public function __construct($model,$options = [])
	{
		$this->query = $model->getQuery();
		$this->tableName = $options['tableName'];
		$this->getColumnsTypes($options['columnsParams']);
	}

	public function getColumnsTypes($types)
	{
		foreach ($types as $column => $options){
			$this->columnsTypes[$column] = $options['type'];
		}
	}

	public function filterWhere($filters)
	{
		foreach ($filters as $filter){
			$this->query->where($filter['data'],'=',$filter['value']);
		}

		return $this->query;
	}

	public function getDateTypes()
	{
		$times = [
			'dateTime',
			'date'
		];

		foreach ($this->columnsTypes as $columns => $type){
			if (in_array($type,$times)){
				$types[$this->tableName.'.'.$columns] = $type;
			}
		}

		if (!empty($types)){
			return $types;
		}

		return false;
	}

	public function getDateType($columnName)
	{
		$dateType = $this->getDateTypes()[$columnName];
		if (!empty($dateType)){
			return $dateType;
		}

		return false;
	}

	public function searchString($searchString)
	{

		if (!empty($searchString)){
			$this->query->whereNested(function ($query) use ($searchString){
				foreach ($this->query->columns as $key => $column){
					$query->orWhere($column,'LIKE',$searchString."%");
						/*$dateType = $this->getDateType($column);
							if ($dateType){
								$this->query->where($column,'LIKE',strtotime($searchString)."%",'or');
							}else{*/

							//}
					}
			});

			return $this->query;
	}

	}

	public function ordering($orders,$countColumn = false)
	{

		 (!$countColumn) ?	$i = 0	:	$i = 1;
			foreach ($this->query->columns as $column){
				foreach ($orders as $order){
					if ($i == $order['column']){
						$this->query->orderBy($column,$order['dir']);
					}
				}
				$i++;
			}
		return $this->query;
	}

	public function count()
	{
		return $this->query->count($this->query->columns[0]);
	}

	public function query()
	{
		return $this->query;
	}

}