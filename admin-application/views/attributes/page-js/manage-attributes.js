$(document).ready(function(){
	searchAttrListing(document.frmAttrSearch);
});
(function() {
	langForm = function( attr_id, lang_id ){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Attributes', 'langForm', [attr_id, lang_id]), '', function(t) {
				$.facebox(t,'faceboxWidth');
			});
		});
	};
	
	reloadAttrList = function() {
		var frm = document.frmAttrSearch;
		searchAttrListing(frm);
	}
	
	setupAttrLang =  function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);	
		fcom.updateWithAjax(fcom.makeUrl('Attributes', 'langSetup'), data, function(t) {
			reloadAttrList();
			if (t.lang_id > 0 ) {
				langForm(t.attr_id, t.lang_id);
				return ;
			}
			$(document).trigger('close.facebox');
			return ;
		});
	};
	
	searchAttrListing = function(form){		
		/*[ this block should be written before overriding html of 'form's parent div/element, otherwise it will through exception in ie due to form being removed from div */
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		/*]*/
		
		$("#listing").html('Loading....');
		
		fcom.ajax(fcom.makeUrl('Attributes','searchAttributes'),data,function(res){
			$("#listing").html(res);
		}); 
	};
})();