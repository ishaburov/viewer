<?php
/**
 * User: Vanya Shaburov  * Date: 07.02.2019  * Time: 14:00
 */


namespace core\includes\classes\Grid;



use core\Helpers\Html;

class Grid extends BaseGrid
{
	/**
	 * @var $model Model
	 * @var $containerClass $this
	 * @var $tableClass $self
	 */
	private $uniqueId;
	private $editType = false;
	private $countColumn;

	private $tableHeader =  false;
	private $containerClass = 'table-container';
	private $tableClass = 'ui celled padded table grid-manager';
	private $addButton = "Добавить запись в таблицу";

	private $linkedTables;
	private $linkedColumns;

	private $serverSide = true; // Не актуально
	private $tableTitles; //

	private $columnsTags = [];
	private $columns;

	private $userFunction;
	/**
	 * @var $builder GridQueries
	 */
	private $builder;
	private $filters;
	/**
	 * @var $modalWindow GridModal
	 */
	private $modalWindow;


	/**
	 * @param array $params
	 * @return boolean|Grid
	 */
	public static function init($options)
	{
		$self = new self();

		if ($self->getModel($options['model'])) {
			$self->connectScript();
			$self->setTableParams($options);
			$self->setUserFunction($options['columns']);
			$self->setColumnsParams($options['columns']);
			$self->parseModel($options['columns']);
			$self->links(
				$options['linkedTables'],
				$options['linkedColumns'],
				$options['startFilter']
			);

			if ($self->session->has('grid'.$self->uniqueId)){
				$self->session->remove('grid'.$self->uniqueId);
			}

		$self->session->set('grid'.$self->uniqueId,$self);

			return $self->render();
		}
		return false;
		//return new Exception('missing MODEL $params["model"] ');
	}
	public function getUserFunction($name,$value)
	{
		if (!empty($value)){
			$func =  $this->userFunction[$name];
			if ($func){
				return call_user_func($func,$value);
			}
		}
		return false;
	}
	public function getTableTitles()
	{
		return $this->tableTitles;
	}
	public function useServerSide()
	{
		return $this->serverSide;
	}
	public function getColumnsTags()
	{
		return $this->columnsTags;
	}
	public function getColumns()
	{
		return $this->columns;
	}
	public function useCountColumn()
	{
		return $this->countColumn;
	}
	public function getEditType()
	{
		return $this->editType;
	}


	private function links($linkedTables,$linkedColumns,$startFilter)
	{
		$builder = new GridQueries(
			$this->model->getTable(),
			$linkedTables,
			$linkedColumns,
			$startFilter
		);
		$this->builder = $builder;
	}


	public function getBuilder()
	{
		return $this->builder;
	}

	public function getLinksTables()
	{
		return $this->linkedTables;
	}

	public function getLinksColumns()
	{
		return $this->linkedColumns;
	}

	public function getFilters()
	{
		return	$this->filters;
	}



	private function connectScript(){
		global $doc;

		$doc['template']['css'][] ="https://cdn.datatables.net/1.10.19/css/dataTables.semanticui.min.css";
		$doc['template']['css'][] = $doc['host']."core/plugins/jquery-air-datepicker/css/datepicker.min.css";
		$doc['template']['css'][] = $doc['host']."core/plugins/1a-grid/grid.css";

		$doc['template']['js'][] = $doc['host']."core/plugins/1a-grid/grid.js";
		$doc['template']['js'][] = $doc['host']."core/plugins/ckeditor/ckeditor.js";
		$doc['template']['js'][] = $doc['host']."core/plugins/jquery-air-datepicker/js/datepicker.min.js";
		$doc['template']['js'][] = '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js';
		$doc['template']['js'][] = "https://cdn.datatables.net/1.10.19/js/dataTables.semanticui.min.js";
		//$doc['template']['js'][] = "https://cdn.datatables.net/select/1.3.0/js/dataTables.select.min.js";
	}
	private function render(){
		return
			$this->getHtmlTable().$this->getModal();
	}
	/**
	 * @param $params
	 * @return bool
	 */
	private function setTableParams($params)
	{
		$this->uniqueId = generatestring(30);
		$this->tableClass = 	isset($params['tableClass'])    ? $params['tableClass'] 	: $this->tableClass ." ". $params['tableClass'];
		$this->containerClass = isset($params['containerClass'])? $params['containerClass'] : $this->containerClass . " ".$params['containerClass'];
		$this->countColumn = 	isset($params['count'])			? $params['count'] : false;

		$this->setHeaderParams($params['header']);

		$this->setEditParams($params);

		if ($this->editType == 'modal'){
			$this->modalWindow = GridModal::setModalParams($params);
		}

	}
	private function setEditParams($params)
	{
		if (isset($params['editType'])){
			if ($params['editType'] === 'modal'){
				$this->editType = 'modal';
			} else{
				$this->editType = 'default';
			}
		}
	}
	private function setColumnsParams($params)
	{
		$index =  $this->editType == true ? 1 : 0;

		foreach ($params as $key => $column){
			if (is_array($column)){
				if (empty($column['value'])) {
					$this->columnsTags[$key] = $column;
				}
				if (!empty($column['filter'])){
					$this->filters[$key]['filter'] = $column['filter'];
					$this->filters[$key]['index'] = $index;
					if (!empty($column['filterUserFunction'])){
						$this->filters[$key]['filterUserFunction'] = $column['filterUserFunction'];
					}
				}
			}
			$index++;
		}



		return $this->columnsTags;
	}
	/**
	 * Передаем пользовательскую функцию запоминаем в свойстве $userFunction , где нужно используем ;
	 * @param $findField
	 * @param $params
	 * @return bool
	 */
	private function setUserFunction($columns)
	{
		foreach ($columns as $key => $param){
			$methodResult = \web\Model::findHashMethod($param['value'], null,false);
			if ($methodResult){
				$this->userFunction[$key] = $methodResult;
			}
		}

		if (!empty($this->userFunction)){
			return $this->userFunction;
		}

		return false;
	}
	private function getAllColumns()
	{
		if (!empty($this->tableTitles)){
			return $this->tableTitles;
		}
		return false;
	}

