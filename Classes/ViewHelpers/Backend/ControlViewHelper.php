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

namespace SGalinski\SgCookieOptin\ViewHelpers\Backend;

use TYPO3\CMS\Backend\RecordList\DatabaseRecordList;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

class ControlViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper {
	/**
	 * Initialize the ViewHelper arguments
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('table', 'string', 'The table to control', TRUE);
		$this->registerArgument('row', 'array', 'The row of the record', TRUE);
	}

	/**
	 * Renders the control buttons for the specified record
	 *
	 * @return string
	 * @throws \InvalidArgumentException
	 * @throws \UnexpectedValueException
	 */
	public function render() {
		$table = $this->arguments['table'];
		$row = $this->arguments['row'];

		$pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
		if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '12', '>=')) {
			// TODO: refactor RequireJS module to regular ES6 module and include here
		} else {
			$pageRenderer->loadRequireJsModule('TYPO3/CMS/Backend/AjaxDataHandler');
		}
		$pageRenderer->addInlineLanguageLabelFile('EXT:backend/Resources/Private/Language/locallang_alt_doc.xlf');

		$currentTypo3Version = VersionNumberUtility::getCurrentTypo3Version();
		$languageService = $GLOBALS['LANG'];
		$languageService->includeLLFile('EXT:backend/Resources/Private/Language/locallang_alt_doc.xlf');

		/** @var DatabaseRecordList $databaseRecordList */
		$databaseRecordList = GeneralUtility::makeInstance(DatabaseRecordList::class);
		$pageInfo = BackendUtility::readPageAccess($row['pid'], $GLOBALS['BE_USER']->getPagePermsClause(1));
		if (version_compare($currentTypo3Version, '11.0.0', '<')) {
			$databaseRecordList->calcPerms = $GLOBALS['BE_USER']->calcPerms($pageInfo);
		} else {
			$permission = GeneralUtility::makeInstance(Permission::class);
			$permission->set($GLOBALS['BE_USER']->calcPerms($pageInfo));
			$databaseRecordList->calcPerms = $permission;

			if (version_compare($currentTypo3Version, '12.0.0', '>=')) {
				$databaseRecordList->setRequest($GLOBALS['TYPO3_REQUEST']);
			}
		}

		return $databaseRecordList->makeControl($table, $row);
	}
}
