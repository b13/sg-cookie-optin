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

use Doctrine\DBAL\Exception;
use Psr\Http\Message\ResponseInterface;
use SGalinski\SgCookieOptin\Domain\Repository\SchedulerTaskRepository;
use SGalinski\SgCookieOptin\Service\OptinHistoryService;
use SGalinski\SgCookieOptin\Traits\InitControllerComponents;
use TYPO3\CMS\Backend\Attribute\Controller;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Http\PropagateResponseException;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Consent Controller
 */
#[Controller]
class ConsentController extends AbstractController {
	use InitControllerComponents;

	/**
	 * @var ModuleTemplateFactory
	 */
	protected ModuleTemplateFactory $moduleTemplateFactory;

	/**
	 * @var ModuleTemplate
	 */
	protected ModuleTemplate $moduleTemplate;

	public function __construct(SchedulerTaskRepository $schedulerTaskRepository) {
		$this->schedulerTaskRepository = $schedulerTaskRepository;
	}

	public function initializeAction(): void {
		// Create and store the template object as a class property
		$this->moduleTemplateFactory = GeneralUtility::makeInstance(ModuleTemplateFactory::class);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
	}

	/**
	 * Displays the user preference consent history
	 *
	 * @throws PropagateResponseException|Exception
	 * @throws \TYPO3\CMS\Core\Exception
	 */
	public function indexAction(): ResponseInterface {
		$this->switchMode();
		$this->initComponents($this->moduleTemplate);
		$this->initPageUidSelection($this->moduleTemplate);
		$pageUid = (int) ($this->request->getParsedBody()['id'] ?? $this->request->getQueryParams()['id'] ?? NULL);
		$this->moduleTemplate->assign('identifiers', OptinHistoryService::getItemIdentifiers(['pid' => $pageUid]));

		// Check if page is site root
		$pageInfo = BackendUtility::readPageAccess($pageUid, $GLOBALS['BE_USER']->getPagePermsClause(1));
		if ($pageInfo && isset($pageInfo['is_siteroot']) && (int) $pageInfo['is_siteroot'] === 1) {
			$this->moduleTemplate->assign('isSiteRoot', TRUE);

			// Add a flash message for "no data found" in a separate queue
			$message = LocalizationUtility::translate('backend.statistics.noDataFound', 'SgCookieOptin');

			// Create a flash message for no data found
			$flashMessage = GeneralUtility::makeInstance(
				FlashMessage::class,
				$message,
				'',
				ContextualFeedbackSeverity::INFO,
				TRUE
			);

			// Add to a separate queue for rendering at the bottom of the page
			$flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
			$messageQueue = $flashMessageService->getMessageQueueByIdentifier('noDataFoundMessage');
			$messageQueue->enqueue($flashMessage);
		}

		// Optionally load JavaScript
		if ($pageUid) {
			$pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
			$pageRenderer->loadJavaScriptModule('@sgalinski/sg-cookie-optin/ConsentManagement.js');
		}

		// Check specifically for website Page 0 => use empty layout
		$pageUid = (int) ($this->request->getQueryParams()['id'] ?? 0);
		$isSiteRoot = ($pageUid === 0);
		$this->moduleTemplate->assign('useEmptyLayout', $isSiteRoot);

		// Render
		return $this->moduleTemplate->renderResponse('Consent/Index');
	}
}
