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

use Exception;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class LicenceCheckService
 *
 * @package SGalinski\SgCookieOptin\Service
 */
class LicenceCheckService {
	public const STATE_LICENSE_VALID = 2;
	public const STATE_LICENSE_INVALID = 1;
	public const STATE_LICENSE_NOT_SET = 0;

	public const DEMO_MODE_KEY = 'demo_mode';
	public const DEMO_MODE_LIFETIME = 86400;
	public const DEMO_MODE_MAX_AMOUNT = 3;

	/**
	 * The product key from ShopWare
	 */
	public const PRODUCT_KEY = 'sg_cookie_optin';

	/**
	 * Namespace for the sys registry
	 */
	public const REGISTRY_NAMESPACE = 'tx_sgcookieoptin';

	/**
	 * Keys for the sys registry
	 */
	public const IS_KEY_VALID_KEY = 'isKeyValid';
	public const LAST_WARNING_TIMESTAMP_KEY = 'lastWarningTimestamp';
	public const HAS_VALID_LICENSE_UNTIL_TIMESTAMP_KEY = 'hasValidLicenseUntilTimestamp';
	public const LICENSE_CHECKED_IN_VERSION_KEY = 'licenceCheckedInVersion';
	public const LAST_CHECKED_TIMESTAMP_KEY = 'lastCheckedTimestamp';
	public const LAST_AJAX_TIMESTAMP_KEY = 'lastAjaxTimestamp';
	public const LAST_LICENSE_KEY_CHECKED_KEY = 'lastLicenseKeyChecked';

	/**
	 * Error codes
	 */
	public const ERROR_INVALID_RESPONSE_CODE = -1;
	public const ERROR_INVALID_RESPONSE_DATA = -2;
	public const ERROR_INVALID_LICENSE_KEY = -3;
	public const ERROR_INVALID_LICENSE_STRUCTURE = -4;
	public const ERROR_TIMESTAMP_INVALID = -5;
	public const ERROR_LICENSE_CHECK_EXCEPTION = -6;

	/**
	 * Earliest TYPO3 Version that we support
	 */
	public const EARLIEST_SUPPORTED_VERSION = 8000000;

	/**
	 * Check the license key once per how many days
	 */
	public const AMOUNT_OF_DAYS_UNTIL_NEXT_CHECK = 1;

	/**
	 * Show a warning if the license has expired, but we are still in the same version once per how many days
	 */
	public const AMOUNT_OF_DAYS_UNTIL_WARNING = 30;

	/**
	 * License server credentials
	 */
	public const API_USER = 'license_check';
	public const API_PASSWORD = 'lGKLiHc5We6gBqsggVlwdLNoWv9CEKnWiy7cgMUO';
	public const API_URL = 'https://shop.sgalinski.de/api/license';

	/**
	 * The current extension version
	 */
	public const CURRENT_VERSION = '7.3.0';

	/**
	 * Last response code from server
	 *
	 * @var int
	 */
	protected static int $lastHttpResponseCode = 0;

	/**
	 * The last exception from the server
	 *
	 * @var string
	 */
	protected static string $lastException = '';

	/**
	 * The validUntil timestamp
	 *
	 * @var null|int
	 */
	protected static ?int $validUntil = NULL;

