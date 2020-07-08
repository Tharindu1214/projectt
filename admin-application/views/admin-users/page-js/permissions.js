$(document).ready(function(){
	searchAdminUsersRoles(document.frmAdminSrchFrm);
});
(function() {
	var runningAjaxReq = false;
	var dv = '#listing';
	
	reloadList = function() {
		var frm = document.frmAdminSrchFrm;
		searchAdminUsersRoles(frm);
	};	
	
	searchAdminUsersRoles = function(form){		
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		$(dv).html(fcom.getLoader());		
		fcom.ajax(fcom.makeUrl('AdminUsers','roles'),data,function(res){
			$(dv).html(res);			
		});
	};
	
	updatePermission = function(moduleId,permission){
		if(1 > moduleId) {
			if(!(permission = $('.permissionForAll').val()))
			{
				return false;
			}
			
		}
	
		data = fcom.frmData(document.frmAdminSrchFrm);				
		fcom.updateWithAjax(fcom.makeUrl('AdminUsers', 'updatePermission',[moduleId,permission]), data, function(t) {
			if(t.moduleId==0)
			{
				searchAdminUsersRoles(document.frmAdminSrchFrm);
			}
		});
	};
	
})();	
