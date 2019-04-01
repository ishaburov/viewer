
function initCkeditors(dimodal) {
	dimodal.find("[id^='ckeditor']").each(function(){
		if( $(this).is("[data-ckeditor='micro']") )
		{
			console.log("init ckeditor",$(this).attr("id"), "micro" )
			CKEDITOR.replace( $(this).attr("id"), {
				customConfig : 'custom/micro_config.js?10',
			});
		}
		else if( $(this).is("[data-ckeditor='short']") )
		{
			console.log("init ckeditor",$(this).attr("id"), "short" )
			CKEDITOR.replace( $(this).attr("id"), {
				customConfig : 'custom/short_config.js?10',
			});
		}
		else
		{
			console.log("init ckeditor",$(this).attr("id"), "full" )
			CKEDITOR.replace( $(this).attr("id"),{
				customConfig : 'custom/full_config.js?10',
			} );
		}
	})
}

function initDatePicker(modal_id) {

	let date = new Date(),
		dateString,
		datepicker = $("."+modal_id).find('input[type="datetime"]');
	if (datepicker.length > 0 ){
		datepicker.each(function () {

			let timestamp = $(this).val() * 1000;
			if(timestamp > 0){
				date.setTime(timestamp);

				let day = date.getDate(),
					month = date.getMonth() + 1,
					year = date.getFullYear();

				day <= 9?day = "0" + day:	day;
				month <= 9?month = "0" + month:month;
				dateString = day + "." + month +  "." + year;

				$(this).attr('value',dateString);
			}else {
				date = null;
			}

			let datePickerParams = {
				startDate:date,
				dateFormat: 'dd.mm.yyyy',
				onRenderCell: function (dates, cellType) {
					let needle = null;
					let current = null;
					if (date > 0 ){
						current = dates.toDateString();
						needle = date.toDateString();
					}

					// Add extra element, if `eventDates` contains `currentDate`
					if (cellType == 'day' &&  needle === current && needle != null) {
						return {
							classes: "datepicker-selected-date",
							html: date.getDate()
						}
					}
				},

			};

			$(this).datepicker(datePickerParams);

			$('.datepicker-selected-date').css({background:"#7d97ff"})
		});
		$('.datepicker').css({zIndex:1001});
	}
}

function initEditForms($dimodal,modal_id){

	let $form = $("."+modal_id);

	$form.submit(function(e) {
		let form = $(this);
		//let uniqueId = $(this).data('uniqueId');

		let	url = form.attr('action');
		let	formData = new FormData(this);

		form.find('button').text('Сохраняю').attr('disabled','disabled').removeClass('btn-light');

		if (CKEDITOR.instances) {
			for (instance in CKEDITOR.instances) CKEDITOR.instances[instance].updateElement();
		}

		let imageUploader = form.find('.j-grid-modal-image');

			imageUploader.addClass('loading');


		$.ajax({
			type: "POST",
			url: url,
			data: formData, // serializes the form's elements.
			cache: false,
			contentType: false,
			processData: false,
			success: function(data)	{
				imageUploader.removeClass('loading');
				if(data.status == 1){

					reinitRowDataTable(data.uniqueId,data.id,data.method);

					if (!jQuery.isEmptyObject(data.images)) {
						for (key in data.images){
							imageUploader.addClass('ui segment compact');
							imageUploader.html('');
							let link = $('<a>').attr('href',data.images[key].full).appendTo(imageUploader);
							$('<img>',{class:"ui small rounded image"}).attr('src',data.images[key].thumb).appendTo(link);
						}
					}

					$form.find('button').text('Сохранено').removeClass('blue').addClass('green');
					setTimeout(function(){
						$form.find('button').text('Отправить').removeAttr('disabled').removeClass('green').addClass('blue');
						//$dimodal.dimodal("hide");
					}, 1500);
					/*if( a.reload ){
					 $(".j-di-tabs__autoload[data-load='"+a.reload+"']").click();
					 }*/
				}else{
					if(data.error) {
						$form.find('button').text('Ошибка').removeClass('green').addClass('red');
						setTimeout(function(){
							$form.find('button').text('Отправить').removeAttr('disabled').removeClass('red').addClass('blue');
						}, 1500);
					}
				}
			}
		});

		e.preventDefault(); // avoid to execute the actual submit of the form.
	});

}

