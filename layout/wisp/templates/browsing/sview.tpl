<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <link rel="stylesheet" href="../../css/browsing/default.css" type="text/css">
    </head>
    <body>
        <a name="top">
        </a>
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
            </div>
            <!-- contenuto -->
            <div id="content_view">
                <div id="contentcontent" class="contentcontent_view">
                <div class="leftcol">
		    <div id="news_sx">
						<div id="titolo_news_sx">
					 	<h2><i18n>Ultimi contenuti</i18n></h2>
						</div>
					<div class="firstnode">
                        <template_field class="template_field" name="sviewnews">sviewnews</template_field>
                    </div>
		</div>
					
                    <div id="box_sx">
						<div id="titolo_box_sx">
					 	<h2><i18n>Timeline</i18n><template_field class="template_field" name="timelinewithuser">timelinewithuser</template_field></h2>
						</div>
					<div class="firstnode">
                        <template_field class="template_field" name="text">text</template_field>
                    </div>
					</div>
</div>					    


							
					
		    
					
                    <!-- blocco2 -->
					<div id="box_dx">
					<div id="titolo_box_sx">
					 	<h2><i18n>Comunicazioni</i18n></h2>
						</div>
                    <div id="blocco_due">
                        <div class="user_agenda">
                             <h3><i18n>i tuoi appuntamenti</i18n></h3>
                             <template_field class="template_field" name="agenda">agenda</template_field>
                             <!-- notifiche eventi -->
            				 <template_field class="template_field" name="events">events</template_field>
            				 <!-- / notifiche eventi -->
                        </div>                        
                        <div class="user_messages">
                             <h3><i18n>messaggi per te </i18n></h3>
                             <template_field class="template_field" name="messages">messages</template_field>
                        </div>
                        <div class="online_user">
                            <h3><i18n>chi e' online </i18n></h3>
                                 <template_field class="template_field" name="chat_users">chat_users</template_field>
                        </div>                        
                    </div>
					</div>
                    <!-- blocco2 end -->
			</div>
			<!-- end due blocchi grafici homepage -->
                        <br class="clearfix">
                    


		    <hr>
                </div>
                <div id="bottomcont">
                </div>
            </div>
            
            <!--  / contenuto -->
            <!-- com_tools -->
            <div id="com_tools">
                <div id="topcom_t">
                </div>
                <div id="bottomcom_t">
                </div>
            </div>
            <!-- /com_tools -->
            <!-- menudestra -->
            <div id="menuright" class="menuright_view ui wide right sidebar">
              <h3 class="ui block dividing center aligned  header"><i class="globe icon"></i><i18n>Contenuti</i18n></h3>
                <div id="menurightcontent">
                  <div class="ui right labeled icon mini fluid top attached button"  onclick="javascript: hideSideBarFromSideBar();">
                    <i class="close icon"></i><i18n>Chiudi</i18n>
                </div>
                  <!-- accordion -->
                  <div class="ui attached segment accordion">
                  
			       <div class="title" onClick="showIndex();">
			         <i class="icon dropdown"></i>
			         <i18n>indice</i18n><i class="sitemap icon" style="float:right;"></i>
                </div>
			       <div class="content field">
			         <div id="show_index">
			             <div class="loader-wrapper">
			                 <div class="ui active inline mini text loader">
			                     <i18n>Caricamento</i18n>...
            </div>
        </div>
                    </div>
                    </div>
		<div class="title">
                     <i class="icon dropdown"></i>
                     <i18n>approfondimenti</i18n><i class="pin icon"></i>
                    </div>
                   <div class="content field">
                     <template_field class="template_field" name="index">index</template_field>
                </div>
                   
                   <div class="title">
                     <i class="icon dropdown"></i>
                     <i18n>collegamenti</i18n><i class="url icon"></i>
                    </div>
                   <div class="content field">
                       <template_field class="template_field" name="link">link</template_field>
                    </div>
                   <div class="title">
                     <i class="icon dropdown"></i>
                     <i18n>risorse</i18n><i class="browser icon"></i>
                   </div>
                   <div class="content field">
                     <template_field class="template_field" name="media">media</template_field>
                   </div>
                  </div>
                  <!-- /accordion -->  
                </div>
            <!-- / menudestra  -->
        </div>
        <!-- / contenitore -->

       
        <!-- pannello video -->
        <div id="rightpanel" class="sottomenu_off rightpanel_view">
            <div id="toprightpanel">
            </div>
            <div id="rightpanelcontent">
                <ul>
                    <li class="close">
                        <a href="#" onClick="hideElement('rightpanel', 'right');">
                            <i18n>chiudi</i18n>
                        </a>
                    </li>
                    <li id="flvplayer">
                    </li>
                </ul>
            </div>
            <div id="bottomrightpanel">
            </div>
        </div>
        <!-- / pannello video -->
        <!-- piede -->
        <div id="footer">
            <template_field class="microtemplate_field" name="footer">footer</template_field>
        </div>
        <!-- / piede -->
    </body>
</html>
