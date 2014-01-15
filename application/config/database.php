<?php defined('SYSPATH') or die('No direct script access.');

return array
(
    'jkit' => array(
        'type'       => 'mysql',
        'connection' => array(
            'hostname'   => '127.0.0.1',
			'port'		 => 9306,
            'username'   => 'jkit',
            'password'   => 'jkit',
            'persistent' => FALSE,
			
            'database'   => 'jkit',
        ),
        'table_prefix' => '',
        'charset'      => 'utf8',
        'profiling'    => TRUE,
    ),
    'jkitpdo' => array(
        'type'       => 'mysql',
        'connection' => array(
            'dsn'   	 => 'mysql:dbname=jkit;host=127.0.0.1;port=9306',
            'username'   => 'jkit',
            'password'   => 'jkit',
            'persistent' => FALSE,
        ),
        'table_prefix' => '',
        'charset'      => 'utf8',
        'profiling'    => TRUE,
    ),
);