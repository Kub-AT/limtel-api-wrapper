Simple Limtel API wrapper
==================

Simple Limtel (http://www.limtel.com/ , http://www.limtel.pl/) API Wrapper in PHP.


Examples
--------

Login

	<?php
	$api = new Limtel('email@example.com', '1a79a4d60de6718e8e5b326e338ae533', 'c1a59bcd1cb1d018ccfe812efdc2a141');
	$api->login();

Get incoming calls

	<?php
	$api = new Limtel('email@example.com', '1a79a4d60de6718e8e5b326e338ae533', 'c1a59bcd1cb1d018ccfe812efdc2a141');
	$api->login();
	$result = $api->history(array(
			'channel' => 1, 
			'typ' => 0, 
			'ile' => 1000, 
			'strona' => 1
		));
