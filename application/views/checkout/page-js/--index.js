var loginDiv = '#login-register';
var addressDiv = '#address';
var addressFormDiv = '#addressFormDiv';
var addressDivFooter = '#addressDivFooter';
var addressWrapper = '#addressWrapper';
var addressWrapperContainer = '.address-wrapper';
var alreadyLoginDiv = '#alreadyLoginDiv';
var shippingSummaryDiv = '#shipping-summary';
var cartReviewDiv = '#cart-review';
var paymentDiv = '#payment';
var financialSummary = '.summary-listing';

function showLoginDiv()
{
	$('.step').removeClass("is-current");
	$(loginDiv).find('.step__body').show();
	$(loginDiv).find('.step__body').html(fcom.getLoader());
	fcom.ajax(fcom.makeUrl('Checkout', 'login'), '', function(ans) {
		$(loginDiv).find('.step__body').html(ans);
		$(loginDiv).addClass("is-current");
	});
}
function showAddressFormDiv()
{
	editAddress();
}
function showAddressList()
{
	loadAddressDiv();
	resetShippingSummary();
	resetPaymentSummary();
}
function resetAddress(){
	loadAddressDiv();
}
function showShippingSummaryDiv()
{
	return loadShippingSummaryDiv();
}
function showCartReviewDiv()
{
	return loadCartReviewDiv();
}
$("document").ready(function()
{

	$('.step').removeClass("is-current");

	if( !isUserLogged()){
		$(loginDiv).find('.step__body').show();
		$(loginDiv).find('.step__body').html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('Checkout', 'login'), '', function(ans) {
			$(loginDiv).find('.step__body').html(ans);

			$(loginDiv).addClass("is-current");
			loadFinancialSummary();
		});
	} else {

		$(alreadyLoginDiv).show();
		loadAddressDiv();
		loadFinancialSummary();
	}
});


