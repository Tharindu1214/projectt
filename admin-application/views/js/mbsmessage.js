(function($){
	$.mbsmessage=function(data, autoclose, cls){
		
		$.mbsmessage.loading();
		$.mbsmessage.fillMbsmessage(data, cls);
		if(autoclose) $.mbsmessage.startTimer();
	};
	$.extend($.mbsmessage, {
		settings:{
			closeimage:siteConstants.webroot + 'img/mbsmessage/close.gif',
			leftimage:siteConstants.webroot + 'img/mbsmessage/left.png',
			rightimage:siteConstants.webroot + 'img/mbsmessage/right.png',
			mbshtml: '\
			<div id="mbsmessage" class="alert alert--positioned-bottom record-scroll"> \
			<div class="close"></div> \
			<div class="content">Content</div> \
			</div>'
		},
		loading: function(){
			initialize();
			$('#mbsmessage').show();
		},
		fillMbsmessage:function(data, cls){
			if(cls){
				
				 $('#mbsmessage').removeClass('alert--process');
				 $('#mbsmessage').removeClass('alert_danger');
				 $('#mbsmessage').removeClass('alert_success');
			
				 $('#mbsmessage').addClass(cls);
			}
			$('#mbsmessage .content').html(data);
			$('#mbsmessage').fadeIn();
			//$('#mbsmessage').css({top:10, left:10});
		},
		close:function(){
			$(document).trigger('close.mbsmessage');
		},
		startTimer:function(){
			if($.mbsmessage.timer) clearTimeout($.mbsmessage.timer);
			if( CONF_AUTO_CLOSE_SYSTEM_MESSAGES == 1 ){
				$.mbsmessage.timeInterval=CONF_TIME_AUTO_CLOSE_SYSTEM_MESSAGES;
			}
			$.mbsmessage.timer=setTimeout('$.mbsmessage.checkTimer()', 3000);
		},
		checkTimer:function(){
			if(!$.mbsmessage.timer) return;
			if(!$.mbsmessage.timeInterval) $.mbsmessage.timeInterval=3;
			$.mbsmessage.timeInterval-=3;
			if($.mbsmessage.timeInterval<=0){
				$(document).trigger('close.mbsmessage');
				clearTimeout($.mbsmessage.timer);
			}
			else{
				$.mbsmessage.timer=setTimeout('$.mbsmessage.checkTimer()', 3000);
			}
		}
	});
	
	$.fn.mbsmessage=function(settings){
		initialize(settings);
	};
	
	
	
	function initialize(settings){
		if($.mbsmessage.timer) clearTimeout($.mbsmessage.timer);
		if($.mbsmessage.settings.initialized) return true;
		$.mbsmessage.settings.initialized=true;
		$(document).trigger('initialize.mbsmessage');
		if(settings) $.extend($.mbsmessage.settings, settings);
		$('body').append($.mbsmessage.settings.mbshtml);
		var preload=[new Image(), new Image(), new Image()];
		preload[0].src='';
		preload[1].src='';
		preload[2].src='';
		$('#mbsmessage .left').html('<img src="' + $.mbsmessage.settings.leftimage + '" />');
		$('#mbsmessage .right').html('<img src="' + $.mbsmessage.settings.rightimage + '" />');
		$('#mbsmessage .close').click($.mbsmessage.close);
		/* $('#mbsmessage .close').attr({src:$.mbsmessage.settings.closeimage}); */
		
	}
	
	$(document).bind('close.mbsmessage', function() {
		if($.mbsmessage.timer) clearTimeout($.mbsmessage.timer);
	    $('#mbsmessage').fadeOut(function() {
	      $('#mbsmessage .content').removeClass().addClass('content');
	    });
	  });
	
})(jQuery);