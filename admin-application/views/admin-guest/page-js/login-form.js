(function() {
	login = function(frm, v) {
		if (!$(frm).validate()) return;
		if (!v.isValid()) return;
		var data = fcom.frmData(frm);
		$.systemMessage(langLbl.processing,'alert--process');
		fcom.ajax(fcom.makeUrl('AdminGuest', 'login'), data, function(t) {
			try{
				t = $.parseJSON(t);
				if(t.errorMsg)
				{
					$.systemMessage(t.errorMsg,'alert--danger',true);
					return false;
				}
				$.systemMessage(t.msg,'alert--success',true);
			}
			catch(exc){
				console.log(exc);
			}
			/* location.href = fcom.makeUrl(); */
			location.href = t.redirectUrl;
		});
	}

})();
