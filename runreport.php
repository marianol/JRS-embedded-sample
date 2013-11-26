<?php
require_once('jasperclient/client/JasperClient.php');
 
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
        $this->client = new Jasper\JasperClient('localhost', 8080, 'demo', 'JasperDemo', '/jasperserver-pro', 'organization_1');
    }
     
  
    /** 
     * run() is to be called via a GET parameter. Using run() will run a report specified by URI and FORMAT get calls.
     * Example: thisfile.php?func=run&uri=/reports/samples/AllAccounts&format=pdf
     * Calling the file in this manner will return the binary of the specified report, in PDF format
     */
    public function run() {
        if(isset($_GET['uri']) && isset($_GET['format'])) {
            $report_data = $this->client->runReport($_GET['uri'], $_GET['format']);
            if ($_GET['format'] !== 'html') {
            echo $this->prepareForDownload($report_data, $_GET['format']);
            }
            else {
                echo $report_data;
            }
        }
    }

     /** NOT GOING TO USE THIS FUNCTION AS PASSING THE INPUT CONTROLS IS MORE AWKWARD THEN MAKING A RAW REST CALL IN JQUERY
     * runWithIC() is to be called via a GET parameter. Using runWithIC() will run a report specified by URI, FORMAT and Input Controls get calls.
     * Example: thisfile.php?func=runWithIC&uri=/reports/samples/AllAccounts&format=pdf&inputControls=~JSON Data~
     * Calling the file in this manner will return the binary of the specified report, in PDF format
     */
    public function runWithIC() {
        if(isset($_GET['uri']) && isset($_GET['format'])) {
            $report_data = $this->client->runReport($_GET['uri'], $_GET['format'], null, null, ($_GET['inputControls']));
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
     * This function returns the reports vailable at the position 'uri'
     * the data is echoed in JSON format so it can be used by a jQuery function
     * to populate a dropdown select HTML element
     * example: thisfile.php?func=getReports&uri=/reports/samples
     */
    public function getReports() {
   	if(isset($_GET['uri'])) {
            $result = array();
            $repo = $this->client->getRepository($_GET['uri']);
            foreach($repo as $r) {
                $result[] = array('name' => $r->getLabel(), 'uri' => $r->getUriString());
            }
            sort($result);
            echo json_encode($result);
        }
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

     /**
     * This function returns the repository available from the position 'uri'
     * the data is echoed in JSON format so it can be used by a jQuery function
     * to populate a dropdown select HTML element
     * example: thisfile.php?func=getRepo&uri=/public
     * This returns all of the reports in JSON format from the "public" folder down
     */
    public function getRepo() {
   	if(isset($_GET['uri'])) {
            $result = array();
            // next line recursively gets every reportUnit from the repository
            $repo = $this->client->getRepository($_GET['uri'], null, 'reportUnit', '1');
            foreach($repo as $r) {
                $result[] = array('name' => $r->getLabel(), 'uri' => $r->getUriString(), 'type' => $r->getWsType());
            }
            sort($result);
            echo json_encode($result);
        }
    }


    //
    public function getResourceInfo() {
        if(isset($_GET['uri'])){
            echo $this->client->getResource($_GET['uri']);
        }
    }


    /** This function retrieves the descriptor for the resource at $path in form $file.
	 *  for other paramaters.. see web services documentation
	 *
	 * @param string $path
	 * @param string $file
	 * @param string $ic_get_query_data
	 * @param string $p_param
	 * @param string $pl_param
	 * @return \Jasper\ResourceDescriptor
	 */
	//public function getResource($path, $file = null, $ic_get_query_data = null, $p_param = null, $pl_param = null) {
	//	$url = $this->restUrl . '/resource' . $path;
	//	$suffix = http_build_query(array('file' => $file, 'IC_GET_QUERY_DATA' => $ic_get_query_data));
	//	if (!empty($suffix)) { $url .= '?' . $suffix; }
	//	$data = $this->prepAndSend($url, 200, 'GET', null, true);
//
//		return ResourceDescriptor::createFromXML($data);
//	}



} // WPReport
 
/* If the function exists in our class, and it is requested, then run it */
 
if(isset($_GET['func']) && 
    method_exists('WPReport', $_GET['func'])) {
        $r = new WPReport();
        $r->$_GET['func']();
}
?>
