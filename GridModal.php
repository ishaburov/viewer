<?php
/**
 * Created by PhpStorm.
 * User: USER
 * Date: 11.02.2019
 * Time: 13:14
 */

/**
 * Class GridModal
 * @property \Symfony\Component\HttpFoundation\Request request;
 */

namespace core\includes\classes\Grid;

use \core\Helpers\Html;


class GridModal extends BaseGrid
{

	protected $ckeditorCount = 0;
	protected $modalId;
	protected  $data;
	protected $uniqueId;
	/**
	 * @var Grid $gridObject
	 */
	protected $gridObject;
	/**
	 * GridModal constructor.
	 * @param \web\Model $model
	 */
	public function __construct($uniqueId)
	{
		$this->uniqueId = $uniqueId;
		parent::__construct($uniqueId);
	}

	/*TODO ВЫНЕСТИ В ОТДЕЛЬНЫЙ КЛАСС */
	protected function choiceElement($elements,$data)
	{

		if (preg_match('/([A-z]+)((:)([A-z]+))?/', $elements['elem'], $output_array)){
			$element = null;
			$elements['type'] = !empty($output_array[4])?$output_array[4]:$output_array[1];
			$elements['elem'] = $output_array[1];
			switch ($output_array[1]){
				case 'input': $element = $this->formElem($elements,$data);
					break;
				case 'text': $element = $this->formElem($elements,$data);
					break;
				case 'select': $element = $this->formElem($elements,$data);
					break;
			}
			if (!is_null($element)){
				return $element;
			}
		}
		return false;
	}
	/*TODO ВЫНЕСТИ В ОТДЕЛЬНЫЙ КЛАСС */
	public function formActions($actions)
	{
		if ($this->modalId == 0) {
		 unset($actions['id']);
		}

		$acts = null;
			foreach ($actions as $key => $action){
				if (!is_null($action)){
					if (!is_array($action)){
						$acts[] = "<input type='hidden' name='{$key}' value='{$action}'>";
					}else{
						$acts[] = $this->formActions($action);
					}
				}
			}

			return implode(PHP_EOL,$acts);
	}
	/*TODO ВЫНЕСТИ В ОТДЕЛЬНЫЙ КЛАСС , ПЕРЕПИСАТЬ ЭТОТ БЛОК !!!!*/
	public function formElem($param,$data)
	{
		$elem = null;
		$class = empty($param["class"])?'form-control':"{$param["class"]} form-control";
		$checked = null;
		$dataAttribute = !empty($param['data'])?
			implode(' ',$param['data']):null;
		$placeHolder = empty($param["placeHolder"])?'':"{$param['placeHolder']}";
		$label = null;


		if ($this->modalId == 0){
			$data[1] = null;
		}

		if ($param["type"] !== 'hidden' && $data[0] !== 'id' && $param["type"] !== 'file'){

				if ($param['label'] == 'inactive'){
					$label = null;
				}else{
					$label = "<label>";
					$label .= !empty($param['label'])?$param['label']:$data[0];
					$label .= "</label>";
				}


			if ($param["type"] === 'checkbox'){
				$placeHolder = '';
				$checked = $data[1] == 1?
					'checked':'';
			}
		}


		if ($param['elem'] == 'input' && $data[0] != 'id' && $param['type'] != 'file' ){   //$param['type'] !== 'file'
			$elem = Html::input($param["type"],$data[1],['name' => $data[0],'class'=>$class,'placeholder'=>$placeHolder,$checked,$dataAttribute]);
		}

		if ($param['elem'] == 'input' && $param['type'] == 'file') {

			$prop['file'] = ['text' => 'Выберите файл','name'=>$data[0],'type'=> $param["type"],'attrs' => [$class,$placeHolder,$checked,$dataAttribute]];
			$prop['width'] = ['text' => 'Ширина','name'=>'width','type'=> 'text', 'value'=>$param["width"]];
			$prop['height'] = ['text' => 'Высота','name'=>'height','type'=> 'text', 'value'=>$param["height"]];

			$div = Html::el('div', ['class'=>'inline fields']);
					$elem.= $div->startTag();
					foreach ($prop as $item){
						$el = Html::el('div', ['class'=>'field']);
						$el->create('label')->addText($item['text']);
						$el->create('input',['type'=>$item['type'],'name' => $item['name'],'value'=>$item['value']],$item['attrs']);
						$elem .= $el->render();
					}
			$elem.= $div->endTag();

			if (!empty($param["dir"])) {

				if ($param['searchPage'] == true){
					$page = "/" . $this->gridObject->model->where('id','=',$this->data['id'])->value('page');
				}
				$fileList = [];
				\File::fileList("site/files/".$param["dir"].$page."/", false, $fileList);
				foreach ($fileList as $key => $file) {
					if (preg_match('/([A-z\/0-9]+)\/([A-z]+)\/([A-z0-9.]+$)/', $file, $filePath)) {
						if ($filePath[3] == $data[1]) {
							$paths[$filePath[2]] = $filePath[1] ."/". $filePath[2] ."/";
							$finalPath[$filePath[2]] = $filePath[1] ."/". $filePath[2] ."/". $filePath[3];
						}
					}
				}

				$div = Html::el('div');
					if (file_exists(DOC_ROOT.$finalPath['full']) && !empty($finalPath['full'])) {
						$elem .= $div->addAttributes(['class' => 'j-grid-modal-image ui segment compact'])->startTag();
							$el = Html::el('a', ['href'=>DOC_HOST.$finalPath['full']]);
							$el->create('img',['src'=>DOC_HOST.$finalPath['thumb'],'class' => 'ui small rounded image']);
							$elem .= $el->render();
						$elem .= $div->endTag();
					}else{
						$elem .= $div->addAttributes(['class' => 'j-grid-modal-image'])->render();
					}

				if ($param['searchPage'] == true){
					$elem .= Html::el('input', ['type'=>'hidden','name'=>'searchPage','value'=> 1]);
				}

				$elem .= Html::el('input', ['type'=>'hidden','name'=>'dir','value'=>$param['dir']]);
			}
		}

		if ($param['elem'] == 'text'){

			$cols = !empty($param['cols'])?
				"cols='{$param['cols']}'":"";
			$rows = !empty($param['rows'])?
				"rows='{$param['rows']}'":"";

			if ($param["type"] === 'ckeditor'){
					!empty($class)?
					$class .= "ckeditor":
					$class = "class ='ckeditor'";
					$dataAttribute .= " data-ckeditor='{$param["config"]}' ";

					$this->ckeditorCount = $this->ckeditorCount .generatestring(8) ;

					$id = "id='ckeditor{$this->ckeditorCount}'";
			}
			$elem = "<textarea name='$data[0]' {$id} {$cols} {$rows} {$placeHolder} {$class} {$dataAttribute}>{$data[1]}</textarea>";
		}

		if ($param['elem'] == 'select'){
			$elem = Html::el('select',['name' => $data[0]])
					->addHtml($this->selectOptions($param['values'],$data[1]))
					->render();
		}

	return "<div class=\"field\" >
				{$label}
				{$elem}
			</div>
				";
		/*


		/*if ($param['elem'] == 'input' && $param['type'] == 'file') {
			var_dump('q');
		}*/
	}
	/*TODO ВЫНЕСТИ В ОТДЕЛЬНЫЙ КЛАСС */
	protected function selectOptions($options,$data)
	{

		$value = null;
		$html = null;
		$glue = null;

		if ($options['attribute']) {
			$attr = $this->gridObject->model->getAttribute($options['attribute']);
		}
		if (!empty($attr)) {
			$value = $attr;
		} else {
			if (!empty($options['attribute'])) {
				$value = $options['attribute'];
			}
		}

		$methodResult = \web\Model::findHashMethod($options, $value);

		if (!empty($methodResult) && !is_string($methodResult)) {
			if (isset($options['glue']) && $options['glue'] === 'number'){
				$glue = 1 ;
			}
				$html[] = Html::el('option',['selected'=>'selected','disabled' => 'disabled'])->addText('Выберете запись');
			foreach ($methodResult as $item) {
				$el = Html::el('option',['value' => $item['id']])->addText($this->getOptionText($item,$options['needles'],$glue));
					if ($item['id'] === $data){
						$el->addAttributes(['selected' => 'selected']);
					}
				$html[] = $el->render();
				if (is_int($glue)){
					$glue++ ;
				}
			}

			return implode('', $html);
		}
		new Exception('Method call string || empty');
		return false;
	}
	/*TODO ВЫНЕСТИ В ОТДЕЛЬНЫЙ КЛАСС */
	protected function getOptionText($item,$needles,$glue = null)
	{
		$needlesArray = null;

		foreach ($needles as $key => $needle){
			if (is_int($glue)){
				$glue = $glue . '.';
			}
			$needlesArray[] = $glue ." ". $item[$needle];
		}

		if (!is_null($needlesArray)){
			return implode('',$needlesArray);
		}

		return false;
	}
	/*TODO ВЫНЕСТИ В ОТДЕЛЬНЫЙ КЛАСС */
	protected function formElements($form)
	{
		$elements = null;
		foreach ($form as $key => $item){
			foreach ($this->data as $column => $value){
				if ($key == $column){
					$elements[] = $this->choiceElement($item,[$column,$value]);
				}
			}
		}

		return $elements;

	}
	/*TODO ВЫНЕСТИ В ОТДЕЛЬНЫЙ КЛАСС */
	protected function form($modalParams,$table)
	{

		if (!empty($modalParams['form'])){

			$model = $this->gridObject->model;

			if ($this->modalId != 0){
				$this->data = $model::where('id','=',$this->modalId)->first()->toArray();
			}else{
				foreach ($model->getFillable() as $fillable){
					if (!empty($modalParams['form'][$fillable])){
						$this->data[$fillable] = '';
					}
				}
			}


			$elements = implode(PHP_EOL,$this->formElements($modalParams['form']));


			$actions = !empty($modalParams['form']['_method']) ? ['_method' => $modalParams['form']['_method']] : null;
			$formAction = !empty($modalParams['form']['formAction']) ? $modalParams['form']['formAction'] : "/grid/gridModalUpdate/";

			//https://laravel.com/docs/5.7/routing#form-method-spoofing
			if ($this->modalId == 0) {
			//	$actions = ['_method' => "CREATE"];
				$formAction = "/grid/GridModalInsert/";
			}


			$form = "<form method='POST' class='{$this->uniqueId} ui large equal width form' action='{$formAction}' enctype='multipart/form-data'>
						{$this->formActions([$actions,'id' => $this->data['id'],'uniqueId' => $this->uniqueId])}
						{$elements}
						<button class='ui button blue'>Сохранить</button>
					</form>";

			return $form;
		}
		return false;
	}

