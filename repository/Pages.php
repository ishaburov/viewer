<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 07.03.2019
 * Time: 17:01
 */

namespace core\includes\classes\Grid\repository;


use Illuminate\Database\Eloquent\Model;

class Pages extends Model
{
	protected $table = 'pages';

	public function linkedTableUsers($select)
	{
		$query = $this
			->leftJoin('users', 'users.id', '=', 'pages.user_create')
			->select($select)
			->addSelect('users.fio');

		return $query;
	}

	public  function linkedColumn1($table)
	{
		$query = $table
			->addSelect('users.fio as user_create');
		return $query;
	}

}