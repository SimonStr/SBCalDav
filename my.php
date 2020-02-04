<?
	include('SBCalDav.php');
	$sb = new SBCalDav('https://p39-caldav.icloud.com/', 'mymail795@gmail.com', 'jcay-ucij-ljql-wrge');
	$sb->init();
	$sb->getListCalendars();
	$sb->getEvents('home', '20200201T000000Z', '20200301T000000Z');
	$sb->add_event('home', '', '', 'test title test', 'test description test');
?>