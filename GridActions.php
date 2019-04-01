<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 07.03.2019
 * Time: 13:31
 */

namespace core\includes\classes\Grid;


use web\Model;

class GridActions extends BaseGrid
{
	/**
	 * @var Grid $gridObject;
	 * */
	protected $gridObject;

	public function __construct($uniqueId)
	{
		parent::__construct($uniqueId);
	}

	public function save($post)
	{
		/**
		 * @var $model Model
		 */

		$model = $this->gridObject->model;
		if (!empty($post['id'])){
			$modelRow = $model->find($post['id']);

		foreach ($modelRow->toArray() as $key => $value){
			if (!empty($post[$key])){
				if (preg_match('/^(0[1-9]|[1-2][0-9]|3[0-1]).(0[1-9]|1[0-2]).([0-9]{4})$/', $post[$key], $date)){
					$timestamp = strtotime($date[0]);
					$modelRow->$key = $timestamp;
				}else{
					$modelRow->$key = $post[$key];
				}
			}
		}

		$modelRow->save();
			return $modelRow->id ;
		}else{
			return "Айди пуст";
		}
	}

	public function insert($post)
	{

		/**
		 * @var $model Model
		 */
		$model = $this->gridObject->model;

		$modelRow = $model->getFillable();
		$rowInsert = [];

		foreach ($modelRow as $key => $value) {
			if (!empty($post[$value])) {
				if (preg_match('/^(0[1-9]|[1-2][0-9]|3[0-1]).(0[1-9]|1[0-2]).([0-9]{4})$/', $post[$value], $date)) {
					$timestamp = strtotime($date[0]);
					$rowInsert[$value] = $timestamp;
				} else {
					$rowInsert[$value] = $post[$value];
				}
			}
		}


		$id = $model->insertGetId($rowInsert);
		return $id;
	}

	public function delete($id)
	{
		/**@var $model Model*/
		$model = $this->gridObject->model;
		$model->whereKey($id)->delete();
	}
}