	/**
	 * @var array
	 */
	private static array $versionToReleaseTimestamp = [
		'1.0' => 1571350984, // 2019-10-17T22:23:04Z
		'1.1' => 1571695928, // 2019-10-21T22:12:08Z
		'1.2' => 1571701161, // 2019-10-21T23:39:21Z
		'1.3' => 1571703014, // 2019-10-22T00:10:14Z
		'1.4' => 1571705101, // 2019-10-22T00:45:01Z
		'1.5' => 1571711597, // 2019-10-22T02:33:17Z
		'1.6' => 1571770960, // 2019-10-22T19:02:40Z
		'1.7' => 1571788178, // 2019-10-22T23:49:38Z
		'1.8' => 1572302073, // 2019-10-28T22:34:33Z
		'2.0' => 1575661906, // 2019-12-06T19:51:46Z
		'3.0' => 1583361764, // 2020-03-04T22:42:44Z
		'3.1' => 1588958065, // 2020-05-08T17:14:25Z
		'3.2' => 1600032423, // 2020-09-13T21:27:03Z
		'3.3' => 1610914552, // 2021-01-17T20:15:52Z
		'4.0' => 1614345302, // 2021-02-26T13:15:02Z
		'4.1' => 1619200227, // 2021-04-23T17:50:27Z
		'4.2' => 1621515339, // 2021-05-20T12:55:39Z
		'4.3' => 1633888545, // 2021-10-10T17:55:45Z
		'4.4' => 1645475601, // 2022-02-21T21:34:44Z
		'4.4.5' => 1651580776, // 2022-05-03T15:27:44Z
		'4.4.6' => 1654122394, // Wed, 01 Jun 2022 22:26:34 GMT
		'4.5.0' => 1655281313, // Wed, 15 Jun 2022 09:22:34 GMT
		'4.5.1' => 1655281313, // Wed, 15 Jun 2022 09:22:34 GMT
		'4.5.2' => 1655281313, // Wed, 15 Jun 2022 09:22:34 GMT
		'4.5.3' => 1655281313, // Wed, 15 Jun 2022 09:22:34 GMT
		'4.6.0' => 1661785282, // Mon Aug 29 2022 18:01:22 GMT+0300
		'5.0.0' => 1672837572, // Wed Jan 04 2023 14:45:57 GMT+0200
		'5.1.0' => 1675793649, // Tue, 07 Feb 2023 18:11:50 GMT
		'5.2.0' => 1684970210, // Wed, 24 May 2023 23:11:50 GMT
		'5.3.0' => 1698254036, // Wed, 10 Aug 2023 23:11:50 GMT
		'5.4.0' => 1712331127, // Fri, 05 Apr 2024 16:11:50 GMT
		'5.5.0' => 1714391660, // Mon Apr 29 2024 14:54:20 GMT+0300
		'5.5.7' => 1728045730, // Fri Oct 04 2024 15:42:10 GMT+0300
		'6.0.0' => 1731805060, // Sun Nov 17 2024 15:42:10 GMT+0300
		'6.0.1' => 1732032780, // Sun Nov 19 2024 16:15:10 GMT+0300
		'6.0.2' => 1733851183, // 2024-12-10T17:19:43Z
		'6.0.3' => 1737579859, // 2025-01-22T21:04:19Z
		'6.0.4' => 1738163661, // 2025-01-29T15:14:21Z
		'6.0.5' => 1741264750, // 2025-03-06T12:39:10Z
		'6.0.6' => 1744142901, // 2025-04-08T20:08:21Z
		'6.0.7' => 1744478149, // 2025-04-12T17:15:49Z
		'7.0.0' => 1746715432, // 2025-05-08T14:43:52Z
		'7.0.1' => 1747063092, // 2025-05-12T15:18:12Z
		'7.0.2' => 1747224364, // 2025-05-14T12:06:04Z
		'7.0.3' => 1747342932, // 2025-05-15T21:02:12Z
		'7.0.4' => 1748278067, // 2025-05-26T16:47:47Z
		'7.0.5' => 1748284645, // 2025-05-26T18:37:25Z
		'7.0.6' => 1749749023, // 2025-06-12T17:23:43Z
		'7.0.7' => 1750152659, // 2025-06-17T09:30:59Z
		'7.0.8' => 1750259212, // 2025-06-18T15:06:52Z
		'7.0.9' => 1750445053, // 2025-06-20T18:44:13Z
		'7.0.10' => 1752076610, // 2025-07-09T15:56:50Z
	'7.0.11' => 1752167653, // 2025-07-10T17:14:13Z
	'7.0.12' => 1757680661, // 2025-09-12T12:37:41Z
	'7.1.0' => 1759341651, // 2025-10-01T18:00:51Z
	'7.1.1' => 1759354423, // 2025-10-01T21:33:43Z
	'7.2.0' => 1762876646, // 2025-11-11T15:57:26Z
	'7.3.0' => 1763372017, //
	'7.3.1' => 1764959582, // 2025-12-05T18:33:02Z
	'7.3.2' => 1765460628, // 2025-12-11T13:43:48Z
	'7.3.3' => 1772062848, // 2026-02-25T23:40:48Z
    '7.3.4' => 1772299246, // 2026-02-28T17:20:46Z
    '7.3.5' => 1772300070, // 2026-02-28T17:34:30Z
];

