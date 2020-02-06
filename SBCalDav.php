<?php
/**
 * SBCalDav Class
 *
 * @package SBitsoft 
 * @author sbitsoft.com
 * @copyright 2020
 */
class SBCalDav{	
	private $url;
	private $user;
	private $passwd;
	private $type;
	private $userID;
	private $calendars = array();
	private $types_url = array(
		'icloud' => array('client_url' => 'https://contacts.icloud.com/')
	);
	
	private $request = array(
		'principal' => '<?xml version="1.0" encoding="UTF-8"?><d:propfind xmlns:d="DAV:"><d:prop><d:current-user-principal/></d:prop></d:propfind>',
		'calendars' => '<?xml version="1.0" encoding="UTF-8"?><d:propfind xmlns:d="DAV:"><d:prop><d:displayname/><d:resourcetype/></d:prop></d:propfind>',
		'calendar_filter' => '<?xml version="1.0" encoding="utf-8" ?><C:calendar-query xmlns:D="DAV:" xmlns:C="urn:ietf:params:xml:ns:caldav"><D:prop><C:calendar-data/></D:prop>%s</C:calendar-query>',
		'date_filter' => '<C:filter><C:comp-filter name="VCALENDAR"><C:comp-filter name="VEVENT">%s</C:comp-filter>    </C:comp-filter></C:filter>',
		'get_filter' => '<?xml version="1.0" encoding="utf-8" ?><C:calendar-query xmlns:D="DAV:" xmlns:C="urn:ietf:params:xml:ns:caldav">
						<D:prop><D:getetag />        
							<C:calendar-data>            
								<C:comp name="VCALENDAR">
									<C:prop name="VERSION"/>
									<C:comp name="VEVENT">
										<C:prop name="SUMMARY"/>
										<C:prop name="DESCRIPTION"/>
										<C:prop name="LOCATION"/>
										<C:prop name="UID"/>
										<C:prop name="DTSTART"/>
										<C:prop name="DTEND"/>
										<C:prop name="ATTENDEE"/>
									</C:comp>
								<C:comp name="VTIMEZONE"/>
								</C:comp>                    
							</C:calendar-data>    
						</D:prop><C:filter><C:comp-filter name="VCALENDAR"><C:comp-filter name="VEVENT">%s</C:comp-filter></C:comp-filter></C:filter></C:calendar-query>'
	);
	
	
	
	/**
     * Creates a new object for managing values
     * @param string $url
     * @param string $user
     * @param string $passwd
     * @param string $type  server icloud|
     */
	function __construct($url, $user, $passwd, $type = 'icloud'){
		$this->url = $url;
		$this->user = $user;
		$this->passwd = $passwd;
		$this->type = $type;
	}
	