	private function filter()
	{
		$collection = [];

		foreach ($this->getAllColumns() as $key => $column) {
			$div = Html::el('div',['class'=>'grid__select-filter']);
			switch ($this->filters[$key]['filter']){
				case 'select':
					$options = $this->filters[$key]['filterUserFunction'];
					$label = Html::el('label')->addText($column);
					$select = Html::el('select',
						[
							'data-unique-id' => $this->uniqueId,'name' => $key,
							'data-index' => $this->filters[$key]['index'],
							'class' => 'ui fluid dropdown j-grid__select-filter'
						]);
					$select->addHtml(Html::el('option',['value' => 0])->addText("Выберите фильтр"));
					foreach ($options as $option){
						$select->addHtml(Html::el('option',['value' => $option['id']])->addText($option["value"]));
					}
					break;
			}
			if (!empty($label) && !empty($select)){
				$collection[] = $div->addHtml($label . $select);
			}
		}

		if (!empty($collection)){
			return implode('',$collection);
		}

		return false;
	}

	private function getHtmlColumns()
	{
		$columns = [];

		if (!empty($this->countColumn)){
			$this->columns[]['data'] = '#' ;
			$columns[] = Html::el('td',['data-column' => '#','data-orderable' => 0])->addText('#');
		}

		foreach ($this->getAllColumns() as $key => $column) {
			$this->columns[]['data'] = $key ;
			$columns[] = Html::el('td',['data-column' => $key])->addText($column);
		}

		if (!empty($this->editType)){
			$this->columns[]['data'] = 'edit' ;
			$columns[] = Html::el('td',['data-column' => 'edit','data-orderable' => 0])->addText('Редактирование');
		}

		return Html::el('tr')->addHtml(implode('',$columns));
	}
	private function getThead()
	{
		return Html::el('thead')->addHtml($this->getHtmlColumns());
	}
	private function getHeaderBlock()
	{
		if (!is_null($this->tableHeader)){
			return $this->tableHeader;
		}
		return false;
	}
	private function setHeaderParams($header)
	{
		if (isset($header['addButton'])){
			$this->addButton = $header['addButton'];
		}
		if (isset($header)){

			$button = Html::el('button',['class' => 'j-grid-edit-table-modal ui blue button','data-id' => 0])->addText($this->addButton);

			Html::el('div')
				->addHtml("<h2>{$header['title']}</h2>". $button);

			$this->tableHeader = Html::el('header',
				['class' => 'dataTable__header ui dividing header'])
				->addHtml(Html::el('div')
					->addHtml("<h2>{$header['title']}</h2>". $button)
					->addHtml("<div>{$header['text']}</div>")
				);

			return $this->tableHeader;
		}

		return false;
	}
	private function getHtmlTable()
	{
		$table = Html::el('table',[
			'id' => $this->uniqueId,
			'class' => $this->tableClass
		])
			->addHtml($this->getThead());

		$attributes = [
			'class'=> $this->containerClass . ' dataTable__container' ,
			'data-unique-id' =>$this->uniqueId
		];

		$filterContainer = $elem = Html::el('div',['class' => 'grid__filter-container'])->addHtml($this->filter());

		return Html::el('div', $attributes)
			->addHtml($this->getHeaderBlock())
			->addHtml($filterContainer)
			->addHtml($table);
	}
	private function getModal()
	{
		if ($this->editType == 'modal' ){
			return GridModal::drawModalWindow($this->uniqueId);
		}
		return false;
	}


	public function getModalParams()
	{
		return $this->modalWindow;
	}

	protected function getLabel($label)
	{
		if (isset($this->model->attributeLabels()[$label])){
			return $this->model->attributeLabels()[$label];
		}
		return $label;
	}
	protected function parseModel($columns)
	{
		if (!empty($columns)){
			foreach ($columns as $key => $item){
				if (is_string($item) || is_string($key)){
					$columnName = !empty($item) && is_string($item)?$item:$key;
					$label = !empty($item['label']) ? $item['label'] : $this->getLabel($columnName);
					$this->tableTitles[$columnName] = $label;
				}
			}
		}else{
			foreach ($this->model->first()->getAttributes()  as  $attribute =>  $value){
				$this->tableTitles[$attribute] = $this->getLabel($attribute);
			}
		}

		if (!empty($this->tableTitles)){
			return $this;
		}

			throw new Exception('tableTitles isEmpty');
			return false;
	}



}