	protected function createModal($params,$id)
	{

		$this->modalId = $id;

		$html = null;
		$title = null;
		$text = null;

		if (isset($params['modal'])){
			$title = $params['modal']['title'];
			$text = $params['modal']['text'];
		}

			$html = $this->modalTextAndTitle($title,$text);
			$html .= $this->form($params,$params['table']);

		return $html;
	}

	protected function modalTextAndTitle($title,$text)
	{

		$html = !empty($title)?"<h1>{$title}</h1>":"<h1>Модальное окно #{$this->modalId}</h1>\n";

		if (!empty($text)){
			$html .= "<div>{$text}</div>";
		}

		return $html;
	}

	public function initModal($id)
	{
		$params = $this->gridObject->getModalParams();

		return $this->createModal($params,$id);
	}

	public static function drawModalWindow($uniqueId)
	{
		return
			Html::el('div',['style' => 'display:none'])
				->addHtml(Html::el('div',[
					'class' => 'j-page-edit__dimodal ui segment',
					'data-modal-id' => $uniqueId
					]));
	}

	public static function setModalParams($params)
	{
		$modalParams = [];

		if (isset($params['modal']['title'])){
			$modalParams['modal']['title'] = $params['modal']['title'];
		}
		if (isset($params['modal']['text'])){
			$modalParams['modal']['text'] = $params['modal']['text'];
		}

		foreach ($params['modal']['form'] as $key => $param){

			$modalParams['form'][$key] = $param;

			if (!empty($param['values'])){

				$pattern = '/(^[A-Z][A-z]+)::([a-z]+[A-z]+)\(([a-z,]+)?\)/';
				if (preg_match($pattern, $param['values'], $output_array)) {

					unset($modalParams['form'][$key]['values']);

					$class = md5($output_array[1]);
					$method = md5($output_array[2]);
					$attribute =  !isset($output_array[3])?null:$output_array[3];

					$modalParams['form'][$key]['values']['class'] = $class;
					$modalParams['form'][$key]['values']['method'] = $method;
					$modalParams['form'][$key]['values']['attribute'] = $attribute;
					$modalParams['form'][$key]['values']['needles'] = $modalParams['form'][$key]['needles'];

					if (isset($modalParams['form'][$key]['glue'])){
						$modalParams['form'][$key]['values']['glue'] = $modalParams['form'][$key]['glue'];
					}
				}
			}

		}


		if (isset($params['form']['_method']) && is_array($params['form']['_method'])){
			$modalParams['form']['_method'] = $params['form']['_method'];
		}

		return $modalParams;
	}
}