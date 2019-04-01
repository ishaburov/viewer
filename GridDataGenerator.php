<?php
/**
 * User: Vanya Shaburov  * Date: 06.03.2019  * Time: 19:35
 */

namespace core\includes\classes\Grid;



use \core\Helpers\Html;
use mysql_xdevapi\Session;
use web\Model;


class GridDataGenerator extends BaseGrid
{

	private $id; //if (!empty($post['id'])){$this->id = $post['id'];}
	//private $serverSideLoad;
	private $serverSideData;
	private $recordsCount;
	private $filter;

	private $icons = [
		'edit'=> '<i class="edit icon"></i>',
		'delete' => '<i class="trash icon"></i>'
	];

	/**
	 * @var Grid $gridObject
	 */
	protected $gridObject;

	public function __construct($uniqueId)
	{
		parent::__construct($uniqueId);
	}

	public function getUpdatedData($id)
	{
		$this->id = $id;
		$data = $this->prepareArray();
		return $data;
	}

	private function getFilters($filters)
	{
		$tableName = $this->gridObject->model->getTable().".";
		foreach ($filters as $filter){
			if (!empty($filter['search']['value'])){
				$this->filter[$filter['data']] = ['data' => $tableName.$filter['data'] , 'value' => $filter['search']['value']];
			}
		}
		return $this->filter;
	}

	public function initData($post)
	{

		$this->getFilters($post['columns']);

		$data = [
			'length' => $post['length'],
			'start' => $post['start'],
			'draw' => $post['draw'],
			'search' => $post['search']['value'],
			'order' => $post['order']
		];


		if ($this->gridObject->useServerSide()){
			return $this->serverSide($data);
		}

		return $this->clientSide($data);
	}

	public function getColumns()
	{
		return $this->gridObject->getColumns();
	}

	private function serverSide($data)
	{
		//$this->serverSideLoad = true;
		$this->serverSideData = $data;

		return [
			'draw' => $this->serverSideData['draw'],
			'data'=> $this->prepareArray(),
			'recordsTotal'=> $this->recordsCount,
			'recordsFiltered'=> $this->recordsCount,
			'order' => $this->serverSideData['order'],
			'debug' => $this->debug
		];
	}

	private function clientSide($data)
	{

		$columns = [];

		if (!empty($this->id)){
			$data['side'] = 0;
		}

		if ($data['side'] == 1){
			$arrayData = [];
		}else{
			$arrayData = $this->prepareArray();
		}


		foreach ($this->gridObject->getColumnsTags() as $column => $q){
			$columns[]["data"] = $column;
		}

		return [
			'columns' => $columns,
			'data' => $arrayData
		];
	}

	private function count ($count)
	{
		return
			$this->recordsCount = $count ;
	}

	private function setTag($itemKey,$text,$id = null){
		foreach ($this->gridObject->getColumnsTags() as $key => $columnParam){

			if ($key == $itemKey){

				/*if (!empty($columnParam['value'])){
					$text = $this->getUserFunction($itemKey, $text);
				}*/
				if (!empty($columnParam['type'])){
					$text = $this->setColumnType($columnParam['type'],$text);
				}

				if (!empty($columnParam['tag'])){

					$el = Html::el($columnParam['tag']);
					if (empty($columnParam['hrefImage'])){
						$el->addText($text);
					}
					if (!empty($columnParam['tagClass'])) {
						$el->addAttributes(['class' => $columnParam['tagClass']]);
					}
				}else{
					$el = Html::el();
					$el->addText($text);
				}
				if (!empty($columnParam['href']) ) {
					$el->addAttributes([
						'href'=> $columnParam['href']
					]);
				}

				if (!empty($columnParam['hrefImage'])) {

					$page = null;

					if ($columnParam['searchPage'] == 'true'){
						$page = $this->gridObject->model->where('id','=',$id)->value('page') ."/";
					}

					$fileList = [];
					\File::fileList("site/files/".$columnParam['hrefImage']."/".$page, false, $fileList);

					foreach ($fileList as $key => $file) {
						if (preg_match('/([A-z\/0-9]+)\/([A-z]+)\/([A-z0-9.]+$)/', $file, $filePath)) {
							if ($filePath[3] == $text) {
								$paths[$filePath[2]] = $filePath[1] ."/". $filePath[2] ."/";
								$finalPath[$filePath[2]] = $filePath[1] ."/". $filePath[2] ."/". $filePath[3];
								if (file_exists(DOC_ROOT. $finalPath['full']) && !empty($finalPath['full'])) {
									$full = $finalPath['full'];
									$img = $finalPath['img'];
									$thumb = $finalPath['thumb'];
								}else{
									$img = $filePath[1] ."/". $filePath[2] ."/". $filePath[3];
									$thumb = $filePath[1] ."/". $filePath[2] ."/". $filePath[3];
								}
							}
						}
					}
					if (!empty($thumb)){
						$el->addAttributes([
							'href'=> DOC_HOST.$img,
							'class' => 'grid-light-gallery'
						]);
						$image = Html::el('img')->src(DOC_HOST.$thumb);
						$el->addHtml($image);
					}
				}

				return $el;
			}
		}
		return false;
	}

