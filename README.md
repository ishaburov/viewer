
	Параметры ::: columns 
	   Название колонки из таблицы  --- должно быть точь в точь как в бд
	       Параметры одной колонки ::: 
	       'tag'=>'a',   // любой тег который хотим 
	       'tagClass' => 'ui green button',   // любой класс для элемента 
	       'hrefImage' => 'gallery', // Указываем папку где находятся картинки /// TODO /// Сделать ещё ссылки для файлов 
	       'searchPage' => 'true',  //  Если используется страница , то сокращаем поиск 
	       'type' => 'dateTime',   // выбираем тип отображения , пока есть date---dd.mm.yyyy   dateTime http://php.net/manual/en/class.datetime.php 
	       'value' => 'Users::getUserById()',   TODO переименовать в userFunction  Передаем метод с которым хотим связать колонку , значение передается автоматически если есть это в методе пример :
	public static function getUserById($id){
	     return self::where('id','=',$id)->value('fio');
	}


	Общие параметры :::

	serverSide :: true || false     метод для загрузки таблицы --- если параметр false (по умолчанию , ограничение в 100 строк)
	header  Параметры ::::  clip015 
	1.title => заголовок 
	2.text => и текст
	3. сейчас автоматом появляется кнопка добавить запись при добавлении header , сделать для нее надстройку
	editType modal || default    --- нет параметра , нет редактирования
	- modal в модальном окне
	-default ?edit


	Параметры для модального окна
	'title' => 'Таблица #1',     // заголовок
	'text' => 'Редактируем #',  // текст под заголовком

	form: 
	-formAction   :: кастомный action для формы можно задать в gridController   например actionGridTest
	в форме будет так (/grid/gridTest/)

	Форма Ни как не связана с полями которые мы задавали в параметрах таблицы 

	'имя колонки в таблице'   => [
	'elem' => //тип элемента       виды :::   select    input:datetime   input:file    input:text   text:ckeditor    input:checkbox
	'class' // класс для элемента
	    'placeHolder' // placeHolder для атрибута
	    'label' / // текст для label   


	<?= Grid::init(
		[
			'model' => new GalleriesItems(),
			'linkedTables' => ['className' => GalleryItems::class ,'method' => 'linkedTableTestQweerty'],
			'linkedColumns' => ['className' => GalleryItems::class ,'method' => 'linkedColumn1'],
			'startFilter' => ['className' => GalleryItems::class ,'method' => 'filterQ'],
			'serverSide' => true,
			'count' => true, // решить проблему при работе с serverSide +
			'header' => ['title'=>'Заголовок','text'=>'textttt'],
			'editType' => 'modal',
			'columns' => [
				'id',
				'time'=> [
					'tag' => 'div',
					'tagClass' => 'ui green button',
					'type' => 'dateTime',
				],
				'filename'=>[
					'label' => 'Имя файла',
					'tag'=>'a',
					'hrefImage' => 'gallery',
					'searchPage' => 'true',
					'orderable' => 'false',
				],
				'title',
				'user' => [
					'tag' => 'div',
					'tagClass' => 'ui red button',
					'filter' => 'select',
					'filterUserFunction' => \core\models\repository\Users::getUserForSelect(),
				],
				'page' => [
					'filter' => 'select',
					'filterUserFunction' => \core\models\repository\Pages::getPages(),
				],
			],

			'modal' => [
				// input: type checkbox|hidden|text TODO file / datetime / color / password
				// text : ckeditor / инициализирует ckeditor , text <textarea>
				'form' => [
					'page' => [
						'elem' => 'select',
						'values' =>  'Pages::getPages()',
						'needles' => ['value'],
						'glue' => 'number'
					],
					'filename' => [
						'elem' => 'input:file', //тип элемента
						'dir' => 'gallery',  //  site/files/grid/test/imageName.jpg ??? Как можно передать страницу
						'searchPage' => true,
						'width' => 400,
						'height' => 300,
					],
					'user' => [
						'elem' => 'select',
						'values' =>  'Users::getUsers(id,fio)',
						'needles' => ['fio'],
					],

					'time' => [
						'elem' => 'input:datetime',
						'label' => 'Дата', // лабел для инпута
					],

					'time_start' => [
						'elem' => 'input:datetime',
						'label' => 'Дата старта', // лабел для инпута
					],
					'time_end' => [
						'elem' => 'input:datetime',
						'label' => 'Дата финиша', // лабел для инпута
					],
				],
			],
		]
	);?>
	<?/*= Grid::init(
		[
			'model' => new GalleriesItems(),
			'linkedTables' => ['className' => GalleryItems::class ,'method' => 'linkedTableTestQweerty'],
			'linkedColumns' => ['className' => GalleryItems::class ,'method' => 'linkedColumn1'],
			'startFilter' => ['className' => GalleryItems::class ,'method' => 'filterUser1','value' => 1],
			'count' => true, // решить проблему при работе с serverSide +
			'header' => ['title'=>'Заголовок','text'=>'textttt'],
			'editType' => 'modal',
			'columns' => [
				'id',
				'time'=> [
					'tag' => 'div',
					'tagClass' => 'ui green button',
					'type' => 'dateTime',
				],
				'filename'=>[
					'label' => 'Имя файла',
					'tag'=>'a',
					'hrefImage' => 'gallery',
					'searchPage' => 'true',
					'orderable' => 'false',
				],
				'user' => [
					'tag' => 'div',
					'tagClass' => 'ui red button',
					//'value' =>  'users.fio',
				],
				'page' => [
					//'value' => 'pages.title'
				],
			],

			'modal' => [
				// input: type checkbox|hidden|text TODO file / datetime / color / password
				// text : ckeditor / инициализирует ckeditor , text <textarea>
				'form' => [
					'filename' => [
						'elem' => 'input:file', //тип элемента
						'dir' => 'gallery',  //  site/files/grid/test/imageName.jpg ??? Как можно передать страницу
						'searchPage' => true,
						'width' => 400,
						'height' => 300,
					],
					'page' => [
						'elem' => 'select',
						'values' =>  'Pages::getPages()',
						'needles' => ['longname'],
						'glue' => 'number'
					],
					'user' => [
						'elem' => 'select',
						'values' =>  'Users::getUsers(id,fio)',
						'needles' => ['fio'],
					],
					'time' => [
						'elem' => 'input:datetime',
						'label' => 'Дата', // лабел для инпута
					],
					'time_start' => [
						'elem' => 'input:datetime',
						'label' => 'Дата старта', // лабел для инпута
					],
					'time_end' => [
						'elem' => 'input:datetime',
						'label' => 'Дата финиша', // лабел для инпута
					],
				],
			],
		]
	);*/?>
	<?= Grid::init([
		'model' => new Settings(),
		//'columns' => ['*'],
		'editType'=>'modal',
		'serverSide' => true,
		'modal' => [
			'form' => [
				'formAction' => '',
				/*'id' => [
					'elem' => 'input:text', //тип элемента
					'class' => 'test', // класс элемента
					'placeHolder' => 'Айди', // атрибут , как хотим там и назовем
					'label' => 'Это Айди', // лабел для инпута
					'data' => ['data-required = "1"', 'data-test = "2"']
				],*/
				'key' => [
					'elem' => 'input:text',
				],
				'val' => [
					'elem' => 'input:text',
				],
				'comment' => [
					'elem' => 'text:ckeditor',
					'config' => 'micro',
				],
			],
		],
	]);?>







