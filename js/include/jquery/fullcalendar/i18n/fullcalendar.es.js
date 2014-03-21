$.fullCalendar.setDefaults({
	allDayText : 'Todo el dia',
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
	monthNames : [ 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
			'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre',
			'Diciembre' ],
	monthNamesShort : [ 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago',
			'Sep', 'Oct', 'Nov', 'Dic' ],
	dayNames : [ 'Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves',
			'Viernes', 'Sabado' ],
	dayNamesShort : [ 'Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sab' ],
	buttonText : {
		prev : '&nbsp;&#9668;&nbsp;',
		next : '&nbsp;&#9658;&nbsp;',
		prevYear : '&nbsp;&lt;&lt;&nbsp;',
		nextYear : '&nbsp;&gt;&gt;&nbsp;',
		today : 'hoy',
		month : 'mes',
		week : 'semana',
		day : 'dia'
	}
});