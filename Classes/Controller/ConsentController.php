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

namespace SGalinski\SgCookieOptin\Controller;

use SGalinski\SgCookieOptin\Service\OptinHistoryService;
use SGalinski\SgCookieOptin\Traits\InitControllerComponents;
use TYPO3\CMS\Backend\Template\Components\DocHeaderComponent;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Consent Controller
 */
#[Controller]
class ConsentController extends ActionController {
	use InitControllerComponents;

	/**
	 * DocHeaderComponent
	 *
	 * @var DocHeaderComponent
	 */
	protected $docHeaderComponent;

	public function __construct(
		protected readonly ModuleTemplateFactory $moduleTemplateFactory,
    )
    {
	}

	/**
	 * Displays the user preference consent history
	 *
	 */
	public function indexAction() {
		$moduleTemplate = $this->moduleTemplateFactory->create($this->request);
		$this->initComponents($moduleTemplate);
		$this->initPageUidSelection($moduleTemplate);

		$pageUid = (int) GeneralUtility::_GP('id');
		$moduleTemplate->assign(
			'identifiers',
			OptinHistoryService::getItemIdentifiers(
				[
					'pid' => $pageUid
				]
			)
		);

		if ($pageUid) {
			$pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
			$pageRenderer->loadRequireJsModule('TYPO3/CMS/SgCookieOptin/Backend/ConsentManagement');
		}

		return $moduleTemplate->renderResponse('Consent/Index');
	}
}
