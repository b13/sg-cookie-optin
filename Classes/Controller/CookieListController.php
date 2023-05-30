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

use DirectoryIterator;
use Exception;
use SGalinski\SgCookieOptin\Exception\JsonImportException;
use SGalinski\SgCookieOptin\Service\BackendService;
use SGalinski\SgCookieOptin\Service\ExtensionSettingsService;
use SGalinski\SgCookieOptin\Service\JsonImportService;
use SGalinski\SgCookieOptin\Service\LanguageService;
use SGalinski\SgCookieOptin\Service\LicenceCheckService;
use SGalinski\SgCookieOptin\Traits\InitControllerComponents;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\Components\DocHeaderComponent;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;


/**
 * Optin Controller
 */
#[Controller]
class CookieListController extends ActionController
{
    use InitControllerComponents;

    /**
     * @var ModuleTemplateFactory
     */
    protected $moduleTemplateFactory;

    public function initializeAction(): void
    {
        $this->moduleTemplateFactory = GeneralUtility::makeInstance(ModuleTemplateFactory::class);
    }

    /**
     * Renders the cookie list.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function cookieListAction()
    {
        $rootPageId = $GLOBALS['TSFE']->rootLine[0]['uid'] ?? 0;
        $languageUid = $GLOBALS['TSFE']->getLanguage()->getLanguageId();

//        $moduleTemplate = GeneralUtility::makeInstance(ModuleTemplate::class);

        $versionNumber = VersionNumberUtility::convertVersionNumberToInteger(VersionNumberUtility::getCurrentTypo3Version());
        if ($versionNumber >= 11000000) {
            $pageRepository = GeneralUtility::makeInstance(PageRepository::class);
        } else {
            $pageRepository = GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\Page\PageRepository::class);
        }

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable(
            'tx_sgcookieoptin_domain_model_optin'
        );
        $optin = $queryBuilder->select('*')
            ->from('tx_sgcookieoptin_domain_model_optin')
            ->where($queryBuilder->expr()->eq('pid', $rootPageId))
            ->andWhere($queryBuilder->expr()->eq('sys_language_uid', 0))
            ->execute()
            ->fetchAssociative();
        $defaultLanguageOptinId = $optin['uid'];

        if ($languageUid > 0) {
            $optin = $pageRepository->getRecordOverlay('tx_sgcookieoptin_domain_model_optin', $optin, $languageUid);
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
                $group = $pageRepository->getRecordOverlay('tx_sgcookieoptin_domain_model_group', $group, $languageUid);
            }
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable(
                'tx_sgcookieoptin_domain_model_cookie'
            );
            $cookies = $queryBuilder->select('*')
                ->from('tx_sgcookieoptin_domain_model_cookie')
                ->where($queryBuilder->expr()->eq('parent_group', $defaultLanguageGroupUid))
                ->andWhere($queryBuilder->expr()->eq('sys_language_uid', 0))
                ->andWhere($queryBuilder->expr()->eq('pid', $rootPageId))
                ->execute()
                ->fetchAll();

            if ($languageUid > 0) {
                foreach ($cookies as &$cookie) {
                    $cookie = $pageRepository->getRecordOverlay(
                        'tx_sgcookieoptin_domain_model_cookie', $cookie, $languageUid
                    );
                }
            }
            $group['cookies'] = $cookies;
        }

        // Set template
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        if ($optin['template_selection'] === 1) {
            $templateNameAndPath = 'EXT:sg_cookie_optin/Resources/Private/Templates/CookieList/Full.html';
        } else {
            $templateNameAndPath = 'EXT:sg_cookie_optin/Resources/Private/Templates/CookieList/Default.html';
        }
        $view->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($templateNameAndPath));
        $view->setPartialRootPaths(['EXT:sg_cookie_optin/Resources/Private/Partials']);
        $view->setLayoutRootPaths(['EXT:sg_cookie_optin/Resources/Private/Layouts']);

        $view->assign('groups', $groups);
        $view->assign('optin', $optin);
        $view->assign('headline', $this->settings['headline'] ?? '');
        $view->assign('description', $this->settings['description'] ?? '');

        return $this->htmlResponse($view->render());
    }

    /**
     * Renders the cookie consent.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function showAction()
    {
        // Set template
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $templateNameAndPath = 'EXT:sg_cookie_optin/Resources/Private/Templates/CookieList/Show.html';
        $view->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($templateNameAndPath));
        $view->setPartialRootPaths(['EXT:sg_cookie_optin/Resources/Private/Partials']);
        $view->setLayoutRootPaths(['EXT:sg_cookie_optin/Resources/Private/Layouts']);

        return $this->htmlResponse($view->render());
    }
}
