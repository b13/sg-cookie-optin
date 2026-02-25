<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) sgalinski Internet Services (https://www.sgalinski.de)
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

namespace SGalinski\SgCookieOptin\UserFunction;

use SGalinski\SgCookieOptin\Service\BaseUrlService;
use SGalinski\SgCookieOptin\Service\ExtensionSettingsService;
use SGalinski\SgCookieOptin\Service\LicenceCheckService;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\ConsumableNonce;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Adds the Cookie Consent JavaScript if it's generated for the current page.
 */
class AddCookieOptinJsAndCss implements SingletonInterface {
	/** @var int|null */
	protected ?int $rootpage = NULL;

	/**
	 * Adds the Cookie Consent JavaScript if it's generated for the current page.
	 *
	 * Example line: fileadmin/sg_cookie_optin/siteroot-1/cookieOptin_0_v2.js
	 *
	 * @param string $content
	 * @param array $configuration
	 * @return string
	 * @throws AspectNotFoundException
	 * @throws SiteNotFoundException
	 */
	public function addJavaScript(string $content, array $configuration): string {
		if (!LicenceCheckService::isInDevelopmentContext()
			&& !LicenceCheckService::isInDemoMode()
			&& !LicenceCheckService::hasValidLicense()
		) {
			LicenceCheckService::removeAllCookieOptInFiles();
			return '';
		}

		$rootPageId = $this->getRootPageId();
		if ($rootPageId <= 0) {
			return '';
		}

		$folder = ExtensionSettingsService::getSetting(ExtensionSettingsService::SETTING_FOLDER);
		if (!$folder) {
			return '';
		}

		$siteBaseUrl = BaseUrlService::getSiteBaseUrl($this->rootpage, BaseUrlService::getLanguage());
		$file = $folder . 'siteroot-' . $rootPageId . '/' . 'cookieOptin.js';
		$sitePath = defined('PATH_site') ? PATH_site : Environment::getPublicPath() . '/';
		$jsonFile = ExtensionSettingsService::getJsonFilePath($folder, $rootPageId, $sitePath);
		if ($jsonFile === NULL) {
			return '';
		}

		$cacheBuster = filemtime($sitePath . $file);
		if (!$cacheBuster) {
			$cacheBuster = '';
		}

		// we decode and encode again to remove the PRETTY_PRINT when rendering for better performance on the frontend
		// for easier debugging, you can check the generated file in the fileadmin
		// see https://gitlab.sgalinski.de/typo3/sg_cookie_optin/-/issues/118
		$jsonData = json_decode(file_get_contents($sitePath . $jsonFile), TRUE);
		if ($jsonData['settings']['disable_for_this_language']) {
			return '';
		}

		$assetCollector = GeneralUtility::makeInstance(AssetCollector::class);
		$keySuffix = (string) $rootPageId;

		// Ensure the inline JSON is rendered before the cookie optin JS by using AssetCollector with priority=true
		$assetCollector->addInlineJavaScript(
			'cookieoptin-data-' . $keySuffix,
			json_encode($jsonData),
			[
				'id' => 'cookieOptinData',
				'type' => 'application/json',
			],
			[
				'useNonce' => TRUE,
				'priority' => TRUE
			]
		);

		// Add a script either inline or as an external file depending on settings
		if (!empty($jsonData['settings']['render_assets_inline'])) {
			$assetCollector->addInlineJavaScript(
				'cookieoptin-inline-js-' . $keySuffix,
				(string) file_get_contents($sitePath . $file),
				[
					'type' => 'text/javascript',
					'data-ignore' => '1',
					'crossorigin' => 'anonymous'
				],
				[
					'useNonce' => TRUE
				]
			);

			return '';
		}

		if (!empty($jsonData['settings']['overwrite_baseurl'])) {
			$overwrittenBaseUrl = $jsonData['settings']['overwrite_baseurl'];
		}
		$fileUrl = ($overwrittenBaseUrl ?? $siteBaseUrl) . $file . '?' . $cacheBuster;
		// Only add external JS when automatic loading is not disabled
		if (!isset($jsonData['settings']['disable_automatic_loading']) || !$jsonData['settings']['disable_automatic_loading']) {
			$assetCollector->addJavaScript(
				'cookieoptin-js-' . $keySuffix,
				$fileUrl,
				[
					'id' => 'cookieOptinScript',
					'data-ignore' => '1',
					'crossorigin' => 'anonymous',
					'defer' => 'defer'
				],
				[
					'useNonce' => TRUE,
					'priority' => TRUE
				]
			);
		}

		// Note: Preload for scripts is intentionally omitted to let TYPO3 manage ordering and CSP.
		return '';
	}

	/**
	 * Adds the Cookie Consent CSS if it's generated for the current page.
	 *
	 * Example line: fileadmin/sg_cookie_optin/siteroot-1/cookieOptin.css
	 *
	 * @param string $content
	 * @param array $configuration
	 * @return string
	 * @throws AspectNotFoundException
	 * @throws SiteNotFoundException
	 */
	public function addCSS(string $content, array $configuration): string {
		$rootPageId = $this->getRootPageId();
		if ($rootPageId <= 0) {
			return '';
		}

		$folder = ExtensionSettingsService::getSetting(ExtensionSettingsService::SETTING_FOLDER);
		if (!$folder) {
			return '';
		}

		$file = $folder . 'siteroot-' . $rootPageId . '/cookieOptin.css';
		$sitePath = defined('PATH_site') ? PATH_site : Environment::getPublicPath() . '/';
		if (!file_exists($sitePath . $file)) {
			return '';
		}

		$cacheBuster = filemtime($sitePath . $file);
		if (!$cacheBuster) {
			$cacheBuster = '';
		}

		$assetCollector = GeneralUtility::makeInstance(AssetCollector::class);
		$keySuffix = (string) $rootPageId;

		$jsonFile = ExtensionSettingsService::getJsonFilePath($folder, $rootPageId, $sitePath);
		if ($jsonFile) {
			$jsonData = json_decode(file_get_contents($sitePath . $jsonFile), TRUE);

			if (!empty($jsonData['settings']['render_assets_inline'])) {
				$assetCollector->addInlineStyleSheet(
					'cookieoptin-inline-css-' . $keySuffix,
					(string) file_get_contents($sitePath . $file),
					[
						'media' => 'all',
						'crossorigin' => 'anonymous',
					]
				);
				return '';
			}

			if (!empty($jsonData['settings']['overwrite_baseurl'])) {
				$overwrittenBaseUrl = $jsonData['settings']['overwrite_baseurl'];
			}
		}

		$siteBaseUrl = $overwrittenBaseUrl ?? BaseUrlService::getSiteBaseUrl(
			$this->rootpage,
			BaseUrlService::getLanguage()
		);
		$href = $siteBaseUrl . $file . '?' . $cacheBuster;

		$assetCollector->addStyleSheet(
			'cookieoptin-css-' . $keySuffix,
			$href,
			[
				'media' => 'all',
				'crossorigin' => 'anonymous',
			]
		);

		// Note: Preload for styles is omitted; modern browsers handle CSS fetching efficiently.
		return '';
	}

	/**
	 * Returns always the first page within the "rootline"
	 *
	 * @return int|null
	 */
	protected function getRootPageId(): ?int {
		if ($this->rootpage === NULL) {
			/** @var Site $site */
			$site = $GLOBALS['TYPO3_REQUEST']->getAttribute('site');
			$this->rootpage = $site->getRootPageId();
		}

		return $this->rootpage;
	}

	/**
	 * Returns the CSP nonce attribute if available for current request
	 */
	protected function getNonceAttribute(): string {
		$nonce = $GLOBALS['TYPO3_REQUEST']?->getAttribute('nonce');
		if ($nonce instanceof ConsumableNonce) {
			return ' nonce="' . htmlspecialchars($nonce->consume(), ENT_QUOTES | ENT_HTML5) . '"';
		}
		return '';
	}
}
