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

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException;
use TYPO3\CMS\Core\Routing\InvalidRouteArgumentsException;
use TYPO3\CMS\Core\Routing\PageRouter;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\TimeTracker\NullTimeTracker;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Frontend\Page\PageGenerator;

/**
 * Class SGalinski\SgCookieOptin\Service\TemplateService
 */
class StaticFileGenerationService implements SingletonInterface {
	const TABLE_NAME = 'tx_sgcookieoptin_domain_model_optin';

	const FOLDER_SITEROOT = 'siteroot-#PID#/';

	const TEMPLATE_JAVA_SCRIPT_PATH = 'typo3conf/ext/sg_cookie_optin/Resources/Public/JavaScript/';
	const TEMPLATE_JAVA_SCRIPT_PATH_EXT = 'EXT:sg_cookie_optin/Resources/Public/JavaScript/';
	const TEMPLATE_JAVA_SCRIPT_NAME = 'cookieOptin.js';

	const TEMPLATE_JSON_NAME = 'cookieOptinData--#LANG#.json';

	const TEMPLATE_STYLE_SHEET_PATH = 'typo3conf/ext/sg_cookie_optin/Resources/Public/StyleSheets/';
	const TEMPLATE_STYLE_SHEET_PATH_EXT = 'EXT:sg_cookie_optin/Resources/Public/StyleSheets/';
	const TEMPLATE_STYLE_SHEET_NAME = 'cookieOptin.css';

	/** @var int */
	protected $siteRoot;

	/**
	 * The google consent mode settings based on the service ID
	 *
	 * @var string[]
	 * @see https://docs.usercentrics.com/#/consent-mode
	 */
	protected $googleNamesByService = [
		1 => 'analytics_storage',
		2 => 'ad_storage, ad_personalization, ad_user_data',
		3 => 'analytics_storage, ad_storage, ad_personalization, ad_user_data'
	];

