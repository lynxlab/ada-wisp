<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <link rel="stylesheet" href="../../css/services/default.css" type="text/css">
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
            <!-- percorso -->
            <div id="journey">
                <i18n>dove sei: </i18n>
                <span>
                    <template_field class="template_field" name="course_title">course_title</template_field>
                </span>
                <span>
                    <template_field class="template_field" name="path">path</template_field>
                </span>
            </div>
            <!-- / percorso -->
            <div id="status_bar">
            <!--dati utente-->            
                    <template_field class="microtemplate_field" name="user_data_micro">user_data_micro</template_field>
            
             <!-- / dati utente -->
            <!-- label -->
            <!--
            <div id="label">
                <div class="topleft">
                    <div class="topright">
                        <div class="bottomleft">
                            <div class="bottomright">
                                <div class="contentlabel">
                                    <h1>
                                        <i18n>corsi</i18n>
                                    </h1>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            -->
            <!-- /label -->
            </div>
            <!-- contenuto -->
            <div id="content">
                <div id="contentcontent" class="contentcontent_default" >
                    <div class="first">
                        <div class="help">
                            <template_field class="template_field" name="help">help</template_field>
                        </div>
                        <template_field class="template_field" name="head">head</template_field>
                        <template_field class="template_field" name="dati">dati</template_field>
                    </div>
                </div>
                <div id="bottomcont">
                </div>
            </div>
            <!--  / contenuto -->
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
                            <a href="#" onclick="toggleElementVisibility('menuright', 'right');">
                                </i18n>chiudi</i18n>
                            </a>
                        </li>
                        <li class="_menu">
                            <a href="author_report.php">
                                <i18n>report</i18n>
                            </a>
                        </li>
                    </ul>
                </div>
                <div id="bottommenur">
                </div>
            </div>
            <!-- / menudestra  -->
        </div>
        <!-- / contenitore -->

        <!-- menu a tendina -->
        <div id="mainmenu">
            <ul id="menu">
                <li id="home">
                    <a href="author.php">
                        <i18n>home</i18n>
                    </a>
                </li>
                <li id="com" class="unselectedcom" onclick="toggleElementVisibility('submenu_com','up')">
                    <a>
                        <i18n>comunica</i18n>
                    </a>
                </li>
                <li id="tools" class="unselectedtools" onclick="toggleElementVisibility('submenu_tools','up')">
                    <a>
                        <i18n>strumenti</i18n>
                    </a>
                </li>
                <li id="actions" class="unselectedactions" onclick="toggleElementVisibility('submenu_actions','up')">
                    <a>
                        <i18n>agisci</i18n>
                    </a>
                </li>
                <li id="ancora_menuright" onclick="toggleElementVisibility('menuright', 'right');">
                    <a>
                        <i18n>Naviga</i18n>
                    </a>
                </li>
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


            <!-- tendina -->
            <div id="dropdownmenu">
                <!-- comunica -->
                <div id="submenu_com" class="sottomenu sottomenu_off">
                    <div id="_comcontent">
                        <ul>
                            <!--
                            <li>
                            <a href="#" onclick='openMessenger("../comunica/list_messages.php",800,600);'>
                            <i18n>messaggeria</i18n>
                            </a>
                            </li>
                            -->
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
                            <!--
                            <li>
                            <a href="#" onclick='openMessenger("../comunica/list_events.php",800,600);'>
                            <i18n>agenda</i18n>
                            </a>
                            </li>
                            -->
                        </ul>
                    </div>
                    <div class="bottomsubmenu">
                    </div>
                </div>
                <!-- / strumenti -->
                <!-- azioni -->
                <div id="submenu_actions" class="sottomenu sottomenu_off">
                    <div id="_actionscontent">
                        <ul>
                            <li>
                            <template_field class="template_field" name="menu">menu</template_field>
                            </li>
                        </ul>
                    </div>
                    <div class="bottomsubmenu">
                    </div>
                </div>
                <!-- / azioni -->
                <!-- puntoint -->
                <div id="submenu_question_mark" class="sottomenu  sottomenu_off">
                    <div id="_question_markcontent">
                        <ul>
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
            <!--/tendina -->
        </div>
        <!-- / menu a tendina -->
        <!-- piede -->
        <div id="footer">
            <template_field class="microtemplate_field" name="footer">footer</template_field>
        </div>
        <!-- / piede -->
    </body>
</html>