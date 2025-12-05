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
	'version' => '7.3.1',
	'state' => 'stable',
	'author' => 'Stefan Galinski',
	'author_email' => 'support@sgalinski.de',
	'author_company' => 'sgalinski Internet Services (https://www.sgalinski.de)',
	'constraints' => [
		'depends' => [
			'typo3' => '12.4.0-13.4.99'
		],
		'conflicts' => [
		],
		'suggests' => [
		],
	],
];
