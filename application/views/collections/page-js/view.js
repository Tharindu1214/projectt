$(document).ready(function(){
	searchCollections(document.frmSearchCollections);
});

(function() {
	var dv = '#listing';
	searchCollections = function(frm, append){
		
		var data = fcom.frmData(frm);
		
		
		fcom.ajax(fcom.makeUrl('Collections','search'), data, function(ans){
			$.mbsmessage.close();
			
				
				$(dv).html(ans);
		
			
		}); 
	};
	
	
})();