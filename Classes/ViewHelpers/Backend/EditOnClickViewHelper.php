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

use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

class EditOnClickViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper {
	/**
	 * Register the ViewHelper arguments
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('table', 'string', 'The table for the edit link', TRUE);
		$this->registerArgument('uid', 'int', 'The uid of the record to edit', TRUE);
		$this->registerArgument('pid', 'int', 'The pid of the record to edit', TRUE);
		$this->registerArgument('new', 'bool', 'Open a new record in the popup', FALSE, FALSE);
	}

	/**
	 * Renders the onclick script for editing a record
	 *
	 * @return string
	 * @throws \TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException
	 */
	public function render() {
		$params = '&edit[' . $this->arguments['table'] . '][' . $this->arguments['uid'] . ']='
			. ($this->arguments['new'] ? 'new' : 'edit');
		if (version_compare(VersionNumberUtility::getNumericTypo3Version(), '12.0.0', '>=')) {
			$uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
			// For some reason TYPO3 has two different URI Builders..
			$indexUrl = (string) $uriBuilder->buildUriFromRoutePath(
				'/module/web/sg-cookie-optin', ['id' => $this->arguments['pid']]
			);
			$uriParameters =
				[
					'edit' =>
						[
							$this->arguments['table'] =>
								[
									$this->arguments['uid'] => 'edit'
								],
						],
					'returnUrl' => $indexUrl
				];
			return $uriBuilder->buildUriFromRoute('record_edit', $uriParameters);
		}

		$uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
		$onclickScript = 'window.location.href=\'' . $uriBuilder->buildUriFromRoute(
				'record_edit'
			) . $params . '&returnUrl=' . rawurlencode(GeneralUtility::getIndpEnv('REQUEST_URI')) . '\'';
		return $onclickScript;

	}
}
