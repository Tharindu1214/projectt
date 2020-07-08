$(document).ready(function(){
	moveToTargetDiv('li.is--active','#tabUl');
});

$(document).on("click","#tabUl li", function(){
	$('#tabUl li').removeClass('is--active');
	$(this).addClass('is--active');
	moveToTargetDiv('li.is--active','#tabUl');
});

function moveToTargetDiv(target, outer){
	/* alert('hi'); */
	var out = $(outer);
	var tar = $(target);
	var x = out.width();
	var y = tar.outerWidth(true);
	var z = tar.index();
	var q = 0;
	var m = out.find('li');
	
	for(var i = 0; i < z; i++){
		q+= $(m[i]).outerWidth(true)+4;
	}
	
	$('#tabUl').animate({
		scrollLeft: Math.max(0, q )
	}, 800);
	return false;
}
	
(function() {
	setupAffiliateRegister = function(frm){
		if (!$(frm).validate()) return;		
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('GuestAffiliate', 'setupAffiliateRegister'), data, function(t) {
			if( t.affiliate_register_step_number ){
				callAffilitiateRegisterStep( t.affiliate_register_step_number );
			}
		});
	};
	
	callAffilitiateRegisterStep = function( registeration_step_number ){
		$("#register-form-div").html( fcom.getLoader() );
		fcom.ajax( fcom.makeUrl( 'GuestAffiliate', 'affiliateRegistrationStep', [registeration_step_number] ), '', function(t){
			$("#register-form-div").html( t );
		});
	};
})();