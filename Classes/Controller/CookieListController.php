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

use SGalinski\SgCookieOptin\Traits\InitControllerComponents;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Optin Controller
 */
#[Controller]
class CookieListController extends ActionController {
	use InitControllerComponents;

	/**
	 * @var ModuleTemplateFactory
	 */
	protected $moduleTemplateFactory;

	public function initializeAction(): void {
		$this->moduleTemplateFactory = GeneralUtility::makeInstance(ModuleTemplateFactory::class);
	}

	/**
	 * Renders the cookie list.
	 *
	 * @return \Psr\Http\Message\ResponseInterface
	 */
	public function cookieListAction() {
		/** @var TypoScriptFrontendController $tsfe */
		$tsfe = $GLOBALS['TSFE'];
		$rootPageId = $tsfe->rootLine[0]['uid'] ?? 0;
		$languageUid = $tsfe->getLanguage()->getLanguageId();

		$versionNumber = VersionNumberUtility::convertVersionNumberToInteger(
			VersionNumberUtility::getCurrentTypo3Version()
		);
		if ($versionNumber >= 11000000) {
			$pageRepository = GeneralUtility::makeInstance(PageRepository::class);
		} else {
			$pageRepository = GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\Page\PageRepository::class);
		}

		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable(
			'tx_sgcookieoptin_domain_model_optin'
		);
		$resultObject = $queryBuilder->select('*')
			->from('tx_sgcookieoptin_domain_model_optin')
			->where($queryBuilder->expr()->eq('pid', $rootPageId))
			->andWhere($queryBuilder->expr()->eq('sys_language_uid', 0))
			->execute();
		if (method_exists($resultObject, 'fetchAssociative')) {
			$optin = $resultObject->fetchAssociative();
		} else {
			$optin = $resultObject->fetch();
		}
		$defaultLanguageOptinId = $optin['uid'];

		if ($languageUid > 0) {
			$optin = $pageRepository->getRecordOverlay(
				'tx_sgcookieoptin_domain_model_optin', $optin, $languageUid, $tsfe->getLanguage()->getFallbackType()
			);
		}

		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable(
			'tx_sgcookieoptin_domain_model_group'
		);
		$groups = $queryBuilder->select('*')
			->from('tx_sgcookieoptin_domain_model_group')
			->where($queryBuilder->expr()->eq('parent_optin', $defaultLanguageOptinId))
			->andWhere($queryBuilder->expr()->eq('sys_language_uid', 0))
			->andWhere($queryBuilder->expr()->eq('pid', $rootPageId))
			->execute()
			->fetchAll();

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
				$group = $pageRepository->getRecordOverlay(
					'tx_sgcookieoptin_domain_model_group', $group, $languageUid, $tsfe->getLanguage()->getFallbackType()
				);
			}
			$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable(
				'tx_sgcookieoptin_domain_model_cookie'
			);
			$cookies = $queryBuilder->select('*')
				->from('tx_sgcookieoptin_domain_model_cookie')
				->where($queryBuilder->expr()->eq('parent_group', $defaultLanguageGroupUid))
				->andWhere(
					$queryBuilder->expr()->andX(
						$queryBuilder->expr()->eq('sys_language_uid', 0),
						$queryBuilder->expr()->eq('pid', $rootPageId)
					)
				)
				->execute()
				->fetchAll();

			if ($languageUid > 0) {
				foreach ($cookies as &$cookie) {
					$cookie = $pageRepository->getRecordOverlay(
						'tx_sgcookieoptin_domain_model_cookie', $cookie, $languageUid,
						$tsfe->getLanguage()->getFallbackType()
					);
				}
			}
			$group['cookies'] = $cookies;
		}

		// Set template
		if ($optin['template_selection'] === 1) {
			$templatePath = $this->settings['templates']['CookieList']['full'];
			$templateRootPaths = $this->view->getRenderingContext()->getTemplatePaths()->getTemplateRootPaths();
			$templateRootPaths[0] = $templatePath;
			$this->view->setTemplateRootPaths($templateRootPaths);
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
	 * @return \Psr\Http\Message\ResponseInterface
	 */
	public function showAction() {
		// Set template
		$view = GeneralUtility::makeInstance(StandaloneView::class);
		$templateNameAndPath = 'EXT:sg_cookie_optin/Resources/Private/Templates/CookieList/Show.html';
		$view->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($templateNameAndPath));
		$view->setPartialRootPaths(['EXT:sg_cookie_optin/Resources/Private/Partials']);
		$view->setLayoutRootPaths(['EXT:sg_cookie_optin/Resources/Private/Layouts']);

		return $this->htmlResponse($view->render());
	}
}
