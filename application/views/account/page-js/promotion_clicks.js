function listPages(p){
	$('body').prepend('<form id="form-paging" method="POST" style="display: none;" ><input type="hidden" name="page" /></form>');
	$('#form-paging input[name=\'page\']').val(p);
	$('#form-paging').submit();
}
