<?php
/**
 * Created by PhpStorm.
 * User: USER
 * Date: 11.02.2019
 * Time: 13:21
 */

namespace core\includes\classes\Grid;

/**
 * @property  \web\Model $model
 */
use \Symfony\Component\HttpFoundation\Session\Session;

abstract class BaseGrid
{
	protected $model;
	protected $session;
	protected $hash;
	protected $debug;
	/**
	 * @var Grid $gridObject
	 */
	protected $gridObject;


	public function __construct($uniqueId)
	{
		$this->session = new Session();
		$this->getSession($uniqueId);
	}

	private function getSession($uniqueId)
	{
		$object = $this->session->get('grid'.$uniqueId);
		$this->gridObject = $object;
	}

	protected function getModel($model)
	{

		if (!empty($model)){
			$this->model = $model;
			return $this;
		}

		return false;
	}

}