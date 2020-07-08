window.onload = function(){
  var debug = /*true ||*/ false;
  var h = document.querySelector('.heart-wrapper-Js');
  
  function toggleActivate(){
    h.classList.toggle('is-active');
  }

  if(!debug){
	  if(h) {
		  
		h.addEventListener('click',function(){
		  toggleActivate();
		},false);
	  }

    // setInterval(toggleActivate,1000);
  }else{
    var elts = Array.prototype.slice.call(h.querySelectorAll(':scope > *'),0);
    var activated = false;
    var animating = false;
    var count = 0;
    var step = 1000;
    
    function setAnim(state){
      elts.forEach(function(elt){
        elt.style.animationPlayState = state;
      });
    }
    
    h.addEventListener('click',function(){
      if (animating) return;
      if (count > 27) {
        h.classList.remove('is-active');
        count = 0;
        return;
      }
      if (!activated) h.classList.add('is-active') && (activated = true);
      
      console.log('Step : '+(++count));
      animating = true;
      
      setAnim('running');
      setTimeout(function(){
        setAnim('paused');
        animating = false;
      },step);
    },false);

    setAnim('paused');
    elts.forEach(function(elt){
      elt.style.animationDuration = step/1000*27+'s';
    });
  }
  
};