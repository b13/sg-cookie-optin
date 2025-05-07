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

use SGalinski\SgCookieOptin\Service\TemplateService;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Handles the template related changes in the TCA.
 */
class HandleTemplateAfterTcaSave {
	public const TABLE_NAME = 'tx_sgcookieoptin_domain_model_optin';

	/**
	 * Hook method for updating the template field in the optin TCA
	 *
	 * @param string $status
	 * @param string $table
	 * @param int $id
	 * @param array $fieldArray
	 * @param DataHandler $dataHandler
	 */
	public function processDatamap_afterDatabaseOperations(
		string $status,
		string $table,
		int $id,
		array $fieldArray,
		DataHandler $dataHandler
	): void {
		if (
			($status !== 'update' && $status !== 'new') || $table !== self::TABLE_NAME ||
			!isset($dataHandler->datamap[self::TABLE_NAME])
		) {
			return;
		}

		// If it's a new object - get it's real ID otherwise the update will not work anyway
		if (str_starts_with($id, 'NEW')) {
			if (!isset($dataHandler->substNEWwithIDs[$id])) {
				return;
			}

			$id = (int) $dataHandler->substNEWwithIDs[$id];
		}

		$templateService = GeneralUtility::makeInstance(TemplateService::class);
		foreach ($dataHandler->datamap[self::TABLE_NAME] as $data) {
			if (!isset($data['template_html'], $data['banner_html'], $data['iframe_html'])) {
				continue;
			}

			if (isset($data['template_overwritten']) && $data['template_overwritten']) {
				$template = $data['template_html'];
			} else {
				if (!isset($data['template_selection'])) {
					$data['template_selection'] = 0;
				}

				$template = $templateService->getMustacheContent(
					TemplateService::TYPE_TEMPLATE,
					(int) $data['template_selection']
				);
			}

			if (isset($data['banner_overwritten']) && $data['banner_overwritten']) {
				$bannerTemplate = $data['banner_html'];
			} else {
				$data['banner_selection'] = 0;

				$bannerTemplate = $templateService->getMustacheContent(
					TemplateService::TYPE_BANNER,
					$data['banner_selection']
				);
			}

			if (isset($data['iframe_overwritten']) && $data['iframe_overwritten']) {
				$iframeTemplate = $data['iframe_html'];
			} else {
				$data['iframe_selection'] = 0;

				$iframeTemplate = $templateService->getMustacheContent(
					TemplateService::TYPE_IFRAME,
					$data['iframe_selection']
				);
			}

			if (isset($data['iframe_replacement_overwritten']) && $data['iframe_replacement_overwritten']) {
				$iframeReplacementTemplate = $data['iframe_replacement_html'];
			} else {
				$data['iframe_replacement_selection'] = 0;

				$iframeReplacementTemplate = $templateService->getMustacheContent(
					TemplateService::TYPE_IFRAME_REPLACEMENT,
					$data['iframe_replacement_selection']
				);
			}

			$connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
			$queryBuilder = $connectionPool->getQueryBuilderForTable(self::TABLE_NAME);
			$queryBuilder
				->update(self::TABLE_NAME)
				->set('template_html', $template)
				->set('banner_html', $bannerTemplate)
				->set('iframe_html', $iframeTemplate)
				->set('iframe_replacement_html', $iframeReplacementTemplate)
				->where(
					$queryBuilder->expr()->eq(
						'uid',
						$queryBuilder->createNamedParameter($id)
					)
				);
			$queryBuilder->executeStatement();
		}
	}
}
