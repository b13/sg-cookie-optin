<?php
return [
	'web_SgcookieoptinOptin' => [
		'parent' => 'web',
		'position' => [],
		'access' => 'user,group',
		'icon' => 'EXT:sg_cookie_optin/Resources/Public/Icons/module-sgcookieoptin.png',
		'labels' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang.xlf',
		'workspaces' => 'live',
		'path' => '/module/web/sg-cookie-optin',
		'extensionName' => 'SgCookieOptin',
		'controllerActions' => [
			\SGalinski\SgCookieOptin\Controller\OptinController::class => ['index', 'activateDemoMode', 'create', 'uploadJson', 'importJson', 'previewImport', 'exportJson'],
			\SGalinski\SgCookieOptin\Controller\StatisticsController::class => ['index'],
			\SGalinski\SgCookieOptin\Controller\ConsentController::class => ['index'],
		],
	],
];