	private function setColumnType($type,$text){
		if ($type == 'dateTime') {
			return date('c', $text);
		}
		if ($type == 'date') {
			return date('d.m.y', $text);
		}
	}


	/**
	 * @param $model \web\Model
	 * @return array|bool
	 */
	private function getData($model)
	{
		$tableTitles = $this->gridObject->getTableTitles();

		unset($tableTitles['#']);
		unset($tableTitles['edit']);

		$modelSelect = array_keys($tableTitles);


				if ($model::select($modelSelect)->first()->exists()) {

					$builder = $this->gridObject->getBuilder();
					if (!empty($builder->getLinkedTables())) {
						$table = $builder->setLinkedTables($modelSelect);
					} else {
						$table = $model::select($modelSelect);
					}



					$options = [
						'tableName' => $model->getTable(),
						'columnsParams' => $this->gridObject->getColumnsTags()
					];


					if (!empty($builder->getStartFilter())) {
						$table = $builder->setStartFilter($table);
					}

					$search = new GridSearch($table, $options);

					if (!empty($this->filter)){
						$search->filterWhere($this->filter);
					}
					$search->searchString($this->serverSideData['search']);
					$search->ordering($this->serverSideData['order'], $this->gridObject->useCountColumn());


					$this->count($search->count());


					if (!isset($tableTitles['id'])){
						$table->addSelect($model->getTable().'.id');
					}

					if (!empty($builder->getLinkedColumns())) {
						$table = $builder->setLinkedColumns($table);
					}

					if (!empty($this->id)){
						$table = $table
							->where($model->getTable().'.id',$this->id)
							->get()
							->toArray();
					}else{
						$table = $table
							->offset($this->serverSideData['start'])
							->limit($this->serverSideData['length'])
							->get()
							->toArray();
					}
				}

			$this->debug['debug'][] = \web\Asite::$app->db->getConnection()->getQueryLog();

			return $table;
	}

	/**
	 * Заполняем массив данными // в контроллере формируем json @class GridController
	 * @return array|bool
	 */
	private function prepareArray(){
		/**
		 * @var $model Model
		 * */
		$model = $this->gridObject->model;


		if ($model->count('id') > 0 ){

			$table = $this->getData($model);

			if (!is_null($table))
			{
				$prepareArray = [];

					$itemCount = $this->serverSideData['start'] + 1;

				foreach ($table as $key => $items){
					if ($this->gridObject->useCountColumn()) {
						$prepareArray[$key]['#'] = $itemCount;
						$itemCount++;
					}

					foreach ($items as $itemKey => $item){
						$tag = $this->setTag($itemKey,$item,$items['id']);
						if ($tag){
							$prepareArray[$key][$itemKey] = $tag->render();
						}else{
							$prepareArray[$key][$itemKey] = $item;
						}
					}

					if (!empty($this->gridObject->getEditType())){
						$prepareArray[$key]['edit'] = $this->prepareButtons($items['id']);
					}


					if (is_null($this->gridObject->getTableTitles()['id'])){
						unset($prepareArray[$key]['id']);
					}

				}

				if (!empty($prepareArray)){
					return $prepareArray;
				}
			}
		}
		return false;
	}

	private function prepareButtons($id)
	{
		$button = '';

		if ($this->gridObject->getEditType()){

			$button = "<div class='ui icon buttons'>
									<button data-id='{$id}'  class='ui button blue j-grid-edit-table-modal'>{$this->icons['edit']}</button>
									<button class='j-grid-delete-row ui red button' data-id='{$id}'>{$this->icons['delete']}</button>
								</div>";

		}

		return $button ;
	}
}