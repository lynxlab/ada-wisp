<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <!--link rel="stylesheet" href="../../../css/comunica/default/default.css" type="text/css"-->
  <link rel="stylesheet" href="../../../css/comunica/masterstudio_stabile/default.css" type="text/css">
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
<div id="status_bar">
    <!--dati utente-->
       <template_field class="microtemplate_field" name="user_data_micro">user_data_micro</template_field>
     <!-- / dati utente -->
</div>

<!--dati utente-->
<div id="user_wrap">

<!-- label -->
<div id="label">
   <h1><template_field class="template_field" name="label">label</template_field></h1>
</div><!-- /label -->
</div> <!-- / dati utente -->

<!-- contenuto -->
<div id="content">	 
<div id="contentcontent">
  <div class="first">
		<template_field class="template_field" name="data">data</template_field>
			 </div>
</div>
<div id="bottomcont">
</div>
</div> <!--  / contenuto --> 
</div> <!-- / contenitore -->
		<div id="push"></div>
		</div>

<!-- PIEDE -->
<div id="footer">
		 <template_field class="microtemplate_field" name="footer">footer</template_field>
</div> <!-- / piede -->
</body>
</html>