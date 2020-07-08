$(document).ready(function(){
	profileInfoForm();
});

(function() {
	var runningAjaxReq = false;
	var dv = '#profileInfoFrmBlock';
	var imgdv = '#profileImageFrmBlock';

	profileInfoForm = function(){
		$(dv).html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('Profile', 'profileInfoForm'), '', function(t) {		
			$(dv).html(t);
			
		});
	};
	
	profileImageForm = function(){
		$(imgdv).html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('Profile', 'profileImageForm'), '', function(t) {		
			$(imgdv).html(t);
			
		});
	};
	
	updateProfileInfo = function(frm){
		if (!$(frm).validate()) return;		
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Profile', 'updateProfileInfo'), data, function(t) {					
			//$.mbsmessage.close();			
		});	
	};
	
	removeProfileImage = function(){
		fcom.ajax(fcom.makeUrl('Profile','removeProfileImage'),'',function(t){
			profileImageForm();
		});
	};
	
	sumbmitProfileImage = function(){
		$("#frmProfile").ajaxSubmit({
			delegation: true,
			success: function(json){
				json = $.parseJSON(json);
				profileImageForm();
				$(document).trigger('close.facebox');
			}
		});
	};
	
	$(document).on('click', '[data-method]', function () {
		var data = $(this).data(),
          $target,
          result;
	
      if (data.method) {
        data = $.extend({}, data); // Clone a new one
        if (typeof data.target !== 'undefined') {
          $target = $(data.target);
          if (typeof data.option === 'undefined') {
            try {
				
              data.option = JSON.parse($target.val());
            } catch (e) {
              console.log(e.message);
            }
          }
        }

        result = $image.cropper(data.method, data.option);
		if (data.method === 'getCroppedCanvas') {
          $('#getCroppedCanvasModal').modal().find('.modal-body').html(result);
		
        }
	
        if ($.isPlainObject(result) && $target) {
          try {
            $target.val(JSON.stringify(result));
          } catch (e) {
            console.log(e.message);
          }
        }
		
      }
    });
	
	var $image ;
	cropImage = function(obj){ 
		$image = obj;
		$image.cropper({
			aspectRatio: 1,
			autoCropArea: 0.4545,
			// strict: true,
			guides: false,
			highlight: false,
			dragCrop: false,
			cropBoxMovable: false,
			cropBoxResizable: false,
			rotatable:true,
			responsive: true,
			crop: function (e) {
				var json = [
					'{"x":' + e.x,
					'"y":' + e.y,
					'"height":' + e.height,
					'"width":' + e.width,
					'"rotate":' + e.rotate + '}'
					].join();					
				$("#img_data").val(json);
				
				fcom.resetFaceboxHeight();				
			  },
			built: function () {
			$(this).cropper("zoom", 0.5);
		  },		 
		})
	};
	
	popupImage = function(input){
		$.facebox('<div class="popup__body"><div id="loader" class="loader">'+fcom.getLoader()+'</div></div>','faceboxWidth');
		
		wid = $(window).width();
		if(wid > 767){
			wid = 500; 
		}else{
			wid = 280;
		}
		
		var defaultform = "#frmProfile";
		$("#avatar-action").val("demo_avatar");		
		$(defaultform).ajaxSubmit({
			delegation: true,
			success: function(json){
				json = $.parseJSON(json);
				if(json.status == 1){
					$("#avatar-action").val("avatar");
					var fn = "sumbmitProfileImage();";
					
					$.facebox('<div class="popup__body"><div class="img-container "><img alt="Picture" src="" class="img_responsive" id="new-img" /></div><span class="gap"></span><div class="align--center"><a href="javascript:void(0)" class="themebtn btn-default btn-sm" title="'+$("#rotate_left").val()+'" data-option="-90" data-method="rotate">'+$("#rotate_left").val()+'</a>&nbsp;<a onclick='+fn+' href="javascript:void(0)" class="themebtn btn-default btn-sm">'+$("#update_profile_img").val()+'</a>&nbsp;<a href="javascript:void(0)" class="themebtn btn-default btn-sm rotate-right" title="'+$("#rotate_right").val()+'" data-option="90" data-method="rotate" type="button">'+$("#rotate_right").val()+'</a></div></div>','faceboxWidth');
					$('#new-img').attr('src', json.file);
					$('#new-img').width(wid);
					cropImage($('#new-img'));
					
					
				}else{
					$.facebox('<div class="popup__body"><div class="img-container marginTop20">'+json.msg+'</div></div>');
				}
			}
		});
	};
})();