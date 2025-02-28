<?php

use TYPO3\CMS\Core\Utility\VersionNumberUtility;

call_user_func(
	static function () {
		\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
			'sg_cookie_optin',
			'OptIn',
			[
				\SGalinski\SgCookieOptin\Controller\CookieListController::class => 'show',
			],
			// non-cacheable actions
			[
				\SGalinski\SgCookieOptin\Controller\CookieListController::class => '',
			],
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
		);
		\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
			'sg_cookie_optin',
			'CookieList',
			[
				\SGalinski\SgCookieOptin\Controller\CookieListController::class => 'cookieList',
			],
			// non-cacheable actions
			[
				\SGalinski\SgCookieOptin\Controller\CookieListController::class => '',
			],
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
		);

		// Add a warning render type
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][] = [
			'nodeName' => 'SgCookieOptinTCAWarningField',
			'priority' => 40,
			'class' => \SGalinski\SgCookieOptin\Backend\TCAWarningField::class,
		];

		// hook registration
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] =
			\SGalinski\SgCookieOptin\Hook\GenerateFilesAfterTcaSave::class;
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] =
			\SGalinski\SgCookieOptin\Hook\HandleTemplateAfterTcaSave::class;
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] =
			\SGalinski\SgCookieOptin\Hook\HandleVersionChange::class;

		// Licence check
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/backend.php']['constructPostProcess'][] =
			\SGalinski\SgCookieOptin\Hook\LicenceCheckHook::class . '->performLicenseCheck';

		// Wizard Registration
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][] = [
			'nodeName' => 'templatePreviewLinkWizard',
			'priority' => 70,
			'class' => \SGalinski\SgCookieOptin\Wizards\TemplatePreviewLinkWizard::class
		];

		// Ajax Endpoint
		$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['sg_cookie_optin_saveOptinHistory'] = \SGalinski\SgCookieOptin\Endpoints\OptinHistoryController::class . '::saveOptinHistory';
	}
);
