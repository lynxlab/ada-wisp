document.write("<script type='text/javascript' src='../js/include/basic.js'></script>");
document.write("<script type='text/javascript' src='../js/include/menu_functions.js'></script>");
//document.write("<script type='text/javascript' src='../js/include/tablekit/fabtabulous.js'></script>");
document.write("<script type='text/javascript' src='../js/include/tablekit/tablekit.js'></script>");

function initDoc() {
	$j(document).ready(function() {
		if ($j().uniform) $j("select, input, a.button, button, textarea").uniform();
	});
}
