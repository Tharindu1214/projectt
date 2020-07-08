(function(){
	createSlug = function(v){
		$('#category_slug').val(v.value.replace(/[^a-zA-Z0-9]+/ig, "-"));
		
	}
	changeImage = function(){
		
		$('#category_image').val('');
		
	}
})();
