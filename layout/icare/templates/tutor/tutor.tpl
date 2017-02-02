<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <link rel="stylesheet" href="../../css/tutor/default.css" type="text/css">
    </head>
    <body>
        <a name="top"> </a>
        <div id="pagecontainer">
        <!-- testata -->
        <div id="header">
            <template_field class="microtemplate_field" name="header">header</template_field>
        </div>
        <!-- / testata -->
        <!-- menu -->
            <template_field class="microtemplate_field" name="adamenu">adamenu</template_field>  
        <!-- / menu --> 
        <!-- contenitore -->
        <div id="container">
            <!-- PERCORSO -->
            <div id="journey" class="ui tertiary inverted red segment">
                <i18n>dove sei: </i18n>
                <span>
                    <template_field class="template_field" name="course_title">course_title</template_field>
                </span>
                <span class="annocorsofilter">
                	<span><i18n>Mostra studenti iscritti all'A/A (AC)</i18n></span>
					<template_field class="template_field" name="annocorsofilter">annocorsofilter</template_field>
                </span>
            </div>
            <!-- / percorso -->
            <!--dati utente-->
             <div id="status_bar">
                <!--dati utente-->
                   <template_field class="microtemplate_field" name="user_data_micro">user_data_micro</template_field>
                 <!-- / dati utente -->
             </div>

            <!-- contenuto -->
            <div id="content_view">
                <div id="contentcontent" class="contentcontent_view">
                    <div class="first">
                        <div id="help">
                        <template_field class="template_field" name="help">help</template_field>
                        </div>
                        
						<!-- blocco6: pre-assigned students -->
                        <div id="blocco_sei">
                        	<div id="bloccoSeiTitoloContenitore">
                            	<template_field class="template_field" name="bloccoSeiTitolo">bloccoSeiTitolo</template_field>
                            </div>
                            <div id="content_blocco_sei">
                            	<template_field class="template_field" name="dati6">dati6</template_field>
                            </div>
                         </div>
                         <!-- blocco6: pre-assigned students end -->


						<div id="twobox">
							<div id="boxone">
								<!-- blocco2 -->
								<div id="blocco_due">
									<div id="bloccoDueTitoloContenitore">
                                    	<template_field class="template_field" name="bloccoDueTitolo">bloccoDueTitolo</template_field>
                                    </div>
									<div id="content_blocco_due">
										<template_field class="template_field" name="bloccoDueAppuntamenti">bloccoDueAppuntamenti</template_field>
										<template_field class="template_field" name="bloccoDueContenuto">bloccoDueContenuto</template_field>
										<template_field class="template_field" name="bloccoDueH3Widget">bloccoDueH3Widget</template_field>
										<template_field class="template_field" name="bloccoDueContenutoWidget">bloccoDueContenutoWidget</template_field>
										<template_field class="template_field" name="bloccoDueContenuto">bloccoDueMessaggi</template_field>
									</div>
								</div>
								<!-- blocco2 end -->

								<!-- blocco1 -->
								<!-- div id="blocco_uno">
									<div id="bloccoUnoTitoloContenitore">
										<template_field class="template_field_disabled" name="bloccoUnoTitolo">bloccoUnoTitolo</template_field>
									</div>
									<div id="content_blocco_uno">
										<template_field class="template_field_disabled" name="dati">dati</template_field>
									</div>
								</div -->
								<!-- blocco1 end -->
							</div> <!--  boxone end -->
							
							<div id="boxtwo">
								<!-- blocco4 -->
								<div id="blocco_quattro">
									<div id="bloccoQuattroTitoloContenitore">
										<template_field class="template_field" name="bloccoQuattroTitolo">bloccoQuattroTitolo</template_field>
									</div>
									<div id="content_blocco_quattro">
										<template_field class="template_field" name="dati4">dati4</template_field>
									</div>
								</div>
								<!-- blocco4 end -->

								<!-- blocco3 -->
								<div id="blocco_tre">
									<div id="bloccoTreTitoloContenitore">
										<template_field class="template_field" name="bloccoTreTitolo">bloccoTreTitolo</template_field>
									</div>
									<div id="content_blocco_tre">
										<template_field class="template_field" name="dati3">dati3</template_field>
									</div>
								</div>
								<!-- blocco3 end -->
							</div>
						</div> <!--  twobox end -->

						<!-- blocco5 -->
						<div id="blocco_cinque">
							<div id="bloccocinqueTitoloContenitore">
								<template_field class="template_field" name="bloccoCinqueTitolo">bloccoCinqueTitolo</template_field>
							</div>
							<div id="content_blocco_quattro">
								<template_field class="template_field" name="dati5">dati5</template_field>
							</div>
						</div>
						<!-- blocco5 end -->
                        
                        <br class="clearfix">
                        
                    </div>
                </div>
            </div>
            <!--  / contenuto -->
        </div>
        <!-- / contenitore -->
        <div id="push"></div>
		</div>
        
        <!-- com_tools -->
        <div class="clearfix"></div>
        <div id="com_tools">
            <div id="com_toolscontent">
                <template_field class="microtemplate_field" name="com_tools">com_tools</template_field>
            </div>
        </div>
        <!-- /com_tools -->
        
        <!-- PIEDE -->
        <div id="footer">
            <template_field class="microtemplate_field" name="footer">footer</template_field>
        </div>
        <!-- / piede -->
    </body>
</html>
