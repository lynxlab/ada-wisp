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
            <div id="menuright" class="sottomenu_off menuright_default">
                <div id="topmenur">
                </div>
                <div id="menurightcontent">
                    <ul>
                        <li class="close">
                            <a href="#" onClick="toggleElementVisibility('menuright', 'right');">
                                <i18n>chiudi</i18n>
                            </a>
                        </li>
                        <li class="_menu">
                            <a href="main_index.php">
                                <i18n>indice</i18n>
                            </a>
                        </li>
                        <li class="_menu">
                        <template_field class="template_field" name="go_map">go_map</template_field>
                        </li>
                    </ul>
                </div>
                <div id="bottommenur">
                </div>
            </div>
            <!-- / menudestra  -->
        </div>
</div>
        <!-- / contenitore -->

        <!-- MENU A TENDINA -->
        <div id="mainmenu">
            <ul id="menu">
                <li id="home">
                    <a href="user.php">
                        <i18n>home</i18n>
                    </a>
                </li>
                <li id="com" class="unselectedcom" onClick="toggleElementVisibility('submenu_com','up')">
                    <a>
                        <i18n>comunica</i18n>
                    </a>
                </li>
                <li id="tools" class="unselectedtools" onClick="toggleElementVisibility('submenu_tools','up')">
                    <a>
                        <i18n>strumenti</i18n>
                    </a>
                </li>
                <!--<li id="actions" class="unselectedactions" onClick="toggleElementVisibility('submenu_actions','up')">
                    <a>
                        <i18n>agisci</i18n>
                    </a>
                </li>
                -->
<!--
                <li id="ancora_menuright" onClick="toggleElementVisibility('menuright', 'right');">
                    <a>
                        <i18n>Naviga</i18n>
                    </a>
                </li>
-->
                <li id="question_mark" class="unselectedquestion_mark" onClick="toggleElementVisibility('submenu_question_mark','up'); return false;">
                    <a>
                        <i18n>Help</i18n>
                    </a>
                </li>
                <li id="esc">
                    <a href="../index.php">
                        <i18n>esci</i18n>
                    </a>
                </li>
            </ul>
            <!-- / menu -->

            <!-- notifiche eventi -->
            <template_field class="template_field" name="events">events</template_field>
            <!-- / notifiche eventi -->
            <!-- tendina -->
            <div id="dropdownmenu">
                <!-- comunica -->
                <div id="submenu_com" class="sottomenu sottomenu_off">
                    <div id="_comcontent">
                        <ul>
                            <li>
                                <a href="#" onclick='openMessenger("../comunica/list_messages.php",800,600);'>
                                    <i18n>messaggeria</i18n>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="bottomsubmenu">
                    </div>
                </div>
                <!-- / comunica -->
                <!-- strumenti -->
                <div id="submenu_tools" class="sottomenu sottomenu_off">
                    <div id="_toolscontent">
                        <ul>
                            <li>
                                <a href="#" onclick='openMessenger("../comunica/list_events.php",800,600);'>
                                    <i18n>agenda</i18n>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="bottomsubmenu">
                    </div>
                </div>
                <!-- /strumenti -->
                <!-- azioni -->
                <!-- 
                <div id="submenu_actions" class="sottomenu sottomenu_off">
                    <div id="_actionscontent">
                        <ul>
                            <li>
                                <a href="edit_user.php">
                                    <i18n>Modifica il tuo profilo</i18n>
                                </a>
                            </li>
                            <template_field class="template_field" name="submenu_actions">submenu_actions</template_field>
                        </ul>
                    </div>
                    <div class="bottomsubmenu">
                    </div>
                </div>
                -->
                <!-- / azioni -->
                <!-- puntoint -->
                <div id="submenu_question_mark" class="sottomenu  sottomenu_off">
                    <div id="_question_markcontent">
                        <ul>
                            <li>
                            <!--template_field class="template_field" name="help">help</template_field-->
                            </li>
                            <li>
                                <a href="../help.php" target="_blank">
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
                </div>
                <!-- / puntoint -->
            </div>
            <!-- /tendina-->
        </div>
        <!-- / MENU A TENDINA -->
        <!-- PIEDE -->
        <div id="footer">
            <template_field class="microtemplate_field" name="footer">footer</template_field>
        </div>
        <!-- / piede -->
    </body>
</html>