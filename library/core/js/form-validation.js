(function($) {
    /*
    Validation Singleton
    */
    var Validation = function() {

        messages={
                required: "{caption} is mandatory.",
                email: "Please enter valid email ID for {caption}.",
                charonly: "Only characters are supported for {caption}.",
                integer: "Please enter integer value for {caption}.",
                floating: "Please enter numeric value for {caption}.",
                username: "{caption} must start with a letter and can contain only alphanumeric characters (letters, _, ., digits). Length must be between 4 to 20 characters.",
                password: "{caption} Length Must be between 6 to 20 characters.",
                user_regex: "Invalid value for {caption}.",
                lengthrange: "Length of {caption} must be between {minlength} and {maxlength}.",
                range: "Value of {caption} must be between {minval} and {maxval}.",
                selectionrange: "Please select {minval} to {maxval} options",
                comparewith_lt: "{caption} must be less than {comparefield}.",
                comparewith_le: "{caption} must be less than or equal to {comparefield}.",
                comparewith_gt: "{caption} must be greater than {comparefield}.",
                comparewith_ge: "{caption} must be greater than or equal to {comparefield}.",
                comparewith_eq: "{caption} must be same as {comparefield}.",
                comparewith_ne: "{caption} should not be same as {comparefield}."
        };
        
        parseDate = function(str, format) {
        	if ( typeof(datePickerFormatToDate) == 'function' ) {
    			return datePickerFormatToDate(str, format);
    		}
    		else {
    			return $.datepicker.parseDate( format, str );
    		}
        };
        
        var rules = {
            email : {
               check: function(rval, value) {
                   if(rval && value)
                       return testPattern(value,"^((?:(?:(?:[a-zA-Z0-9][\\.\\-\\+_]?)*)[a-zA-Z0-9])+)\\@((?:(?:(?:[a-zA-Z0-9][\\.\\-_]?){0,62})[a-zA-Z0-9])+)\\.([a-zA-Z0-9]{2,63})$");
                   return true;
               }
            },
            
            required : {
               check: function(rval, value) {
                  if(rval){ 
                       if(value){
                           return true;
                       }
                       else{
                           return false;
                       }
                  }
                  else{
                      return true;
                  }
               }
            },

            charonly:{
                check: function(rval, value){
                    if(rval && value){
                        return testPattern(value, '^[a-zA-Z\\s]*$');
                    }
                    return true;
                }
            },

            integer:{
                check: function(rval, value){
                    if(rval && value){
                        return testPattern(value, '^-?[\\d]+$');
                    }
                    return true;
                }
            },

            floating:{
                check: function(rval, value){
                    if(rval && value){
                        return testPattern(value, '^-?[\\d]*\\.?[\\d]+$');
                    }
                    return true;
                }
            },

            username:{
                check: function(rval, value){
                    if(rval && value){
                        return testPattern(value, '^[a-zA-Z][a-zA-Z_\\.0-9]{3,19}$');
                    }
                    return true;
                }
            },

            password:{
                check: function(rval, value){
                    if(rval && value){
                        return testPattern(value, '^.{6,20}$');
                    }
                    return true;
                }
            },

            user_regex:{
                check: function(rval, value){
                    if(value){
                        return testPattern(value, rval);
                    }
                    return true;
                }
            },

            lengthrange:{
                check: function(rval, value){
                    if(value && $.isArray(rval) && rval.length==2){
                        return (value.length>=rval[0] && value.length<=rval[1]);
                    }
                    return true;
                }
            },
            
            selectionrange: {
                check: function(rval, value){
                    if($.isArray(rval) && rval.length==2){
                        return (value >= rval[0] && value <= rval[1]);
                    }
                    return true;
                }
            },

            range:{
                check: function(rval, value){
                    if(value){
                        var minval=rval.minval;
                        var maxval=rval.maxval;
                        if(rval.dateFormat){
                        	minval = $.Validation.convertStringToDate(minval, rval.dateFormat).getTime();
                            maxval = $.Validation.convertStringToDate(maxval, rval.dateFormat).getTime();
                            value = $.Validation.convertStringToDate(value, rval.dateFormat).getTime();
                        }
                        if(rval.numeric){
                            minval=parseFloat(minval);
                            maxval=parseFloat(maxval);
                            value=parseFloat(value);
                        }
                        return(value >= minval && value <= maxval);
                    }
                    return true;
                }
            },

            comparewith:{
                check: function(rval, value, o){
                    
                    if(o.dateFormat && rval != '' && value != ''){
                        rval = $.Validation.convertStringToDate(rval, o.dateFormat).getTime();
                        value = $.Validation.convertStringToDate(value, o.dateFormat).getTime();
                    }
                    if(o.numeric && rval != '' && value != ''){
                        rval=parseFloat(rval);
                        value=parseFloat(value);
                    }
                    
                    switch (o.operator) {
                    case 'lt':
                        return(value < rval);
                        break;
                    case 'le':
                        return(value<=rval);
                        break;
                    case 'gt':
                        return(value>rval);
                        break;
                    case 'ge':
                        return(value>=rval);
                        break;
                    case 'ne':
                        return(value!=rval);
                        break;
                    default:
                        return(value==rval);
                        break;
                    }
                }
            }
        }
        var testPattern = function(value, pattern) {
            var regExp = new RegExp(pattern,"");
            return regExp.test(value);
        }
        return {
            
            addRule : function(name, rule) {

                rules[name] = rule;
            },
            getRule : function(name) {
                return rules[name];
            },

            getMessage: function(name){
                return messages[name];
            },

            setMessages: function(obj){
                jQuery.extend(messages, obj);
            },
            
            convertStringToDate: function (str, format) {
            	return parseDate(str, format);
            }
        }
    };
    
    /* 
    Form factory 
    */
    var Form = function(form, options) {
        this.form=form;
        this.options=options;
    }
    Form.prototype = {
    		validate : function() {
    			if(typeof FCKeditorAPI !== 'undefined'){
    				for(x in FCKeditorAPI.Instances){
    					this.form.find(':hidden[name="' + x + '"]').val(FCKeditorAPI.GetInstance(x).GetXHTML(true));
    				}
    			}
    			if ( window.CKEDITOR ){
    				for (x in CKEDITOR.instances) CKEDITOR.instances[x].updateElement();
    			}

    			if (typeof oUtil == 'object'){
    				var editors = oUtil.arrEditor;
    				for (x in editors){
    					var obj = eval(editors[x]);
    					if ($('#' + obj.idTextArea).length) {
    						$('#' + obj.idTextArea).val(obj.getXHTMLBody());
    					}
    				}
    			}

    			var $form = this;
    			$form.valid = true;
    			this.form.find("input, textarea, select").each(function() {
    				var field = new Field(this, $form.options);
    				field.validate();
    				$form.valid = $form.valid && field.valid;
    				if ( 0 == $form.options.errordisplay && !field.valid ) {
    					this.focus();
    					return false;
    				} 
    			});
    			
    		},
    		isValid : function() {
    			return this.valid;
    		}
    }
    
    /* 
    Field factory 
    */
    var Field = function(field, options) {
        this.settings=options;
        this.field = $(field);
        this.valid = false;
        if(this.settings.errordisplay != 0 && this.settings.errordisplay != 1) this.attach("change");
        
        if(this.settings.errordisplay==1 && this.settings.summaryElementId=='validation_default') {
        	this.settings.summaryElementId=$(field).parents('form').attr('id');
        }
        if ($(field).attr('data-mbsunichk')) {
        	$(field).attr('autocomplete', 'off').keyup(function() { $(this).attr('data-mbsunichk', '2') });
        }
    }
    Field.prototype = {
        
        attach : function(event) {
        
            var obj = this;
            if(event == "change") {
                obj.field.bind("change",function() {
                    return obj.validate();
                });
            }
            if(event == "keyup") {
                obj.field.bind("keyup",function(e) {
                    return obj.validate();
                });
            }
        },
        validate : function() {
            var clname='erlist_' + this.field.attr('name').replace(/\[/g, '_').replace(/\]/g, '_');
            if (this.field.attr('data-fat-arr-index')) {
            	clname += '_' + this.field.attr('data-fat-arr-index');
            }
            
            $('.'+clname).remove();
            
            var obj = this,
                field = obj.field,
                errorClass = "errorlist",
                errorlist = $(document.createElement("ul")).addClass(errorClass).addClass(clname),
                types = {};
            
            	if (jQuery(field).attr('data-fatreq')) {
            		var s = eval('[' + jQuery(field).attr('data-fatreq') + ']');
            		types = s[0];
            	}
            
                errors = []; 
            jQuery.each(types, function(rname, rval){
                if(rname!='customMessage'){
                    var rule=$.Validation.getRule(rname);
                    
                    var fldval = $.trim(field.val());
                    
                    if (field.attr('data-fatdateformat') && 'range' == rname) {
                    	rval.dateFormat = field.attr('data-fatdateformat');
                    }
                    
                    if (field.attr('type')){
                        if(field.attr('type').toLowerCase()==='checkbox'){
                        	if (field.attr('name').indexOf('[') > 0 && rname == 'selectionrange') {
                        		var arrayFieldNamePre = field.attr('name');
                        		arrayFieldNamePre = arrayFieldNamePre.substring(0, arrayFieldNamePre.indexOf('[') + 1);
                        		fldval = $(field).parents('form').find('input[name^="' + arrayFieldNamePre + '"]:checked').length;
                        	}
                        	else {
                        		fldval=(field.is(':checked'))?fldval:'';
                        	}
                        }
                    }
                    
                    if(rname=='comparewith'){
                        for (x in rval){
                        	if (field.attr('data-fatdateformat')) {
                            	rval[x].dateFormat = field.attr('data-fatdateformat');
                            }
                            var validvalue=rule.check($(field).parents('form').find('[name='+rval[x].fldname+']').val(), fldval, rval[x]);
                            if (!validvalue) break;
                        }
                    }
                    else{
                        var validvalue=rule.check(rval, fldval);
                    }
                    
                    if(!validvalue){
                        field.addClass("error");
                        if(types.customMessage){
                            msg=types.customMessage;
                        }
                        else{
                            if(rname=='comparewith'){
                                msg=$.Validation.getMessage(rname+'_'+rval[x].operator);
                                msg=msg.replace("{comparefield}", $(field).parents('form').find('[name='+rval[x].fldname+']').attr('data-field-caption'));
                            }
                            else{
                                msg=$.Validation.getMessage(rname);
                            }
                        }
                        msg=msg.replace("{caption}",$(field).attr('data-field-caption'));
                        if(jQuery.isArray(rval) && rval.length==2){
                            msg=msg.replace("{minval}", rval[0]);
                            msg=msg.replace("{minlength}", rval[0]);
                            msg=msg.replace("{maxval}", rval[1]);
                            msg=msg.replace("{maxlength}", rval[1]);
                        }
                        if(rname=='range'){
                            msg=msg.replace("{minval}", rval.minval);
                            msg=msg.replace("{maxval}", rval.maxval);
                        }
                        
                        errors.push(msg);
                    }
            }});
            
            if(errors.length) {
                if(this.settings.errordisplay!=0 && this.settings.errordisplay!=1){
                    obj.field.unbind("keyup");
                    obj.attach("keyup");
                }
                switch(this.settings.errordisplay){
                case 1:
                    $('#'+this.settings.summaryElementId).append(errorlist.empty());
                    document.getElementById(this.settings.summaryElementId).scrollIntoView();
                    break;
                case 2:
                	if(field.attr('type') && field.attr('type').toLowerCase()==='checkbox') {
                		field.parent().before(errorlist.empty());
                	}
                	else {
                		field.before(errorlist.empty());
                	}
                    break;
                case 3:
                	if(field.attr('type') && field.attr('type').toLowerCase()==='checkbox') {
                		field.parent().after(errorlist.empty());
                	}
                	else {
                		field.after(errorlist.empty());
                	}
                    break;
                case 0:
                    
                    break;
                }
                for(error in errors) {
                    if(this.settings.errordisplay == 0){
                        alert(errors[error]);
                        return;
                    }
                    else {
                    		var li=$(document.createElement('li')).append($(document.createElement('a')).html(errors[error]).attr({'href':'javascript:void(0);'}).bind('click', function(){$(field).focus();}));
                    		li.appendTo(errorlist);
                    }
                }
                obj.valid = false;
            } 
            else {
                errorlist.remove();
                field.removeClass("error");
                obj.valid = true;
            }
            if (this.field.attr('data-mbsunichk')) {
            	if (this.field.attr('data-mbsunichk') == '2') {
            		obj.valid = false;
            		if (!this.field.hasClass('field-processing')) this.field.trigger('change');
            	}
            }
        }
    }
    
    /* 
    Validation extends jQuery prototype
    */
    $.extend($.fn, {
        
        validation : function(options) {
            
        	$('[data-fat-req-change]', $(this)).each(function() {
            	$(this).trigger('change');
            });
        	
            var validator = new Form($(this), options);
            $.data($(this)[0], 'validator', validator);
            
            var $this = $(this);
            $(this).bind("submit", function(e) {
            	if ($this.hasClass('data-fat-submitted')) {
            		e.preventDefault();
            		console.log('Prevented resubmit of form.');
            		return ;
            	}
                validator.validate(); 
                if(!validator.isValid()) {
                    e.preventDefault();
                }
                else {
                	$this.addClass('data-fat-submitted');
                	setTimeout(function() {
                		$this.removeClass('data-fat-submitted');
                	}, 3000);
                }
            });
            return validator;
        },
        validate : function() {
            var validator = $.data($(this)[0], 'validator');
            validator.validate();
            return validator.isValid();
        }
    });
    $.Validation = new Validation();
})(jQuery);

