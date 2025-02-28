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

namespace SGalinski\SgCookieOptin\Hook;

use Doctrine\DBAL\Exception;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Adds the Cookie Consent JavaScript if it's generated for the current page.
 */
class HandleVersionChange {
	/**
	 * Handles the version update
	 *
	 * @param array $fieldArray
	 * @param string $table
	 * @param int $id
	 * @param DataHandler $dataHandler
	 * @throws Exception
	 */
	public function processDatamap_preProcessFieldArray(
		array &$fieldArray,
		string $table,
		int $id,
		DataHandler $dataHandler
	): void {
		if (isset($fieldArray['update_version_checkbox']) && $fieldArray['update_version_checkbox']) {

			$currentVersionQuery = "SELECT max(IFNULL(version, 0)), pid FROM tx_sgcookieoptin_domain_model_optin
				WHERE deleted = 0 AND pid = (SELECT pid FROM tx_sgcookieoptin_domain_model_optin WHERE uid = ?)";
			$connection = GeneralUtility::makeInstance(ConnectionPool::class)
				?->getConnectionForTable($table);
			$resultObject = $connection->executeQuery($currentVersionQuery, [$id]);
			$result = $resultObject->fetchAssociative();
			[$currentVersion, $pid] = array_values($result);

			$sqlQuery = "UPDATE tx_sgcookieoptin_domain_model_optin SET version = $currentVersion + 1 WHERE pid = $pid AND deleted = 0";
			$connection->executeQuery($sqlQuery);

			$fieldArray['update_version_checkbox'] = 0;
		}
	}
}
