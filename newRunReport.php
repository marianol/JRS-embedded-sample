<?php

//require_once __DIR__ . "/jasperclient_new/autoload.dist.php";
require_once "jasperclient_new/src/Jaspersoft/Client/Client.php";
require_once "jasperclient_new/src/Jaspersoft/Tool/RESTRequest.php";
require_once "jasperclient_new/src/Jaspersoft/Service/ReportService.php";
require_once "jasperclient_new/src/Jaspersoft/Service/OptionsService.php";
require_once "jasperclient_new/src/Jaspersoft/Dto/Options/ReportOptions.php";
require_once "jasperclient_new/src/Jaspersoft/Service/RepositoryService.php";
require_once "jasperclient_new/src/Jaspersoft/Service/Criteria/Criterion.php";
require_once "jasperclient_new/src/Jaspersoft/Tool/Util.php";
require_once "jasperclient_new/src/Jaspersoft/Service/Criteria/RepositorySearchCriteria.php";
require_once "jasperclient_new/src/Jaspersoft/Exception/RESTRequestException.php";
require_once "jasperclient_new/src/Jaspersoft/Dto/Report/InputControl.php";

use Jaspersoft\Client\Client;
use Jaspersoft\Service\Criteria\RepositorySearchCriteria;

class WPReport {
     
    public $client;
    private $mime_types = array(
            //'html' => 'text/html',
            'pdf' => 'application/pdf',
            'xls' => 'application/vnd.ms-excel',
            'csv' => 'text/csv',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'rtf' => 'text/rtf',
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
            'xlsx' => 'application/vnd.ms-excel'
            );
 
    public function __construct() {
        $this->client = new Client('localhost', 8080, 'jasperadmin', 'jasperadmin', '/jasperserver-pro', 'organization_1');
    }
     
  
    /** 
     * run() is to be called via a GET parameter. Using run() will run a report specified by URI and FORMAT get calls.
     * Example: thisfile.php?func=run&uri=/reports/samples/AllAccounts&format=pdf
     * Calling the file in this manner will return the binary of the specified report, in PDF format
     */
    public function run() {
        if(isset($_GET['uri']) && isset($_GET['format'])) {
            $report_data = $this->client->reportService()->runReport($_GET['uri'], $_GET['format']);
            if ($_GET['format'] !== 'html') {
                echo $this->prepareForDownload($report_data, $_GET['format']);
            }
            else {
                echo $report_data;
            }
        }
    }
     
    /**
     * This function prepares a page with the proper headers to initiate a download dialog in modern browsers
     * by using this function we can supply the report binary and users can download the file
     */
    private function prepareForDownload($data, $format) {
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Description: File Transfer');
            header('Content-Disposition: attachment; filename=report.'.$format);
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: ' . strlen($data));
            if(isset($this->mime_types[$format])) {
                header('Content-Type: ' . $this->mime_types[$format]);
            } else {
                header('Content-Type: application/octet-stream');
            }
            echo $data;
    }

    /**
     * This function simply json-ifys the array above to populate a drop-down menu
     * select HTML element. This way it is easy to change the formats available
     */
    public function getTypes() {
        $result = array();
        foreach($this->mime_types as $key => $val) {
            $result[] = array('name' => $key, 'value' => $val);
        }
        echo json_encode($result);
    }

     /* ALERT- THIS FUNCTION DOES NOT WORK YET DUE, REPOSITORY SERVICES WILL ONLY BE IMPLEMENTED
     *         IN 5.5 RELEASE, CURRENTLY 2 VERSIONS AWAY. 
     *
     * This function returns the repository available from the position 'uri'
     * the data is echoed in JSON format so it can be used by a jQuery function
     * to populate a dropdown select HTML element
     * example: thisfile.php?func=getRepo&uri=/public
     * This returns all of the reports in JSON format from the "public" folder down
     */
    public function getRepo() {
        if(isset($_GET['uri'])){
            $result = array();
            $searchCriteria = new RepositorySearchCriteria();
            $searchCriteria->folderUri = $_GET['uri'];

            echo json_encode($searchCriteria->toArray());

            // next line recursively gets every reportUnit from the repository
            $repo = $this->client->repositoryService()->resourceSearch($searchCriteria);
        }
    }

    // ALERT - THIS FUNCTION DOES NOT WORK, PROBABLY DUE TO THE SAME REASON AS ABOVE
    // obtains the report options in json format for the respective report
    public function getReportOptions() {
        if(isset($_GET['uri'])){
           $options = $this->client->optionsService()->getReportOptions($_GET['uri']);
           echo json_encode($options);
        }
    } 

    // obtains the input controls in json format for the respective report
    public function getInputControls() {
        if(isset($_GET['uri'])){
            $ic = $this->client->reportService()->getReportInputControls($_GET['uri']);
            echo json_encode($ic);
        }
    }

} // WPReport
 
/* If the function exists in our class, and it is requested, then run it */
 
if(isset($_GET['func']) && 
    method_exists('WPReport', $_GET['func'])) {
        $r = new WPReport();
        $r->$_GET['func']();
}
?>
