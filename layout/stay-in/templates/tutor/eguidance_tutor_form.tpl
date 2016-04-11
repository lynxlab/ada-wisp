<html>
<head>
  <!--link rel="stylesheet" href="../../../css/comunica/default/default.css" type="text/css"-->
  <link rel="stylesheet" href="../../../css/comunica/claire/default.css" type="text/css">
</head>

<body>
<a name="top"></a>
<div id="pagecontainer">
<div id="header">
<template_field class="microtemplate_field" name="header">header</template_field>
</div> 
<!-- menu -->
    <template_field class="microtemplate_field" name="adamenu">adamenu</template_field>  
<!-- / menu --> 
<!-- contenitore -->
<div id="container">
<!-- PERCORSO -->
<div id="journey" class="ui tertiary inverted teal segment">
		 <i18n>dove sei: </i18n>
		 <span>
		 			 <i18n>Modulo di valutazione della sessione di guidance</i18n>
		 </span>
</div> <!-- / percorso -->

<!--dati utente-->
<div id="status_bar">
<div id="user_data" class="user_data_default">
                		 <i18n>utente: </i18n>
                		 					 <span>
                							 			 <template_field class="template_field" name="user_name">user_name</template_field>
                							 </span>
                		 <i18n>tipo: </i18n>
                		 						 <span>
                								 			 <template_field class="template_field" name="user_type">user_type</template_field>
                								 </span>
										 <div class="status">
                    		 <i18n>status: </i18n>
                    		 <span>
                    		 			 <template_field class="template_field" name="status">status</template_field>
                    		 </span>
										 </div>
</div> 
</div>
<!-- / dati utente -->
<!-- label -->
<div id="label">
		 <div class="topleft">
         <div class="topright">
            <div class="bottomleft">
               <div class="bottomright">
                  <div class="contentlabel">
                		  <h1><i18n>Modulo di valutazione</i18n></h1>
									</div>
							</div>
						</div>
					</div>
			</div>		
</div><!-- /label -->
<!-- contenuto -->
<div id="content">	 
<div id="contentcontent">
  <div class="first">
  		 <i18n>Modulo di valutazione</i18n>
  		 <div>
			 			<template_field class="template_field" name="dati">dati</template_field>
			 </div>

  </div> 
</div>
</div> <!--  / contenuto --> 
</div> <!-- / contenitore -->
<div id="push"></div>
</div>
<div class="clearfix"></div>
<!-- PIEDE -->
<div id="footer">
		 <template_field class="microtemplate_field" name="footer">footer</template_field>
</div> <!-- / piede -->
</body>
</html>
