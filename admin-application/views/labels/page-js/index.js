$(document).ready(function(){
	searchLabels(document.frmLabelsSearch);
});

(function(){
	var currentPage = 1;
	var runningAjaxReq = false;
	var dv = '#listing';

	goToSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmLabelsSrchPaging;
		$(frm.page).val(page);
		searchLabels(frm);
	};

	reloadList = function() {
		var frm = document.frmLabelsSrchPaging;
		searchLabels(frm);
	};

	searchLabels = function(form){
		$(dv).html(fcom.getLoader());
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		fcom.ajax(fcom.makeUrl('Labels','search'),data,function(res){
			$(dv).html(res);
		});
	};

	labelsForm = function(labelId, type){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Labels', 'form', [labelId, type]), '', function(t) {
				$.facebox(t,'faceboxWidth');
			});
		});
	};

	setupLabels = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Labels', 'setup'), data, function(t) {
			reloadList();
			$(document).trigger('close.facebox');
		});
	};

	clearSearch = function(){
		document.frmLabelsSearch.reset();
		searchLabels(document.frmLabelsSearch);
	};

	exportLabels = function(){
		document.frmLabelsSearch.action = fcom.makeUrl( 'Labels', 'export' );
		document.frmLabelsSearch.submit();
	};

	importLabels = function(){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Labels', 'importLabelsForm'), '', function(t) {
				$.facebox(t,'faceboxWidth');
			});
		});
	};

	submitImportLaeblsUploadForm = function ( ){
		var data = new FormData(  );
		$inputs = $('#frmImportLabels input[type=text],#frmImportLabels select,#frmImportLabels input[type=hidden]');
		$inputs.each(function() { data.append( this.name,$(this).val());});

		$.each( $('#import_file')[0].files, function(i, file) {
			$('#fileupload_div').html(fcom.getLoader());
			data.append('import_file', file);
			$.ajax({
				url : fcom.makeUrl('Labels', 'uploadLabelsImportedFile'),
				type: "POST",
				data : data,
				processData: false,
				contentType: false,
				success: function(t){

					try {
						var ans = $.parseJSON(t);
						if( ans.status == 1 ){
							fcom.displaySuccessMessage(ans.msg);
							//$.systemMessage( ans.msg, 'alert--success' );
							reloadList();
							$(document).trigger('close.facebox');
						} else {
							fcom.displayErrorMessage(ans.msg);
							//$.systemMessage( ans.msg, 'alert--danger' );
							$('#fileupload_div').html('');
						}
						//productImages( $('#frmImportLabels input[name=product_id]').val() );

					}
					catch(exc){
						//productImages( $('#frmImportLabels input[name=product_id]').val() );
						fcom.displayErrorMessage(t);
						//$.systemMessage( t,'alert--danger' );
					}
				},
				error: function(jqXHR, textStatus, errorThrown){
					alert("Error Occured.");
				}
			});
		});
	};

	updateFile = function(labelType = 1){
		fcom.updateWithAjax(fcom.makeUrl('Labels', 'updateJsonFile', [labelType]), '', function(ans) {
			//var ans = $.parseJSON(t);
			if( ans.status == 1 ){
				fcom.displaySuccessMessage(ans.msg);
			} else {
				fcom.displayErrorMessage(ans.msg);
			}
		});
	};
})()
