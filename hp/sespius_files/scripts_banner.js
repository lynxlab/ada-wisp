Drupal.som = {};

Drupal.som.replaceimage = function () {
  
  if (!$.browser.safari) {
  	$('#som-image-rotation a img').fadeOut(500);
  	setTimeout(function() { $('#som-image-rotation-image').html(som_rotating_images[som_rotating_pointer]); $('#som-image-rotation-image a img').load( function() { $('#som-image-rotation a img').fadeIn(500); }); som_rotating_pointer++; }, 500 );
  }
  else {
  	$('#som-image-rotation').fadeOut(500);
  
  	setTimeout(function() { $('#som-image-rotation-image').html(som_rotating_images[som_rotating_pointer]); $('#som-image-rotation-image a img').load( function() { $('#som-image-rotation a img').show(); $('#som-image-rotation').fadeIn(500); }); som_rotating_pointer++; }, 500 );
  }
  
  if (som_rotating_pointer == som_rotating_number) {
    som_rotating_pointer = 0;
    som_rotating_switch = 0;
  }
  
  if (som_rotating_switch == 1) {
    $.preloaddivs(som_rotating_images[som_rotating_pointer + 1]);
  }
  
}


jQuery.preloaddivs = function(){
  for(var i = 0; i<arguments.length; i++)
  {
    jQuery("<div>").html(arguments[i]);
  }
}

Drupal.som.headerimages = function() {
  
  jQuery.preloaddivs(som_rotating_images[som_rotating_pointer], som_rotating_images[som_rotating_pointer + 1]);
  
  setInterval("Drupal.som.replaceimage()", 7000);
};

