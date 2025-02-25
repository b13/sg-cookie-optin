<?php

call_user_func(
	static function () {
		$currentTypo3Version = \TYPO3\CMS\Core\Utility\VersionNumberUtility::getCurrentTypo3Version();

		$hideModuleInProductionContext = \SGalinski\SgCookieOptin\Service\ExtensionSettingsService::getSetting(
			\SGalinski\SgCookieOptin\Service\ExtensionSettingsService::SETTING_HIDE_MODULE_IN_PRODUCTION_CONTEXT
		);

		$showModule = TRUE;
		if ($hideModuleInProductionContext) {
			$applicationContext = \TYPO3\CMS\Core\Core\Environment::getContext();
			$showModule = !$applicationContext->isProduction();
		}

		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\TYPO3\CMS\Scheduler\Task\TableGarbageCollectionTask::class]['options']['tables']['tx_sgcookieoptin_domain_model_user_preference'] = [
			'dateField' => 'tstamp',
			'expirePeriod' => 540
		];
	}
);
