<?php

call_user_func(function () {
	// Register the OptIn plugin as a content element (CType)
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(
		[
			'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_backend.xlf:optInPluginLabel',
			'sgcookieoptin_optin',
			'iconIdentifier' => 'ext-sg_cookie_optin'
		],
		'CType',
		'sg_cookie_optin'
	);

	// Include pages (storage pid) field in the tt_content TCA for the Cookie Optin plugin
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
		'tt_content',
		'pages,',
		'sgcookieoptin_optin',
		'after:subheader'
	);


	// Register the CookieList plugin as a content element (CType)
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(
		[
			'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_backend.xlf:cookieListPluginLabel',
			'sgcookieoptin_cookielist',
			'iconIdentifier' => 'ext-sg_cookie_optin'
		],
		'CType',
		'sg_cookie_optin'
	);

	// Add FlexForm configuration for the CookieList plugin
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
		'*',
		'FILE:EXT:sg_cookie_optin/Configuration/FlexForms/CookieList.xml',
		'sgcookieoptin_cookielist'
	);

	// Include the FlexForm field in the tt_content TCA for the CookieList plugin
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
		'tt_content',
		'--div--;Configuration,pi_flexform,pages,',
		'sgcookieoptin_cookielist',
		'after:subheader'
	);
});