(function() {
	setUpLogin = function(frm, v) {
		v.validate();
		if ( !v.isValid() ) return;
		fcom.ajax(fcom.makeUrl('GuestUser', 'login'), fcom.frmData(frm), function(t) {
			var ans = JSON.parse(t);
			if(ans.notVerified==1)
			{
				var autoClose = false;
			}else{
				var autoClose = true;
			}
			if( ans.status == 1 ){
				$.mbsmessage(ans.msg, autoClose, 'alert--success');
				location.href = ans.redirectUrl;
				return;
			}
			$.mbsmessage(ans.msg, autoClose, 'alert--danger');
		});
		return false;
	};

	guestUserLogin = function(frm, v) {
		v.validate();
		if ( !v.isValid() ) return;
		$.mbsmessage(langLbl.processing,false,'alert--process');
		fcom.ajax(fcom.makeUrl('GuestUser', 'guestLogin'), fcom.frmData(frm), function(t) {
			var ans = JSON.parse(t);
			if( ans.status == 1 ){
				$.mbsmessage(ans.msg, true, 'alert--success');
				location.href = ans.redirectUrl;
				return;
			}
			$.mbsmessage(ans.msg, true, 'alert--danger');
		});
		return false;
	};

	loadloginDiv = function(){
		fcom.ajax(fcom.makeUrl('Checkout', 'loadLoginDiv'), '', function(ans) {
			$(loginDiv).html(ans);
		});
	};

	loadFinancialSummary= function(){
		$(financialSummary).html( fcom.getLoader() );
		fcom.ajax(fcom.makeUrl('Checkout', 'getFinancialSummary'), '', function(ans) {
			$(financialSummary).html(ans);
		});
	};

	setUpRegisteration = function( frm, v ){
		v.validate();
		if ( !v.isValid() ) return;
		fcom.updateWithAjax(fcom.makeUrl('GuestUser', 'register'), fcom.frmData(frm), function(t) {

			if( t.status == 1 ){
				if(t.needLogin){
					window.location.href=t.redirectUrl;
					return;
				}
				else{
					loadAddressDiv();
				}
			}
		});
	};

	removeAddress = function(id){
		var agree = confirm(langLbl.confirmDelete);
		if( !agree ){
			return false;
		}
		data='id='+id;
		fcom.updateWithAjax(fcom.makeUrl('Addresses','deleteRecord'),data,function(res){
			loadAddressDiv();
		});
	};

	editAddress = function( address_id ){
		fcom.ajax(fcom.makeUrl('Checkout', 'editAddress'), 'address_id=' + address_id , function( ans ) {
			$(addressFormDiv).html( ans ).show();
			$(addressWrapper).hide();
			$(addressWrapperContainer).hide();
			$(addressWrapper).hide();
			$(addressFormDiv).addClass("is-current");
			/* $("#shipping-summary-inner").html( ans );
			fcom.scrollToTop("#shipping-summary");
			$(".sduration_id-Js").trigger("change"); */
		});
	};

	setUpAddress = function(frm){
		if ( !$(frm).validate() ) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Addresses', 'setUpAddress'), data, function(t) {
			if( t.status == 1 ){
				loadAddressDiv(t.ua_id);
			}
		});
	};


	setUpAddressSelection = function(elm){

		var shipping_address_id = $(elm).parent().parent().parent().find('input[name="shipping_address_id"]:checked').val();

		var billing_address_id = $(elm).parent().parent().parent().parent().find('input[name="billing_address_id"]:checked').val();

		var isShippingSameAsBilling = $('input[name="isShippingSameAsBilling"]:checked').val();

		var data = 'shipping_address_id='+shipping_address_id+'&billing_address_id='+billing_address_id+'&isShippingSameAsBilling='+isShippingSameAsBilling;
		fcom.updateWithAjax(fcom.makeUrl('Checkout', 'setUpAddressSelection'), data , function(t) {

			if( t.status == 1 ){
				if( t.loadAddressDiv ){
					loadAddressDiv();
				} else {
					if( t.hasPhysicalProduct ){
						$(shippingSummaryDiv).show();
						loadShippingSummaryDiv();
					} else {
						$(shippingSummaryDiv).hide();
						loadShippingAddress();
						loadCartReviewDiv();
					}
					//$(addressDivFooter).show();
				}
			}
		});
	};

	setUpShippingApi = function(frm){
		var data = fcom.frmData(frm);
		$(shippingSummaryDiv).html( fcom.getLoader() );
		fcom.ajax(fcom.makeUrl('Checkout', 'setUpShippingApi'), data , function(ans) {
			$(shippingSummaryDiv).html( ans );
			/* fcom.scrollToTop("#shipping-summary"); */
			$(".sduration_id-Js").trigger("change");
		});
	};

	getProductShippingComment = function(el, selprod_id){
		var sduration_id = $(el).find(":selected").val();
		$(".shipping_comment_"+selprod_id).hide();
		$("#shipping_comment_"+selprod_id + '_' + sduration_id).show();
	};

	getProductShippingGroupComment = function(el, prodgroup_id){
		var sduration_id = $(el).find(":selected").val();
		$(".shipping_group_comment_"+prodgroup_id).hide();
		$("#shipping_group_comment_"+prodgroup_id + '_' + sduration_id).show();
	};

	setUpShippingMethod = function(){
		var data = $("#shipping-summary select").serialize();
		fcom.updateWithAjax(fcom.makeUrl('Checkout', 'setUpShippingMethod'), data , function(t) {
			if( t.status == 1 ){
				loadFinancialSummary();
				loadShippingSummary();
				loadCartReviewDiv();
			}
		});
	};

	loadAddressDiv = function(ua_id){


		$(addressDiv).html( fcom.getLoader());
		fcom.ajax(fcom.makeUrl('Checkout', 'addresses'), '', function(ans) {
			$(addressDiv).html(ans);
			$('.section-checkout').removeClass('is-current');
			$(addressDiv).addClass('is-current');
			$(addressDiv).find(".address-"+ua_id +" label .radio").click();
		});
	};

	loadShippingAddress  = function(){
		fcom.ajax(fcom.makeUrl('Checkout', 'loadBillingShippingAddress'), '' , function(t) {
			$(addressDiv).html(t);
			/* fcom.scrollToTop("#alreadyLoginDiv"); */
		});
	};

	resetShippingSummary = function(){
		resetCartReview();
		fcom.ajax(fcom.makeUrl('Checkout', 'resetShippingSummary'), '' , function(ans) {
			$(shippingSummaryDiv ).html( ans );

		});
	};

	removeShippingSummary = function(){
		resetCartReview();
		fcom.ajax(fcom.makeUrl('Checkout', 'removeShippingSummary'), '' , function(ans) {


		});
	};

	resetCartReview = function(){
		fcom.ajax(fcom.makeUrl('Checkout', 'resetCartReview'), '' , function(ans) {
			$(cartReviewDiv ).html( ans );

		});
	};

	loadShippingSummary = function(){
		$(shippingSummaryDiv).show();
		$(shippingSummaryDiv).html( fcom.getLoader());

		fcom.ajax(fcom.makeUrl('Checkout', 'loadShippingSummary'), '' , function(ans) {
			$(shippingSummaryDiv ).html( ans );
			/* fcom.scrollToTop("#shipping-summary"); */

		});
	};

	changeShipping = function(){
		loadShippingSummaryDiv();
		resetCartReview();
		resetPaymentSummary();
	};

	loadShippingSummaryDiv = function(){
		$(shippingSummaryDiv).show();
		$(addressDiv).html(fcom.getLoader() );
		$(shippingSummaryDiv).append(fcom.getLoader() );
		/* $(shippingSummaryDiv+' .short-detail').append(fcom.getLoader() ); */
		loadShippingAddress();
		$('.section-checkout').removeClass('is-current');
		$(shippingSummaryDiv).addClass('is-current');
		$(shippingSummaryDiv + ".selected-panel-data").html( fcom.getLoader());
		fcom.ajax(fcom.makeUrl('Checkout', 'shippingSummary'), '' , function(ans) {
			$(shippingSummaryDiv ).html( ans );
			/* fcom.scrollToTop("#shipping-summary"); */
			$(".sduration_id-Js").trigger("change");
		});
	};

	viewOrder = function(){
		resetPaymentSummary();
		loadShippingSummary();
		loadCartReviewDiv();
	};

	resetPaymentSummary = function(){
		$(paymentDiv).removeClass('is-current');
		fcom.ajax(fcom.makeUrl('Checkout', 'resetPaymentSummary'), '', function(ans) {
			$(paymentDiv).html(ans);
		});
	};

	loadCartReviewDiv = function(){
		$(cartReviewDiv).html( fcom.getLoader() );
		$('.section-checkout').removeClass('is-current');
		$(cartReviewDiv).addClass('is-current');
		fcom.ajax(fcom.makeUrl('Checkout', 'reviewCart'), '', function(ans) {
			$(cartReviewDiv).html(ans);
		});
	};

	loadCartReview = function(){
		fcom.ajax(fcom.makeUrl('Checkout', 'loadCartReview'), '', function(ans) {
			$(cartReviewDiv).html(ans);
		});
	};

	loadPaymentSummary = function(){
		loadCartReview();
		$(paymentDiv).html( fcom.getLoader() );
		$('.section-checkout').removeClass('is-current');
		$(paymentDiv).addClass('is-current');
		fcom.ajax(fcom.makeUrl('Checkout', 'PaymentSummary'), '', function(ans) {
			$(paymentDiv).addClass('is-current');
			$(paymentDiv).html(ans);

			$("#payment_methods_tab  li:first a").trigger('click');

		});
	};

	walletSelection = function(el){
		var wallet = ( $(el).is(":checked") ) ? 1 : 0;
		var data = 'payFromWallet=' + wallet;
		fcom.ajax(fcom.makeUrl('Checkout', 'walletSelection'), data, function(ans) {
			loadPaymentSummary();
		});
	};

	getPromoCode = function(){
		if( isUserLogged() == 0 ){
			loginPopUpBox();
			return false;
		}

		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Checkout','getCouponForm'), '', function(t){
				$.facebox(t,'faceboxWidth');
				$("input[name='coupon_code']").focus();
			});
		});
	};

	applyPromoCode  = function(frm){
		if( isUserLogged() == 0 ){
			loginPopUpBox();
			return false;
		}
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);

		fcom.updateWithAjax(fcom.makeUrl('Cart','applyPromoCode'),data,function(res){
			$("#facebox .close").trigger('click');
			$.systemMessage.close();
			loadFinancialSummary();
			if($(paymentDiv).hasClass('is-current')){
				loadPaymentSummary();
			}
		});
	};

	triggerApplyCoupon = function(coupon_code){
		document.frmPromoCoupons.coupon_code.value = coupon_code;
		applyPromoCode(document.frmPromoCoupons);
		return false;
	};

	removePromoCode  = function(){
		fcom.updateWithAjax(fcom.makeUrl('Cart','removePromoCode'),'',function(res){
		loadFinancialSummary();
		if($(paymentDiv).hasClass('is-current')){
				loadPaymentSummary();
			}
		});
	};

	useRewardPoints  = function(frm){
		$.systemMessage.close();
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Checkout','useRewardPoints'),data,function(res){
			loadFinancialSummary();
			loadPaymentSummary();
		});
	};

	removeRewardPoints  = function(){
		$.systemMessage.close();
		fcom.updateWithAjax(fcom.makeUrl('Checkout','removeRewardPoints'),'',function(res){
			loadFinancialSummary();
			loadPaymentSummary();
		});
	};

	resetCheckoutDiv = function(){
		if($(paymentDiv).hasClass('is-current')){
				removeShippingSummary();
				resetPaymentSummary();
				loadShippingSummaryDiv();

		}else if($(cartReviewDiv).hasClass('is-current')){
				removeShippingSummary();
				loadShippingSummaryDiv();

				resetCartReview();

		}else if($(shippingSummaryDiv).hasClass('is-current')){
				loadShippingSummaryDiv();
		}

	};
})();