(function() {
	checkUnique = function(fld, tbl, tbl_fld, tbl_key, key_fld, constraints){
		fld.removeClass('field-unique-error');
		fld.removeClass('field-unique-success');
		fld.addClass('field-processing');
	    var entered = fld.val();
	    $.ajax({
	        url: fcom.makeUrl('checkunique', 'check'),
	        type: 'POST',
	        dataType: 'json',
	        data: {'val':entered, 'tbl':tbl, 'tbl_fld':tbl_fld, 'tbl_key':tbl_key, 'key_val':key_fld.val(), 'constraints':constraints},
	        success: function(ans){
	            fld.removeClass('field-processing');
	            $(fld).attr('data-mbsunichk', 1);
	            if(ans.status==0){
	            	fld.addClass('field-unique-error');
	            	
	            	checkUniqueErrorNotify(fld.attr('data-field-caption'),  entered);
	                fld.val(ans.existing_value);
	                fld.focus();
	            }
	            if (ans.status == 1) {
	            	fld.addClass('field-unique-success');
	            }
	        }
	    });
	    
	    fld.bind('keyup', function(event) {
	    	$( this ).removeClass('field-unique-error');
	    	$( this ).removeClass('field-unique-success');
	    	$( this ).unbind( event );
	    });
	}
	
	fatUpdateRequirement = function(el) {
		el = $(el);
		var str = el.attr('data-fat-req-change');
		if (!str) return;
		var arr = JSON.parse(str);
		var v = el.val();
		if (el.attr('type') == 'checkbox' && !el.is(':checked')) {
			v = '';
		}
		for (var i = arr.length - 1; i >=0; i--) {
			var match = false;
			switch (arr[i].operator) {
			case 'eq':
				match = (v == arr[i].val);
				break;
			case 'ne':
				match = (v != arr[i].val);
				break;
			case 'gt':
				match = (v > arr[i].val);
				break;
			case 'lt':
				match = (v < arr[i].val);
				break;
			case 'ge':
				match = (v >= arr[i].val);
				break;
			case 'le':
				match = (v <= arr[i].val);
				break;
			}
			if (!match) continue;
			$(el[0].form.elements[arr[i].fldname]).attr('data-fatreq', JSON.stringify(arr[i].requirement));
		}
	};
	
	checkUniqueErrorNotify = function (caption, value) {
		alert(caption + " '" + value + "' is not available");
	};
})();