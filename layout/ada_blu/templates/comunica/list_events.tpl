<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <link rel="stylesheet" href="../../../css/comunica/masterstudio_stabile/default.css" type="text/css">
</head>

<body>
<a name="top">
</a>
<div id="header">
		 <template_field class="microtemplate_field" name="header_com">header_com</template_field>
</div>
<!-- contenitore -->
<div id="container">
<!--dati utente-->
<div id="user_wrap">
<!-- label -->
<div id="label">
		 <div class="topleft">
         <div class="topright">
            <div class="bottomleft">
               <div class="bottomright">
                  <div class="contentlabel">
                		  <h1><i18n>appuntamenti</i18n></h1>
									</div>
							</div>
						</div>
					</div>
			</div>		
</div>
<!-- /label -->

<div id="user_data" class="user_data_default">
  <template_field class="microtemplate_field" name="user_data_mini_micro">user_data_mini_micro</template_field>
</div>
</div>
 <!-- / dati utente -->

<!-- contenuto -->
<div id="content">	 
<div id="contentcontent">
  <div class="first">
  		 <i18n>appuntamenti: </i18n>
  		 <div>
			 			<template_field class="template_field" name="messages">messages</template_field>
			 </div>
			 <div>
			 			<template_field class="template_field" name="menu_02">menu_02</template_field>  
			 </div>
  </div> 
</div>
<div id="bottomcont">
</div>
</div> <!--  / contenuto --> 
</div> <!-- / contenitore -->
<!-- MENU -->
<div id="mainmenucom">
<ul id="menu">
		<li id="selfclose">
				<a href="#" onClick="closeMeAndReloadParent();"><i18n>chiudi</i18n></a> 
		</li>
		<!-- li id="new">
				<template_field class="template_field" name="menu_01">menu_01</template_field>
		</li -->  
		
</ul> <!-- / menu -->
<!-- PERCORSO -->
<div id="journey">
		 <i18n>dove sei: </i18n>
		 <span>
		 			 <i18n>agenda</i18n>
		 </span>
	</div> <!-- / percorso -->
</div> <!-- / MAINMENU -->
<!-- PIEDE -->
<div id="footer">
		 <template_field class="microtemplate_field" name="footer">footer</template_field>
</div> <!-- / piede -->
</body>
</html>
