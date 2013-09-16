document.write("<script type='text/javascript' src='../js/include/basic.js'></script>");
document.write("<script type='text/javascript' src='../js/include/menu_functions.js'></script>");
document.write("<script type='text/javascript' src='../js/include/ts_picker.js'></script>");

/**
 * JavaScript class to handle appointments
 * 
 * @author giorgio
 * @param fullCalendar
 * @returns
 */
function Appointments (fullCalendar)
{	
	this.maxAppointments = 3;
	this.currAppointment = 0;
	this.calendarObj = fullCalendar;
	
	this.titlesArray = new Array ("Prima Proposta","Seconda Proposta","Terza Proposta");
	this.idsArray = new Array(0,0,0);
	
	/**
	 * adds a new appointment after user has completed the selecion action
	 */
	this.addAppointment = function ( startDate, endDate, allDay, jsEvent, view )
	{
		var retval = false;
		var index = findIndex (0, this.idsArray);
		
		if (this.currAppointment>=this.maxAppointments || index==-1)
		{			
			alert ("Massimo "+this.maxAppointments + " proposte");			
		}
		else if (this.startDateAndTimeOk(allDay, startDate))
		{
			this.idsArray[index] = generateID();
			
			var newEvent = {
					id   : this.idsArray[index],
					title: this.titlesArray[index],
					start: startDate,
					allDay: false,
					// uncomment and/or comment below as you wish...
					// end: endDate,
					durationEditable : false
				};
			
			this.calendarObj.fullCalendar('renderEvent',newEvent,true);
			this.currAppointment++;
			this.updateForm();
			retval = true;			
		}
		this.calendarObj.fullCalendar('unselect');
		return retval;
	};
	
	this.moveAppointment = function(event,dayDelta,minuteDelta,allDay,revertFunc) {
		if (!this.startDateAndTimeOk(allDay, event.start)) {
			revertFunc();
		}
		else {
			this.updateForm();
		}
		
		this.calendarObj.fullCalendar('unselect');
    };
    
    this.startDateAndTimeOk = function  (allDay, startDate) {
    	var retval = false;
		var now = new Date();
		
		if (allDay==true)
		{
			// notify the user he cannot make allDay events?
		}
		else if (startDate.getTime() < now.getTime())
		{
			alert ("Non si possono fare proposte nel passato");
		}
		else retval = true;
		
		return retval;
    };
	
	/**
	 * handles event click:
	 * basically checks if the event is an event proposal and ask to remove it
	 */
	this.eventClick = function( event, jsEvent, view ) {
		if (in_array(event.id,this.idsArray) && confirm("Confermi la cancellazione della proposta?"))
		{
			this.calendarObj.fullCalendar ('removeEvents', event.id);			
			this.currAppointment--;			
			var index = findIndex(event.id,this.idsArray); 			
			// remove the delete index for the array of ids
			if (index!=-1) this.idsArray[index] = 0;
			this.updateForm();
		}
	};
	
	this.updateForm = function ()
	{
//		var eventsArray = this.calendarObj.fullCalendar('clientEvents');
		for (var i=0; i< this.maxAppointments; i++)
		{
			formIndex = i+1;
			if (this.idsArray[i]!=0)
			{
				eventsArray = this.calendarObj.fullCalendar('clientEvents', this.idsArray[i]);
				event = eventsArray[0];
				setDate = this.calendarObj.fullCalendar( 'formatDate' ,event.start , "dd/MM/yyyy" );
				setTime = this.calendarObj.fullCalendar( 'formatDate' ,event.start , "HH:mm" );				
			}
			else
			{
				setDate = '';
				setTime = '';
			}
			$j('#date'+formIndex).val (setDate);
			$j('#time'+formIndex).val (setTime);			
		}
	};
	
	/**
	 * generates a random id and returns it if it's not
	 * already assigned to one of the appointments
	 * 
	 * @returns {Integer}
	 * @access private
	 */	  
	function generateID()
	{
		var candidate = null;
		do {
			// generates a random string of 5 chars, thanks stackoverflow!
			candidate = (Math.random()+1).toString(36).substr(2,5);			
		} while (in_array(candidate,this.idsArray));
		return candidate;
	}
	
	/**
	 * equivalent of the php in_array function
	 * 
	 * @param needle
	 * @param haystack
	 * @returns {Boolean}
	 * @access private
	 */
	function in_array(needle, haystack) {
	    for(var i in haystack) {
	        if(haystack[i] == needle) return true;
	    }
	    return false;
	}
		
	function findIndex (needle, haystack)
	{
		for (var i in haystack) {
			if (haystack[i] == needle) return i;
		}
		return -1;
	}		
}



function initDoc() {
	
	// needed for sample events
	// can safely delete from here...
//	var date = new Date();
//	var d = date.getDate();
//	var m = date.getMonth();
//	var y = date.getFullYear();
	// .. to here	

    var fullcal = $j('#fullcalendar').fullCalendar({
        // put your options and callbacks here
    	theme 	 : true,	// enables jQuery UI theme
    	firstDay : 1,		// monday is the first day
    	minTime  : 8,		// events starts at 08AM ,
    	defaultEventMinutes: 60,
    	height : 500,
    	editable : true,
    	selectable : true,
    	selectHelper : true,
    	defaultView : 'agendaWeek',
    	select : function( startDate, endDate, allDay, jsEvent, view ) {
    				appointments.addAppointment ( startDate, endDate, allDay, jsEvent, view );
    	},
    	eventClick : function( event, jsEvent, view ) { 
    				appointments.eventClick ( event, jsEvent, view );
    	},
    	eventDrop: function( event, dayDelta, minuteDelta, allDay, revertFunc ) {
    				appointments.moveAppointment ( event, dayDelta, minuteDelta, allDay, revertFunc ); 
    	},
		header: {
			left: 'prev,next today',
			center: 'title',
			right: 'month,agendaWeek,agendaDay'
		},
		eventSources : [  {
		                	url : GCAL_HOLIDAYS_FEED,
							className: 'holiday'
						  }
		                ],
		events : loadedProposal
		// set sample events
//		events: [
//					{
//						title: 'All Day Event',
//						start: new Date(y, m, 1),
//					},
//					{
//						title: 'Long Event',
//						start: new Date(y, m, d-5),
//						end: new Date(y, m, d-2)
//					},
//					{
//						id: 999,
//						title: 'Repeating Event',
//						start: new Date(y, m, d-3, 16, 0),
//						allDay: false
//					},
//					{
//						id: 999,
//						title: 'Repeating Event',
//						start: new Date(y, m, d+4, 16, 0),
//						allDay: false
//					},
//					{
//						title: 'Meeting',
//						start: new Date(y, m, d, 10, 30),
//						allDay: false
//					},
//					{
//						title: 'Lunch',
//						start: new Date(y, m, d, 12, 0),
//						end: new Date(y, m, d, 14, 0),
//						allDay: false
//					},
//					{
//						title: 'Birthday Party',
//						start: new Date(y, m, d+1, 19, 0),
//						end: new Date(y, m, d+1, 22, 30),
//						allDay: false
//					},
//					{
//						title: 'Click for Google',
//						start: new Date(y, m, 28),
//						end: new Date(y, m, 29),
//						url: 'http://google.com/'
//					}
//				]
		// end sample events
    });
    var appointments = new Appointments(fullcal);
}