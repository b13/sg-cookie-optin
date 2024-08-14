<?php

$EM_CONF[$_EXTKEY] = [
	'title' => 'sgalinski Cookie Consent',
	'description' => '
		Elevate your TYPO3 website with our premier Cookie Consent Solution, placing consent at the forefront!
		Our highly customizable solution not only offers essential tag manager options,
		but seamlessly integrates the latest Google Consent Mode v2 and more.
		Ensure compliance and user trust with our robust consent framework. Explore all the features and benefits
		on our website: https://www.sgalinski.de/en/typo3-products-web-development/cookie-optin-for-typo3/.
	',
	'category' => 'module',
	'version' => '5.5.6',
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
