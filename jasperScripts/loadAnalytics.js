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

        // appends the spinner code into a containing div
	$(".jasperLoadingContainer").append(new Spinner(opts).spin().el);
        
        // smoothly transitions the content and loading spinner as it is being loaded
        $(".jasperLoadingTarget").load(function(){
            $(".jasperLoadingContainer").fadeIn(500);
            $(".jasperLoadingContainer").fadeOut(4500);
            $(this).fadeIn(6500);
        });
        

    });

});
