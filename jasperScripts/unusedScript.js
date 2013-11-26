$(function(){
// This function is used for displaying a report in a popup. It is commented out since we use an inline version instead.
/*
	$('#displayReport').dialog({
            height:650,
            width:600,
            position:[200,550],
            resizable: false,
            autoOpen: false,
            title: 'View Report',
            modal: true,
            dialogClass: 'dialogWithDropShadow',
            open: function(){},
            buttons: 
            {   
            	Close: function()
                {
                	$(this).dialog("close");
		  	$(this).empty();
                }
            }
    }); 
*/
    // Waiting popup is a jQuery way to let the user know what we're waiting for the report to render.
    // mdahlman had [probably had] a namespace collision with the jasperserver-pro .js files, so it didn't work right.
    // I did not bother trying to troubleshoot, but it could be a useful future enhancement.
    // Instead this script just displays then hides an animated "loading" gif to let the user know that a report is loading.
    //$().waitingpopup();
    //

    // changes the page that #currentPage is showing (injects respective page into #displayPage)
    function changePage() {
        var pages = $('#displayReport .jrPage').clone();
        var curPage = (parseInt($('#currentPage').val()) - 1);
        var content = pages.eq(curPage);
        $('#displayPage').html(content);
    }
    
   
    // display loading gif while dropdowns are being populated and initial report is being loaded
    $('#loading').show();	
     
    // populate the dropdown box for the names of the reports
    $.getJSON("./runreport.php?func=getReports&uri=/public/Samples/Reports",
        function(data){
            var sel = $("#reportList").empty();
            $.each(data, function(){
                sel.append($("<option />").val(this.uri).text(this.name));
            });
            $('#displayReport').load('runreport.php?func=run&uri='+jQuery('#reportList').val()+'&format=html', function(){
                $('#loading').show();
                $('#currentPage').val(1);
                var pages = $("#displayReport .jrPage");
                var numOfPages = pages.length;
                $('#numberOfPages').val(numOfPages);
                $('#loading').hide();
            });
            changePage();
        }
    );
     
    // populate the dropdown for types of export
    $.getJSON("./runreport.php?func=getTypes",
        function(data){
            var sel = $("#exportList").empty();
            $.each(data, function(){
                sel.append($("<option />").val(this.name).text(this.name));
            });
        }
    );
     
    // on 'export' get the report and activate hidden iframe to trigger download
    $('#reportExport').on('click', function(event) {
        document.getElementById("hFrame").src = './runreport.php?func=run&uri='+$('#reportList').val()+'&format='+$('#exportList').val(); 
    });
    
    // binds to #reportList selector and runs when the selected report changes, it smoothly loads the newly selected report
    $('#reportList').on('change', function(event) {
        //$('#displayReport').hide();
        $('#displayPage').hide();
        $('#loading').show();
        $('#displayReport').load('runreport.php?func=run&uri='+jQuery('#reportList').val()+'&format=html', function() {
            $('#loading').hide();
            //$('#displayReport').show();
            var pages = $("#displayReport .jrPage");
            var numOfPages = pages.length;
            $('#currentPage').val("1");
            $('#numberOfPages').val(numOfPages);
            changePage();
            $('#displayPage').show();
        });
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
