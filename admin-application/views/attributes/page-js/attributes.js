var showNumericAttributeBtnString = '<div class="row" style="text-align:right;"><a href="javascript:void(0)" onclick="showNumericAttribute(this)" class="themebtn btn-primary">Show Next Row</a></div>';

var showTextAttributeBtnString = '<div class="row" style="text-align:right;"><a href="javascript:void(0)" onclick="showTextAttribute(this)" class="themebtn btn-primary">Show Next Row</a></div>';

var numericAttrHideCounterVal = 2;
var TextAttrHideCounterVal = 2;
	
$("document").ready(function(){
	
	/* show/hide numeric attributed rows according to filled/selected checkbox[ */
	for( var i = 2; i<= MAX_NUMERIC_ATTRIBUTE_ROWS; i++ ){
		if( $("input[name='prodnumattr_num_" + i + "']").is(":checked") ){
			numericAttrHideCounterVal = i;
			continue;
		}
		$("#prodnumattr_num_" + i).parent(".row").addClass("hide");
	}
	/* ] */
	
	/* show/hide textual attributed rows according to filled/selected checkbox[ */
	for( var i = 2; i<= MAX_TEXTUAL_ATTRIBUTE_ROWS; i++ ){
		if( $("input[name='prodtxtattr_text_" + i + "']").is(":checked") ){
			TextAttrHideCounterVal = i;
			continue;
		}
		$("#prodtxtattr_text_" + i).parent(".row").addClass("hide");
	}
	/* ] */
	
	$( "#prodnumattr_num_" + numericAttrHideCounterVal ).parent(".row").after( showNumericAttributeBtnString );
	$( "#prodtxtattr_text_" + TextAttrHideCounterVal ).parent(".row").after( showTextAttributeBtnString );
});

function showNumericAttribute(e){
	for( var i = numericAttrHideCounterVal; i<= MAX_NUMERIC_ATTRIBUTE_ROWS; i++ ){
		if( $("#prodnumattr_num_" + i).parent(".row").hasClass("hide") ){
			$(e).remove();
			$("#prodnumattr_num_" + i).parent(".row").removeClass("hide");
			if( i != MAX_NUMERIC_ATTRIBUTE_ROWS ){
				$("#prodnumattr_num_" + i).parent(".row").after(showNumericAttributeBtnString);
			}
			break;
		}
	}
}

function showTextAttribute(e){
	for( var i = 2; i<= MAX_TEXTUAL_ATTRIBUTE_ROWS; i++ ){
		if( $("#prodtxtattr_text_" + i).parent(".row").hasClass("hide") ){
			$(e).remove();
			$("#prodtxtattr_text_" + i).parent(".row").removeClass("hide");
			if( i != MAX_TEXTUAL_ATTRIBUTE_ROWS ){
				$("#prodtxtattr_text_" + i).parent(".row").after(showTextAttributeBtnString);
			}
			break;
		}
	}
}