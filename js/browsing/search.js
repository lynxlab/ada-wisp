function initDoc()
{
    $j('input, a.button, button').uniform();
    var showElement=$j('#s_AdvancedForm').val();
    
    if(showElement==1)
    {
        $j("#div_advancedSearch_form").css("display","block");
        $j("#div_form").css("display","none");
        $j("#div_menu").css("display","none");
        $j("#advanced_searchLink").css("display","none");
        //$j("#contentcontent").css("height","800px");
        $j('#s_AdvancedForm').val("0");
      
    }
  
    
    dataTablesExec();
    
}

function dataTablesExec() {
    var datatable = $j('#table_result').dataTable();
}



function advancedSearch()
{
    $j("#div_form").animate({"height": "toggle"}, { duration: 400 });
    $j("#div_form").css("display","none");
    $j("#div_menu").css("display","none");
    $j("#div_Result").animate({"height": "toggle"}, { duration: 400 });
    $j("#div_Result").css("margin-top","120px");
    $j("#div_advancedSearch_form").animate({"height": "toggle"}, { duration: 400 });
    $j("#advanced_searchLink").css("display","none");
    $j("#div_menuAdvanced").css("display","block");
    
}
function simpleSearch()
{
    $j("#div_advancedSearch_form").animate({"height": "toggle"}, { duration: 400 });
    $j("#div_menu").css("display","none");
    $j("#div_Result").animate({"height": "toggle"}, { duration: 400 });
    $j("#div_form").css("display","block");
    $j("#div_menuAdvanced").css("display","block");
    $j("#advanced_searchLink").css("display","block");
    $j("#advanced_searchLink").css("margin-top","90px");
}

function disableForm()
{
   $j('#s_AdvancedForm').val("1");
}