	/**
	 * @param mixed $validUntil A timestamp, which says the lifetime of this key.
	 * @return boolean True, if the timestamp is invalid.
	 */
	public static function isTimestampInvalid(mixed $validUntil): bool {
		if ($validUntil < 0) {
			return TRUE;
		}
		$releaseTimestampOfCurrentVersion = self::$versionToReleaseTimestamp[self::CURRENT_VERSION];
		if ($releaseTimestampOfCurrentVersion === NULL || $validUntil < $releaseTimestampOfCurrentVersion) {
			return TRUE;
		}

		self::$validUntil = $validUntil;
		return FALSE;
	}

	/**
	 * Should we perform the license check for this key and in this version at this point of time?
	 *
	 * @param string $licenseKey
	 * @return bool
	 */
	public static function shouldCheckKey(string $licenseKey): bool {
		if ($licenseKey !== self::getLastKey()) {
			return TRUE;
		}

		if (self::getLicenseCheckedInVersion() !== self::CURRENT_VERSION) {
			return TRUE;
		}

		// the license was valid the last time we checked, but it has expired, and we haven't done
		// another check since it expired let's make sure we don't have the wrong state in this case
		$licenseExpirationDate = self::getValidLicenseUntilTimestamp();
		return self::getValidLicense() && $licenseExpirationDate < $GLOBALS['EXEC_TIME']
			&& $licenseExpirationDate >= self::getLastLicenseCheckTimestamp();
	}

	/**
	 * Returns the license key that has been set
	 *
	 * @return string
	 */
	public static function getLicenseKey(): string {
		return (string) ExtensionSettingsService::getSetting(ExtensionSettingsService::SETTING_LICENSE);
	}

