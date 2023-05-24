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
use SGalinski\SgCookieOptin\Traits\V9InitControllerComponents;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\Components\DocHeaderComponent;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Optin Controller
 */
class V9OptinController extends ActionController {
	use V9InitControllerComponents;

	/**
	 * DocHeaderComponent
	 *
	 * @var DocHeaderComponent
	 */
	protected $docHeaderComponent;

	/**
	 * Starts the module, even opens up a TCEForm, or shows where the domain root is.
	 *
	 */
	public function indexAction() {
		$this->initComponents();
		$this->checkLicenseStatus();

		session_start();
		if (isset($_SESSION['tx_sgcookieoptin']['configurationChanged'])) {
			unset($_SESSION['tx_sgcookieoptin']['configurationChanged']);
			$this->addFlashMessage(
				LocalizationUtility::translate('backend.hasChanges.message', 'sg_cookie_optin'),
				LocalizationUtility::translate('backend.hasChanges.title', 'sg_cookie_optin'),
				AbstractMessage::INFO
			);
		}

		$pageUid = (int) GeneralUtility::_GP('id');
		$pageInfo = BackendUtility::readPageAccess($pageUid, $GLOBALS['BE_USER']->getPagePermsClause(1));
		if ($pageInfo && isset($pageInfo['is_siteroot']) && (int) $pageInfo['is_siteroot'] === 1) {
			$optIns = BackendService::getOptins($pageUid);

			if (count($optIns) > 1) {
				$this->addFlashMessage(
					LocalizationUtility::translate('backend.tooManyRecorsException.description', 'sg_cookie_optin'),
					LocalizationUtility::translate('backend.tooManyRecorsException.header', 'sg_cookie_optin'),
					AbstractMessage::ERROR
				);
			}

			$this->view->assign('isSiteRoot', TRUE);
			$this->view->assign('optins', $optIns);
		}

		$this->view->assign('pages', BackendService::getPages());
	}

	/**
	 * Activates the demo mode for the given instance.
	 *
	 * @throws StopActionException
	 */
	public function activateDemoModeAction() {
		if (LicenceCheckService::isInDemoMode() || !LicenceCheckService::isDemoModeAcceptable()) {
			$this->redirect('index');
		}

		LicenceCheckService::activateDemoMode();
		$this->redirect('index');
	}

	/**
	 * Renders the cookie opt in.
	 *
	 * @return void
	 */
	public function showAction() {
	}

	/**
	 * Renders the cookie list.
	 *
	 * @return void
	 */
	public function cookieListAction() {
		$rootPageId = $GLOBALS['TSFE']->rootLine[0]['uid'] ?? 0;
		$languageUid = $GLOBALS['TSFE']->sys_language_uid;

		$versionNumber = VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version);
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

