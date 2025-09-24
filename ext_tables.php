<?php

call_user_func(
	static function () {
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\TYPO3\CMS\Scheduler\Task\TableGarbageCollectionTask::class]['options']['tables']['tx_sgcookieoptin_domain_model_user_preference'] = [
			'dateField' => 'tstamp',
			'expirePeriod' => 540
		];
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\TYPO3\CMS\Scheduler\Task\TableGarbageCollectionTask::class]['options']['tables']['tx_sgcookieoptin_domain_model_rate_limit'] = [
			'dateField' => 'tstamp',
			'expirePeriod' => 24
		];
	}
);
