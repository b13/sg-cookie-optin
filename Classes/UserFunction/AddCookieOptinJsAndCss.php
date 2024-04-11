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
use SGalinski\SgCookieOptin\Service\JsonImportService;
use SGalinski\SgCookieOptin\Service\LicenceCheckService;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Adds the Cookie Consent JavaScript if it's generated for the current page.
 */
class AddCookieOptinJsAndCss implements SingletonInterface {
	/** @var int|null */
	protected $rootpage = NULL;

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
	public function addJavaScript(string $content, array $configuration) {
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
		$jsonFile = $this->getJsonFilePath($folder, $rootPageId, $sitePath);
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
		if (!$jsonData['settings']['disable_for_this_language']) {
			if ($jsonData['settings']['render_assets_inline']) {
				return '<script id="cookieOptinData" type="application/json">' . json_encode($jsonData) .
					"</script>\n" . '<script type="text/javascript" data-ignore="1" crossorigin="anonymous">' .
					file_get_contents($sitePath . $file) . "</script>\n";
			}

			if ($jsonData['settings']['overwrite_baseurl']) {
				$overwrittenBaseUrl = $jsonData['settings']['overwrite_baseurl'];
			}

			$fileUrl = ($overwrittenBaseUrl ?? $siteBaseUrl) . $file . '?' . $cacheBuster;

			$returnString = '<script id="cookieOptinData" type="application/json">' . json_encode($jsonData) . '</script>';
			if (!isset($jsonData['settings']['disable_automatic_loading']) || !$jsonData['settings']['disable_automatic_loading']) {
				$returnString .= "\n" . '<link rel="preload" as="script" href="' . $fileUrl . '" data-ignore="1" crossorigin="anonymous">
					<script src="' . $fileUrl . '" data-ignore="1" crossorigin="anonymous"></script>';
			}
			return $returnString;
		}

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
	public function addCSS(string $content, array $configuration) {
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

		$jsonFile = $this->getJsonFilePath($folder, $rootPageId, $sitePath);
		if ($jsonFile) {
			$jsonData = json_decode(file_get_contents($sitePath . $jsonFile), TRUE);

			if ($jsonData['settings']['render_assets_inline']) {
				return '<style>' . file_get_contents($sitePath . $file) . "</style>\n";
			}

			if ($jsonData['settings']['overwrite_baseurl']) {
				$overwrittenBaseUrl = $jsonData['settings']['overwrite_baseurl'];
			}
		}

		$siteBaseUrl = $overwrittenBaseUrl ?? BaseUrlService::getSiteBaseUrl(
			$this->rootpage, BaseUrlService::getLanguage()
		);
		return '<link rel="preload" as="style" href="' . $siteBaseUrl . $file . '?' . $cacheBuster . '" media="all" crossorigin="anonymous">' . "\n"
			. '<link rel="stylesheet" href="' . $siteBaseUrl . $file . '?' . $cacheBuster . '" media="all" crossorigin="anonymous">' . "\n";
	}

	/**
	 * Returns always the first page within the "rootline"
	 *
	 * @return int
	 */
	protected function getRootPageId() {
		if ($this->rootpage === NULL) {
			/** @var Site $site */
			$site = $GLOBALS['TYPO3_REQUEST']->getAttribute('site');
			$this->rootpage = $site->getRootPageId();
		}

		return $this->rootpage;
	}

	/**
	 * Get the path to the json file
	 *
	 * @param string $folder
	 * @param int $rootPageId
	 * @param string $sitePath
	 * @return string|null
	 * @throws AspectNotFoundException
	 * @throws SiteNotFoundException
	 */
	protected function getJsonFilePath(string $folder, int $rootPageId, string $sitePath) {
		$jsonFile = $folder . 'siteroot-' . $rootPageId . '/' . 'cookieOptinData' . JsonImportService::LOCALE_SEPARATOR .
			$this->getLanguageWithLocale() . '.json';
		if (!file_exists($sitePath . $jsonFile)) {
			$jsonFile = $folder . 'siteroot-' . $rootPageId . '/' . 'cookieOptinData_' .
				BaseUrlService::getLanguage() . '.json';
			if (!file_exists($sitePath . $jsonFile)) {
				$jsonFile = $folder . 'siteroot-' . $rootPageId . '/' . 'cookieOptinData_0.json';
				if (!file_exists($sitePath . $jsonFile)) {
					return NULL;
				}
			}
		}

		return $jsonFile;
	}

	/**
	 * Get the current CSS or JS asset file path
	 *
	 * @param string $sitePath
	 * @param string $folder
	 * @param int $rootPageId
	 * @param string $pattern
	 * @return mixed|null
	 */
	protected function getAssetFilePath(string $sitePath, string $folder, int $rootPageId, string $pattern) {
		$files = glob($sitePath . $folder . 'siteroot-' . $rootPageId . '/' . $pattern);
		if (count($files) > 0) {
			return str_replace($sitePath, '', $files[0]);
		}

		return NULL;
	}

	/**
	 * Returns the current language id with locale
	 *
	 * @return string
	 * @throws AspectNotFoundException
	 * @throws SiteNotFoundException
	 */
	protected function getLanguageWithLocale() {
		$site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($this->getRootPageId());
		$language = $site->getLanguageById(BaseUrlService::getLanguage());
		return $language->getLocale() . JsonImportService::LOCALE_SEPARATOR . $language->getLanguageId();
	}
}
