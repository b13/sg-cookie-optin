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

namespace SGalinski\SgCookieOptin\Traits;

use SGalinski\SgCookieOptin\Service\BackendService;
use SGalinski\SgCookieOptin\Service\LicenceCheckService;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
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
	protected function initComponents(ModuleTemplate $moduleTemplate) {
		$typo3Version = VersionNumberUtility::convertVersionNumberToInteger(\TYPO3\CMS\Core\Utility\VersionNumberUtility::getCurrentTypo3Version());
		$keyState = LicenceCheckService::checkKey();
		$isInDemoMode = LicenceCheckService::isInDemoMode();
		$hasValidLicense = LicenceCheckService::hasValidLicense();
		if ($isInDemoMode && !$hasValidLicense) {
			// - 1 because the flash message would show 00:00:00 instead of 23:59:59
			$this->addFlashMessage(
				LocalizationUtility::translate(
					'backend.licenseKey.isInDemoMode.description',
					'sg_cookie_optin',
					[
						date('H:i:s', mktime(0, 0, LicenceCheckService::getRemainingTimeInDemoMode() - 1))
					]
				),
				LocalizationUtility::translate('backend.licenseKey.isInDemoMode.header', 'sg_cookie_optin'),
				AbstractMessage::INFO
			);
		} elseif ($keyState === LicenceCheckService::STATE_LICENSE_NOT_SET) {
			if (!LicenceCheckService::isInDevelopmentContext()) {
				LicenceCheckService::removeAllCookieOptInFiles();
			}


            $description = LocalizationUtility::translate(
                'backend.licenseKey.notSet.descriptionTYPO3-9',
                'sg_cookie_optin'
            );

			if (LicenceCheckService::isInDevelopmentContext()) {
				$description .= ' ' . LocalizationUtility::translate(
					'backend.licenseKey.error.dev',
					'sg_cookie_optin'
				);
			}

			$this->addFlashMessage(
				$description,
				LocalizationUtility::translate('backend.licenseKey.notSet.header', 'sg_cookie_optin'),
				AbstractMessage::WARNING
			);
		} elseif (!$hasValidLicense) {
			if (!LicenceCheckService::isInDevelopmentContext()) {
				LicenceCheckService::removeAllCookieOptInFiles();
			}

            $description = LocalizationUtility::translate(
                'backend.licenseKey.invalid.descriptionTYPO3-9',
                'sg_cookie_optin'
            );

			if (LicenceCheckService::isInDevelopmentContext()) {
				$description .= ' ' . LocalizationUtility::translate(
					'backend.licenseKey.error.dev',
					'sg_cookie_optin'
				);
			}

			$this->addFlashMessage(
				$description,
				LocalizationUtility::translate('backend.licenseKey.invalid.header', 'sg_cookie_optin'),
				AbstractMessage::ERROR
			);
		}

		// create doc header component
		$pageUid = (int) GeneralUtility::_GP('id');
		$pageInfo = BackendUtility::readPageAccess($pageUid, $GLOBALS['BE_USER']->getPagePermsClause(1));

		// the docHeaderComponent do not exist below version 7
        if ($pageInfo === FALSE) {
            $pageInfo = ['uid' => $pageUid];
        }
        $moduleTemplate->getDocHeaderComponent()->setMetaInformation($pageInfo);
        BackendService::makeButtons($moduleTemplate->getDocHeaderComponent(), $this->request);
        $moduleTemplate->assign('docHeader', $moduleTemplate->getDocHeaderComponent()->docHeaderContent());

        $moduleTemplate->assign('typo3Version', $typo3Version);
        $moduleTemplate->assign('pageUid', $pageUid);
        $moduleTemplate->assign('invalidKey', !$hasValidLicense);
        $moduleTemplate->assign('controller', $this->request->getControllerName());
        $moduleTemplate->assign('showDemoButton', !$isInDemoMode && LicenceCheckService::isDemoModeAcceptable());
	}

	/**
	 * Initializes the root page selection
	 */
	protected function initPageUidSelection(ModuleTemplate $moduleTemplate) {
		$pageUid = (int) GeneralUtility::_GP('id');
		$pageInfo = BackendUtility::readPageAccess($pageUid, $GLOBALS['BE_USER']->getPagePermsClause(1));
		if ($pageInfo && isset($pageInfo['is_siteroot']) && (int) $pageInfo['is_siteroot'] === 1) {
            $moduleTemplate->assign('isSiteRoot', TRUE);
		} else {
            $moduleTemplate->assign('pages', BackendService::getPages());
		}
	}
}
