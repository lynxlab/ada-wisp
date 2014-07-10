<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head>
        <link rel="stylesheet" href="../../css/browsing/default.css" type="text/css">
    </head>
    <body>
        <a name="top"></a>
        <!-- testata -->
        <div id="header">
            <template_field class="microtemplate_field" name="header">header</template_field>
        </div> <!-- / testata -->
        <!-- contenitore -->
        <div id="container">
            <!-- PERCORSO -->
            <div id="journey">
                <i18n>dove sei: </i18n>
                <span>
                    <template_field class="template_field" name="course_title">course_title</template_field>
                </span>
            </div>
            <!-- / percorso -->
            <div id="status_bar">
			
			
   				<!--dati utente-->
                   <template_field class="microtemplate_field" name="user_data_micro">user_data_micro</template_field>
                 <!-- / dati utente -->
			
			
			
			
			
            <!-- label -->
            <div id="labelview">
                <div class="topleft">
                    <div class="topright">
                        <div class="bottomleft">
                            <div class="bottomright">
                                <div class="contentlabel">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- /label -->
            </div>
            <!-- contenuto -->
            <!--<div id="content_view" class="content_small">-->
            <div id="content_view">
                <div id="contentcontent" class="contentcontent_view">
                    <div id="info_nodo">
                    </div>
                    <div class="firstnode">
                        <template_field class="template_field" name="text">text</template_field>
                    </div>
                </div>
                <div id="bottomcont">
                </div>
            </div> <!--  / contenuto -->
            <!-- com_tools -->
            <div id="com_tools">
                <div id="topcom_t">
                </div>
                <div id="com_toolscontent">
                    <!--
                      <template_field class="microtemplate_field" name="com_tools">com_tools</template_field>
                    -->
                </div>
                <div id="bottomcom_t">
                </div>
            </div> <!-- /com_tools -->
            <!-- menudestra -->
            <!--<div id="menuright" class="sottomenu_on menuright_view">--><!-- to show right panel -->

            <div id="menuright" class="sottomenu_off menuright_view">

                <div id="topmenur">
                </div>
                <div id="menurightcontent">
                    <ul>
                        <li class="close">
                            <a href="#" onClick="toggleElementVisibility('menuright', 'right');">
                                </i18n>chiudi</i18n>
                            </a>
                        </li>
                        <li class="_menu">
                            <a href="main_index.php">
                                <i18n>indice</i18n>
                            </a>
                        </li>
                        <li class="_menu">
                        <template_field class="template_field" name="search_form">search_form</template_field>
                        </li>
                        <li class="_menu">
                        <template_field class="template_field" name="go_map">go_map</template_field>
                        </li>
                    </ul>
                    <ul id="attachment">
                        <li class="_name">
                        <i18n>approfondimenti</i18n>
                        </li>
                        <ul>
                            <li>
                            <template_field class="template_field" name="index">index</template_field>
                            </li>
                        </ul>
                        <!--<li class="_name">
              				 		 <i18n>collegamenti</i18n>
              				 </li>
    											 <ul>
                    		 		 <li>
                    				 		 <template_field class="template_field" name="link">link</template_field>
                    				 </li>
    											 </ul>
    						-->
                        <!--li class="_name">
              				 		 <i18n>esercizi</i18n>
              				 </li>
    											 <ul>
                    				 <li>
                    				 		 <template_field class="template_field" name="exercises">exercises</template_field>
                    				 </li>
    					     </ul-->
                        <li class="_name">
                        <i18n>risorse</i18n>
                        </li>
                        <ul>
                            <li>
                            <template_field class="template_field" name="media">media</template_field>
                            </li>
                        </ul>
                        <!--li class="_name">
              				 		 <i18n>media di classe</i18n>
              				 </li>
    											 <ul>
                    				 <li>
                    				 		 <template_field class="template_field" name="user_media">user_media</template_field>
                    				 </li>
    											 </ul>
              				 <li class="_name">
              				 		 <i18n>note di classe</i18n>
              				 </li>
    											 <ul>
                    				 <li>
                    				 		 <template_field class="template_field" name="notes">notes</template_field>
                    				 </li>
    											 </ul>
              				 <li class="_name">
              				 		 <i18n>note personali</i18n>
              				 </li>
    											 <ul>
                    				 <li>
                    				 		 <template_field class="template_field" name="personal">personal</template_field>
                    				 </li>
    					</ul-->
                    </ul>
                </div>
                <div id="bottommenur">
                </div>
            </div> <!-- / menudestra  -->
        </div> <!-- / contenitore -->

        <!-- MENU -->
        <div id="mainmenu">
            <ul id="menu">
                <li id="home">
                    <a href="index.php">
                        <i18n>home</i18n>
                    </a>
                </li>
                <li id="question_mark" class="unselectedquestion_mark">
                    <a href="../info.php">
                        <i18n>informazioni</i18n>
                    </a>
                </li>
                <!--li id="ancora_menuright" onclick="toggleElementVisibility('menuright', 'right');">
				<a>
					 <i18n>Naviga</i18n>
		 		</a>
		</li-->
                <!--li id="question_mark" class="unselectedquestion_mark" onclick="toggleElementVisibility('submenu_question_mark','up'); return false;">
				<a>
					 <i18n>Help</i18n>
				</a>
		</li-->
                <li id="esc">
                    <a href="../index.php">
                        <i18n>esci</i18n>
                    </a>
                </li>
            </ul> <!-- / menu -->

            <!-- tendina -->
            <div id="dropdownmenu">


                <!-- puntoint -->
                <div id="submenu_question_mark" class="sottomenu  sottomenu_off">
                    <div id="_question_markcontent">
                        <ul>
                            <li>
                            <!-- <template_field class="template_field" name="help">help</template_field> -->
                            </li>
                            <li>
                                <a href="../info.php">
                                    <i18n>informazioni</i18n>
                                </a>
                            </li>
                            <li>
                                <a href="../credits.php">
                                    <i18n>credits</i18n>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="bottomsubmenu">
                    </div>
                </div> <!-- / puntoint -->
            </div> <! --/tendina -->

        </div> <!-- / MENU A TENDINA -->

      
	 <!-- piede -->
        <div class="clearfix"></div>
        <div id="footer">
            <template_field class="microtemplate_field" name="footer">footer</template_field>
        </div>
        <!-- / piede -->
	  
	  
	  
	    <!-- PIEDE RIMOSSO -->
        <!-- <div id="footer">
            <template_field class="microtemplate_field" name="footer_guest">footer_guest</template_field>
        </div> 
		-->
		<!-- / piede RIMOSSO -->
    </body>
</html>