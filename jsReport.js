
jQuery(function(){
 	
    // Manage AJAX loading image
    jQuery('#loading').hide();
 
    jQuery('#loading').bind("ajaxStart", function(){
        jQuery(this).show();
    }).bind("ajaxStop", function() {
        jQuery(this).hide();
    });
     
    // populate the dropdown box for the names of the reports
    jQuery.getJSON("./runreport.php?func=getReports&uri=/reports/samples",
        function(data){
            var sel = jQuery("#reportList").empty();
                jQuery.each(data, function(){
                    sel.append(jQuery("<option />").val(this.uri).text(this.name));
        });
    });
     
    // populate the dropdown for types of export
    jQuery.getJSON("./runreport.php?func=getTypes",
        function(data){
            var sel = jQuery("#exportList").empty();
                jQuery.each(data, function(){
                    sel.append(jQuery("<option />").val(this.name).text(this.name));
        });
    });
     
    // on 'submit' get the report. display html in div, other formats activate hidden iframe to trigger download
    jQuery('#repsub').on('click', function(event) {
        if (jQuery('#exportList').val() !== 'html') {
            document.getElementById("hFrame").src = './runreport.php?func=run&uri='+jQuery('#reportList').val()+'&format='+jQuery('#exportList').val();
        } else {
            jQuery('#displayReport').load('runreport.php?func=run&uri='+jQuery('#reportList').val()+'&format='+jQuery('#exportList').val());
        }
 
    });
     
});
