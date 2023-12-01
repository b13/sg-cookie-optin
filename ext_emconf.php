<?php

$EM_CONF[$_EXTKEY] = [
	'title' => 'sgalinski Cookie Consent',
	'description' => '
		This extension adds a customizable cookie consent solution for the frontend including a very basic tag manager.
		It also allows you to load third party content only when the user allows it.
		For more details visit https://www.sgalinski.de/en/typo3-products-web-development/cookie-optin-for-typo3/.
	',
	'category' => 'module',
	'version' => '5.3.7',
	'state' => 'stable',
	'uploadfolder' => FALSE,
	'createDirs' => '',
	'clearCacheOnLoad' => FALSE,
	'author' => 'Stefan Galinski',
	'author_email' => 'stefan@sgalinski.de',
	'author_company' => 'sgalinski Internet Services (https://www.sgalinski.de)',
	'constraints' => [
		'depends' => [
			'typo3' => '9.5.0-12.4.99'
		],
		'conflicts' => [
		],
		'suggests' => [
		],
	],
];
