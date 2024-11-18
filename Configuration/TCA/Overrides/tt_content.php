<?php

if (version_compare(\TYPO3\CMS\Core\Utility\VersionNumberUtility::getCurrentTypo3Version(), '12.0.0', '<')) {
	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
		'SGalinski.sg_cookie_optin',
		'OptIn',
		'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_backend.xlf:optInPluginLabel'
	);

	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
		'SGalinski.sg_cookie_optin',
		'CookieList',
		'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_backend.xlf:cookieListPluginLabel'
	);
} else {
	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
		'sg_cookie_optin',
		'OptIn',
		'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_backend.xlf:optInPluginLabel',
		'ext-sg_cookie_optin',
		'plugins',
		'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_backend.xlf:optInPluginDescription'
	);

	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
		'sg_cookie_optin',
		'CookieList',
		'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_backend.xlf:cookieListPluginLabel',
		'ext-sg_cookie_optin',
		'plugins',
		'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_backend.xlf:cookieListPluginDescription'
	);
}

$pluginSignature = 'sgcookieoptin_cookielist';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
	$pluginSignature,
	// FlexForm configuration schema file
	'FILE:EXT:sg_cookie_optin/Configuration/FlexForms/CookieList.xml'
);
