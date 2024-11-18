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
use SGalinski\SgCookieOptin\Traits\InitControllerComponents;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Expression\ExpressionBuilder;
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
	 * @return ResponseInterface
	 * @throws Exception
	 * @throws AspectNotFoundException
	 */
	public function cookieListAction(): ResponseInterface {
		/** @var TypoScriptFrontendController $tsfe */
		$tsfe = $GLOBALS['TSFE'];
		$rootPageId = $tsfe->rootLine[0]['uid'] ?? 0;
		$typo3Version = VersionNumberUtility::getCurrentTypo3Version();

		if (version_compare($typo3Version, '13.0.0', '<')) {
			$languageUid = $tsfe->getLanguage()->getLanguageId();
		} else {
			$context = GeneralUtility::makeInstance(Context::class);
			$languageAspect = $context->getAspect('language');
			$languageUid = $languageAspect->getId();
		}

		if (version_compare($typo3Version, '11.0.0', '>=')) {
			$pageRepository = GeneralUtility::makeInstance(PageRepository::class);
		} else {
			$pageRepository = GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\Page\PageRepository::class);
		}

		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)?->getQueryBuilderForTable(
			'tx_sgcookieoptin_domain_model_optin'
		);
		$queryBuilder->select('*')
			->from('tx_sgcookieoptin_domain_model_optin')
			->where($queryBuilder->expr()->eq('pid', $rootPageId))
			->andWhere($queryBuilder->expr()->eq('sys_language_uid', 0));
		if (version_compare($typo3Version, '13.0.0', '<')) {
			$resultObject = $queryBuilder->execute();
		} else {
			$resultObject = $queryBuilder->executeQuery();
		}

		if (method_exists($resultObject, 'fetchAssociative')) {
			$optin = $resultObject->fetchAssociative();
		} else {
			$optin = $resultObject->fetch();
		}
		$defaultLanguageOptinId = $optin['uid'];

		if ($languageUid > 0) {
			if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '13.0.0', '<')) {
				$optin = $pageRepository->getRecordOverlay(
					'tx_sgcookieoptin_domain_model_optin', $optin, $languageUid, $tsfe->getLanguage()->getFallbackType()
				);
			} else {
				$languageAspect = GeneralUtility::makeInstance(LanguageAspect::class, $languageUid);
				$optin = $pageRepository->getLanguageOverlay(
					'tx_sgcookieoptin_domain_model_optin', $optin, $languageAspect
				);
			}
		}

		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)?->getQueryBuilderForTable(
			'tx_sgcookieoptin_domain_model_group'
		);
		$groups = $queryBuilder->select('*')
			->from('tx_sgcookieoptin_domain_model_group')
			->where($queryBuilder->expr()->eq('parent_optin', $defaultLanguageOptinId))
			->andWhere($queryBuilder->expr()->eq('sys_language_uid', 0))
			->andWhere($queryBuilder->expr()->eq('pid', $rootPageId));
		if (version_compare($typo3Version, '13.0.0', '<')) {
			$groups = $queryBuilder->execute()->fetchAll();
		} else {
			$groups = $queryBuilder->executeQuery()->fetchAllAssociative();
		}

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
				if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '13.0.0', '<')) {
					$group = $pageRepository->getRecordOverlay(
						'tx_sgcookieoptin_domain_model_group', $group, $languageUid,
						$tsfe->getLanguage()->getFallbackType()
					);
				} else {
					$languageAspect = GeneralUtility::makeInstance(LanguageAspect::class, $languageUid);
					$group = $pageRepository->getLanguageOverlay(
						'tx_sgcookieoptin_domain_model_group', $group, $languageAspect
					);
				}
			}

			// Get the QueryBuilder instance
			$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
				?->getQueryBuilderForTable('tx_sgcookieoptin_domain_model_cookie');

			// Build the conditions
			$conditions = [
				$queryBuilder->expr()->eq('sys_language_uid', 0),
				$queryBuilder->expr()->eq('pid', $rootPageId),
			];

			// Compatibility for TYPO3 v12+ and earlier
			if (method_exists(ExpressionBuilder::class, 'and')) {
				// TYPO3 v12+ (andX removed)
				$andCondition = $queryBuilder->expr()->and(...$conditions);
			} else {
				// TYPO3 v11 or earlier
				$andCondition = $queryBuilder->expr()->andX(...$conditions);
			}

			// Build the query
			$query = $queryBuilder->select('*')
				->from('tx_sgcookieoptin_domain_model_cookie')
				->where($queryBuilder->expr()->eq('parent_group', $defaultLanguageGroupUid))
				->andWhere($queryBuilder->expr()->eq('parent_optin', $optin['uid']))
				->andWhere($andCondition);

			// Execute the query and fetch results
			if (method_exists($queryBuilder, 'executeQuery')) {
				// TYPO3 v13+ (executeQuery replaces execute, fetchAllAssociative replaces fetchAll)
				$cookies = $query->executeQuery()->fetchAllAssociative();
			} else {
				// TYPO3 v12 and earlier
				$cookies = $query->execute()->fetchAll();
			}

			if ($languageUid > 0) {
				foreach ($cookies as &$cookie) {
					if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '13.0.0', '<')) {
						$cookie = $pageRepository->getRecordOverlay(
							'tx_sgcookieoptin_domain_model_cookie', $cookie, $languageUid,
							$tsfe->getLanguage()->getFallbackType()
						);
					} else {
						$languageAspect = GeneralUtility::makeInstance(LanguageAspect::class, $languageUid);
						$cookie = $pageRepository->getLanguageOverlay(
							'tx_sgcookieoptin_domain_model_cookie', $cookie, $languageAspect
						);
					}
				}
				unset($cookie);
			}
			$group['cookies'] = $cookies;
		}
		unset($group);

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
	 * @return ResponseInterface
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
