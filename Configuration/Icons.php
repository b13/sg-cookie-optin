<?php

$iconList = [];
foreach (['ext-sg_cookie_optin' => 'extension-sg_cookie_optin.svg'] as $identifier => $path) {
	$iconList[$identifier] = [
		'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
		'source' => 'EXT:sg_cookie_optin/Resources/Public/Icons/' . $path,
	];
}

return $iconList;
