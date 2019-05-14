<?php

class MyDB extends SQLite3 
{
	function __construct() 
	{
		$this->open('db/db.sqlite3');
	}
}
$db = new MyDB();
if (!$db)
{
	die('Cannot connect to database.');
}

?>
