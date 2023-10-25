<?php

use TYPO3\CMS\Core\Utility\VersionNumberUtility;

call_user_func(
	static function () {
		$currentTypo3Version = VersionNumberUtility::getCurrentTypo3Version();
		if (version_compare($currentTypo3Version, '12.0.0', '>=')) {
			\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
				'sg_cookie_optin',
				'OptIn',
				[
					\SGalinski\SgCookieOptin\Controller\CookieListController::class => 'show',
				],
				// non-cacheable actions
				[
					\SGalinski\SgCookieOptin\Controller\CookieListController::class => '',
				]
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
				]
			);
		} elseif (version_compare($currentTypo3Version, '11.0.0', '>=')) {
			\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
				'sg_cookie_optin',
				'OptIn',
				[
					\SGalinski\SgCookieOptin\Controller\LegacyOptinController::class => 'show',
				],
				// non-cacheable actions
				[
					\SGalinski\SgCookieOptin\Controller\LegacyOptinController::class => '',
				]
			);
			\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
				'sg_cookie_optin',
				'CookieList',
				[
					\SGalinski\SgCookieOptin\Controller\LegacyOptinController::class => 'cookieList',
				],
				// non-cacheable actions
				[
					\SGalinski\SgCookieOptin\Controller\LegacyOptinController::class => '',
				]
			);
		} else {
			\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
				'SGalinski.sg_cookie_optin',
				'OptIn',
				[
					'LegacyOptin' => 'show',
				],
				// non-cacheable actions
				[
					'LegacyOptin' => '',
				]
			);
			\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
				'SGalinski.sg_cookie_optin',
				'CookieList',
				[
					'LegacyOptin' => 'cookieList',
				],
				// non-cacheable actions
				[
					'LegacyOptin' => '',
				]
			);
		}

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

		if (version_compare($currentTypo3Version, '12.0.0', '>=')) {
			\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig(
				'@import \'EXT:sg_cookie_optin/Configuration/TsConfig/User/HideTableButtons.tsconfig\''
			);

			\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
				'@import \'EXT:sg_cookie_optin/Configuration/TsConfig/Page/NewContentElementWizard.tsconfig\'
				@import \'EXT:sg_cookie_optin/Configuration/TsConfig/Page/ExternalContentFrameClass.tsconfig\''
			);
		} else {
			// User TSConfig
			\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig(
				'<INCLUDE_TYPOSCRIPT: source="FILE:EXT:sg_cookie_optin/Configuration/TsConfig/User/HideTableButtons.tsconfig">'
			);

			// Page TSConfig
			\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
				'<INCLUDE_TYPOSCRIPT: source="FILE:EXT:sg_cookie_optin/Configuration/TsConfig/Page/NewContentElementWizard.tsconfig">'
			);

			// External Content Frame Class TSConfig
			\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
				'<INCLUDE_TYPOSCRIPT: source="FILE:EXT:sg_cookie_optin/Configuration/TsConfig/Page/ExternalContentFrameClass.tsconfig">'
			);

			// Register Icons
			$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
				\TYPO3\CMS\Core\Imaging\IconRegistry::class
			);
			$iconRegistry->registerIcon(
				'ext-sg_cookie_optin',
				\TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
				['source' => 'EXT:sg_cookie_optin/Resources/Public/Icons/extension-sg_cookie_optin.svg']
			);
		}

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

		// Polyfill for older versions
		if (!class_exists('SgCookieAbstractViewHelper')) {
			$typo3Version = \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(
				$currentTypo3Version
			);
			if ($typo3Version >= 10000000) {
				/** @noinspection PhpIgnoredClassAliasDeclaration */
				class_alias('\TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper', 'SgCookieAbstractViewHelper');
			} else {
				/** @noinspection PhpIgnoredClassAliasDeclaration */
				class_alias('\TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper', 'SgCookieAbstractViewHelper');
			}
		}
	}
);
