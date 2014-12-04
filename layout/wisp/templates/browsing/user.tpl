<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <link rel="stylesheet" href="../../css/browsing/default.css" type="text/css">
    </head>
    <body>
        <a name="top"></a>
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
            <div id="journey">
                <i18n>dove sei: </i18n>
                <span>
                    <template_field class="template_field" name="course_title">course_title</template_field>
                </span>
                <span> > </span>
                <span>
                    <template_field class="template_field" name="path">path</template_field>
                </span>
            </div>
            <!-- / percorso -->
            <div id="status_bar">
            <!--dati utente-->            
                    <template_field class="microtemplate_field" name="user_data_micro">user_data_micro</template_field>
            <!-- / dati utente -->
            </div>
            <!-- label -->
            <div id="label">
                <div class="topleft">
                    <div class="topright">
                        <div class="bottomleft">
                            <div class="bottomright">
                                <div class="contentlabel">
                                    <template_field class="template_field" name="message">message</template_field>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /label -->
            </div>

            <!-- contenuto -->
            
            <div id="content">
                <div id="contentcontent" class="contentcontent_default">
                    <!-- start tre blocchi grafici homepage -->

			<div id="twobox">
				<div id="boxone">
								<!-- giorgio, blocco "3" con RSS -->
				<div id="blocco_tre">
					<div id="bloccoTreTitoloContenitore"><h2>RSS</h2>
                                        </div>
					<div id="content_blocco_tre">
                                                <template_field class="template_field" name="clientRSS">clientRSS</template_field>
                                        </div>
				</div>
                                <!-- fine blocco RSS-->
				</div> <!--  boxone end -->
				<div id="boxtwo">
<!-- blocco2 -->
				<div id="blocco_due">
					<div id="bloccoDueTitoloContenitore">
                                                <template_field class="template_field" name="bloccoDueTitolo">bloccoDueTitolo</template_field>
                                        </div>
					<div id="content_blocco_due">
                                                <template_field class="template_field" name="bloccoDueContenuto">bloccoDueContenuto</template_field>
                                                <template_field class="template_field" name="bloccoDueIscrizione">bloccoDueIscrizione</template_field>
                                        </div>
				</div>
				<!-- blocco2 end -->			
				<!-- blocco1 -->
				<div id="blocco_uno">
					<div id="bloccoUnoTitoloContenitore">
                                                <template_field class="template_field" name="bloccoUnoTitolo">bloccoUnoTitolo</template_field>
                                        </div>
 					<div id="content_blocco_uno">
                                                <template_field class="template_field" name="bloccoUnoAppuntamenti">bloccoUnoAppuntamenti</template_field>
                                                <template_field class="template_field" name="bloccoUnoContenuto">bloccoUnoContenuto</template_field>
                                                <template_field class="template_field" name="bloccoUnoAskService">bloccoUnoAskService</template_field>
                                                <template_field class="template_field" name="bloccoUnoH3Widget">bloccoUnoH3Widget</template_field>
                                                <template_field class="template_field" name="bloccoUnoContenutoWidget">bloccoUnoContenutoWidget</template_field>
					</div>
				</div>
				<!-- blocco1 end -->
                </div> <!--  boxtwo end -->
			</div>
			<!-- end due blocchi grafici homepage -->
                        <br class="clearfix">
                 </div>
            </div>
            <!--  / contenuto -->
            <br class="clearfix">
            
            
            
            <!-- com_tools -->
            <div id="com_tools">
                <div id="topcom_t">
                </div>
                <div id="com_toolscontent">
                    <template_field class="microtemplate_field" name="com_tools">com_tools</template_field>
                </div>
                <div id="bottomcom_t">
                </div>
            </div>
            <!-- /com_tools -->
            <!-- menudestra -->
           
            <!-- / menudestra  -->
        </div>
</div>
        <!-- / contenitore -->

        <!-- PIEDE -->
        <div id="footer">
            <template_field class="microtemplate_field" name="footer">footer</template_field>
        </div>
        <!-- / piede -->
    </body>
</html>