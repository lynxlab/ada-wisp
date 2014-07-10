//document.write("<script type='text/javascript' src='../js/include/basic.js'></script>");
//document.write("<script type='text/javascript' src='../js/include/menu_functions.js'></script>");
//document.write("<script type='text/javascript' src='../js/include/ts_picker.js'></script>");


/**
 * JavaScript class to handle appointments
 * 
 * @author giorgio
 * @param fullCalendar
 * @returns
 */
function Appointments (fullCalendar, inputProposalNames, max_proposal_count)
{	
	this.maxAppointments = max_proposal_count;
	this.currAppointment = 0;
	this.calendarObj = fullCalendar;
	
	this.titlesArray = JSON.parse(inputProposalNames);
	this.idsArray = new Array();	
	this.alertTitle = $j('#alertDialog').attr('title'); 
	this.confirmedEvents = null;
	
	$j('#varMaximumProposalNumber').html(this.maxAppointments);
	for (var i=0;  i< this.maxAppointments; i++) this.idsArray[i]=0;
	
	var that=this;
	/*
	 * By convention, we make a private that variable. This is used to make the object available to the private methods.
	 * This is a workaround for an error in the ECMAScript Language Specification which causes this to be set incorrectly for inner functions.
	 * (see http://javascript.crockford.com/private.html)
	 */
	
    
	/**
	 * fills and render the appointment passed as json_encoded
	 */
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
	this.addAppointment = function ( startDate, endDate, allDay )
	{
		var retval = false;
		var index = findIndex (0, this.idsArray);
		
		if (this.currAppointment>=this.maxAppointments || index==-1)
		{	
			showDialogByID('#alertDialog', this.alertTitle,'#maximumProposal');			
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
			this.currAppointment++;
			retval = true;			
		}
		this.calendarObj.fullCalendar('unselect');
		return retval;
	};
	
	/**
	 * moves an appointment by checking if it has been moved in the past
	 */
	this.moveAppointment = function(moveEvent,dayDelta,minuteDelta,allDay,revertFunc) {
		if (!this.startDateAndTimeOk(allDay, moveEvent.start)) {
			revertFunc();
		}
		else {
			this.updateForm();
		}		
		this.calendarObj.fullCalendar('unselect');
    };
    
    /**
     * checks if start date and time of appointments are ok for our needs
     */
    this.startDateAndTimeOk = function  (allDay, startDate) {
    	var retval = false;
		var now = new Date();
		
		if (allDay==true)
		{
			// notify the user he cannot make allDay events?
		}
		else if (startDate.getTime() < now.getTime())
		{
			showDialogByID('#alertDialog', this.alertTitle,'#pastProposal');
		}
		else if (this.checkOverlappingEvents(startDate))
		{
			showDialogByID('#alertDialog', this.alertTitle,'#overlappingProposal');
		}
		else retval = true;
		
		return retval;
    };
    
    /**
     * checks if startdate overlaps with some event
     */
    this.checkOverlappingEvents = function (startDate) {
    	
    	// loads confirmed appointments
    	loadConfirmedEvents();
    	
    	for (var i=0; i< this.confirmedEvents.length; i++)
    	{
    		if (this.confirmedEvents[i].start.getTime() == startDate.getTime()) return true;    		
    	}
    	
    	return false;    	
    };
    
    /**
     * deletes the appointment associated to the passed eventID
     */
    this.deleteAppointment = function (eventID) {
    	this.calendarObj.fullCalendar ('removeEvents', eventID);			
		this.currAppointment--;			
		var index = findIndex(eventID,this.idsArray); 			
		// remove the delete index for the array of ids
		if (index!=-1) this.idsArray[index] = 0;
		this.updateForm();
    };
    
    /**
     * called after the form has been reset to reset the appointments in the calendar
     */
    this.resetAppointments = function()
    {
    	for (var i=0; i<this.maxAppointments; i++)
    	{
    		if (this.idsArray[i]!=0) this.deleteAppointment(this.idsArray[i]);
    	}
    };
	
	/**
	 * handles event click:
	 * basically checks if the event is an event proposal and ask to remove it
	 * and if it's a loaded event it will show the details dialog box
	 */
	this.eventClick = function( event ) {
		if (in_array(event.id,this.idsArray))
		{
			jQueryConfirm('#confirmDialog', '#questionDelete', function() { that.deleteAppointment (event.id); });
		}
		else if (typeof event.source != 'undefined' && event.source.className[0]=="loadedEvents")
		{
			// prepares and shows the details dialog
			$j('#proposalUserDetails').html(event.recipientFullName);
			$j('#proposalNotes').html(event.notes);
			$j('#proposalTypeDetails').html(event.type);
			showDialogByID ('#proposalDetails', event.title);
		}
		// returning false prevents event url from opening when clicked
		return false;
	};
	
	/**
	 * updates the associated form with the appointments from fullcalendar
	 */
	this.updateForm = function ()
	{
		for (var i=0; i< this.maxAppointments; i++)
		{
			formIndex = i+1;
			if (this.idsArray[i]!=0)
			{
				eventsArray = this.calendarObj.fullCalendar('clientEvents', this.idsArray[i]);
				
				setDate = this.calendarObj.fullCalendar( 'formatDate' ,eventsArray[0].start , "dd/MM/yyyy" );
				setTime = this.calendarObj.fullCalendar( 'formatDate' ,eventsArray[0].start , "HH:mm" );				
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
	 * loads confirmed events into object property
	 */
	function loadConfirmedEvents ()
	{ 
		if (that.confirmedEvents===null) {
			loadEvents ('confirmed');
		}		
	}
	
	/**
	 * loads passed type of events into object property
	 */
	function loadEvents (eventType)
	{ 
		that.confirmedEvents = that.calendarObj.fullCalendar('clientEvents', function (eventObj) { 
								return (typeof eventObj.source.className != 'undefined' && 
												eventObj.source.className.length>1 && 
												eventObj.source.className[1]==eventType); });
	}
	
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
		} while (in_array(candidate,that.idsArray));
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



function initDoc(initDatas, inputProposalNames, max_proposal_count) {

	$j(document).ready(function() {
		if ($j().uniform) $j("select, input, a.button, button, textarea").uniform();
	});
	
	if ($j('#fullcalendar').length>0) {
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
	    					appointments.addAppointment ( startDate, endDate, allDay );
	    	},
	    	eventClick :function( event, jsEvent, view ) { 
	    					appointments.eventClick ( event );
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
				                // WARNING: js code is based on these classnmes, do not change them!
				                className : 'loadedEvents proposal',
				                editable  : 	false,
				                allDayDefault : false			                
							  },
							  {
				                url : HTTP_ROOT_DIR + "/comunica/ajax/getProposals.php?type=C",
				                // WARNING: js code is based on these classnmes, do not change them!			                
				                className : 'loadedEvents confirmed',
				                editable  : 	false,
				                allDayDefault : false			                
							  }	
			                ]
	    });
	
	    var appointments = new Appointments(fullcal,inputProposalNames, max_proposal_count);
	    appointments.fillWithDatas(initDatas);
	    
	    $j('input:reset').button();
	    $j('input:reset').click (  function ( event ){
	    	// not stopping event propagation will cause the form reset PLUS
	    	// the function specified
	    	event.preventDefault();
	    	jQueryConfirm('#confirmDialog', '#questionReset', function() {
	    		// reset form fields
	    		$j('input:reset').closest('form')[0].reset();
	    		// reset proposed appointments on the calendar
	    		appointments.resetAppointments(); 
	    		});    	
	    } );
	    
	    $j('input:submit').button();
	    $j('input:submit').closest('form').submit( function ( event ) {
	    	if (appointments.currAppointment<=0)
	    	{
	    		event.preventDefault();
	    		showDialogByID('#alertDialog', appointments.alertTitle,'#oneProposalAtLeast');
	    	}    	
	    } );
	}   
}


function setModalDialogText (windowId, spanId)
{
	// hides all spans that do not contain a variable
	$j(windowId + ' span').not('[id^="var"]').hide();
	// shows the passed span that holds the message to be shown
	$j(spanId).show();
}

function showDialogByID(id, title, messageId)
{	
	if (messageId) setModalDialogText (id, messageId);
	
	var buttonLbl = $j(id+' .buttonLbl').html();
	$j(id).dialog({ 
		modal: true, 
		title: title,
		resizable: false,
		buttons: [ { text: buttonLbl ,click: function () { $j(this).dialog("close"); } } ] 
	});	
}

function jQueryConfirm(id, questionId, OKcallback)
{
	var okLbl = $j(id + ' .confirmOKLbl').html();
	var cancelLbl = $j(id + ' .confirmCancelLbl').html();

	setModalDialogText(id, questionId);

	$j(id).dialog({
		resizable : false,
		height : 140,
		modal : true,
		buttons : [ {
			text : okLbl,
			click : function() {
				OKcallback();
				$j(this).dialog("close");
			}
		}, {
			text : cancelLbl,
			click : function() {
				$j(this).dialog("close");
			}
		} ]
	});
}