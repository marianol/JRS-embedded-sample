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
});