<?php

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

$pluginSignature = 'sgcookieoptin_cookielist';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    $pluginSignature,
    // FlexForm configuration schema file
    'FILE:EXT:sg_cookie_optin/Configuration/FlexForms/CookieList.xml'
);
