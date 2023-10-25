<?php

/**
 *
 * Copyright notice
 *
 * (c) sgalinski Internet Services (https://www.sgalinski.de)
 *
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 */

namespace SGalinski\SgCookieOptin\Hook;

use SGalinski\SgCookieOptin\Service\LicenceCheckService;
use TYPO3\CMS\Backend\Controller\BackendController;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class BackendControllerHook
 *
 * @package SGalinski\ProjectBase\Hook
 * @author Georgi Mateev <georgi.mateev@sgalinski.de>
 */
class LicenceCheckHook {
	/**
	 * Add JavaScript to display the expiring license warning
	 */
	protected function addAjaxLicenseCheck() {
		$pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
		$pageRenderer->loadRequireJsModule('TYPO3/CMS/SgCookieOptin/Backend/LicenseNotification');
	}

	/**
	 * Checks if the license key is OK
	 *
	 * @param array $configuration
	 * @param BackendController $parentBackendController
	 */
	public function performLicenseCheck(array $configuration, BackendController $parentBackendController) {
		if (!LicenceCheckService::isTYPO3VersionSupported()
			|| LicenceCheckService::isInDevelopmentContext()
		) {
			return;
		}

		$this->addAjaxLicenseCheck();
	}
}
