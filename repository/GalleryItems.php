<?php
/**
 * User: Vanya Shaburov  * Date: 06.03.2019  * Time: 21:50
 */

namespace core\includes\classes\Grid\repository;


use core\models\repository\GalleriesItems;
use web\Model;

class GalleryItems extends Model
{
	protected $table = 'galleries_items';
	/**
	 * Получение связанных таблиц
	 * Ипользуется в грид-менеджере
	 * @param $select
	 * @return GalleriesItems|\Illuminate\Database\Query\Builder
	 */
	public function linkedTableTestQweerty($select)
	{

		$query = $this
			->leftJoin('pages', 'pages.id', '=', 'galleries_items.page')
			->leftJoin('users', 'users.id', '=', 'galleries_items.user')
			->select($select)
			->addSelect('pages.title')
			->addSelect('users.fio');
		return $query;
	}
	/**
	 * Добавление в таблицу значение из других таблиц после поиска
	 * @param $table
	 * @return mixed
	 */
	public  function linkedColumn1($table)
	{
		$query = $table
			->addSelect('galleries_items.title as title')
			->addSelect('pages.title as page')
			->addSelect('users.fio as user');
		return $query;
	}

	/**
	 * @param $table Model
	 * @return mixed
	 */
	public  function filterQ($table)
	{
		$query = $table
			->where('galleries_items.id','<','200');
		return $query;
	}

	public function filterUser1($table,$value)
	{
		$query = $table
			->where('galleries_items.user','=',$value);
		return $query;
	}

}