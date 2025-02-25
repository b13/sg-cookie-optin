<?php

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

$pluginSignature = 'sgcookieoptin_cookielist';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_content', '--div--;Configuration,pi_flexform,', $pluginSignature, 'after:subheader');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
	'*',
	// FlexForm configuration schema file
	'FILE:EXT:sg_cookie_optin/Configuration/FlexForms/CookieList.xml',
 $pluginSignature
);