	/**
     * Send Request to url
     * @param string $url 
     * @param string $post  post params
     */
	function doRequest($url, $post, $req = "PROPFIND") {
//		var_dump($url);
		$c = curl_init($url);
		curl_setopt($c, CURLOPT_HTTPHEADER, array("Depth: 1", "Content-Type: text/xml; charset='UTF-8'", 
					"User-Agent: DAVKit/4.0.1 (730); CalendarStore/4.0.1 (973); iCal/4.0.1 (1374); Mac OS X/10.6.2 (10C540)"));
		curl_setopt($c, CURLOPT_HEADER, 0);
		curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($c, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($c, CURLOPT_USERPWD, $this->user . ":" . $this->passwd);
		curl_setopt($c, CURLOPT_CUSTOMREQUEST, $req);
		curl_setopt($c, CURLOPT_POSTFIELDS, $post);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		$data=curl_exec($c);
		curl_close($c);
//		var_dump($data);
		return $data;
	}
	/**
	 * Do a CalDAV PUT request to add an iCloud event.
	 *
	 * @access private
	 * @param string $url
	 * @param string $data
	 * @return string
	 */
	private function doPutRequest($url, $headers, $body, $req) {

//		$headers = array(
//			'Content-Type: text/calendar; charset=utf-8',
//			'If-None-Match: *',
//			'Expect: ',
//			'Content-Length: '.strlen($body),
//		);
		// Initialize cURL
		$c = curl_init($url);
		// Set headers
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($c, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($c, CURLOPT_USERPWD, $this->user . ":" . $this->passwd);
		curl_setopt($c, CURLOPT_CUSTOMREQUEST, $req);
		curl_setopt($c, CURLOPT_POSTFIELDS, $body);

		// Execute and return value
		$data = curl_exec($c);
		curl_close($c);
		return $data;
	}
	
	/**
     * get userID for icloud
     */
	public function init(){
		$response = simplexml_load_string($this->doRequest( $this->types_url[$this->type]['client_url'], $this->request['principal']));
		if ($response === false) return false;
		$principal_url = $response->response[0]->propstat[0]->prop[0]->{'current-user-principal'}->href;
		$userID = explode("/", $principal_url);
		$this->userID = $userID[1];
//		var_dump($this);
		return true;
	}
	
	/**
     * get user calendar lists
     */
	public function getListCalendars(){
		$response = simplexml_load_string($this->doRequest($this->url.$this->userID."/calendars/", $this->request['calendars']));
		if ($response === false) return false;
		foreach($response->response as $cal){
			$entry["href"]=$cal->href;
			$entry["name"]=$cal->propstat[0]->prop[0]->displayname;
			if ($entry["name"] != '') $this->calendars[] = $entry;
		}
//		var_dump($this->calendars);
		return true;
	}	
	
	/**
     * get user events lists
     */
	public function getEvents($calandar, $start, $finish){
		$range = "<C:time-range start=\"$start\" end=\"$finish\"/>";

		$query = sprintf($this->request["get_filter"], $range);
		
		$response = $this->doRequest($this->url.$this->userID."/calendars/".$calandar.'/', $query, 'REPORT');
		if ($response === false) return false;

		$this->ParseResponse($response);
		$xml_parser = xml_parser_create_ns('UTF-8');
		$this->xml_tags = array();
		xml_parser_set_option ( $xml_parser, XML_OPTION_SKIP_WHITE, 1 );
		xml_parse_into_struct( $xml_parser, $this->xmlResponse, $this->xml_tags );
		xml_parser_free($xml_parser);

		$report = array();
		foreach( $this->xml_tags as $k => $v ) {
			switch( $v['tag'] ) {
				case 'DAV::RESPONSE':
					if ( $v['type'] == 'open' ) {
						$response = array();
					}
					elseif ( $v['type'] == 'close' ) {
						$report[] = $response;
					}
					break;
				case 'DAV::HREF':
					$response['href'] = basename( $v['value'] );
					break;
				case 'DAV::GETETAG':
					$response['etag'] = preg_replace('/^"?([^"]+)"?/', '$1', $v['value']);
					break;
				case 'URN:IETF:PARAMS:XML:NS:CALDAV:CALENDAR-DATA':
					$response['data'] = $v['value'];
					break;
			}
		}
        echo '<pre>' . var_export($report, true) . '</pre>';

		return $report;

	}

	/**
	 * Add new iCloud event.
	 *
	 * @access public
     * @param string $calandar
	 * @param string $date_time_from Format: yyyy-mm-dd HH:ii:ss
	 * @param string $date_time_to Format: yyyy-mm-dd HH:ii:ss
	 * @param string $title
	 * @param string $description (Optional)
	 * @param string $location (Optional)
     * @param string $eid (Optional)
	 * @return string
	 */
	public function add_event($calandar, $date_time_from, $date_time_to, $title, $description = "", $eid = "", $attendee = "") {


        if (!empty($eid)) {
            $headers = array(
                'Content-Type: text/calendar; charset=utf-8',
                'If: '.$eid,
                'Expect: ',
            );
        } else {
            $headers = array(
                'Content-Type: text/calendar; charset=utf-8',
                'If-None-Match: *',
                'Expect: ',
            );
            // Set random event_id
            $eid = md5('event-'.rand(1000000, 9999999).time());
        }


		// Set current timestamp
		$tstamp = gmdate("Ymd\THis\Z");

		// Build ICS content
        $body  = "BEGIN:VCALENDAR\n";
        $body .= "VERSION:2.0\n";
        $body .= "BEGIN:VEVENT\n";
        $body .= "DTSTAMP:".$tstamp."\n";
        $body .= "UID:".$eid."\n";
		if (!empty($date_time_from)) {
			// Set date end
			$tstart = gmdate("Ymd\THis\Z", strtotime($date_time_from));
			$body .= "DTSTART:".$tstart."\n";
		}
		if (!empty($date_time_to)) {
			// Set date end
			$tend = gmdate("Ymd\THis\Z", strtotime($date_time_to));
			$body .= "DTEND:".$tend."\n";
		}
        if (!empty($description)) {
            $body .= "DESCRIPTION:".$description."\n";
        }
//        if (!empty($location)) {
//            $body .= "LOCATION:".$location."\n";
//        }
        if (!empty($title)) {
            $body .= "SUMMARY:".$title."\n";
        }
        if (!empty($attendee)) {
            $body .= "ATTENDEE;CUTYPE=INDIVIDUAL;EMAIL=".$attendee.";SCHEDULE-STATUS=5.1:mailto:".$attendee."\n";
        }
        $body .= "END:VEVENT\n";
        $body .= "END:VCALENDAR\n";

        $headers[] = 'Content-Length: '.strlen($body);


		// Do request
		$url = $this->url.$this->userID.'/calendars/'.$calandar.'/' . $eid . '.ics';

		$response = $this->doPutRequest($url, $headers, $body, 'PUT');
//        echo '<pre>' . var_export($response, true) . '</pre>';

		return $eid;
	}
    /**
     * DELETE a text/icalendar resource
     *
     * @param string $eid The eid of an existing resource to be deleted, or '*' for any resource at that URL.
     *
     * @return int The HTTP Result Code for the DELETE
     */
    function DoDELETERequest( $calandar, $eid ) {

        $headers = array(
            'Content-Type: text/calendar; charset=utf-8',
            'If: '.$eid,
            'Expect: ',
            'Content-Length: 0',
        );
        $url = $this->url.$this->userID.'/calendars/'.$calandar.'/' . $eid . '.ics';

        $response = $this->doPutRequest($url, $headers, "", 'DELETE');
        return $response;
    }

	/**
	 * Split response into httpResponse and xmlResponse
	 *
	 * @param string Response from server
	 */
	function ParseResponse( $response ) {
		$pos = strpos($response, '<?xml');
		if ($pos === false) {
			$this->httpResponse = trim($response);
		}
		else {
			$this->httpResponse = trim(substr($response, 0, $pos));
			$this->xmlResponse = trim(substr($response, $pos));
		}
	}

}
?>