	/**
	 * Generates the JavaScript, JSON and CSS files for this site root
	 *
	 * @param int $siteRootId
	 * @param array|null $originalRecord
	 * @throws \TYPO3\CMS\Core\Error\Http\PageNotFoundException
	 * @throws \TYPO3\CMS\Core\Error\Http\ServiceUnavailableException
	 * @throws \TYPO3\CMS\Core\Exception\SiteNotFoundException
	 * @throws \TYPO3\CMS\Core\Http\ImmediateResponseException
	 */
	public function generateFiles(int $siteRootId, $originalRecord) {
		if (!LicenceCheckService::isInDevelopmentContext()
			&& !LicenceCheckService::isInDemoMode()
			&& !LicenceCheckService::hasValidLicense()
		) {
			return;
		}

		$this->siteRoot = $siteRootId;
		if ($this->siteRoot <= 0) {
			return;
		}

		$folder = ExtensionSettingsService::getSetting(ExtensionSettingsService::SETTING_FOLDER);
		if (!$folder) {
			return;
		}

		$folderName = str_replace('#PID#', $this->siteRoot, $folder . self::FOLDER_SITEROOT);
		$sitePath = defined('PATH_site') ? PATH_site : Environment::getPublicPath() . '/';
		// First remove the folder with all files and then create it again. So no data artifacts are kept.
		GeneralUtility::rmdir($sitePath . $folderName, TRUE);
		GeneralUtility::mkdir_deep($sitePath . $folderName);
		GeneralUtility::fixPermissions($sitePath . $folder, TRUE);
		$currentVersion = VersionNumberUtility::convertVersionNumberToInteger(
			VersionNumberUtility::getCurrentTypo3Version()
		);

		$fullData = $this->getFullData($originalRecord, self::TABLE_NAME);
		$minifyFiles = (bool) $fullData['minify_generated_data'];
		$cssData = [
			'color_box' => $fullData['color_box'],
			'color_headline' => $fullData['color_headline'],
			'color_text' => $fullData['color_text'],
			'color_confirmation_background' => $fullData['color_confirmation_background'],
			'color_confirmation_text' => $fullData['color_confirmation_text'],
			'color_checkbox' => $fullData['color_checkbox'],
			'color_checkbox_required' => $fullData['color_checkbox_required'],
			'color_button_all' => $fullData['color_button_all'],
			'color_button_all_hover' => $fullData['color_button_all_hover'],
			'color_button_all_text' => $fullData['color_button_all_text'],
			'color_button_specific' => $fullData['color_button_specific'],
			'color_button_specific_hover' => $fullData['color_button_specific_hover'],
			'color_button_specific_text' => $fullData['color_button_specific_text'],
			'color_button_essential' => $fullData['color_button_essential'],
			'color_button_essential_hover' => $fullData['color_button_essential_hover'],
			'color_button_essential_text' => $fullData['color_button_essential_text'],
			'color_button_close' => $fullData['color_button_close'],
			'color_button_close_hover' => $fullData['color_button_close_hover'],
			'color_button_close_text' => $fullData['color_button_close_text'],
			'color_list' => $fullData['color_list'],
			'color_list_text' => $fullData['color_list_text'],
			'color_table' => $fullData['color_table'],
			'color_Table_data_text' => $fullData['color_Table_data_text'],
			'color_table_header' => $fullData['color_table_header'],
			'color_table_header_text' => $fullData['color_table_header_text'],
			'color_full_box' => $fullData['color_full_box'],
			'color_full_headline' => $fullData['color_full_headline'],
			'color_full_text' => $fullData['color_full_text'],
			'color_full_button_close' => $fullData['color_full_button_close'],
			'color_full_button_close_hover' => $fullData['color_full_button_close_hover'],
			'color_full_button_close_text' => $fullData['color_full_button_close_text'],
			'iframe_color_consent_box_background' => $fullData['iframe_color_consent_box_background'],
			'iframe_color_button_load_one' => $fullData['iframe_color_button_load_one'],
			'iframe_color_button_load_one_hover' => $fullData['iframe_color_button_load_one_hover'],
			'iframe_color_button_load_one_text' => $fullData['iframe_color_button_load_one_text'],
			'iframe_color_open_settings' => $fullData['iframe_color_open_settings'],
			'banner_color_box' => $fullData['banner_color_box'],
			'banner_color_text' => $fullData['banner_color_text'],
			'banner_color_link_text' => $fullData['banner_color_link_text'],
			'banner_color_button_settings' => $fullData['banner_color_button_settings'],
			'banner_color_button_settings_hover' => $fullData['banner_color_button_settings_hover'],
			'banner_color_button_settings_text' => $fullData['banner_color_button_settings_text'],
			'banner_color_button_accept_essential' => $fullData['banner_color_button_accept_essential'],
			'banner_color_button_accept_essential_hover' => $fullData['banner_color_button_accept_essential_hover'],
			'banner_color_button_accept_essential_text' => $fullData['banner_color_button_accept_essential_text'],
			'banner_color_button_accept' => $fullData['banner_color_button_accept'],
			'banner_color_button_accept_hover' => $fullData['banner_color_button_accept_hover'],
			'banner_color_button_accept_text' => $fullData['banner_color_button_accept_text'],
			'color_fingerprint_image' => $fullData['color_fingerprint_image'],
			'color_fingerprint_background' => $fullData['color_fingerprint_background'],
		];
		$this->createCSSFile($fullData, $folderName, $cssData, $minifyFiles);

		$languages = LanguageService::getLanguages($this->siteRoot);
		foreach ($languages as $language) {
			$languageUid = (int) $language['uid'];
			if ($languageUid < 0) {
				continue;
			}

			$locale = isset($language['locale']) ? $language['locale'] : '';

			$translatedRecord = $originalRecord;
			if ($languageUid > 0) {
				if ($currentVersion >= 11000000) {
					$pageRepository = GeneralUtility::makeInstance(PageRepository::class);
				} else {
					$pageRepository = GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\Page\PageRepository::class);
				}
				$translatedRecord = $pageRepository->getRecordOverlay(
					self::TABLE_NAME, $originalRecord, $languageUid, '1'
				);
			}

			$translatedFullData = $this->getFullData($translatedRecord, self::TABLE_NAME, $languageUid);
			if (count($translatedFullData) <= 0) {
				continue;
			}

			$this->createJavaScriptFile($folderName, $minifyFiles);
			$this->createJsonFile(
				$folderName,
				$fullData,
				$translatedFullData,
				$cssData,
				$minifyFiles,
				$languageUid,
				$locale
			);
		}