		return $view->render();
	}

	/**
	 * Imports JSON configuration
	 *
	 * @throws StopActionException
	 */
	public function importJsonAction() {
		session_start();
		$pid = (int) GeneralUtility::_GP('id');
		try {
			if (!isset($_SESSION['tx_sgcookieoptin']['importJsonData']['defaultLanguageId'])) {
				throw new JsonImportException(
					LocalizationUtility::translate(
						'jsonImport.error.theStoredImportedDataIsCorrupt',
						'sg_cookie_optin'
					),
					101
				);
			}

			$defaultLanguageId = $_SESSION['tx_sgcookieoptin']['importJsonData']['defaultLanguageId'];
			$objectManager = GeneralUtility::makeInstance(ObjectManager::class);
			$jsonImportService = $objectManager->get(JsonImportService::class);

			$defaultLanguageJsonData = $_SESSION['tx_sgcookieoptin']['importJsonData']['languageData'][$defaultLanguageId];
			$defaultLanguageOptinId = $jsonImportService->importJsonData($defaultLanguageJsonData, $pid);

			foreach ($_SESSION['tx_sgcookieoptin']['importJsonData']['languageData'] as $languageId => $jsonData) {
				if ($languageId !== $defaultLanguageId) {
					$jsonImportService->importJsonData(
						$jsonData,
						$pid,
						$languageId,
						$defaultLanguageOptinId
					);
				}
			}

			unset($_SESSION['tx_sgcookieoptin']['importJsonData']);
			$_SESSION['tx_sgcookieoptin']['configurationChanged'] = TRUE;
			$this->redirectToTCAEdit((int) $defaultLanguageOptinId);
		} catch (Exception $exception) {
			$this->addFlashMessage(
				$exception->getMessage(),
				'',
				AbstractMessage::ERROR
			);
			$this->redirect('previewImport', 'Optin', 'sg_cookie_optin');
		}
	}

	/**
	 * Redirects to the edit action
	 *
	 * @param int $optInId
	 * @throws RouteNotFoundException
	 */
	protected function redirectToTCAEdit(int $optInId) {
		$pid = (int) GeneralUtility::_GP('id');
		$uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
		$params = [
			'edit' => ['tx_sgcookieoptin_domain_model_optin' => [$optInId => 'edit']],
			'returnUrl' => (string) $uriBuilder->buildUriFromRoute('web_SgCookieOptinOptin', ['id' => $pid])
		];
		$uri = (string) $uriBuilder->buildUriFromRoute('record_edit', $params);
		header('Location: ' . $uri);
		exit;
	}

	/**
	 * Displays statistics about the imported data for a  preview
	 *
	 * @throws StopActionException
	 * @throws \TYPO3\CMS\Extbase\Object\Exception
	 */
	public function previewImportAction() {
		session_start();
		$this->initComponents();
		$pageUid = (int) GeneralUtility::_GP('id');
		$pageInfo = BackendUtility::readPageAccess($pageUid, $GLOBALS['BE_USER']->getPagePermsClause(1));
		if ($pageInfo && (int) $pageInfo['is_siteroot'] === 1) {
			$optIns = BackendService::getOptins($pageUid);

			if (count($optIns) > 0) {
				$this->addFlashMessage(
					LocalizationUtility::translate(
						'backend.jsonImport.tooManyRecorsException.description',
						'sg_cookie_optin'
					),
					LocalizationUtility::translate(
						'backend.jsonImport.tooManyRecorsException.header',
						'sg_cookie_optin'
					),
					AbstractMessage::INFO
				);
			}

			$this->view->assign('isSiteRoot', TRUE);
			$this->view->assign('optins', $optIns);
		}
		try {
			$languages = LanguageService::getLanguages($pageUid);
		} catch (SiteNotFoundException $e) {
			$languages = [];
		}
		$objectManager = GeneralUtility::makeInstance(ObjectManager::class);
		$jsonImportService = $objectManager->get(JsonImportService::class);
		try {
			if (!isset($_FILES['tx_sgcookieoptin_web_sgcookieoptinoptin'])) {
				throw new JsonImportException(
					LocalizationUtility::translate('frontend.error.noFileUploaded', 'sg_cookie_optin'),
					104
				);
			}

			$jsonImportService->parseAndStoreImportedData($languages);

			// check if all local languages are translated
			foreach ($languages as $language) {
				if (!isset($_SESSION['tx_sgcookieoptin']['importJsonData']['languageData'][$language['uid']])) {
					$this->addFlashMessage(
						LocalizationUtility::translate(
							'backend.jsonImport.warnings.language.missing',
							'sg_cookie_optin',
							['lang' => $language['title'] . ' (' . $language['locale'] . ')']
						),
						LocalizationUtility::translate(
							'backend.jsonImport.warnings.language.header',
							'sg_cookie_optin'
						),
						AbstractMessage::WARNING
					);
				}
			}

			// check groups, cookies and scripts count
			$groupsCounts = [];
			$cookiesCounts = [];
			$scriptsCounts = [];
			$warningCookies = FALSE;
			$warningGroups = FALSE;
			$warningScripts = FALSE;
			$dataSummary = [];
			if (isset($_SESSION['tx_sgcookieoptin']['importJsonData']['languageData'])) {
				foreach ($_SESSION['tx_sgcookieoptin']['importJsonData']['languageData'] as $languageId => $languageData) {
					$groupsCounts[$languageId] = count($languageData['cookieGroups']);
					foreach ($languageData['cookieGroups'] as $group) {
						if (!isset($cookiesCounts[$languageId])) {
							$cookiesCounts[$languageId] = 0;
							$scriptsCounts[$languageId] = 0;
						}

						$cookiesCounts[$languageId] += isset($group['cookieData']) ? count($group['cookieData']) : 0;
						$scriptsCounts[$languageId] += isset($group['scriptData']) ? count($group['scriptData']) : 0;
					}
				}
			}

			if (count(array_unique($groupsCounts)) > 1) {
				$this->addFlashMessage(
					LocalizationUtility::translate('backend.jsonImport.warnings.groupsCount', 'sg_cookie_optin'),
					LocalizationUtility::translate('backend.jsonImport.warnings.header', 'sg_cookie_optin'),
					AbstractMessage::WARNING
				);
				$warningGroups = TRUE;
			}

			if (count(array_unique($cookiesCounts)) > 1) {
				$this->addFlashMessage(
					LocalizationUtility::translate('backend.jsonImport.warnings.cookiesCount', 'sg_cookie_optin'),
					LocalizationUtility::translate('backend.jsonImport.warnings.header', 'sg_cookie_optin'),
					AbstractMessage::WARNING
				);
				$warningCookies = TRUE;
			}

			if (count(array_unique($scriptsCounts)) > 1) {
				$this->addFlashMessage(
					LocalizationUtility::translate('backend.jsonImport.warnings.scriptsCount', 'sg_cookie_optin'),
					LocalizationUtility::translate('backend.jsonImport.warnings.header', 'sg_cookie_optin'),
					AbstractMessage::WARNING
				);
				$warningScripts = TRUE;
			}

			foreach ($languages as $language) {
				$dataSummary[$language['uid']] = [
					'translated' => array_key_exists($language['uid'], $groupsCounts),
					'groups' => $groupsCounts[$language['uid']],
					'cookies' => $cookiesCounts[$language['uid']],
					'scripts' => $scriptsCounts[$language['uid']],
					'title' => $language['title'],
					'locale' => $language['locale'],
					'flagIdentifier' => $language['flagIdentifier']
				];
			}
			$this->view->assign('dataSummary', $dataSummary);
			$this->view->assign('warningGroups', $warningGroups);
			$this->view->assign('warningScripts', $warningScripts);
			$this->view->assign('warningCookies', $warningCookies);
		} catch (Exception $exception) {
			$this->addFlashMessage(
				$exception->getMessage(),
				'',
				AbstractMessage::ERROR
			);
			$this->redirect('uploadJson', 'Optin', 'sg_cookie_optin');
		}
	}

	/**
	 * Downloads a JSON file containing all the configuration for each language
	 *
	 * @throws StopActionException
	 */
	public function exportJsonAction() {
		try {
			$pid = (int) GeneralUtility::_GP('id');

			$data = JsonImportService::getDataForExport($pid);
			if ($data->rowCount() !== 1) {
				throw new JsonImportException(
					LocalizationUtility::translate('backend.jsonExport.error.exactlyOneEntry', 'sg_cookie_optin')
				);
			}

			$folder = ExtensionSettingsService::getSetting(ExtensionSettingsService::SETTING_FOLDER);
			$sitePath = defined('PATH_site') ? PATH_site : Environment::getPublicPath() . DIRECTORY_SEPARATOR;
			$filesPath = $sitePath . $folder . 'siteroot-' . $pid . DIRECTORY_SEPARATOR;
			$jsonData = [];
			foreach (new DirectoryIterator($filesPath) as $file) {
				if (strpos($file->getFilename(), 'cookieOptinData') !== 0) {
					continue;
				}

				$contents = file_get_contents($filesPath . $file->getFilename());
				$locale = LanguageService::getLocaleByFileName(
					str_replace('.json', '', $file->getFilename())
				);
				$jsonData[$locale] = json_decode($contents, TRUE);
			}

			header('Content-disposition: attachment; filename=sg_cookie_optin.json');
			header('Content-type: application/json');
			echo json_encode($jsonData, TRUE);
			die();
		} catch (Exception $exception) {
			$this->addFlashMessage(
				LocalizationUtility::translate('backend.jsonExport.error', 'sg_cookie_optin') . $exception->getMessage(
				),
				LocalizationUtility::translate('backend.exportConfig', 'sg_cookie_optin'),
				AbstractMessage::ERROR
			);
			$this->redirect('index');
		}
	}

	/**
	 * Displays the user preference statistics
	 *
	 */
	public function statisticsAction() {
		$this->initComponents();
	}

	/**
	 * Renders the upload JSON form
	 */
	public function uploadJsonAction() {
		$this->initComponents();

		$this->view->assign('pages', BackendService::getPages());
	}

	/**
	 * Create an optin entry in the database and redirect to edit action
	 *
	 * @throws RouteNotFoundException
	 * @throws SiteNotFoundException
	 */
	public function createAction() {
		$pid = (int) GeneralUtility::_GP('id');
		// create with DataHandler
		// adding default values for the german language. The values are hardcoded because they must not change since we don't know
		// the language keys or whatsoever in the target system

		$dataMapArray = [
			'description' => JsonImportService::TEXT_BANNER_DESCRIPTION,
			'template_html' => '',
			'show_button_close' => '0',
			'iframe_description' => JsonImportService::TEXT_IFRAME_DESCRIPTION,
			'iframe_html' => '',
			'iframe_replacement_html' => '',
			'iframe_whitelist_regex' => JsonImportService::DEFAULT_IFRAME_WHITELIST,
			'banner_description' => JsonImportService::TEXT_BANNER_DESCRIPTION,
			'banner_html' => '',
			'essential_description' => JsonImportService::TEXT_ESSENTIAL_DESCRIPTION,
			'groups' => '',
			'set_cookie_for_domain' => '',
			'save_history_webhook' => '',
			'pid' => $pid,
		];

		$newOptinKey = StringUtility::getUniqueId('NEW');
		$data['tx_sgcookieoptin_domain_model_optin'][$newOptinKey] = $dataMapArray;

		$newCookieKey = StringUtility::getUniqueId('NEW');
		$data['tx_sgcookieoptin_domain_model_cookie'][$newCookieKey] = [
			'pid' => $pid,
			'name' => 'cookie_optin',
			'provider' => '',
			'purpose' => JsonImportService::TEXT_ESSENTIAL_DEFAULT_COOKIE_PURPOSE,
			'lifetime' => '1 Jahr',
			'parent_optin' => $newOptinKey
		];

		$newCookieKey = StringUtility::getUniqueId('NEW');
		$data['tx_sgcookieoptin_domain_model_cookie'][$newCookieKey] = [
			'pid' => $pid,
			'name' => 'SgCookieOptin.lastPreferences',
			'provider' => '',
			'purpose' => JsonImportService::TEXT_ESSENTIAL_DEFAULT_LAST_PREFERENCES_PURPOSE,
			'lifetime' => '1 Jahr',
			'parent_optin' => $newOptinKey
		];

		$dataHandler = GeneralUtility::makeInstance(DataHandler::class);
		$dataHandler->start($data, []);
		$dataHandler->process_datamap();

		$newOptinId = $dataHandler->substNEWwithIDs[$newOptinKey];

		$this->redirectToTCAEdit((int) $newOptinId);
		$this->redirect('index');
	}

	/**
	 * Checks the license status and displays it
	 */
	protected function checkLicenseStatus() {
		if (LicenceCheckService::isTYPO3VersionSupported() && !LicenceCheckService::isInDevelopmentContext()) {
			$licenseStatus = LicenceCheckService::getLicenseCheckResponseData();
			$this->view->assign('licenseError', $licenseStatus['error']);
			$this->view->assign('licenseMessage', $licenseStatus['message']);
			$this->view->assign('licenseTitle', $licenseStatus['title']);
		}
	}
}
