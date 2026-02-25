<?php

$hideModuleInProductionContext = \SGalinski\SgCookieOptin\Service\ExtensionSettingsService::getSetting(
	\SGalinski\SgCookieOptin\Service\ExtensionSettingsService::SETTING_HIDE_MODULE_IN_PRODUCTION_CONTEXT
);

$showModule = TRUE;
if ($hideModuleInProductionContext) {
	$applicationContext = \TYPO3\CMS\Core\Core\Environment::getContext();
	$showModule = !$applicationContext->isProduction();
}

return $showModule ? [
	'web_SgcookieoptinOptin' => [
		'parent' => 'web',
		'position' => [],
		'access' => 'user',
		'icon' => 'EXT:sg_cookie_optin/Resources/Public/Icons/module-sgcookieoptin.png',
		'labels' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang.xlf',
		'workspaces' => 'live',
		'path' => '/module/web/sg-cookie-optin',
		'extensionName' => 'SgCookieOptin',
		'controllerActions' => [
			\SGalinski\SgCookieOptin\Controller\OptinController::class => [
				'index',
				'activateDemoMode',
				'create',
				'uploadJson',
				'importJson',
				'previewImport',
				'exportJson'
			],
			\SGalinski\SgCookieOptin\Controller\StatisticsController::class => ['index'],
			\SGalinski\SgCookieOptin\Controller\ConsentController::class => ['index'],
		],
	],
] : [];
