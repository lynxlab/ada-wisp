<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">
<html>
  <head>
    <!-- link rel="stylesheet" href="../../../css/comunica/default/default.css" type="text/css" -->
    <link rel="stylesheet" href="../../../css/comunica/claire/default.css" type="text/css">
  </head>
<body>
	<a name="top">
	</a>
<div id="header">
	<template_field class="microtemplate_field" name="header">header</template_field>
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
                		  <h1><i18n>chat</i18n></h1>
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
 <!-- / contenitore -->
</div>
<!-- MENU A TENDINA -->
<div id="mainmenucom">
            <ul id="menu">
            			<li id="selfclose">
											<!--<a onclick="exitChat();"><i18n>chiudi</i18n></a>-->
											<template_field class="template_field" name="exit_chat">exit_chat</template_field> 
									</li>
            </ul> <!-- / menu -->
                     <! -- PERCORSO -->
            <div id="journey">
            		 <i18n>dove sei: </i18n>
            		 <span>
            		 			 <i18n>chat</i18n>
            		 </span>
            	</div> <!-- / percorso -->
</div> <!-- / MENU A TENDINA -->

<!-- PIEDE -->
<div id="footer">
		 <template_field class="microtemplate_field" name="footer">footer</template_field>
</div> <!-- / piede -->

</body>
</html>