	/**
	 * Checks whether the system has a valid license
	 *
	 * @return bool
	 */
	public static function hasValidLicense(): bool {
		$licenseKey = self::getLicenseKey();
		if (!self::shouldCheckKey($licenseKey)) {
			return self::getValidLicense();
		}
		self::clearRegistryValues();

		if (!self::isLicenseServerReachable()) {
			return TRUE;
		}

		if (!self::isLicenseValid($licenseKey)) {
			self::setLastKey($licenseKey);
			self::setValidLicense(FALSE);
			self::setLicenseCheckedInVersion(self::CURRENT_VERSION);
			self::setValidLicenseUntilTimestamp(0);
			self::setLastLicenseCheckTimestamp();
		} else {
			self::setValidLicenseUntilTimestamp(self::getValidUntil());
			self::setValidLicense(TRUE);
			self::setLastKey($licenseKey);
			self::setLicenseCheckedInVersion(self::CURRENT_VERSION);
			self::setLastLicenseCheckTimestamp();
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Gets the last key checked for from the registry
	 *
	 * @return mixed|null
	 */
	public static function getLastKey(): mixed {
		return GeneralUtility::makeInstance(Registry::class)->get(
			self::REGISTRY_NAMESPACE,
			self::LAST_LICENSE_KEY_CHECKED_KEY
		);
	}

	/**
	 * Gets the timestamp of the last check from the registry
	 *
	 * @return mixed|null
	 */
	public static function getLastLicenseCheckTimestamp(): mixed {
		return GeneralUtility::makeInstance(Registry::class)->get(
			self::REGISTRY_NAMESPACE,
			self::LAST_CHECKED_TIMESTAMP_KEY
		);
	}

	/**
	 * Sets the timestamp of the last AJAX Notification check in the registry
	 */
	public static function setLastAjaxNotificationCheckTimestamp(): void {
		$registry = GeneralUtility::makeInstance(Registry::class);
		$registry->set(self::REGISTRY_NAMESPACE, self::LAST_AJAX_TIMESTAMP_KEY, $GLOBALS['EXEC_TIME']);
	}

	/**
	 * Gets the validUntil date from this current check
	 *
	 * @return mixed
	 */
	public static function getValidUntil(): mixed {
		if (self::$validUntil === NULL) {
			self::$validUntil = self::getValidLicenseUntilTimestamp();
		}
		return self::$validUntil;
	}

	/**
	 * The timestamp of the key lifetime, if the given license key is valid, or -1 if invalid.
	 *
	 * @param string $licenseKey A license key, which should be validated.
	 * @return bool
	 */
	public static function isLicenseValid(string $licenseKey): bool {
		if (!self::checkLicenseKeyStructure($licenseKey)) {
			return FALSE;
		}

		$validUntil = self::getValidUntilTimestampByLicenseKey($licenseKey);
		return !self::isTimestampInvalid($validUntil);
	}

	/**
	 * Check if the given license key is valid.
	 *
	 * @param string $licenseKey A license key, which should be validated.
	 * @return boolean
	 */
	public static function checkLicenseKeyStructure(string $licenseKey): bool {
		// Structure: XXXXXX-XXXXXX-XXXXXX-XXXXXX | All upper case
		if (substr_count($licenseKey, '-') !== 3) {
			return FALSE;
		}

		$caseControl = strtoupper($licenseKey);
		return $licenseKey === $caseControl && mb_strlen($licenseKey) === 27;
	}

	/**
	 * True, if the license server is reachable.
	 *
	 * @return boolean
	 */
	public static function isLicenseServerReachable(): bool {
		try {
			$requestFactory = GeneralUtility::makeInstance(RequestFactory::class);
			$response = $requestFactory->request(
				self::API_URL,
				'GET',
				[
					'auth' => [self::API_USER, self::API_PASSWORD],
					'timeout' => 1,
					'connect_timeout' => 1,
				]
			);

			self::$lastHttpResponseCode = $response->getStatusCode();

			if (self::$lastHttpResponseCode !== 200 && self::$lastHttpResponseCode !== 201) {
				return FALSE;
			}
		} catch (Exception) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Checks whether we are in the development context
	 *
	 * @return bool
	 */
	public static function isInDevelopmentContext(): bool {
		return Environment::getContext()->isDevelopment();
	}

	/**
	 * Checks if the current TYPO3 version is supported for the license check
	 *
	 * @return bool
	 */
	public static function isTYPO3VersionSupported(): bool {
		$versionNumber = VersionNumberUtility::convertVersionNumberToInteger(
			VersionNumberUtility::getCurrentTypo3Version()
		);
		return $versionNumber >= self::EARLIEST_SUPPORTED_VERSION;
	}

	/**
	 * Checks if the time for the next check has expired.
	 *
	 * Error = 0 means no error
	 *  = 1 is an error
	 *  = 2 is a warning
	 *
	 * @return bool
	 */
	public static function isTimeForNextCheck(): bool {
		return self::getLastAjaxNotificationCheckTimestamp()
			+ self::AMOUNT_OF_DAYS_UNTIL_NEXT_CHECK * 24 * 60 * 60 < $GLOBALS['EXEC_TIME'];
	}

	/**
	 * Performs the license check and returns the output data to the frontend
	 *
	 * @param bool $isAjaxCheck
	 * @return array
	 */
	public static function getLicenseCheckResponseData(bool $isAjaxCheck = FALSE): array {
		// if the key is empty - error
		if (!self::getLicenseKey()) {
			return [
				'error' => 1,
				'title' => LocalizationUtility::translate('backend.licenceCheck.error.title', 'sg_cookie_optin'),
				'message' => LocalizationUtility::translate(
					'backend.licenceCheck.noLicenseKey',
					'sg_cookie_optin',
					[LocalizationUtility::translate('backend.licenceCheck.shopLink', 'sg_cookie_optin')]
				)
			];
		}

		// if not valid - error
		if (!self::hasValidLicense()) {
			return [
				'error' => 1,
				'title' => LocalizationUtility::translate('backend.licenceCheck.error.title', 'sg_cookie_optin'),
				'message' => LocalizationUtility::translate(
					'backend.licenceCheck.expiredError.message',
					'sg_cookie_optin',
					[LocalizationUtility::translate('backend.licenceCheck.shopLink', 'sg_cookie_optin')]
				)
			];
		}

		// if it's valid - check validUntil and throw a warning if the license has expired, but you are still
		// on the valid version
		$date = date('d.m.Y', self::getValidUntil());
		if (self::getValidUntil() < $GLOBALS['EXEC_TIME']) {
			// relevant only for the AJAX notifications
			if ($isAjaxCheck) {
				$lastWarningTimestamp = (int) self::getLastWarningTimestamp();
			}

			if (!$isAjaxCheck ||
				(($lastWarningTimestamp + self::AMOUNT_OF_DAYS_UNTIL_WARNING * 24 * 60 * 60) < $GLOBALS['EXEC_TIME'])
			) {
				if ($isAjaxCheck) {
					self::setLastWarningTimestamp($GLOBALS['EXEC_TIME']);
				}

				return [
					'error' => 2,
					'title' => LocalizationUtility::translate('backend.licenceCheck.warning.title', 'sg_cookie_optin'),
					'message' => LocalizationUtility::translate(
						'backend.licenceCheck.expiringWarning.message',
						'sg_cookie_optin',
						[$date, LocalizationUtility::translate('backend.licenceCheck.shopLink', 'sg_cookie_optin')]
					)
				];
			}
		}

		// 19.01.2038 == lifetime license
		$date = date('d.m.Y', self::getValidUntil());
		if ($date === '19.01.2038') {
			$date = LocalizationUtility::translate('backend.licenceCheck.status.lifetime', 'sg_cookie_optin');
		}

		return [
			'error' => 0,
			'title' => LocalizationUtility::translate('backend.licenceCheck.status.title', 'sg_cookie_optin'),
			'message' => LocalizationUtility::translate('backend.licenceCheck.status.okMessage', 'sg_cookie_optin', [$date])
		];
	}

	/**
	 * Returns one of the state constants of this class.
	 *
	 * @return int
	 */
	public static function checkKey(): int {
		$key = ExtensionSettingsService::getSetting(ExtensionSettingsService::SETTING_LICENSE);
		if (empty($key)) {
			return self::STATE_LICENSE_NOT_SET;
		}

		if (preg_match('/^([A-Z\d]{6}-?){4}$/', $key)) {
			return self::STATE_LICENSE_VALID;
		}

		return self::STATE_LICENSE_INVALID;
	}

	/**
	 * Checks if this instance is in the demo mode.
	 *
	 * @return bool
	 */
	public static function isInDemoMode(): bool {
		$demoData = self::getDemoModeData();
		if (!$demoData) {
			return FALSE;
		}

		if (self::getRemainingTimeInDemoMode() <= 0) {
			return FALSE;
		}

		if ((int) $demoData['amount'] > self::DEMO_MODE_MAX_AMOUNT) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Returns the remaining time in seconds for the demo mode.
	 *
	 * @return int
	 */
	public static function getRemainingTimeInDemoMode(): int {
		$demoData = self::getDemoModeData();
		if (!$demoData) {
			return 0;
		}

		return $demoData['lastActivation'] + self::DEMO_MODE_LIFETIME - $GLOBALS['EXEC_TIME'];
	}

	/**
	 * Activates the demo mode for this instance.
	 *
	 * @return void
	 */
	public static function activateDemoMode(): void {
		$amount = 1;
		$demoData = self::getDemoModeData();
		if ($demoData && isset($demoData['amount'])) {
			$amount += (int) $demoData['amount'];
		}

		$registry = GeneralUtility::makeInstance(Registry::class);
		$registry->set(self::REGISTRY_NAMESPACE, self::DEMO_MODE_KEY, [
			'lastActivation' => $GLOBALS['EXEC_TIME'],
			'amount' => $amount,
		]);
	}

	/**
	 * Returns true if this instance can use the demo mode.
	 *
	 * @return bool
	 */
	public static function isDemoModeAcceptable(): bool {
		$demoData = self::getDemoModeData();
		if (!$demoData) {
			return TRUE;
		}

		return (int) $demoData['amount'] < self::DEMO_MODE_MAX_AMOUNT;
	}

	/**
	 * Removes all files within the specific generated file folder.
	 *
	 * @return void
	 */
	public static function removeAllCookieOptInFiles(): void {
		$folder = ExtensionSettingsService::getSetting(ExtensionSettingsService::SETTING_FOLDER);
		if (!$folder) {
			return;
		}

		$sitePath = defined('PATH_site') ? PATH_site : Environment::getPublicPath() . '/';
		GeneralUtility::rmdir($sitePath . $folder, TRUE);
	}

	/**
	 * Sets the last key checked for from the registry
	 *
	 * @param string $licenseKey
	 */
	protected static function setLastKey(string $licenseKey): void {
		$registry = GeneralUtility::makeInstance(Registry::class);
		$registry->set(self::REGISTRY_NAMESPACE, self::LAST_LICENSE_KEY_CHECKED_KEY, $licenseKey);
	}

	/**
	 * Sets if the license is valid in the registry
	 *
	 * @param bool $isValid
	 */
	protected static function setValidLicense(bool $isValid): void {
		$registry = GeneralUtility::makeInstance(Registry::class);
		$registry->set(self::REGISTRY_NAMESPACE, self::IS_KEY_VALID_KEY, $isValid);
	}

	/**
	 * Gets the isValid from the registry
	 *
	 * @return bool
	 */
	protected static function getValidLicense(): bool {
		$registry = GeneralUtility::makeInstance(Registry::class);
		return (bool) $registry->get(self::REGISTRY_NAMESPACE, self::IS_KEY_VALID_KEY);
	}

	/**
	 * Stores the last warning timestamp
	 *
	 * @param int $timestamp
	 */
	protected static function setLastWarningTimestamp(int $timestamp): void {
		$registry = GeneralUtility::makeInstance(Registry::class);
		$registry->set(self::REGISTRY_NAMESPACE, self::LAST_WARNING_TIMESTAMP_KEY, $timestamp);
	}

	/**
	 * Gets the last warning timestamp
	 *
	 * @return mixed
	 */
	protected static function getLastWarningTimestamp(): mixed {
		return GeneralUtility::makeInstance(Registry::class)->get(
			self::REGISTRY_NAMESPACE,
			self::LAST_WARNING_TIMESTAMP_KEY
		);
	}

	/**
	 * Stores the valid until timestamp in the registry
	 *
	 * @param mixed $validUntil
	 */
	protected static function setValidLicenseUntilTimestamp(mixed $validUntil): void {
		$registry = GeneralUtility::makeInstance(Registry::class);
		$registry->set(self::REGISTRY_NAMESPACE, self::HAS_VALID_LICENSE_UNTIL_TIMESTAMP_KEY, $validUntil);
	}

	/**
	 * Gets the valid until timestamp from the registry
	 *
	 * @return mixed|null
	 */
	protected static function getValidLicenseUntilTimestamp(): mixed {
		return GeneralUtility::makeInstance(Registry::class)->get(
			self::REGISTRY_NAMESPACE,
			self::HAS_VALID_LICENSE_UNTIL_TIMESTAMP_KEY
		);
	}

	/**
	 * Sets the version that the license was last valid for
	 *
	 * @param string $version
	 */
	protected static function setLicenseCheckedInVersion(string $version): void {
		$registry = GeneralUtility::makeInstance(Registry::class);
		$registry->set(self::REGISTRY_NAMESPACE, self::LICENSE_CHECKED_IN_VERSION_KEY, $version);
	}

	/**
	 * Gets the version that the license was last valid for
	 *
	 * @return mixed|null
	 */
	protected static function getLicenseCheckedInVersion(): mixed {
		return GeneralUtility::makeInstance(Registry::class)->get(
			self::REGISTRY_NAMESPACE,
			self::LICENSE_CHECKED_IN_VERSION_KEY
		);
	}

	/**
	 * Sets the timestamp of the last check in the registry
	 */
	protected static function setLastLicenseCheckTimestamp(): void {
		$registry = GeneralUtility::makeInstance(Registry::class);
		$registry->set(self::REGISTRY_NAMESPACE, self::LAST_CHECKED_TIMESTAMP_KEY, $GLOBALS['EXEC_TIME']);
	}

	/**
	 * Gets the timestamp of the last AJAX Notification check from the registry
	 *
	 * @return mixed|null
	 */
	protected static function getLastAjaxNotificationCheckTimestamp(): mixed {
		return GeneralUtility::makeInstance(Registry::class)->get(
			self::REGISTRY_NAMESPACE,
			self::LAST_AJAX_TIMESTAMP_KEY
		);
	}

	/**
	 * Clears the registry values
	 */
	protected static function clearRegistryValues(): void {
		$registry = GeneralUtility::makeInstance(Registry::class);
		$registry->remove(self::REGISTRY_NAMESPACE, self::LAST_CHECKED_TIMESTAMP_KEY);
		$registry->remove(self::REGISTRY_NAMESPACE, self::HAS_VALID_LICENSE_UNTIL_TIMESTAMP_KEY);
		$registry->remove(self::REGISTRY_NAMESPACE, self::LICENSE_CHECKED_IN_VERSION_KEY);
		$registry->remove(self::REGISTRY_NAMESPACE, self::IS_KEY_VALID_KEY);
		$registry->remove(self::REGISTRY_NAMESPACE, self::LAST_LICENSE_KEY_CHECKED_KEY);
		$registry->remove(self::REGISTRY_NAMESPACE, self::LAST_WARNING_TIMESTAMP_KEY);
		$registry->remove(self::REGISTRY_NAMESPACE, self::LAST_AJAX_TIMESTAMP_KEY);
	}

	/**
	 * Returns the demo mode data, or an empty FALSE on error.
	 *
	 * @return array
	 */
	protected static function getDemoModeData(): array {
		$registry = GeneralUtility::makeInstance(Registry::class);
		$demoData = $registry->get(self::REGISTRY_NAMESPACE, self::DEMO_MODE_KEY);
		if (!isset($demoData['lastActivation'], $demoData['amount'])) {
			return [];
		}

		return $demoData;
	}

	/**
	 * Returns The timestamp of the key lifetime, if the given license key is valid, on the server, or -1 if invalid.
	 *
	 * @param string $licenseKey
	 * @return int
	 */
	private static function getValidUntilTimestampByLicenseKey(string $licenseKey): int {
		try {
			$url = self::API_URL . '/' . urldecode($licenseKey) . '?product='
				. self::PRODUCT_KEY;

			$requestFactory = GeneralUtility::makeInstance(RequestFactory::class);
			$response = $requestFactory->request(
				$url,
				'GET',
				[
					'auth' => [self::API_USER, self::API_PASSWORD],
					'timeout' => 1,
					'connect_timeout' => 1,
				]
			);

			self::$lastHttpResponseCode = $response->getStatusCode();

			if (self::$lastHttpResponseCode !== 200 && self::$lastHttpResponseCode !== 201) {
				return self::ERROR_INVALID_RESPONSE_CODE;
			}

			if (!$response->getBody()) {
				return self::ERROR_INVALID_RESPONSE_DATA;
			}

			$jsonData = json_decode($response->getBody(), TRUE);
			if (!$jsonData['serial']['valid']) {
				return self::ERROR_INVALID_LICENSE_KEY;
			}

			return (int) $jsonData['serial']['validUntil'];
		} catch (Exception $exception) {
			self::$lastException = $exception->getMessage();
		}

		return self::ERROR_LICENSE_CHECK_EXCEPTION;
	}
}
