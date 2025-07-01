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
use TYPO3\CMS\Backend\Template\Components\DocHeaderComponent;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Http\PropagateResponseException;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Consent Controller
 */
#[Controller]
class StatisticsController extends AbstractController {
	use InitControllerComponents;

	/**
	 * DocHeaderComponent
	 *
	 * @var DocHeaderComponent
	 */
	protected DocHeaderComponent $docHeaderComponent;

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
		// Create and store the template as a class property
		$this->moduleTemplateFactory = GeneralUtility::makeInstance(ModuleTemplateFactory::class);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
	}

	/**
	 * Displays the user preference statistics
	 *
	 * @throws PropagateResponseException|Exception
	 * @throws Exception
	 */
	public function indexAction(): ResponseInterface {
		// Switch mode, init components, etc.
		$this->switchMode();
		$this->initComponents($this->moduleTemplate);
		$this->initPageUidSelection($this->moduleTemplate);

		// Grab page UID from request
		$pageUid = (int) ($this->request->getParsedBody()['id'] ?? $this->request->getQueryParams()['id'] ?? NULL);
		$this->moduleTemplate->assign(
			'versions',
			OptinHistoryService::getVersions(
				[
					'pid' => $pageUid
				]
			)
		);

		// Check if page is site root in page record
		$pageInfo = BackendUtility::readPageAccess($pageUid, $GLOBALS['BE_USER']->getPagePermsClause(1));
		if ($pageInfo && isset($pageInfo['is_siteroot']) && (int) $pageInfo['is_siteroot'] === 1) {
			$this->moduleTemplate->assign('isSiteRoot', TRUE);

			// Add flash message for "no data found" in a separate queue
			$message = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('backend.statistics.noDataFound', 'SgCookieOptin');

			// Create flash message for no data found
			$flashMessage = GeneralUtility::makeInstance(
				\TYPO3\CMS\Core\Messaging\FlashMessage::class,
				$message,
				'',
				\TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::INFO,
				true
			);

			// Add to a separate queue for rendering at the bottom of the page
			$flashMessageService = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Messaging\FlashMessageService::class);
			$messageQueue = $flashMessageService->getMessageQueueByIdentifier('noDataFoundMessage');
			$messageQueue->enqueue($flashMessage);
		}

		// If we have a real pid > 0, load additional JS
		if ($pageUid) {
			$pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
			$pageRenderer->loadJavaScriptModule('@sgalinski/sg-cookie-optin/dist/statistics.es.js');
		}

		// Check specifically for website page 0 => use empty layout
		$pageUid = (int) ($this->request->getQueryParams()['id'] ?? 0);
		$isSiteRoot = ($pageUid === 0);
		$this->moduleTemplate->assign('useEmptyLayout', $isSiteRoot);

		return $this->moduleTemplate->renderResponse('Statistics/Index');
	}
}
