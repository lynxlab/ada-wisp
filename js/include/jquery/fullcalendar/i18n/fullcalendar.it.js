$.fullCalendar.setDefaults({
	allDayText : 'Tutto il giorno',
	axisFormat : 'H:mm',
	titleFormat : {
		month : 'MMMM yyyy',
		week : "d[ MMM][ yyyy]{ '&#8212;' d MMM yyyy}",
		day : 'dddd, d MMM yyyy'
	},
	columnFormat : {
		month : 'ddd',
		week : 'ddd d/M',
		day : 'dddd d/M'
	},
	timeFormat : {
		'' : 'H(:mm)',
		agenda : 'H:mm{ - H:mm}'
	},
	firstDay : 1,
	
	 monthNames: ['Gennaio','Febbraio','Marzo','Aprile','Maggio','Giugno','Luglio','Agosto','Settembre','Ottobre','Novembre','Dicembre'],
     monthNamesShort: ['Gen','Feb','Mar','Apr','Mag','Giu','Lug','Ago','Set','Ott','Nov','Dic'],
     dayNames: ['Domenica','Lunedì','Martedì','Mercoledì','Giovedì','Venerdì','Sabato'],
     dayNamesShort: ['Dom','Lun','Mar','Mer','Gio','Ven','Sab'],
     buttonText: {
         prev: "<span class='fc-text-arrow'>&lsaquo;</span>",
         next: "<span class='fc-text-arrow'>&rsaquo;</span>",
         prevYear: "<span class='fc-text-arrow'>&laquo;</span>",
         nextYear: "<span class='fc-text-arrow'>&raquo;</span>",
         today: 'oggi',
         month: 'mese',
         week: 'settimana',
         day: 'giorno'
     }
});