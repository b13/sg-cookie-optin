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

namespace SGalinski\SgCookieOptin\Service;

use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class SGalinski\SgCookieOptin\Service\ExtensionSettingsService
 */
class ExtensionSettingsService {
	const SETTING_LICENSE = 'key';
	const SETTING_FOLDER = 'folder';
	const SETTING_HIDE_MODULE_IN_PRODUCTION_CONTEXT = 'hideModuleInProductionContext';

	/**
	 * @var array Default settings mapped to constants.
	 */
	protected static $defaultValueMap = [
		self::SETTING_FOLDER => 'fileadmin/sg_cookie_optin/',
		self::SETTING_HIDE_MODULE_IN_PRODUCTION_CONTEXT => FALSE,
	];

	/**
	 * Returns the setting of one of the constants of this class.
	 *
	 * @param string $settingKey
	 * @return mixed
	 */
	public static function getSetting($settingKey) {
		$configuration = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['sg_cookie_optin'] ?? [];

		$setting = '';
		if (isset(self::$defaultValueMap[$settingKey])) {
			$setting = self::$defaultValueMap[$settingKey];
		}

		if (isset($configuration[$settingKey])) {
			$setting = self::postProcessSetting($configuration[$settingKey], $settingKey);
		}

		return $setting;
	}

	/**
	 * Post process of the given setting, by the given setting key.
	 *
	 * @param mixed $value
	 * @param string $settingKey
	 * @return mixed
	 */
	protected static function postProcessSetting($value, $settingKey) {
		if ($settingKey === self::SETTING_FOLDER) {
			$value = trim($value, " \t\n\r\0\x0B\/") . '/';

			if (strpos($value, 'EXT:') === 0) {
				$value = 'typo3conf/ext/' . substr($value, 4);
			}
		}

		// TYPO3 6 stores all settings as strings, some are expected to be booleans, though.
		return ($value === 'FALSE') ? FALSE : $value;
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
	public static function getJsonFilePath(string $folder, int $rootPageId, string $sitePath) {
		$jsonFile = $folder . 'siteroot-' . $rootPageId . '/' . 'cookieOptinData' . JsonImportService::LOCALE_SEPARATOR .
			self::getLanguageWithLocale($rootPageId) . '.json';
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
	public static function getAssetFilePath(string $sitePath, string $folder, int $rootPageId, string $pattern) {
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
	public static function getLanguageWithLocale(int $rootPageId) {
		$site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($rootPageId);
		$language = $site->getLanguageById(BaseUrlService::getLanguage());
		return $language->getLocale() . JsonImportService::LOCALE_SEPARATOR . $language->getLanguageId();
	}
}
