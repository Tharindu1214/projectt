function loadForm() {
	
    $.facebox(function() {
        fcom.ajax(fcom.makeUrl('Test', 'loadForm'), '', function(t) {
            $.facebox(t);

            var frm = $('#facebox form')[0];

            var validator = $(frm).validation({errordisplay: 3});
						$(frm).submit(function(e) {
							e.preventDefault();

							validator.validate();
							if (!validator.isValid()) return;

                            var data = fcom.frmData(frm);
    fcom.ajax(fcom.makeUrl('Test', 'submitForm'), data, function(t) {
        alert(t);
		$(document).trigger('close.facebox');
		
		var editors = oUtil.arrEditor;
    				for (x in editors){
						eval('delete ' + editors[x]);
    					//var obj = eval(editors[x]);
						//console.log(editors[x]);
    					//$('#' + obj.idTextArea).val(obj.getXHTMLBody());
    				}
		
		oUtil.arrEditor = [];
		$('#dv-debug').append('Now removed <br>');
		/* setTimeout(function() {
			$('#facebox').remove();
		}, 2000); */
		
    });

						});


        });
    });
}

function submitForm(frm) {
    
}

$(document).bind('close.facebox', function() {	
   console.log('close');
});