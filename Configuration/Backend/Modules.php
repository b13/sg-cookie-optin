<?php
$hideModuleInProductionContext = \SGalinski\SgCookieOptin\Service\ExtensionSettingsService::getSetting(
	\SGalinski\SgCookieOptin\Service\ExtensionSettingsService::SETTING_HIDE_MODULE_IN_PRODUCTION_CONTEXT
);

$showModule = TRUE;
if ($hideModuleInProductionContext) {
	if (version_compare(
		\TYPO3\CMS\Core\Utility\VersionNumberUtility::getCurrentTypo3Version(),
		'10.2.0',
		'<'
	)) {
		$applicationContext = \TYPO3\CMS\Core\Utility\GeneralUtility::getApplicationContext();
	} else {
		$applicationContext = \TYPO3\CMS\Core\Core\Environment::getContext();
	}

	if (isset($applicationContext)) {
		$showModule = !$applicationContext->isProduction();
	}
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
			\SGalinski\SgCookieOptin\Controller\OptinController::class => ['index', 'activateDemoMode', 'create', 'uploadJson', 'importJson', 'previewImport', 'exportJson'],
			\SGalinski\SgCookieOptin\Controller\StatisticsController::class => ['index'],
			\SGalinski\SgCookieOptin\Controller\ConsentController::class => ['index'],
		],
	],
] : [];
