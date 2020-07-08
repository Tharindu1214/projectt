$("document").ready(function(){
	$(".buySubscription--js").on('click', function(event){
		event.preventDefault();
		if( $(this).parent().find('input[name=packages]:checked').val()=='' ||  $(this).parent().find('input[name=packages]:checked').val()== 0||  $(this).parent().find('input[name=packages]:checked').val()== undefined){
			$.mbsmessage(langLbl.selectPlan,true,'alert--danger');
			return false;
		}

		if(currentActivePlanId!=undefined && currentActivePlanId ==  $(this).parent().find('input[name=packages]:checked').val() ){
			$.mbsmessage(langLbl.alreadyHaveThisPlan,true,'alert--danger');
			return false;
		}

		/* $packageId = $(this).attr('data-id'); */

		$spplan_id = $(this).parent().find('input[name=packages]:checked').val();

		subscription.add( $spplan_id, true);
		return false;
	});
	/* $(".buyFreeSubscription").on('click', function(event){
		event.preventDefault();
		$packageId = $(this).attr('data-id');

		subscription.add( $packageId, true , true);
		return false;
	}); */
});

function htmlDecode(input){
  var e = document.createElement('div');
  e.innerHTML = input;
  return e.childNodes.length === 0 ? "" : e.childNodes[0].nodeValue;
}
