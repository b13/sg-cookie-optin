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

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class BaseUrlService
 *
 * Determines the base URL of the current system
 *
 * @package SGalinski\SgCookieOptin\Service
 */
class BaseUrlService {
	/**
	 * Gets the base Url for this site root
	 *
	 * @param int $rootPid
	 * @param int $languageId
	 * @return string
	 */
	public static function getSiteBaseUrl($rootPid, int $languageId = 0) {
		$rootPid = (int) $rootPid;

		try {
			$siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
			$site = $siteFinder->getSiteByPageId($rootPid);
			$basePath = $site->getLanguageById($languageId)->getBase();
			if ($languageId > 0) {
				$defaultBasePath = $site->getLanguageById(0)->getBase();
				// only use the translated host if it differs from the default host
				if ($basePath->getHost() === $defaultBasePath->getHost()) {
					$basePath = $defaultBasePath;
				}
			}

			$basePath = (string) $basePath;

		} catch (SiteNotFoundException $exception) {
			$basePath = '/';
		}

		if ($basePath[strlen($basePath) - 1] !== '/') {
			$basePath .= '/';
		}

		return $basePath;
	}

	/**
	 * Returns the current language id. Only in frontend context usable.
	 *
	 * @return int
	 * @throws AspectNotFoundException
	 */
	public static function getLanguage() {
		$languageAspect = GeneralUtility::makeInstance(Context::class)->getAspect('language');
		// no object check, because if the object is not set, we don't know which language that is anyway
		return $languageAspect->getId();
	}
}
