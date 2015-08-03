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
        <!-- menu -->
            <template_field class="microtemplate_field" name="adamenu">adamenu</template_field>  
        <!-- / menu -->  
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
                                    <h1><i18n>Home autore</i18n></h1>
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
                <div id="contentcontent" class="contentcontent_default">
                    <div class="first">
                        <div class="help">
                            <template_field class="template_field" name="help">help</template_field>
                        </div>
                        <template_field class="template_field" name="head">head</template_field>
                        <template_field class="template_field" name="form">form</template_field>
                    </div>
                </div>
                <div id="bottomcont">
                </div>
            </div>
            <!--  / contenuto -->

            <!-- com_tools -->
            <template_field class="microtemplate_field" name="com_tools">com_tools</template_field>
            <!-- /com_tools -->

            <!-- menudestra -->
       
            <!-- / menudestra  -->
        </div>
        <!-- / contenitore -->
        <!-- piede -->
        <div id="footer">
            <template_field class="microtemplate_field" name="footer">footer</template_field>
        </div>
        <!-- / piede -->
    </body>
</html>