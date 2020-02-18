<?
	include('SBCalDav.php');
	$sb = new SBCalDav('https://p39-caldav.icloud.com/', 'mymail795@gmail.com', 'jcay-ucij-ljql-wrge');
//    $sb = new SBCalDav('https://p33-caldav.icloud.com/', 'bee.test.sbitsoft@gmail.com', 'amrb-etqh-xtku-ejoo');
	$sb->init();
	$sb->getListCalendars();

    $range_time_from = date("Y-m-d H:i:s", strtotime("+2 days"));
    $range_time_to = date("Y-m-d H:i:s", strtotime("+2 days"));
	$sb->put_event('home', $range_time_from, $range_time_to, 'event for share', 'share description', '', 'sbitsoft@icloud.com');

//$sb->add_event('home', '', '', '', '', '7938AE4D-6FD9-4118-B239-B9EA0991A4BE', 'qwerty@test123321.com');

//$sb->DoDELETERequest('home', '7260592d6eafd0d492b013567b1688fc');

$sb->getEvents('home', '20200101T000000Z', '20200401T000000Z');
?>