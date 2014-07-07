<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <link rel="stylesheet" href="../../css/switcher/default.css" type="text/css">
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
                    <template_field class="template_field" name="title">title</template_field>
                </span>
            </div>
            <div id="user_wrap">
            <!--dati utente-->
	            <div id="status_bar">
	            	<template_field class="microtemplate_field" name="user_data_micro">user_data_micro</template_field>
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
            </div>

            <!-- contenuto -->
            <div id="content">
                <div id="contentcontent-nostyle">
                    <div class="first">
                        <div id="help">
                            <template_field class="template_field" name="help">help</template_field>
                        </div>
                        <template_field class="template_field" name="data">data</template_field>
                    </div>
                </div>

                <div id="bottomcont">
                </div>
            </div>
            <!--  / contenuto -->
        </div>
        <!-- / contenitore -->

        <!-- menu a tendina -->
        <div id="mainmenu">
            <ul id="menu">
                <li id="home">
                    <a href="../../browsing/user.php">
                        <i18n>home</i18n>
                    </a>
                </li>
                <li id="back">
                    <a href="javascript:history.go(-1)"><i18n>Indietro</i18n></a>
				</li> 
                <li id="question_mark" class="unselectedquestion_mark">
                    <a href="../../help.php" target="_blank">
                        <i18n>aiuto</i18n>
                    </a>
                </li>
                <li id="esc">
                    <a href="../../index.php">
                        <i18n>esci</i18n>
                    </a>
                </li>
            </ul>
            <!-- tendina -->
            <div id="dropdownmenu">
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
