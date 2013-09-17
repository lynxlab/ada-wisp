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
    
    this.fillWithDatas = function (initDatas) 
    {
    	if (initDatas != '')
        {
        	var data = JSON.parse(initDatas);
        	for (var i=0; i< data.length; i++)
        	{
        		var dateArray = data[i].date.split("/");
        		var timeArray = data[i].time.split(":");

        		this.addAppointment(new Date (dateArray[2], dateArray[1]-1, dateArray[0], timeArray[0], timeArray[1]), null, false, null, null);
        	}
        }
    };

	
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
		else if (typeof event.source != 'undefined' && event.source.className=='loadedEvents')
		{
			$j('#proposalUserDetails').html(event.recipientFullName);
			$j('#proposalNotes').html(event.notes);
			$j('#proposalTypeDetails').html(event.type);
			$j('#proposalDetails').dialog({ modal: true, 
											title: event.title,
											resizable: false,
											buttons: { "Ok": function () { $j(this).dialog("close"); } } 
										});
		}

		// returning false prevents event url from opening when clicked
		return false;
	};
	
	this.updateForm = function ()
	{
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



function initDoc(initDatas) {

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
    	select :	function( startDate, endDate, allDay, jsEvent, view ) {
    					appointments.addAppointment ( startDate, endDate, allDay, jsEvent, view );
    	},
    	eventClick :function( event, jsEvent, view ) { 
    					appointments.eventClick ( event, jsEvent, view );
    	},
    	eventDrop:  function( event, dayDelta, minuteDelta, allDay, revertFunc ) {
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
						  },
						  {
			                url : HTTP_ROOT_DIR + "/comunica/ajax/getProposals.php",
			                className : 'loadedEvents',
			                editable  : 	false,
			                allDayDefault : false			                
						  },
						  {
			                url : HTTP_ROOT_DIR + "/comunica/ajax/getProposals.php?type=C",
			                className : 'loadedEvents',
			                editable  : 	false,
			                allDayDefault : false			                
						  }	
		                ]
    });
    
    var appointments = new Appointments(fullcal);
    appointments.fillWithDatas(initDatas);
}