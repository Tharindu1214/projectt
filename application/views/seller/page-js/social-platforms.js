$(document).ready(function(){
	searchSocialPlatforms();
});
(function() {
	var runningAjaxReq = false;
	var dv = '#listing';

	reloadList = function() {
		searchSocialPlatforms();
	};

	searchSocialPlatforms = function (form){
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		$(dv).html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('Seller','socialPlatformSearch'),data,function(res){
			$('.btn-back').addClass('d-none');
			$(dv).html(res);
		});
	};

	addForm = function( id ) {
		fcom.ajax(fcom.makeUrl('Seller', 'socialPlatformForm', [id]), '', function(t) {
			$('.btn-back').removeClass('d-none');
			$(dv).html(t);
		});
	};

	setup = function( frm ) {
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Seller', 'socialPlatformSetup'), data, function(t) {
			$.mbsmessage.close();
			reloadList();
			if ( t.langId > 0 ) {
				addLangForm( t.splatformId, t.langId );
				return ;
			}

		});
	};

	addLangForm = function( splatformId, langId ){
		fcom.ajax(fcom.makeUrl('Seller', 'socialPlatformLangForm', [splatformId, langId]), '', function(t) {
			$(dv).html(t);
		});
	};

	setupLang = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Seller', 'socialPlatformLangSetup'), data, function(t) {
			$.mbsmessage.close();
			reloadList();
			if ( t.langId > 0 ) {
				addLangForm(t.splatformId, t.langId);
				return ;
			}
		});
	};

	deleteRecord = function(id){
		if(!confirm(langLbl.confirmDelete)){ return; }
		data='splatformId='+id;
		fcom.updateWithAjax(fcom.makeUrl('Seller', 'deleteSocialPlatform'),data,function(res){
			reloadList();
		});
	};
	cancelForm = function(frm){
		reloadList();
		$(dv).html('');
	};
})();
