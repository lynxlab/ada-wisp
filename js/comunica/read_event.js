/**
 * Performs the steps required to enter the chatroom or the videochat
 * when the user click on the appropriate link in the appointment message.
 * 
 * @param id_course				the id of the course
 * @param id_course_instance    the id of the course instance
 * @param id_msg				the id of the appointment message
 * 
 * @return void
 */
function performEnterEventSteps(event, id_course, id_course_instance) {
		
	var windowOpenerLocationHref = HTTP_ROOT_DIR + '/browsing/view.php'
	                       + '?id_node=' + id_course + '_0'
	                       + '&id_course=' + id_course
	                       + '&id_course_instance=' + id_course_instance;
	var thisWindowLocationHref = HTTP_ROOT_DIR + '/comunica/enter_event.php'
				               + '?event=' + event
							   + '&id_course=' + id_course
 						       + '&id_course_instance=' + id_course_instance;
	                           
	window.opener.location.href = windowOpenerLocationHref;
	window.location = thisWindowLocationHref;
}

function initDoc() {	
	if ($j('#enter_appointment').length > 0) $j('#enter_appointment').hide();
	
	if ($j('#appointmentCountdown').length >0 )
	{
		// conversion from unix timestamp to JS date
		// is done by multiplying by 1000
		var until = parseInt($j('#countdownUntil').html())*1000;		
		var expiryText = $j('#enter_appointment').html();

		$j('#appointmentCountdown').countdown({ 
		    until:new Date(until), 
		    serverSync: serverTime,
		    alwaysExpire: true,
		    onExpiry: function() {
		    $j('#countdownWrapper')
		          .animate( { height: "toggle" }, 700, 'easeInOutExpo' , function() {
		        	  $j('#appointmentCountdown').html(expiryText);
			    	  $j('.countdownMessage').hide();
		        	  $j('#countdownWrapper').animate( { height: "toggle" }, 400, 'easeInOutExpo' ); 
		          } );
		    }
		}); 		
	}
}


function serverTime() { 
    var time = null; 
    $j.ajax({url: HTTP_ROOT_DIR + '/comunica/ajax/serverTime.php', 
        async: false, dataType: 'text', 
        success: function(text) { 
            time = new Date(text); 
        }, error: function(http, message, exc) { 
            time = new Date(); 
    }}); 
    return time; 
}