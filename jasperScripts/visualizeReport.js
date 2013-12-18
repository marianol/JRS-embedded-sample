jQuery.getScript("http://localhost:8080/jasperserver-pro/bif/visualize.js");
	
BIF.init({
    domain: 'http://localhost:8080/jasperserver-pro',
    username: 'superuser',
    password: 'superuser',
    mods: ['reports']
}, function(Reports) {
    myReport = Reports.open({
        uri: '/public/Samples/Reports/1._Geographic_Results_by_Segment_Report',
        container: document.getElementById('report-container'),
        onReportFinished: function() {
            $('#overlay').hide();
        }
    });           
    
    myReport.rdy.then(function() {
        $.each(myReport.components.charts[0].chartTypes, function(i, type) {
            $('#chartypes').append('<option id="'+type+'">'+type+'</option>');
        });
    });
    
    $('#chartypes').change(function() {
        var type = $(this).val();
        type && myReport.components.charts[0].changeType(type);
    });
});