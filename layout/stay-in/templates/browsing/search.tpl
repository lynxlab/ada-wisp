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
            <!--dati utente-->
            <div id="status_bar">
                    <template_field class="microtemplate_field" name="user_data_micro">user_data_micro</template_field>
            <!-- / dati utente -->
            <!-- label -->
            <!--div id="label">
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
            <!-- / dati utente -->
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
                    <div class="first">
                        <div id="help">
                            <template_field class="template_field" name="help">help</template_field>
                        </div>
                        <!--template_field class="template_field" name="data">data</template_field-->
                    </div>
                    <!--Ricerca avanzata -->
                    <div id="div_advancedSearch">
                        <div id="align_leftAdvanced">
                            <div class="search_formAdvanced">
                                <template_field class="template_field" name="advancedSearch_form">advancedSearch_form</template_field>
                            </div>
                            <template_field class="template_field" name="menuAdvanced_search">menuAdvanced_search</template_field>
                            <span>
                                 <template_field class="template_field" name="simpleSearchLink">simpleSearchLink</template_field>
                            </span>
                        </div>
                        
                        <div id="result_AdvancedSearch">
                            <template_field class="template_field" name="result_AdvancedSearch">result_AdvancedSearch</template_field>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    
                    <!--Ricerca semplice -->
                    <div id="div_simpleSearch">
                        <div id="align_leftSimple">
                            <div class="search_SimpleForm">
                                <template_field class="template_field" name="form">form</template_field>
                            </div>
                            <template_field class="template_field" name="menu">menu</template_field>
                            <span>
                                <template_field class="template_field" name="advanced_searchLink">advanced_searchLink</template_field>
                            </span>
                         </div>
                        
                         <div id="result_SimpleSearch">
                            <template_field class="template_field" name="results">results</template_field>
                         </div>
                   </div>
                   <div class="clearfix"></div>
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
         
            <!-- / menudestra  -->
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
               
                <li id="ancora_menuright">
                    <a href="../info.php">
                        <i18n>corsi</i18n>
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
            
            <!-- puntoint -->
            <div id="dropdownmenu">
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
            </div>
                <!-- / puntoint -->
            </div>
            <!-- /tendina-->
        </div>
        <!-- / MENU A TENDINA -->
        <!-- PIEDE -->
        <div class="clearfix"></div>
        <div id="footer">
            <template_field class="microtemplate_field" name="footer">footer</template_field>
        </div>
        <!-- / piede -->
    </body>
</html>
