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

namespace SGalinski\SgCookieOptin\Service;

use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\Components\DocHeaderComponent;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Backend Service class
 */
class BackendService {
	/**
	 * Get all pages the backend user has access to
	 *
	 * @return array
	 * @throws \InvalidArgumentException|\Doctrine\DBAL\Exception
	 */
	public static function getPages() {
		$connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
		$queryBuilder = $connectionPool->getQueryBuilderForTable('pages');
		$queryBuilder->getRestrictions()
			->removeAll()
			->add(GeneralUtility::makeInstance(DeletedRestriction::class));
		$queryBuilder->select('*')
			->from('pages')
			->where(
				$queryBuilder->expr()->eq(
					'is_siteroot',
					1
				),
				$queryBuilder->expr()->eq(
					't3ver_oid',
					0
				),
				$queryBuilder->expr()->eq(
					'sys_language_uid',
					0
				)
			);
		if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '12', '>=')) {
			$rows = $queryBuilder->executeQuery()->fetchAllAssociative();
		} else {
			$rows = $queryBuilder->execute()->fetchAll();
		}

		if (!is_array($rows)) {
			return [];
		}

		$out = [];
		foreach ($rows as $row) {
			$pageInfo = BackendUtility::readPageAccess($row['uid'], $GLOBALS['BE_USER']->getPagePermsClause(1));
			if ($pageInfo) {
				$rootline = BackendUtility::BEgetRootLine($pageInfo['uid'], '', TRUE);
				ksort($rootline);
				$path = '/root';
				foreach ($rootline as $page) {
					$path .= '/p' . dechex($page['uid']);
				}
				$pageInfo['path'] = $path;
				$out[] = $pageInfo;
			}
		}
		return $out;
	}

	/**
	 * Get all optins for the current page.
	 *
	 * @param int $pageUid
	 * @return array
	 * @throws \InvalidArgumentException|\Doctrine\DBAL\Exception
	 */
	public static function getOptins($pageUid) {
		$connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
		$queryBuilder = $connectionPool->getQueryBuilderForTable('tx_sgcookieoptin_domain_model_optin');
		$queryBuilder->getRestrictions()
			->removeAll()
			->add(GeneralUtility::makeInstance(DeletedRestriction::class));
		$queryBuilder->select('*')
			->from('tx_sgcookieoptin_domain_model_optin')
			->where(
				$queryBuilder->expr()->eq(
					'pid',
					$pageUid
				),
				$queryBuilder->expr()->eq(
					'sys_language_uid',
					0
				)
			);
		if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '12', '>=')) {
			$rows = $queryBuilder->executeQuery()->fetchAllAssociative();
		} else {
			$rows = $queryBuilder->execute()->fetchAll();
		}

		return (is_array($rows) ? $rows : []);
	}

	/**
	 * create buttons for the backend module header
	 *
	 * @param DocHeaderComponent $docHeaderComponent
	 * @param Request $request
	 * @throws \InvalidArgumentException
	 * @throws \UnexpectedValueException
	 */
	public static function makeButtons($docHeaderComponent, $request) {
		/** @var ButtonBar $buttonBar */
		$buttonBar = $docHeaderComponent->getButtonBar();

		/** @var IconFactory $iconFactory */
		$iconFactory = GeneralUtility::makeInstance(IconFactory::class);
		$locallangPath = 'LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:';

		if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '12.0.0', '<')) {
			// Refresh
			$refreshButton = $buttonBar->makeLinkButton()
				->setHref(GeneralUtility::getIndpEnv('REQUEST_URI'))
				->setTitle(
					LocalizationUtility::translate(
						$locallangPath . 'labels.reload'
					)
				)
				->setIcon($iconFactory->getIcon('actions-refresh', Icon::SIZE_SMALL));
			$buttonBar->addButton($refreshButton, ButtonBar::BUTTON_POSITION_RIGHT);

			// shortcut button
			$shortcutButton = $buttonBar->makeShortcutButton()
				->setModuleName($request->getPluginName())
				->setGetVariables(
					[
						'id',
						'M'
					]
				)
				->setSetVariables([]);

			$buttonBar->addButton($shortcutButton, ButtonBar::BUTTON_POSITION_RIGHT);
		} else {
			// Refresh
			$refreshButton = $buttonBar->makeLinkButton()
				->setHref(GeneralUtility::getIndpEnv('REQUEST_URI'))
				->setTitle(
					LocalizationUtility::translate(
						$locallangPath . 'labels.reload'
					)
				)
				->setIcon($iconFactory->getIcon('actions-refresh', Icon::SIZE_SMALL));
			$buttonBar->addButton($refreshButton, ButtonBar::BUTTON_POSITION_RIGHT);

			// shortcut button
			$shortcutButton = $buttonBar->makeShortcutButton()
				->setRouteIdentifier($request->getPluginName())
				->setDisplayName('test')
				->setArguments(
					[
						'id',
						'M'
					]
				);

			$buttonBar->addButton($shortcutButton, ButtonBar::BUTTON_POSITION_RIGHT);
		}
	}
}
