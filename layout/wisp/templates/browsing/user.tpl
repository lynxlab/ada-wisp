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
            <div id="welcomeMsg">
                <template_field class="template_field" name="welcome_msg">welcome_msg</template_field>
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
                <div id="holisuserhome">
                
                    <div id="funzioni" class="rotate"><span>Sottosistemi e Funzioni</span></div>
                    <div id="funzioni1"><span>Le fonti informative a supporto degli Operatori
                    del mondo Giustizia</span></div>
                    <div id="funzioni2"><span>Il DataWarehouse del PCT a supporto della gestione
                    degli Organi della Giustizia</span></div>
                    <div id="funzioni3"><span>Conoscere e valutare l'impatto di una<br/>nuova legge</span></div>
                    
                    <div id="servizi" class="rotate"><span>Servizi</span></div>
                    <div id="servizi1"><span>Temi risolti</span></div>
                    <div id="servizi2"><span>Consulenza online</span></div>
                    <div id="servizi3"><span>Esempi e corsi per conoscere e<br/>ottimizzare l'uso del sistema</span></div>
                    <div id="servizi4"><span>Data base Fonti informative</span></div>
                    
                    <div id="giurhome"><a href="<template_field class="template_field" name="giurLink">giurLink</template_field>">GIUR</a></div>
                    <div id="orghome"><a href="<template_field class="template_field" name="orgLink">orgLink</template_field>">ORG</a></div>
                    <div id="leghome"><a href="<template_field class="template_field" name="legLink">legLink</template_field>">LEG</a></div>
                    
                    <div id="soluzionihome"><a href="<template_field class="template_field" name="soluzioniLink">soluzioniLink</template_field>">SOLUZIONI</a></div>
                    <div id="etutoringhome"><a href="<template_field class="template_field" name="etutoringLink">etutoringLink</template_field>">eTUTORING</a></div>
                    <div id="elearninghome"><a href="<template_field class="template_field" name="elearningLink">elearningLink</template_field>">eLEARNING</a></div>
                    <div id="fontihome"><a href="<template_field class="template_field" name="fontiLink">fontiLink</template_field>">FONTI</a></div>
                    
                </div>


			<div id="twobox">
				<div id="boxone">
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
				<!-- blocco1 end -->			<!-- giorgio, blocco "3" con RSS -->
				<!--div id="blocco_tre">
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