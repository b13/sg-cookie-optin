<?php

/**
 * Important! Do not return a variable named $icons, because it will result in an error.
 * The core requires this file and then the variable names will clash.
 * Either use a closure here, or do not call your variable $icons.
 */

$iconList = [];
foreach (['ext-sg_cookie_optin' => 'extension-sg_cookie_optin.svg'] as $identifier => $path) {
	$iconList[$identifier] = [
		'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
		'source' => 'EXT:sg_cookie_optin/Resources/Public/Icons/' . $path,
	];
}

return $iconList;
