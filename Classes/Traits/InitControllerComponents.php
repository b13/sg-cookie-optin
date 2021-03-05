<?php

namespace SGalinski\SgCookieOptin\Traits;

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

use SGalinski\SgCookieOptin\Service\BackendService;
use SGalinski\SgCookieOptin\Service\DemoModeService;
use SGalinski\SgCookieOptin\Service\LicenceCheckService;
use SGalinski\SgCookieOptin\Service\OptinHistoryService;
use TYPO3\CMS\Backend\Template\Components\DocHeaderComponent;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Trait InitControllerComponents
 * Initializes the view componetns for the optin-related controllers
 *
 * @package SGalinski\SgCookieOptin\Traits
 */
trait InitControllerComponents {
	/**
	 * Initialize the demo mode check and the doc header components
	 */
	protected function initComponents() {
		$typo3Version = VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version);
		$keyState = DemoModeService::checkKey();
		$isInDemoMode = DemoModeService::isInDemoMode();
		if ($keyState !== DemoModeService::STATE_LICENSE_VALID && $isInDemoMode) {
			// - 1 because the flash message would show 00:00:00 instead of 23:59:59
			$this->addFlashMessage(
				LocalizationUtility::translate(
					'backend.licenseKey.isInDemoMode.description', 'sg_cookie_optin', [
						date('H:i:s', mktime(0, 0, DemoModeService::getRemainingTimeInDemoMode() - 1))
					]
				),
				LocalizationUtility::translate('backend.licenseKey.isInDemoMode.header', 'sg_cookie_optin'),
				AbstractMessage::INFO
			);
		} elseif ($keyState === DemoModeService::STATE_LICENSE_INVALID) {
			DemoModeService::removeAllCookieOptInFiles();

			if ($typo3Version < 9000000) {
				$description = LocalizationUtility::translate(
					'backend.licenseKey.invalid.description', 'sg_cookie_optin'
				);
			} else {
				$description = LocalizationUtility::translate(
					'backend.licenseKey.invalid.descriptionTYPO3-9', 'sg_cookie_optin'
				);
			}

			$this->addFlashMessage(
				$description,
				LocalizationUtility::translate('backend.licenseKey.invalid.header', 'sg_cookie_optin'),
				AbstractMessage::ERROR
			);
		} elseif ($keyState === DemoModeService::STATE_LICENSE_NOT_SET) {
			DemoModeService::removeAllCookieOptInFiles();

			if ($typo3Version < 9000000) {
				$description = LocalizationUtility::translate(
					'backend.licenseKey.notSet.description', 'sg_cookie_optin'
				);
			} else {
				$description = LocalizationUtility::translate(
					'backend.licenseKey.notSet.descriptionTYPO3-9', 'sg_cookie_optin'
				);
			}

			$this->addFlashMessage(
				$description,
				LocalizationUtility::translate('backend.licenseKey.notSet.header', 'sg_cookie_optin'),
				AbstractMessage::WARNING
			);
		}

		// create doc header component
		$pageUid = (int) GeneralUtility::_GP('id');
		$pageInfo = BackendUtility::readPageAccess($pageUid, $GLOBALS['BE_USER']->getPagePermsClause(1));

		// the docHeaderComponent do not exist below version 7
		if ($typo3Version > 7000000) {
			$this->docHeaderComponent = GeneralUtility::makeInstance(DocHeaderComponent::class);
			if ($pageInfo === FALSE) {
				$pageInfo = ['uid' => $pageUid];
			}
			$this->docHeaderComponent->setMetaInformation($pageInfo);
			BackendService::makeButtons($this->docHeaderComponent, $this->request);
			$this->view->assign('docHeader', $this->docHeaderComponent->docHeaderContent());
		}

		$this->view->assign('typo3Version', $typo3Version);
		$this->view->assign('pageUid', $pageUid);
		$this->view->assign('invalidKey', $keyState !== DemoModeService::STATE_LICENSE_VALID);
		$this->view->assign('showDemoButton', !$isInDemoMode && DemoModeService::isDemoModeAcceptable());
	}

	/**
	 * Initializes the root page selection
	 */
	protected function initPageUidSelection() {
		$pageUid = (int) GeneralUtility::_GP('id');
		$pageInfo = BackendUtility::readPageAccess($pageUid, $GLOBALS['BE_USER']->getPagePermsClause(1));
		if ($pageInfo && (int) $pageInfo['is_siteroot'] === 1) {
			$this->view->assign('isSiteRoot', TRUE);
		} else {
			$this->view->assign('pages', BackendService::getPages());
		}
	}
}