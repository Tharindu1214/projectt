$(document).ready(function(){
	searchBackupList();
});

(function() {
	
	searchBackupList = function(){
		var data = '';
		$("#listing").html('Loading....');
		fcom.ajax(fcom.makeUrl('DatabaseBackupRestore','search'),data,function(res){
			$("#listing").html(res);
		});
	};
	
	restoreBackup = function( file ){
		if( !confirm(langLbl.confirmRestoreBackup) ){ return; }
		fcom.displayProcessing();
		fcom.updateWithAjax( fcom.makeUrl('DatabaseBackupRestore', 'restore',[file]), '', function(t) {
			return true;
		});
	};
	
	deleteBackup = function( file ){
		if( !confirm(langLbl.confirmDelete) ){ return; }
		fcom.displayProcessing();
		fcom.updateWithAjax( fcom.makeUrl('DatabaseBackupRestore', 'delete',[file]), '', function(t) {
			searchBackupList();
		});
	};
	
})();