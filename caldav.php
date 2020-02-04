<?php

//https://p64-calendarws.icloud.com/1716171341/calendars/home

//$account = array(
//    'server' => 'p64',
//    'id'    => '1716171341',
//    'user'  => 'sergey.buchok@gmail.com',
//    'pass'  => 'vazo-askt-mias-ezuc'
//);

$account = array(
    'server' => 'p39',
    'id'    => '17101237685',
    'user'  => 'mymail795@gmail.com',
    'pass'  => 'jcay-ucij-ljql-wrge'
);

$uid = 'event-12345';
$url = 'https://'.$account['server'].'-caldav.icloud.com/'.$account['id'].'/calendars/home/' . $uid . '.ics';
$userpwd = $account['user'] .":". $account['pass'];
$description = 'Test event description';
$summary = 'Test event';
$tstart = gmdate("Ymd\THis\Z", strtotime("-2 days"));
$tend = gmdate("Ymd\THis\Z", strtotime("-2 days"));
$tstamp = gmdate("Ymd\THis\Z");

$body = <<<__EOD
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
DTSTAMP:$tstamp
DTSTART:$tstart
DTEND:$tend
UID:$uid
DESCRIPTION:$description
LOCATION:Office
SUMMARY:$summary
END:VEVENT
END:VCALENDAR
__EOD;

$headers = array(
    'Content-Type: text/calendar; charset=utf-8',
    'If-None-Match: *',
    'Expect: ',
    'Content-Length: '.strlen($body),
);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($ch, CURLOPT_USERPWD, $userpwd);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
$content = curl_exec($ch);
curl_close($ch);
var_dump($content);

?>