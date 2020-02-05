<?
	include('SBCalDav.php');
	$sb = new SBCalDav('https://p39-caldav.icloud.com/', 'mymail795@gmail.com', 'jcay-ucij-ljql-wrge');
	$sb->init();
	$sb->getListCalendars();

    $range_time_from = date("Y-m-d H:i:s", strtotime("-1 days"));
    $range_time_to = date("Y-m-d H:i:s", strtotime("-1 days"));
//	$sb->add_event('home', $range_time_from, $range_time_to, 'event for share', 'share description');

//$sb->add_event('home', $range_time_from, $range_time_to, '', '', '', 'f2b12010b6de9942a980cfc384abd71e', 'qwerty@test123321.com');

//$sb->DoDELETERequest('home', '7260592d6eafd0d492b013567b1688fc');

$sb->getEvents('home', '20200101T000000Z', '20200401T000000Z');
?>