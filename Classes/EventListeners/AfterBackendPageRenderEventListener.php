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

namespace SGalinski\SgCookieOptin\EventListeners;

use SGalinski\SgCookieOptin\Service\LicenceCheckService;
use TYPO3\CMS\Backend\Controller\Event\AfterBackendPageRenderEvent;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AfterBackendPageRenderEventListener {
	public function __invoke(AfterBackendPageRenderEvent $event) {
		if (!LicenceCheckService::isTYPO3VersionSupported()
			|| LicenceCheckService::isInDevelopmentContext()
		) {
			return;
		}

		$pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
		/** @todo change this to a non-requireJS module inclusion when LicenseNotification.js becomes a native JS module */
		$pageRenderer->loadRequireJsModule('TYPO3/CMS/SgCookieOptin/Backend/LicenseNotification');
		$event->setContent($event->getView()->render());
	}
}
