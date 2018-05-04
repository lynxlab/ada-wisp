<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
    </head>
    <body>
        <a name="top"></a>
		<div id="pagecontainer">
        <!-- testata -->
        <div id="header">
            <template_field class="microtemplate_field" name="header">header</template_field>
        </div> <!-- / testata -->
<!-- menu -->
    <template_field class="microtemplate_field" name="adamenu">adamenu</template_field>
<!-- / menu -->
        <!-- contenitore -->
        <div id="container">
            <!-- PERCORSO -->
            <div id="journey" class="ui tertiary inverted teal segment">
                <i18n>dove sei: </i18n>
                <span>
                    <template_field class="template_field" name="title">title</template_field>
                </span>
            </div>
            <div id="user_wrap">
                <div id="status_bar">
                    <!--dati utente-->
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
                            </span> </div>
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
                                            <h1>
                                                <template_field class="template_field" name="label">label</template_field>
                                            </h1>
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
                <div id="contentcontent">
                    <div class="first">
                        <template_field class="template_field" name="data">data</template_field>
						<div class="ui icon success message" style="display:none;">
						  <i class="checkmark icon"></i>
						  <div class="content">
						    <div class="header">
						      <i18n>Il tuo numero di pratica è</i18n>: <span id="requestUUID" class="requestUUID"></span>
						    </div>
						    <p><i18n>Scrivi questo numero in un posto sicuro! Dovrà essere usato per ogni comunicazione relativa alla richiesta</i18n></p>
						    <p class="newRequestButtons">
						    	<button type="button" id="redirectBtn" class="ui orange button" style="display:none;"><span id="redirectLbl"><i18n>clicca qui per evadere la pratica</i18n></span></button>
						    	<a href="list.php" id="requestsListBtn" class="ui purple button"><i18n>Vai all'elenco richieste</i18n></a>
						    </p>
						  </div>
						</div>
                    </div>
                </div>
            </div>
            <!--  / contenuto -->

        </div>
        <!-- / contenitore -->
		<div id="push"></div>
		</div>

        <!-- piede -->
        <div id="footer">
            <template_field class="microtemplate_field" name="footer">footer</template_field>
        </div>
        <!-- / piede -->

    </body>
</html>
