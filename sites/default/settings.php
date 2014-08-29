<?php
// ToDo: Copy this file to settings.php and set the rdbms settings

// Presentornot database settings
$settings["presentornot_server"] = 'website-db0.iisg.nl';
$settings["presentornot_user"] = 'presentornot';
$settings["presentornot_password"] = '$g7HH%*3k';
$settings["presentornot_database"] = 'presentornot';

// Protime (KNAW) database settings
$settings["protime_server"] = '10.14.42.96';
$settings["protime_user"] = 'IISG-195.169.123.192';
$settings["protime_password"] = 'ProllyN0tProb4bly';
$settings["protime_database"] = 'premiisg';

$databases = array (
	'default' =>
		array (
			'database' => 'presentornot',
			'username' => 'presentornot',
			'password' => '$g7HH%*3k',
			'host' => 'website-db0.iisg.nl',
			'port' => '',
			'driver' => 'mysql',
			'prefix' => '',
		),
	'protime_cache' =>
		array (
			'database' => 'presentornot',
			'username' => 'presentornot',
			'password' => '$g7HH%*3k',
			'host' => 'website-db0.iisg.nl',
			'port' => '',
			'driver' => 'mysql',
			'prefix' => '',
		),
	'protime_live' =>
		array (
			'database' => 'premiisg',
			'username' => 'IISG-195.169.123.192',
			'password' => 'ProllyN0tProb4bly',
			'host' => '10.14.42.96',
			'port' => '',
			'driver' => 'mssql',
			'prefix' => '',
		),
);

$domain_controllers = array(
	array(
		'server' => 'apollo3.iisg.net',
		'loginname_prefix' => 'iisgnet\\',
		'loginname_postfix' => '',
	)
	, array(
		'server' => 'apollo2.iisg.net',
		'loginname_prefix' => 'iisgnet\\',
		'loginname_postfix' => '',
	)
);
