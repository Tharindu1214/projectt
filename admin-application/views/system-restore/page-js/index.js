$(function () {
	toggleStatus = function(e,obj){
		if(!confirm(langLbl.confirmUpdate)){
			e.preventDefault();
			return;
		}
		fcom.displayProcessing();
		var isChecked = parseInt(obj.id);
		fcom.ajax(fcom.makeUrl('systemRestore','updateSetting',[isChecked]), '',function(res){
		var ans = $.parseJSON(res);
			if( ans.status == 1 ){
				fcom.displaySuccessMessage(ans.msg);
				$(obj).toggleClass("active");
			} else{
				fcom.displayErrorMessage(ans.msg);
			}
		});
	};
})