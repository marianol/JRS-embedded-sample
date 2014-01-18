/*
 * Web Services Script - A Javascript/jQuery script that implements functionality and UI on the web services page on the
 *                       "Simple Corporate Demo Portal" website. It can be accessed through the "My Reports" dropdown
 *                       in the main menu.
 */

jQuery(document).ready(function ($) { 
      
    var DIRECTORY_TO_SEARCH_FROM = "/public"; // this is the directory that the repository search will start and recurse downwards from

        // gets all the reports from the public folder down in the repository and creates a nested list to create a file directory browser
	$.getJSON("./runreport.php?func=getRepository&uri="+DIRECTORY_TO_SEARCH_FROM,
		function(data){
		    $.each(data, function(){
                var uriSplit = this.uri.split("/");
                var idJoin = "";
                var prevIdJoin = "";

                var targetString = "target=\"report_viewer\"";
                var loginCredentials = "j_username=demo&j_password=JasperDemo";
                //var baseURL = "href=\"/jasperserver-pro/flow.html?_flowId=viewReportFlow&standAlone=true&decorate=no&"+loginCredentials+"&_flowId=viewReportFlow&reportUnit="
                
                for(var i = 1; i < uriSplit.length; i++){
                    idJoin += uriSplit[i]; 
                    
                    if($("#"+idJoin).length == 0){
                        if(i == uriSplit.length-1){
                            $("#"+prevIdJoin).append("<li class=\"repoListItems\" id=\""+idJoin+"\"><a href=\""+this.uri+"\" onclick=\"loadHref(this.href);return false;\">"+this.label+"</a></li>");
                        }else{
                            $("#"+prevIdJoin).append("<ul class=\"repoFolderItems\" id=\""+idJoin+"\" style=\"display: none;\">"+uriSplit[i]+"</ul>");
                        }
                    }
                    
                    prevIdJoin = idJoin;
                }
            });
            $("#public").click();  
        }           
    );

        // binds a click event to the folder items to animate opening and closing of the folders
        $(".repoFolderItems").bind("click", function(event){
            // stops a list item from being hidden by mistake
            if($(event.target).hasClass("repoFolderItems")){
                $("#"+event.target.id).children(".repoFolderItems").slideToggle("slow");
                $("#"+event.target.id).children(".repoFolderItems").children().slideToggle("slow");
                $("#"+event.target.id).toggleClass("open");
            }
        });

        // animates the collapsible panel
        $(".verticalWritingContainer").on("click", function(){
            if($("#repo_display").is(":visible")){
                $("#repo_display").fadeOut("slow");
                //$("#report_viewer").animate({"width": "90%"}, "slow");
                $(this).toggleClass("open");
                $(".verticalWriting").text("Show container");
            }else{
                //$("#report_viewer").animate({"width": "40%"}, "slow");
                $("#repo_display").fadeIn("slow");
                $(this).toggleClass("open");
                $(".verticalWriting").text("Hide container");
            }
        });  
     
    // populate the dropdown for types of export
    $.getJSON("./runreport.php?func=getTypes",
        function(data){
            var sel = $("#exportList").empty();
            $.each(data, function(){
                sel.append($("<option />").val(this.name).text(this.name));
            });
        }
    );

    // when the submit button is clicked it will get the values from the Input Controls and run the current report again
    $("#report_options_bar").on("click", "#options_submit", function() {
        var ICInfo = ""; // used to store Input Control information

        // fetch the currently selected input controls
        $(".ICVals").each(function(){
           var curId = $(this).attr("id");

           switch($(this).attr("name")){
               case "singleValueNumber":
                   ICInfo += curId+"="+$(this).val()+"&";
                   break;
               case "singleSelect":
                   ICInfo += curId+"="+$(this).val()+"&";
                   break;
               case "multiSelect":
                   $($(this).val()).each(function(){
                       ICInfo += curId+"="+this+"&";
                   }); 
                   break;
               case "singleValueDate":
                   ICInfo += curId+"="+$(this).val()+"&";
                   break;
               case "singleValueDatetime": 
                   ICInfo += curId+"="+$(this).val()+" 00:00:00"+"&";
                   break;
               default:
                   alert("Input control type "+$(this).attr("name")+" is not supported..."); 
           }
        });

        //alert(ICInfo);
        ICInfo = ICInfo.replace(/ /g, "%20"); // have to encode spaces in their ASCI code format
       
        // run the report with Input Controls
        jQuery("#displayReport").load("../../jasperserver-pro/rest_v2/reports"+clickedURL+".html?"+ICInfo, function(){
	       updatePageInfo();
	       changePage();
           refreshPage();
        });
    });

    // refresh the report by re-running the currently selected report
    $('#refresh_report').on('click', function(event) {
        jQuery("#displayReport").load("runreport.php?func=run&uri="+clickedURL+"&format=html", function(){
	       updatePageInfo();
	       changePage();
           updateInputControls(clickedURL);
           refreshPage(); 
        });
    });

    // on 'export' get the report and activate hidden iframe to trigger download
    $('#reportExport').on('click', function(event) {
        document.getElementById("hFrame").src = './runreport.php?func=run&uri='+clickedURL+'&format='+$('#exportList').val(); 
    });

    // binds to #prevPage and modifies #currentPage text input
    $('#prevPage').on('click', function(event) {    
        var curPageElement = $('#currentPage');
        if(parseInt(curPageElement.val()) > 1){
            curPageElement.val(parseInt(curPageElement.val())-1);
            changePage();
        }
    });
    
    // binds to #nextPage and modifies #currentPage text input
    $('#nextPage').on('click', function(event) {
        var curPageElement = $('#currentPage');
        if(parseInt(curPageElement.val()) < $("#displayReport .jrPage").length){
            curPageElement.val(parseInt(curPageElement.val())+1);
            changePage();
        }
    });

    $('#currentPage').on('change', function(event) {
        changePage();
    });
 
});

