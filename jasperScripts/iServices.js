 /*
 * Web Services Script - A Javascript/jQuery script that implements functionality and UI on the iFrame/web services page on the
 *                       "Simple Corporate Demo Portal" website. It can be accessed through the "My Reports" dropdown
 *                       in the main menu.
 */

jQuery(document).ready(function ($) {
       
        // gets all the reports from the public folder down in the repository and creates a nested list to create a file directory browser
	$.getJSON("./runreport.php?func=getRepository&uri=/public",
		function(data){
		    $.each(data, function(){
			var uriSplit = this.uri.split("/");
                        var idJoin = "";
                        var prevIdJoin = "";

                        var targetString = "target=\"report_viewer\"";
                        var loginCredentials = "j_username=demo&j_password=JasperDemo";
                        var baseURL = "href=\"/jasperserver-pro/flow.html?_flowId=viewReportFlow&standAlone=true&decorate=no&"+loginCredentials+"&_flowId=viewReportFlow&reportUnit="
                        
                        for(var i = 1; i < uriSplit.length; i++){
                            idJoin += uriSplit[i]; 
                            
                            if($("#"+idJoin).length == 0){
                                if(i == uriSplit.length-1){
                                    $("#"+prevIdJoin).append("<li class=\"repoListItems\" id=\""+idJoin+"\" style=\"display: none;\"><a "+targetString+
                                                             " "+baseURL+this.uri+"\" >"+this.label+"</a></li>");
                                }else{
                                    $("#"+prevIdJoin).append("<ul class=\"repoFolderItems\" id=\""+idJoin+"\">"+uriSplit[i]+"</ul>");                                    
                                }
                            }
                            
                            prevIdJoin = idJoin;
                        }
                    });
                }           
        );

        // animates the folder in the file browser
        $(".repoFolderItems").bind("click", function(event){
            if($(event.target).hasClass("repoFolderItems")){
                $("#"+event.target.id).children().slideToggle("slow");
                $("#"+event.target.id).toggleClass("open");
            }
        });

        // animates the collapse panel
        $(".verticalWritingContainer").click(function(){
            if($("#repo_display").is(":visible")){
                $("#repo_display").hide();
                $("#report_viewer").css({"width": "95%"});
                $(this).toggleClass("open");
                $(".verticalWriting").text("Show container");
            }else{
                $("#report_viewer").css({"width": "49%"});
                $("#repo_display").fadeIn("slow");                
                $("#report_viewer").fadeIn("slow");
                $(this).toggleClass("open");
                $(".verticalWriting").text("Hide container");
            }
        });
 
});
