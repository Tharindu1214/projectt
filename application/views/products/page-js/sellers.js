$("document").ready(function(){	

	
	$(".btnProductBuy--js").on('click', function(event){
		event.preventDefault();
		
		var selprod_id = $(this).attr('data-id');
		var quantity = $(this).attr('data-min-qty');
		cart.add( selprod_id, quantity, true);
		return false;
	});
	
	$(".btnAddToCart--js").on('click', function(event){
		event.preventDefault();
	 	var data = $("#frmBuyProduct").serialize();
			var selprod_id = $(this).attr('data-id');
			var quantity = $(this).attr('data-min-qty');
    			data = "selprod_id="+selprod_id+"&quantity="+quantity;
				
			fcom.updateWithAjax(fcom.makeUrl('cart', 'add' ),data, function(ans) {
					if (ans['redirect']) {
						location = ans['redirect'];
						return false;
					}
					
					$('span.cartQuantity').html(ans.total);
					/* $('html, body').animate({ scrollTop: 0 }, 'slow');
					$('html').toggleClass("cart-is-active");
					$('.cart').toggleClass("cart-is-active"); */
					$('#cartSummary').load(fcom.makeUrl('cart', 'getCartSummary'));
					});
			return false;
		}); 
});