function getDataTable(table,columns) {

	let settings = {
		columns:columns,
		processing:true,
		serverSide:true,
		paging: true,
		//select: true,
		'language': {
			'url': '//cdn.datatables.net/plug-ins/1.10.19/i18n/Russian.json'
		},
		ajax:{
			url: "/grid/gridInit/",
			type: "POST",
			data: {
				uniqueId: table,
			},
		}
	};
			let initedTable = $('#' + table).DataTable(settings);

			if ($('#' + table).find("[data-column='#']").length > 0){
				initedTable.order([1, 'asc']).draw();
			}else{
				initedTable.order([0, 'asc']).draw();
			}
}

function UpdateRowDataTable(uniqueId,setData,rowId) {

	let row = $('.j-grid-edit-table-modal[data-id="' + rowId + '"]').closest('tr');

	$.each($('#' + uniqueId).dataTable().api().data(), function () {
		if (this.id == rowId) {
			$('#' + uniqueId).dataTable().api().row(row).data(setData.data[0]);
		}
	});
}

function InsertRowDataTable(uniqueId,setData) {
	$('#' + uniqueId).dataTable().api().row.add(setData.data[0]);
}

function reinitRowDataTable(uniqueId,rowId,method) {
	console.log(uniqueId,rowId,method);

	$.ajax({
		url:"/grid/gridUpdateData/",
		data:{uniqueId:uniqueId,id:rowId},
		success:function (data) {

			if (!jQuery.isEmptyObject(uniqueId)) {
				if ($.fn.dataTable.isDataTable('#' + uniqueId)) {

					if (method == 'update') {
						UpdateRowDataTable(uniqueId,data,rowId)
					}

					if (method == 'insert') {
						InsertRowDataTable(uniqueId,data)
					}
				}
			}
		},
	});
}

function initDataTable($this,filter) {
	let table =  $($this);
	let uniqueId = table.data('uniqueId');

	$.ajax({
		method:"POST",
		url:'/grid/getGridColumns/',
		data:{uniqueId:uniqueId},
		success:function(result){
			console.log(result);
			if (result.status == 1){
				getDataTable(uniqueId,result.columns,filter);
			}
		}
	});
}

$(function () {

	$('.dataTable__container').each(function () {
		initDataTable(this);
	});

	$(document).on("click",".j-grid-edit-table-modal",function(e){
		e.preventDefault();
		let modal_id = $(this).closest('.dataTable__container').data('uniqueId');
		let dimodal = $('.j-page-edit__dimodal[data-modal-id='+modal_id+']');

		let editRow = $(this).closest('tr');
		let id = $(this).data("id");

		dimodal.addClass('loading');

		$.ajax({
			url:"/grid/gridModalInit/",
			data:{id:id, uniqueId:modal_id},
			success:function(result){
				console.log(result);
				if(result.status == 1){
					dimodal.removeClass('loading');
					dimodal.html( result.html );
					initEditForms(dimodal,modal_id,editRow);
					initDatePicker(modal_id);
					initCkeditors(dimodal);
				}
			}
		});
		dimodal.dimodal({
			"hideOnBackgroundClick": false,
			/*"autoShow": true,*/
			"hideOnEscape": false,
		}).dimodal("show");
	})

	$(document).on("click",".j-grid-delete-row",function () {
		let deletedRow = $(this).closest('tr');
		let id = $(this).data('id');
		let uniqueId = $(this).closest('.dataTable__container').data('uniqueId');

		if (confirm('Удалить?')){
			$.ajax({
				url:"/grid/gridDeleteRow/",
				data:{id:id,uniqueId:uniqueId},
				success:function(result) {
					if (result.status == 1){
						console.log(deletedRow);
						$('#'+uniqueId).dataTable().api().row( deletedRow).remove();
						deletedRow.fadeOut(500);
						setTimeout(function () {
							deletedRow.remove();
							$('#'+uniqueId).dataTable().api().row( deletedRow).draw();
						},500);
					}
				}
			})
		}
	});
})