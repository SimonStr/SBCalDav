<?
	include('SBCalDav.php');
	$sb = new SBCalDav('https://p39-caldav.icloud.com/', 'mymail795@gmail.com', 'jcay-ucij-ljql-wrge');
	$sb->init();
	$sb->getListCalendars();
	$sb->getEvents('home', '20200201T000000Z', '20200301T000000Z');

    $range_time_from = date("Y-m-d H:i:s", strtotime("-1 days"));
    $range_time_to = date("Y-m-d H:i:s", strtotime("-1 days"));
	$sb->add_event('home', $range_time_from, $range_time_to, 'test title test', 'test description test');
?>