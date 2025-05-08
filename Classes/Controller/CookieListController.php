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
 *  the Free Software Foundation; either version 3 of the License or
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
use SGalinski\SgCookieOptin\Traits\InitControllerComponents;
use TYPO3\CMS\Backend\Attribute\Controller;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3Fluid\Fluid\View\AbstractTemplateView;

/**
 * Optin Controller
 */
#[Controller]
class CookieListController extends AbstractController {
	use InitControllerComponents;

	/**
	 * @var ModuleTemplateFactory
	 */
	protected ModuleTemplateFactory $moduleTemplateFactory;

	public function initializeAction(): void {
		$this->moduleTemplateFactory = GeneralUtility::makeInstance(ModuleTemplateFactory::class);
	}

	/**
	 * Renders the cookie list.
	 *
	 * @return ResponseInterface
	 * @throws Exception
	 * @throws AspectNotFoundException
	 */
	public function cookieListAction(): ResponseInterface {
		/** @var TypoScriptFrontendController $tsfe */
		$tsfe = $GLOBALS['TSFE'];
		$rootPageId = $tsfe->rootLine[0]['uid'] ?? 0;

		$context = GeneralUtility::makeInstance(Context::class);
		$languageAspect = $context->getAspect('language');
		$languageUid = $languageAspect->getId();

		$pageRepository = GeneralUtility::makeInstance(PageRepository::class);
		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)?->getQueryBuilderForTable(
			'tx_sgcookieoptin_domain_model_optin'
		);
		$queryBuilder->select('*')
			->from('tx_sgcookieoptin_domain_model_optin')
			->where($queryBuilder->expr()->eq('pid', $rootPageId))
			->andWhere($queryBuilder->expr()->eq('sys_language_uid', 0));
		$resultObject = $queryBuilder->executeQuery();
		$optin = $resultObject->fetchAssociative();
		$defaultLanguageOptinId = $optin['uid'];

		if ($languageUid > 0) {
			$languageAspect = GeneralUtility::makeInstance(LanguageAspect::class, $languageUid);
			$optin = $pageRepository->getLanguageOverlay(
				'tx_sgcookieoptin_domain_model_optin', $optin, $languageAspect
			);
		}

		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable(
			'tx_sgcookieoptin_domain_model_group'
		);
		$queryBuilder->select('*')
			->from('tx_sgcookieoptin_domain_model_group')
			->where($queryBuilder->expr()->eq('parent_optin', $defaultLanguageOptinId))
			->andWhere($queryBuilder->expr()->eq('sys_language_uid', 0))
			->andWhere($queryBuilder->expr()->eq('pid', $rootPageId));
		$groups = $queryBuilder->executeQuery()->fetchAllAssociative();

		array_unshift($groups, [
			'uid' => 0,
			'title' => $optin['essential_title'],
			'description' => $optin['essential_description'],
			'cookies' => 0
		]);

		foreach ($groups as &$group) {
			$defaultLanguageGroupUid = $group['uid'];
			if ($group['uid'] > 0 && $languageUid > 0) {
				// fix language first
				$languageAspect = GeneralUtility::makeInstance(LanguageAspect::class, $languageUid);
				$group = $pageRepository->getLanguageOverlay(
					'tx_sgcookieoptin_domain_model_group', $group, $languageAspect
				);
			}

			// Get the QueryBuilder instance
			$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
				->getQueryBuilderForTable('tx_sgcookieoptin_domain_model_cookie');

			// Build the conditions
			$conditions = [
				$queryBuilder->expr()->eq('sys_language_uid', 0),
				$queryBuilder->expr()->eq('pid', $rootPageId),
			];

			// Compatibility for TYPO3 v12+ and earlier
			$andCondition = $queryBuilder->expr()->and(...$conditions);

			// Build the query
			$queryBuilder->select('*')
				->from('tx_sgcookieoptin_domain_model_cookie')
				->where($queryBuilder->expr()->eq('parent_group', $defaultLanguageGroupUid))
				->andWhere($andCondition);

			// Execute the query and fetch results
			$cookies = $queryBuilder->executeQuery()->fetchAllAssociative();

			if ($languageUid > 0) {
				foreach ($cookies as &$cookie) {
					$languageAspect = GeneralUtility::makeInstance(LanguageAspect::class, $languageUid);
					$cookie = $pageRepository->getLanguageOverlay(
						'tx_sgcookieoptin_domain_model_cookie', $cookie, $languageAspect
					);
				}
				unset($cookie);
			}
			$group['cookies'] = $cookies;
		}
		unset($group);

		// Set template
		if ($optin['template_selection'] === 1) {
			$templatePath = $this->settings['templates']['CookieList']['full'];
			/** @var AbstractTemplateView $view */
			$view = $this->view;
			$templateRootPaths = $view->getRenderingContext()->getTemplatePaths()->getTemplateRootPaths();
			$templateRootPaths[0] = $templatePath;
			$view->getRenderingContext()->getTemplatePaths()->setTemplateRootPaths($templateRootPaths);
		}

		$this->view->assign('groups', $groups);
		$this->view->assign('optin', $optin);
		$this->view->assign('headline', $this->settings['headline'] ?? '');
		$this->view->assign('description', $this->settings['description'] ?? '');
		return $this->htmlResponse($this->view->render());
	}

	/**
	 * Renders the cookie consent.
	 *
	 * @return ResponseInterface
	 */
	public function showAction(): ResponseInterface {
		// Set template
		$view = GeneralUtility::makeInstance(StandaloneView::class);
		$templateNameAndPath = 'EXT:sg_cookie_optin/Resources/Private/Templates/CookieList/Show.html';
		$view->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($templateNameAndPath));
		$view->setPartialRootPaths(['EXT:sg_cookie_optin/Resources/Private/Partials']);
		$view->setLayoutRootPaths(['EXT:sg_cookie_optin/Resources/Private/Layouts']);

		return $this->htmlResponse($view->render());
	}
}