		GeneralUtility::fixPermissions($sitePath . $folder, TRUE);
	}

	/**
	 * Checks if we edited/deleted something and saves data in the session for the controller to make a flash message
	 *
	 * @param DataHandler $dataHandler
	 */
	protected function handleFlashMessage(DataHandler $dataHandler) {
		if (isset($dataHandler->cmdmap[self::TABLE_NAME]) || isset($dataHandler->datamap[self::TABLE_NAME])) {
			session_start([
				'cookie_secure' => TRUE,
				'cookie_httponly' => TRUE,
				'cookie_samesite' => 'Strict'
			]);
			$_SESSION['tx_sgcookieoptin']['configurationChanged'] = TRUE;
		}
	}

	/**
	 * Returns the full data for the given data array.
	 *
	 * @param array $data
	 * @param string $table
	 * @param int $language
	 *
	 * @return array
	 */
	protected function getFullData(array $data, $table, $language = 0) {
		$fullData = [];
		$parentUid = (!empty($data['l10n_parent']) ? (int) $data['l10n_parent'] : (int) $data['uid']);

		foreach ($data as $fieldName => $value) {
			$tcaConfig = $this->getTCAConfigForInlineField($table, $fieldName);
			if (count($tcaConfig) <= 0) {
				$fullData[$fieldName] = $value;
				continue;
			}

			$tcaConfig = $this->getTCAConfigForInlineField($table, $fieldName);
			$foreignTable = $tcaConfig['foreign_table'];
			$foreignField = $tcaConfig['foreign_field'];
			if (empty($foreignTable) || empty($foreignField)) {
				$fullData[$fieldName] = [];
				continue;
			}

			$fullData[$fieldName] = [];
			$inlineData = $this->getDataForInlineField($foreignTable, $foreignField, $parentUid, $language);
			if (\count($inlineData) > 0) {
				foreach ($inlineData as $index => $inlineDataEntry) {
					if (!isset($inlineDataEntry['uid'])) {
						continue;
					}

					$fullData[$fieldName][$index] = $this->getFullData($inlineDataEntry, $foreignTable, $language);
				}
			}
		}

		return $fullData;
	}

	/**
	 * Returns the table of the given inline field from the given table.
	 *
	 * @param string $table
	 * @param string $field
	 *
	 * @return array
	 */
	protected function getTCAConfigForInlineField($table, $field) {
		$tableData = $GLOBALS['TCA'][$table];
		if (!is_array($tableData)) {
			return [];
		}

		$tableColumn = NULL;
		if (isset($tableData['columns'][$field])) {
			$tableColumn = $tableData['columns'][$field];
		}
		if (!is_array($tableColumn)) {
			return [];
		}

		if (!isset($tableColumn['config'])) {
			return [];
		}

		if ($tableColumn['config']['type'] !== 'inline') {
			return [];
		}

		return $tableColumn['config'];
	}

	/**
	 * Returns the data for the given field, table configuration.
	 *
	 * @param string $table
	 * @param string $field
	 * @param int $parentUid
	 * @param int $language
	 *
	 * @return array
	 */
	protected function getDataForInlineField($table, $field, $parentUid, $language = 0) {
		$languageField = $this->getTCALanguageField($table);
		$connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
		$queryBuilder = $connectionPool->getQueryBuilderForTable($table);
		$queryBuilder->getRestrictions()
			->removeAll()
			->add(GeneralUtility::makeInstance(HiddenRestriction::class))
			->add(GeneralUtility::makeInstance(DeletedRestriction::class));
		$queryBuilder->select('*')
			->from($table)
			->orderBy('sorting', 'ASC')
			->where(
				$queryBuilder->expr()->eq(
					$field,
					$parentUid
				)
			);

		if ($languageField) {
			$queryBuilder->andWhere(
				$queryBuilder->expr()->eq(
					$languageField,
					0
				)
			);
		}

		$rows = $queryBuilder->execute()->fetchAll();
		if (!is_array($rows)) {
			return [];
		}

		$translatedRows = [];
		$currentVersion = VersionNumberUtility::convertVersionNumberToInteger(
			VersionNumberUtility::getCurrentTypo3Version()
		);
		if ($currentVersion >= 11000000) {
			$pageRepository = GeneralUtility::makeInstance(PageRepository::class);
		} else {
			$pageRepository = GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\Page\PageRepository::class);
		}

		foreach ($rows as $row) {
			$translatedRows[] = $pageRepository->getRecordOverlay($table, $row, $language, '1');
		}

		return $translatedRows;
	}

	/**
	 * Returns the language field of the given table.
	 *
	 * @param string $table
	 * @return string
	 */
	protected function getTCALanguageField($table) {
		$tableData = $GLOBALS['TCA'][$table];
		if (!is_array($tableData)) {
			return '';
		}

		if (!isset($tableData['ctrl'])) {
			return '';
		}

		return (isset($tableData['ctrl']['languageField']) ? $tableData['ctrl']['languageField'] : '');
	}

	/**
	 * Creates a CSS file out of the given data array.
	 *
	 * @param array $data
	 * @param string $folder
	 * @param array $cssData
	 * @param boolean $minifyFile
	 *
	 * @return void
	 * @throws ResourceDoesNotExistException
	 */
	protected function createCSSFile(array $data, $folder, array $cssData, $minifyFile = TRUE) {
		$sitePath = defined('PATH_site') ? PATH_site : Environment::getPublicPath() . '/';
		$content = '';
		if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '11.0.0', '>')) {
			$resourceFactory = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Resource\ResourceFactory::class);
			$file = $resourceFactory->retrieveFileOrFolderObject(
				self::TEMPLATE_STYLE_SHEET_PATH_EXT . self::TEMPLATE_STYLE_SHEET_NAME
			);
			if ($file) {
				$content = '/* Base styles: ' . self::TEMPLATE_STYLE_SHEET_NAME . " */\n\n" .
					file_get_contents($sitePath . $file->getPublicUrl());
			}
		} else {
			$content = '/* Base styles: ' . self::TEMPLATE_STYLE_SHEET_NAME . " */\n\n" .
				file_get_contents($sitePath . self::TEMPLATE_STYLE_SHEET_PATH . self::TEMPLATE_STYLE_SHEET_NAME);
		}

		$templateService = GeneralUtility::makeInstance(TemplateService::class);
		$content .= " \n\n" . $templateService->getCSSContent(
				TemplateService::TYPE_TEMPLATE,
				$data['template_selection']
			);
		if ((bool) $data['banner_enable'] || (int) $data['banner_force_min_width'] > 0) {
			$content .= " \n\n" . $templateService->getCSSContent(TemplateService::TYPE_BANNER, 0);
		}

		if ((bool) $data['iframe_enabled']) {
			$content .= " \n\n" . $templateService->getCSSContent(TemplateService::TYPE_IFRAME, 0);
			$content .= " \n\n" . $templateService->getCSSContent(TemplateService::TYPE_IFRAME_REPLACEMENT, 0);
		}

		if ($data['fingerprint_position'] > 0) {
			$content .= " \n\n" .
				$templateService->getCSSContent(
					TemplateService::TYPE_FINGERPRINT,
					TemplateService::IFRAME_FINGERPRINT_TEMPLATE_ID_DEFAULT
				);
		}

		if ((bool) $data['monochrome_enabled']) {
			$content .= " \n\n" .
				$templateService->getCSSContent(
					TemplateService::TYPE_MONOCHROME,
					TemplateService::IFRAME_MONOCHROME_TEMPLATE_ID_DEFAULT
				);
		}

		$keys = $data = [];
		foreach ($cssData as $key => $value) {
			$keys[] = '%23###' . $key . '###';
			$data[] = '%23' . ltrim($value, '#');

			$keys[] = '###' . $key . '###';
			$data[] = $value;
		}

		$file = $sitePath . $folder . self::TEMPLATE_STYLE_SHEET_NAME;
		file_put_contents($file, str_replace($keys, $data, $content));

		if ($minifyFile) {
			$minificationService = GeneralUtility::makeInstance(MinificationService::class);
			$minificationService->minifyCSSFile($file);
		}

		GeneralUtility::fixPermissions($file);
	}

	/**
	 * Creates a html string out of the given scripts.
	 *
	 * @param array $scripts
	 * @return string
	 */
	protected function getActivationHTML(array $scripts) {
		$content = '';
		foreach ($scripts as $script) {
			$htmlContent = trim($script['html']);
			if (!$htmlContent) {
				continue;
			}

			$content .= $htmlContent . "\n\n";
		}

		return $content;
	}

	/**
	 * Creates a javascript file out of the given scripts array.
	 *
	 * @param string $folder
	 * @param string $groupName
	 * @param array $scripts
	 * @param int $languageUid
	 * @param bool $minifyFile
	 * @param string $overwrittenBaseUrl
	 * @return string
	 */
	protected function createActivationScriptFile(
		$folder,
		$groupName,
		array $scripts,
		$languageUid = 0,
		$minifyFile = TRUE,
		$overwrittenBaseUrl = ''
	) {
		$content = '';
		foreach ($scripts as $script) {
			$scriptContent = trim($script['script']);
			if (!$scriptContent) {
				continue;
			}

			$content .= '// Script: ' . $script['title'] . "\n\n" . $scriptContent . "\n\n";
		}

		if ($content === '') {
			return '';
		}

		$file = $folder . $groupName . '-' . $languageUid . '.js';
		$sitePath = defined('PATH_site') ? PATH_site : Environment::getPublicPath() . '/';
		$groupFile = $sitePath . $file;
		file_put_contents($groupFile, $content);

		if ($minifyFile) {
			$minificationService = GeneralUtility::makeInstance(MinificationService::class);
			$minificationService->minifyJavaScriptFile($groupFile);
		}

		GeneralUtility::fixPermissions($groupFile);
		// $this->siteRoot cannot be null here, because this always gets called after the DataHandler logic where
		// it is being set
		return ($overwrittenBaseUrl ?: BaseUrlService::getSiteBaseUrl($this->siteRoot, $languageUid)) . $file;
	}

	/**
	 * Creates a JS file out of the given data array.
	 *
	 * @param string $folder
	 * @param bool $minifyFile
	 * @return void
	 * @throws ResourceDoesNotExistException
	 */
	protected function createJavaScriptFile($folder, $minifyFile = TRUE) {
		$sitePath = defined('PATH_site') ? PATH_site : Environment::getPublicPath() . '/';
		$file = $sitePath . $folder . self::TEMPLATE_JAVA_SCRIPT_NAME;

		if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '11.0.0', '>')) {
			$resourceFactory = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Resource\ResourceFactory::class);
			$fileExt = $resourceFactory->retrieveFileOrFolderObject(
				self::TEMPLATE_JAVA_SCRIPT_PATH_EXT . self::TEMPLATE_JAVA_SCRIPT_NAME
			);
			if ($fileExt) {
				copy($sitePath . $fileExt->getPublicUrl(), $file);
			}
		} else {
			copy($sitePath . self::TEMPLATE_JAVA_SCRIPT_PATH . self::TEMPLATE_JAVA_SCRIPT_NAME, $file);
		}

		if ($minifyFile) {
			$minificationService = GeneralUtility::makeInstance(MinificationService::class);
			$minificationService->minifyJavaScriptFile($file);
		}

		GeneralUtility::fixPermissions($file);
	}

	/**
	 * Creates a JSON file out of the given data array.
	 *
	 * @param string $folder
	 * @param array $data
	 * @param array $translatedData
	 * @param array $cssData
	 * @param bool $minifyFiles
	 * @param int $languageUid
	 * @param string $locale
	 * @return void
	 * @throws \TYPO3\CMS\Core\Exception\SiteNotFoundException
	 * @throws \JsonException
	 * @throws InvalidRouteArgumentsException
	 */
	protected function createJsonFile(
		$folder,
		array $data,
		array $translatedData,
		array $cssData,
		$minifyFiles,
		$languageUid = 0,
		$locale = ''
	) {
		$essentialCookieData = [];
		$iframeCookieData = [];
		$pseudoElements = 0;
		$groupIndex = 0;
		foreach ($translatedData['essential_cookies'] as $index => $cookieData) {
			$essentialCookieData[] = [
				'Name' => $cookieData['name'],
				'Provider' => $cookieData['provider'],
				'Purpose' => $cookieData['purpose'],
				'Lifetime' => $cookieData['lifetime'],
				'index' => $groupIndex,
				'crdate' => $cookieData['crdate'],
				'tstamp' => $cookieData['tstamp'],
				'pseudo' => FALSE,
			];
			++$groupIndex;
			$pseudoElements = $groupIndex % 3;
		}

		for ($index = 1; $index < $pseudoElements; ++$index) {
			$essentialCookieData[] = [
				'Name' => '',
				'Provider' => '',
				'Purpose' => '',
				'Lifetime' => '',
				'index' => $groupIndex,
				'crdate' => '',
				'tstamp' => '',
				'pseudo' => TRUE,
			];
			++$groupIndex;
		}

		$essentialScriptData = [];
		foreach ($translatedData['essential_scripts'] as $index => $scriptData) {
			$essentialScriptData[] = [
				'title' => $scriptData['title'],
				'script' => $scriptData['script'],
				'html' => $scriptData['html'],
				'index' => $index,
			];
		}

		$cookieGroups = [
			[
				'groupName' => 'essential',
				'label' => $translatedData['essential_title'],
				'description' => $translatedData['essential_description'],
				'required' => TRUE,
				'cookieData' => $essentialCookieData,
				'scriptData' => $essentialScriptData,
				'loadingHTML' => $this->getActivationHTML($translatedData['essential_scripts']),
				'loadingJavaScript' => $this->createActivationScriptFile(
					$folder,
					'essential',
					$translatedData['essential_scripts'],
					$languageUid,
					$minifyFiles,
					$translatedData['overwrite_baseurl']
				),
			],
		];

		foreach ($translatedData['groups'] as $group) {
			$groupCookieData = [];
			$pseudoElements = 0;
			$groupIndex = 0;
			foreach ($group['cookies'] as $index => $cookieData) {
				$groupCookieData[] = [
					'Name' => $cookieData['name'],
					'Provider' => $cookieData['provider'],
					'Purpose' => $cookieData['purpose'],
					'Lifetime' => $cookieData['lifetime'],
					'index' => $groupIndex,
					'crdate' => $cookieData['crdate'],
					'tstamp' => $cookieData['tstamp'],
					'pseudo' => FALSE,
				];
				++$groupIndex;
				$pseudoElements = $groupIndex % 3;
			}

			for ($index = 1; $index < $pseudoElements; ++$index) {
				$groupCookieData[] = [
					'Name' => '',
					'Provider' => '',
					'Purpose' => '',
					'Lifetime' => '',
					'index' => $groupIndex,
					'crdate' => '',
					'tstamp' => '',
					'pseudo' => TRUE,
				];
				++$groupIndex;
			}

			$groupScriptData = [];
			foreach ($group['scripts'] as $index => $scriptData) {
				$groupScriptData[] = [
					'title' => $scriptData['title'],
					'script' => $scriptData['script'],
					'html' => $scriptData['html'],
					'index' => $index,
				];
			}

			$groupName = $group['group_name'];
			$dependentGroupTitles = [];
			$dependentGroupsArray = GeneralUtility::trimExplode(',', $group['dependent_groups']);
			foreach ($dependentGroupsArray as $dependentGroupName) {
				foreach ($translatedData['groups'] as $groupToLookup) {
					if ($groupToLookup['group_name'] === $dependentGroupName) {
						$dependentGroupTitles[] = $groupToLookup['title'];
					}
				}
			}
			$dependentGroupTitles = implode(', ', $dependentGroupTitles);

			$cookieGroups[] = [
				'groupName' => $groupName,
				'label' => $group['title'],
				'description' => $group['description'],
				'googleService' => $group['google_service'],
				'googleName' => (($group['google_service'] < 1) ? $group['google_name'] : $this->googleNamesByService[$group['google_service']]),
				'dependentGroups' => $group['dependent_groups'],
				'dependentGroupTitles' => $dependentGroupTitles,
				'required' => FALSE,
				'cookieData' => $groupCookieData,
				'scriptData' => $groupScriptData,
				'loadingHTML' => $this->getActivationHTML($group['scripts']),
				'loadingJavaScript' => $this->createActivationScriptFile(
					$folder,
					$group['group_name'],
					$group['scripts'],
					$languageUid,
					$minifyFiles,
					$translatedData['overwrite_baseurl']
				),
				'crdate' => $group['crdate'],
				'tstamp' => $group['tstamp'],
			];
		}

		$pseudoElements = 0;
		$groupIndex = 0;
		foreach ($translatedData['iframe_cookies'] as $index => $cookieData) {
			$iframeCookieData[] = [
				'Name' => $cookieData['name'],
				'Provider' => $cookieData['provider'],
				'Purpose' => $cookieData['purpose'],
				'Lifetime' => $cookieData['lifetime'],
				'index' => $groupIndex,
				'crdate' => $cookieData['crdate'],
				'tstamp' => $cookieData['tstamp'],
				'pseudo' => FALSE,
			];
			++$groupIndex;
			$pseudoElements = $groupIndex % 3;
		}

		for ($index = 1; $index < $pseudoElements; ++$index) {
			$iframeCookieData[] = [
				'Name' => '',
				'Provider' => '',
				'Purpose' => '',
				'Lifetime' => '',
				'index' => $groupIndex,
				'crdate' => '',
				'tstamp' => '',
				'pseudo' => TRUE,
			];
			++$groupIndex;
		}

		$iFrameGroup = [
			'groupName' => 'iframes',
			'label' => $translatedData['iframe_title'],
			'description' => $translatedData['iframe_description'],
			'required' => FALSE,
			'cookieData' => $iframeCookieData,
		];

		if ((bool) $translatedData['iframe_enabled']) {
			$cookieGroups[] = $iFrameGroup;
		}

		$navigationEntries = $this->getPagesFromNavigation($translatedData['navigation'], $languageUid);
		if (count($navigationEntries) <= 0) {
			$navigationEntries = $this->getPagesFromNavigation($data['navigation'], $languageUid);
		}

		$footerLinks = [];
		$index = 0;
		$siteBaseUrl = BaseUrlService::getSiteBaseUrl($this->siteRoot, $languageUid, FALSE);
		$parsedSiteBaseUrl = parse_url($siteBaseUrl);
		foreach ($navigationEntries as $pageData) {
			$uid = (int) $pageData['uid'];
			if ($uid <= 0) {
				// can be happened if the page is not accessible
				continue;
			}

			$name = $pageData['title'];
			$site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($uid);
			try {
				$url = (string) $site->getRouter()->generateUri(
					$uid, ['disableOptIn' => 1, '_language' => $languageUid]
				);
			} catch (\Exception $exception) {
				continue;
			}

			if (strpos($url, '?') === 0) {
				$url = '/' . $url;
			}

			if (strpos($url, '//') === 0) {
				$url = substr($url, 1);
			}

			if ($siteBaseUrl !== '/') {
				if (!$parsedSiteBaseUrl['path'] || $parsedSiteBaseUrl['path'] === '/') {
					$url = '/' . str_replace($siteBaseUrl, '', $url);
				}
			}

			$footerLinks[$index] = [
				'url' => $url,
				'name' => $name,
				'uid' => $uid,
				'index' => $index,
			];

			++$index;
		}

		if ($translatedData['overwrite_baseurl']) {
			$baseUri = $translatedData['overwrite_baseurl'];
		} else {
			$siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
			$site = $siteFinder->getSiteByPageId($this->siteRoot);
			$pageRouter = GeneralUtility::makeInstance(PageRouter::class, $site);
			$baseUri = $pageRouter->generateUri($this->siteRoot, ['_language' => $languageUid]);
		}

		$settings = [
			'banner_enable' => (bool) $translatedData['banner_enable'],
			'banner_force_min_width' => (int) $translatedData['banner_force_min_width'],
			'version' => (int) $translatedData['version'],
			'banner_position' => (int) $translatedData['banner_position'],
			'banner_show_settings_button' => (bool) $translatedData['banner_show_settings_button'],
			'cookie_lifetime' => (int) $translatedData['cookie_lifetime'],
			'session_only_essential_cookies' => (bool) $translatedData['session_only_essential_cookies'],
			'iframe_enabled' => (bool) $translatedData['iframe_enabled'],
			'minify_generated_data' => (bool) $translatedData['minify_generated_data'],
			'show_button_close' => (bool) $translatedData['show_button_close'],
			'activate_testing_mode' => (bool) $translatedData['activate_testing_mode'],
			'disable_powered_by' => (bool) $translatedData['disable_powered_by'],
			'disable_for_this_language' => (bool) $translatedData['disable_for_this_language'],
			'set_cookie_for_domain' => (string) $translatedData['set_cookie_for_domain'],
			'save_history_webhook' => $baseUri . '?saveOptinHistory',
			'cookiebanner_whitelist_regex' => (string) $translatedData['cookiebanner_whitelist_regex'],
			'banner_show_again_interval' => (int) $translatedData['banner_show_again_interval'],
			'identifier' => $this->siteRoot,
			'language' => $languageUid,
			'render_assets_inline' => (bool) $translatedData['render_assets_inline'],
			'consider_do_not_track' => (bool) $translatedData['consider_do_not_track'],
			'domains_to_delete_cookies_for' => (string) $translatedData['domains_to_delete_cookies_for'],
			'subdomain_support' => (bool) $translatedData['subdomain_support'],
			'overwrite_baseurl' => (string) $translatedData['overwrite_baseurl'],
			'unified_cookie_name' => (bool) $translatedData['unified_cookie_name'],
			'disable_usage_statistics' => (bool) $translatedData['disable_usage_statistics'],
			'fingerprint_position' => (int) $translatedData['fingerprint_position'],
			'iframe_replacement_background_image' => (string) $translatedData['iframe_replacement_background_image'],
			'monochrome_enabled' => (bool) $translatedData['monochrome_enabled'],
			'show_fingerprint' => (bool) $translatedData['show_fingerprint'],
			'disable_automatic_loading' => (bool) $translatedData['disable_automatic_loading'],
			'auto_action_for_bots' => (int) $translatedData['auto_action_for_bots'],
		];

		$textEntries = [
			'header' => $translatedData['header'],
			'description' => $translatedData['description'],
			'accept_all_text' => $translatedData['accept_all_text'],
			'accept_specific_text' => $translatedData['accept_specific_text'],
			'accept_essential_text' => $translatedData['accept_essential_text'],
			'extend_box_link_text' => $translatedData['extend_box_link_text'],
			'extend_box_link_text_close' => $translatedData['extend_box_link_text_close'],
			'extend_table_link_text' => $translatedData['extend_table_link_text'],
			'extend_table_link_text_close' => $translatedData['extend_table_link_text_close'],
			'cookie_name_text' => $translatedData['cookie_name_text'],
			'cookie_provider_text' => $translatedData['cookie_provider_text'],
			'cookie_purpose_text' => $translatedData['cookie_purpose_text'],
			'cookie_lifetime_text' => $translatedData['cookie_lifetime_text'],
			'iframe_button_allow_all_text' => $translatedData['iframe_button_allow_all_text'],
			'iframe_button_allow_one_text' => $translatedData['iframe_button_allow_one_text'],
			'iframe_button_reject_text' => $translatedData['iframe_button_reject_text'],
			'iframe_button_load_one_text' => $translatedData['iframe_button_load_one_text'],
			'iframe_open_settings_text' => $translatedData['iframe_open_settings_text'],
			'iframe_button_load_one_description' => $translatedData['iframe_button_load_one_description'],
			'banner_button_accept_text' => $translatedData['banner_button_accept_text'],
			'banner_button_accept_essential_text' => $translatedData['banner_button_accept_essential_text'],
			'banner_button_settings_text' => $translatedData['banner_button_settings_text'],
			'banner_description' => $translatedData['banner_description'],
			'save_confirmation_text' => $translatedData['save_confirmation_text'],
			'user_hash_text' => $translatedData['user_hash_text'],
			'dependent_groups_text' => $translatedData['dependent_groups_text'],
		];

		$placeholders = [
			'iframe_consent_description' => '<p class="sg-cookie-optin-box-flash-message"></p>'
		];

		$jsonDataArray = [
			'cookieGroups' => $cookieGroups,
			'cssData' => $cssData,
			'footerLinks' => $footerLinks,
			'iFrameGroup' => $iFrameGroup,
			'settings' => $settings,
			'textEntries' => $textEntries,
			'placeholders' => $placeholders
		];

		$jsonDataArray['mustacheData'] = [
			'template' => [
				'template_html' => $translatedData['template_html'],
				'template_overwritten' => $translatedData['template_overwritten'],
				'template_selection' => $translatedData['template_selection'],
				'markup' => $this->getRenderedMustacheTemplate(
					$translatedData['template_overwritten'],
					$translatedData['template_html'],
					$translatedData['template_selection'],
					TemplateService::TYPE_TEMPLATE,
					$jsonDataArray
				),
			],
			'banner' => [
				'banner_html' => $translatedData['banner_html'],
				'banner_overwritten' => $translatedData['banner_overwritten'],
				'markup' => $this->getRenderedMustacheTemplate(
					$translatedData['banner_overwritten'],
					$translatedData['banner_html'],
					0,
					TemplateService::TYPE_BANNER,
					$jsonDataArray
				),
			],
			'iframe' => [
				'iframe_html' => $translatedData['iframe_html'],
				'iframe_overwritten' => $translatedData['iframe_overwritten'],
				'markup' => $this->getRenderedMustacheTemplate(
					$translatedData['iframe_overwritten'],
					$translatedData['iframe_html'],
					0,
					TemplateService::TYPE_IFRAME,
					$jsonDataArray
				),
			],
			'iframeReplacement' => [
				'iframe_replacement_html' => $translatedData['iframe_replacement_html'],
				'iframe_replacement_overwritten' => $translatedData['iframe_replacement_overwritten'],
				'markup' => $this->getRenderedMustacheTemplate(
					$translatedData['iframe_replacement_overwritten'],
					$translatedData['iframe_replacement_html'],
					0,
					TemplateService::TYPE_IFRAME_REPLACEMENT,
					$jsonDataArray
				),
			],
			'iframeWhitelist' => [
				'iframe_whitelist_regex' => $translatedData['iframe_whitelist_regex'],
				'markup' => $translatedData['iframe_whitelist_regex']
			],
		];

		$jsonDataArray['mustacheData']['services'] = [];
		if ((int) $translatedData['services'] > 0 ||
			(is_countable($translatedData['services']) && (count($translatedData['services']) > 0))
		) {
			$templateService = GeneralUtility::makeInstance(TemplateService::class);
			foreach ($translatedData['services'] as $service) {

				if ($service['replacement_html_overwritten']) {
					$rendered = $templateService->renderTemplate($service['replacement_html'], $jsonDataArray);
				} else {
					$rendered = $jsonDataArray['mustacheData']['iframeReplacement']['markup'];
				}

				$jsonDataArray['mustacheData']['services'][$service['identifier']] = [
					'rendered' => $rendered,
					'mustache' => $service['replacement_html'],
					'replacement_html_overwritten' => $service['replacement_html_overwritten'],
					'replacement_background_image' => $service['replacement_background_image'],
					'regex' => $service['source_regex'],
				];
			}
		}

		$sitePath = defined('PATH_site') ? PATH_site : Environment::getPublicPath() . '/';
		$file = $sitePath . $folder . str_replace(
				'#LANG#',
				$locale . JsonImportService::LOCALE_SEPARATOR . $languageUid,
				self::TEMPLATE_JSON_NAME
			);

		$mask = JSON_PRETTY_PRINT;
		if (defined('JSON_THROW_ON_ERROR')) {
			$mask = constant('JSON_THROW_ON_ERROR') | JSON_PRETTY_PRINT | constant('JSON_INVALID_UTF8_SUBSTITUTE');
		}

		// Call pre-processing function for constructor:
		if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['sg_cookie_optin']['GenerateFilesAfterTcaSave']['preSaveJsonProc']) &&
			is_array(
				$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['sg_cookie_optin']['GenerateFilesAfterTcaSave']['preSaveJsonProc']
			)
		) {
			$_params = [
				'pObj' => &$this,
				'data' => &$jsonDataArray,
				'languageUid' => $languageUid
			];
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['sg_cookie_optin']['GenerateFilesAfterTcaSave']['preSaveJsonProc'] as $_funcRef) {
				GeneralUtility::callUserFunction($_funcRef, $_params, $this);
			}
		}

		/** @noinspection JsonEncodingApiUsageInspection */
		file_put_contents($file, json_encode($jsonDataArray, $mask));
		GeneralUtility::fixPermissions($file);
	}

	/**
	 * Renders a mustache template by the given data and returns it.
	 *
	 * @param bool $overwritten
	 * @param string $overwrittenTemplate
	 * @param int $templateSelection
	 * @param int $type
	 * @param array $data
	 *
	 * @return string
	 */
	protected function getRenderedMustacheTemplate(
		$overwritten,
		$overwrittenTemplate,
		$templateSelection,
		$type,
		array $data
	) {
		$templateService = GeneralUtility::makeInstance(TemplateService::class);
		if ((bool) $overwritten && $overwrittenTemplate) {
			$template = $overwrittenTemplate;
		} else {
			$template = $templateService->getMustacheContent((int) $type, (int) $templateSelection);
		}

		$mustacheTemplate = '';
		if ($template) {
			$mustacheTemplate = $templateService->renderTemplate($template, $data);
		}

		return $mustacheTemplate;
	}

	/**
	 * Returns an array with page data out of the given data string.
	 *
	 * @param string $navigationData
	 * @param int $languageUid
	 *
	 * @return array
	 */
	protected function getPagesFromNavigation($navigationData, $languageUid = 0) {
		if (!$navigationData) {
			return [];
		}

		$records = [];
		$navigationEntries = GeneralUtility::trimExplode(',', $navigationData);
		$versionNumber = VersionNumberUtility::convertVersionNumberToInteger(
			VersionNumberUtility::getCurrentTypo3Version()
		);
		if ($versionNumber >= 11000000) {
			$pageRepository = GeneralUtility::makeInstance(PageRepository::class);
		} else {
			$pageRepository = GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\Page\PageRepository::class);
		}
		foreach ($navigationEntries as $navigationEntry) {
			if (!$navigationEntry) {
				continue;
			}

			$record = BackendUtility::getRecord('pages', $navigationEntry);
			if (!$record) {
				continue;
			}

			if ($languageUid > 0) {
				$record = $pageRepository->getRecordOverlay('pages', $record, $languageUid, '1');
			}

			$records[] = $record;
		}

		return $records;
	}
}
