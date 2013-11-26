jQuery(document).ready(function ($) {

    // gets the script "spin.min.js" which allows you to inject a ajax style loading spinner into the dom
    // function then runs once the script has been fully loaded
    $.getScript("./jasperScripts/spin.min.js", function(){

        var opts = {
            lines: 13,
            length: 12,
            width: 10,
            radius: 10,
            corners: 1,
            rotate: 0,
            trail: 60,
            speed: 1,
            top: 'auto',
            left: 'auto'
        };        
        
        var repoOpts = {
            lines: 13,
            length: 3,
            width: 3,
            radius: 3,
            corners: 1,
            rotate: 0,
            trail: 60,
            speed: 1,
            top: 'auto',
            left: 'auto'
        };
 
        function smoothLoad(){
            $(".jasperLoadingContainer").fadeIn(500);
            $(".jasperLoadingTarget").hide();
            $(".jasperLoadingContainer").fadeOut(4500);
            $(".jasperLoadingTarget").fadeIn(5500);
        }

        // loading screen while the repository is being loaded
        $("#repoLoadContainer").append(new Spinner(repoOpts).spin().el);

        $("#repo_display").ready(function(){
            $("#repoLoadContainer").show();
            window.setTimeout(function(){
                $("#repoLoadContainer").hide();
            }, 2500);
        });

        // loading screen when each report is being loaded
	$(".jasperLoadingContainer").append(new Spinner(opts).spin().el);
 
        // smoothly transitions the content and loading spinner as it is being loaded
        $("#reportBox").on("click", ".repoListItems", function(){
            smoothLoad();
        });
        
        $("#reportBox").on("click", "#options_submit", function(){
            smoothLoad();
        });

        $("#reportBox").on("click", "#refresh_report", function(){
            smoothLoad();
        });

        

    });

});
