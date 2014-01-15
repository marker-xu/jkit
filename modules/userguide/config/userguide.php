<?php defined('SYSPATH') OR die('No direct access allowed.');

return array
(
	// Enable the API browser.  TRUE or FALSE
	'api_browser'  => TRUE,
	
	// Enable these packages in the API browser.  TRUE for all packages, or a string of comma seperated packages, using 'None' for a class with no @package
	// Example: 'api_packages' => 'Kohana,Kohana/Database,Kohana/ORM,None',
	'api_packages' => TRUE,

	'class_prefix_filter' => array('Kohana_', 'JKit_', 'JKit_Antispam_'),
	
	'class_path_filter' => array(APPPATH),

	'nav' => array(
		array(
			'route' => 'docs/guide',
			'title' => __('User Guide'),
		),
		array(
			'route' => 'docs/api',
			'title' => __('API Browser'),
		),
		array(
			'link' => 'http://dev.qwrap.com/resource/js/_docs/_youa/',
			'title' => 'QWrap 文档',
		),
		array(
			'link' => 'https://github.com/akira-cn/jkit',
			'title' => '源码下载',
		),
	),
	
	// Leave this alone
	'modules' => array(

		// This should be the path to this modules userguide pages, without the 'guide/'. Ex: '/guide/modulename/' would be 'modulename'
		'userguide' => array(

			// Whether this modules userguide pages should be shown
			'enabled' => TRUE,
			
			// The name that should show up on the userguide index page
			'name' => 'Userguide',

			// A short description of this module, shown on the index page
			'description' => 'Documentation viewer and api generation.',
			
			// Copyright message, shown in the footer for this module
			'copyright' => '&copy; 2008–2011 Kohana Team',
		),
	)
);
