(function() {
	var runningAjaxReq = false;
	setUpShopSpam = function(frm){
		if ( !$(frm).validate() ) return;
		if( runningAjaxReq == true ){
			console.log(langLbl.requestProcessing);
			return;
		}
		runningAjaxReq = true;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Shops', 'setUpShopSpam'), data, function(t) {
			runningAjaxReq = false;
			if( t.status ){
				/* window.location.href = fcom.makeUrl('Shops', 'reportSpam', [frm.elements["shop_id"].value]); */
				setTimeout("pageRedirect("+frm.elements["shop_id"].value+")", 1000);
			}
		});
		return false;
	}
})();
function pageRedirect(shopId) {
	window.location.replace(fcom.makeUrl('Shops', 'reportSpam',[shopId]));
}