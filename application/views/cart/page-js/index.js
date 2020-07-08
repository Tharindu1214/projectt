$(document).ready(function(){
	listCartProducts();
});
(function() {
	listCartProducts = function(){
		$('#cartList').html( fcom.getLoader() );
		fcom.ajax(fcom.makeUrl('Cart','listing'),'',function(res){
			$("#cartList").html(res);
		});
	};

	getPromoCode = function(){
		if( isUserLogged() == 0 ){
			loginPopUpBox(true);
			return false;
		}

		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Checkout','getCouponForm'), '', function(t){
				$.facebox(t,'faceboxWidth medium-fb-width');
				$("input[name='coupon_code']").focus();
			});
		});
	};

	triggerApplyCoupon = function(coupon_code){
		document.frmPromoCoupons.coupon_code.value = coupon_code;
		applyPromoCode(document.frmPromoCoupons);
		return false;
	};

	applyPromoCode  = function(frm){
		if( isUserLogged() == 0 ){
			loginPopUpBox(true);
			return false;
		}
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Cart','applyPromoCode'),data,function(res){
			$("#facebox .close").trigger('click');
			$.systemMessage.close();
			listCartProducts();
		});
	};

	goToCheckout = function(){
		if( isUserLogged() == 0 ){
			loginPopUpBox(true);
			return false;
		}
		document.location.href = fcom.makeUrl('Checkout');
	};

	removePromoCode  = function(){
		fcom.updateWithAjax(fcom.makeUrl('Cart','removePromoCode'),'',function(res){
			listCartProducts();
		});
	};

	moveToWishlist = function( selprod_id, event, key ){
		event.stopPropagation();
		if( isUserLogged() == 0 ){
			loginPopUpBox();
			return false;
		}

		fcom.ajax(fcom.makeUrl('Account','moveToWishList', [selprod_id]),'',function( resp ){
			removeFromCart( key );
		});
	};

	addToFavourite = function( key, product_id ){
		if( isUserLogged() == 0 ){
			loginPopUpBox();
			return false;
		}
		var data = 'product_id='+product_id;
		$.mbsmessage.close();
		fcom.updateWithAjax(fcom.makeUrl('Account', 'toggleProductFavorite'), data, function(ans) {
			if( ans.status ){
				removeFromCart( key );
			}
		});
	};

})();
