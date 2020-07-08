(function(){
	getChild = function(tag,v){
		
		//console.log($(tag).parent());
		fcom.ajax(fcom.makeUrl('category', 'listsByTree'), {category_id:v}, function(t) {
			
			$(tag).parent().append(t);
		});
	}
})();