//
// ************* functions below have to be outside of the "document ready" anonymous function in order to be called in another document *************
//


// changes the page that #currentPage is showing (injects respective page into #displayPage)
// gets teh set of all ".jrPage"s in the #displayReport div, clones them and then sets index curPage
// as the active page in the "#displayPage" div
function changePage() {
    var pages = jQuery('#displayReport .jrPage').clone();
    var curPage = (parseInt(jQuery('#currentPage').val()) - 1);
    var content = pages.eq(curPage);
    jQuery('#displayPage').html(content);
}

// updates the info in the pagination controls
// gets the set of all ".jrPage"s in the #displayReport div and gets the length of that set
function updatePageInfo(){
    jQuery('#currentPage').val(1);
    var pages = jQuery("#displayReport .jrPage");
    var numOfPages = pages.length;
    jQuery('#numberOfPages').val(numOfPages);
}


// the following process functions are for injecting the different Input Controls into the options menu
function processSingleValue(target, inputControl){
    var currentId = inputControl.id;
    //alert(inputControl.state.value);
    var default_value = inputControl.state.value;
    target.append(jQuery("<input>").attr("type", "text").val(default_value).attr("name", inputControl.type).attr("id", currentId).addClass("inputControls ICVals"));
}

function processSingleSelect(target, inputControl){
    var currentId = inputControl.id;
    target.append(jQuery("<select>").attr("id", currentId).attr("name", inputControl.type).addClass("inputControls ICVals"));
    //alert(JSON.stringify(inputControl.state.options));

    jQuery.each(inputControl.state.options, function(index){ 
	//alert("this.value: "+this.value+"   this.label: "+this.label);      
	jQuery("#"+currentId).append($("<option />").val(this.value).text(this.label));
    });   
}

function processMultiSelect(target, inputControl){
    var currentId = inputControl.id;
    target.append(jQuery("<select multiple>").attr("id", currentId).attr("name", inputControl.type).addClass("inputControls ICVals"));
    //alert(JSON.stringify(inputControl.state.options));

    jQuery.each(inputControl.state.options, function(index){ 
	//alert("this.value: "+this.value+"   this.label: "+this.label);      
	jQuery("#"+currentId).append($("<option />").val(this.value).text(this.label));
    }); 
}

function processSingleDate(target, inputControl){
    var currentId = inputControl.id;

    target.append(jQuery("<input>").attr("type", "date").attr("id", currentId).addClass("inputControls ICVals").attr("name", inputControl.type).val(inputControl.state.value));
}

function processDatetime(target, inputControl){
    var currentId = inputControl.id;
    var defaultValue = inputControl.state.value.replace(" 00:00:00", "");

    target.append(jQuery("<input>").attr("type", "date").attr("id", currentId).addClass("inputControls ICVals").attr("name", inputControl.type).val(defaultValue));
    //$.getScript("./jasperScripts/jquery-ui-timepicker-addon.js", function(){
    //    $(currentId).datetimepicker();
    //});
}


//
function updateInputControls(uri){
    var options_bar = jQuery("#report_options_content").empty();

    jQuery.get("../../jasperserver-pro/rest/login?j_username=demo&j_password=JasperDemo", function(){
    });
    //jQuery.getJSON("newRunReport.php?func=getInputControls&uri="+uri, function(data){
    jQuery.getJSON("../../jasperserver-pro/rest_v2/reports"+uri+"/inputControls/", function(data){
        //alert(JSON.stringify(data));
        
        jQuery.each(data.inputControl, function(index){
            //alert("label: "+this.label+"   type: "+this.type);
            options_bar.append(jQuery("<label>").text(this.label).addClass("inputControls"));
            
            switch(this.type){
                case "singleValueNumber":
                    processSingleValue(options_bar, this);
                    break;
                case "singleSelect":
                    processSingleSelect(options_bar, this);
                    break;
                case "multiSelect":
                    processMultiSelect(options_bar, this);
                    break;
                case "singleValueDate":
                    processSingleDate(options_bar, this);
                    break;
                case "singleValueDatetime":
                    processDatetime(options_bar, this);
                    break;
                default:
                    alert("Type "+ this.type  +" Input Control not handled... Default value if one: "+this.state.value);
            }
        });
    }); 
}

// used to make sure graphs load correctly in the report page
function refreshPage(){
    setTimeout(function(){
        jQuery("#currentPage").val(0);
        changePage();
        jQuery("#currentPage").val(1);
        changePage();
    }, 1000);
}


// used to store what report was last clicked by the user for the process of exporting it
var clickedURL = "";

// called by onclick event by the list items so that the respective report loads when one is clicked
function loadHref(href){
    var index = href.indexOf("/public");
    clickedURL = href.substring(index);
    jQuery("#displayReport").load("runreport.php?func=run&uri="+clickedURL+"&format=html", function(){
	   updatePageInfo();
	   changePage();
        updateInputControls(clickedURL);
        refreshPage(); 
    });
}
