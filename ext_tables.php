<?php

call_user_func(
	static function () {
		$currentTypo3Version = \TYPO3\CMS\Core\Utility\VersionNumberUtility::getCurrentTypo3Version();
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages(
			'tx_sgcookieoptin_domain_model_optin'
		);
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages(
			'tx_sgcookieoptin_domain_model_group'
		);
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages(
			'tx_sgcookieoptin_domain_model_script'
		);
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages(
			'tx_sgcookieoptin_domain_model_cookie'
		);
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages(
			'tx_sgcookieoptin_domain_model_service'
		);

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
		if ($showModule) {
			if (version_compare($currentTypo3Version, '11.0.0', '>=')) {
				\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
					'sg_cookie_optin',
					'web',
					'Optin',
					'',
					[
						\SGalinski\SgCookieOptin\Controller\LegacyOptinController::class => 'index, activateDemoMode, create, uploadJson, importJson, previewImport, exportJson',
						\SGalinski\SgCookieOptin\Controller\LegacyStatisticsController::class => 'index',
						\SGalinski\SgCookieOptin\Controller\LegacyConsentController::class => 'index',
					],
					[
						'access' => 'user,group',
						'icon' => 'EXT:sg_cookie_optin/Resources/Public/Icons/module-sgcookieoptin.png',
						'labels' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang.xlf',
					]
				);
			} else {
				\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
					'SGalinski.sg_cookie_optin',
					'web',
					'Optin',
					'',
					[
						'LegacyOptin' => 'index, activateDemoMode, create, uploadJson, importJson, previewImport, exportJson',
						'LegacyStatistics' => 'index',
						'LegacyConsent' => 'index',
					],
					[
						'access' => 'user,group',
						'icon' => 'EXT:sg_cookie_optin/Resources/Public/Icons/module-sgcookieoptin.png',
						'labels' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang.xlf',
					]
				);
			}
		}

		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
			'tx_sgcookieoptin_domain_model_optin',
			'EXT:sg_cookie_optin/Resources/Private/Language/locallang_csh_tx_sgcookieoptin_domain_model_optin.xlf'
		);
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
			'tx_sgcookieoptin_domain_model_cookie',
			'EXT:sg_cookie_optin/Resources/Private/Language/locallang_csh_tx_sgcookieoptin_domain_model_cookie.xlf'
		);

		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\TYPO3\CMS\Scheduler\Task\TableGarbageCollectionTask::class]['options']['tables']['tx_sgcookieoptin_domain_model_user_preference'] = [
			'dateField' => 'tstamp',
			'expirePeriod' => 540
		];
	}
);
