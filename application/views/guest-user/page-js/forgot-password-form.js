(function() {
	forgot = function(frm, v) {
		v.validate();
		if (!v.isValid()) return;		
		fcom.updateWithAjax(fcom.makeUrl('GuestUser', 'forgotPassword'), fcom.frmData(frm), function(t) {
			if( t.status == 1){
				location.href = fcom.makeUrl('GuestUser', 'loginForm');
			}else{
				$.systemMessage(t.msg,'alert--danger');				
			}
			$.mbsmessage.close();
			return;
		});
	};
})();