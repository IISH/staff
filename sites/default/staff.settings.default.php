<?php 
// ToDo: Copy this file to settings.php and set the rdbms settings

$databases = array (
	'default' =>
		array (
			'database' => '',
			'username' => '',
			'password' => '',
			'host' => '',
			'port' => '',
			'driver' => 'mysql',
			'prefix' => '',
		),
	'protime_cache' =>
		array (
			'database' => '',
			'username' => '',
			'password' => '',
			'host' => '',
			'port' => '',
			'driver' => 'mysql',
			'prefix' => '',
		),
	'protime_live' =>
		array (
			'database' => '',
			'username' => '',
			'password' => '',
			'host' => '',
			'port' => '',
			'driver' => 'mssql',
			'prefix' => '',
		),
);

$domain_controllers = array(
	array(
		'server' => '',
		'loginname_prefix' => '',
		'loginname_postfix' => '',
	)
);