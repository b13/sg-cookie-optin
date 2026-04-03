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
 *  the Free Software Foundation; either version 3 of the License or
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
use Psr\Http\Message\ResponseInterface;
use SGalinski\SgCookieOptin\Domain\Repository\SchedulerTaskRepository;
use SGalinski\SgCookieOptin\Exception\JsonImportException;
use SGalinski\SgCookieOptin\Service\BackendService;
use SGalinski\SgCookieOptin\Service\ExtensionSettingsService;
use SGalinski\SgCookieOptin\Service\JsonImportService;
use SGalinski\SgCookieOptin\Service\LanguageService;
use SGalinski\SgCookieOptin\Service\LicenceCheckService;
use SGalinski\SgCookieOptin\Traits\InitControllerComponents;
use TYPO3\CMS\Backend\Attribute\Controller;
use TYPO3\CMS\Backend\Module\ModuleData;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Http\PropagateResponseException;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Optin Controller
 */
#[Controller]
class OptinController extends AbstractController {
	use InitControllerComponents;

	/**
	 * @var ModuleTemplateFactory
	 */
	protected ModuleTemplateFactory $moduleTemplateFactory;

	/**
	 * @var array|ModuleData|null
	 */
	protected ModuleData|array|NULL $moduleData;

	/**
	 * @var ModuleTemplate
	 */
	protected ModuleTemplate $moduleTemplate;

	public function __construct(SchedulerTaskRepository $schedulerTaskRepository) {
		$this->schedulerTaskRepository = $schedulerTaskRepository;
	}

	/**
	 * Init module state.
	 * This isn't done within __construct() since the controller
	 * object is only created once in extbase when multiple actions are called in
	 * one call. When those change module state, the second action would see old state.
	 */
	public function initializeAction(): void {
		$this->moduleTemplateFactory = GeneralUtility::makeInstance(ModuleTemplateFactory::class);
		$this->moduleData = $this->request->getAttribute('moduleData');
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
		$this->moduleTemplate->setFlashMessageQueue($this->getFlashMessageQueue());
	}

	/**
	 * Starts the module, even opens up a TCEForm, or shows where the domain root is.
	 *
	 * @throws Exception
	 * @throws PropagateResponseException
	 */
	public function indexAction(): ResponseInterface {
		$this->switchMode();
		$typo3Version = VersionNumberUtility::convertVersionNumberToInteger(
			VersionNumberUtility::getCurrentTypo3Version()
		);

		$this->initComponents($this->moduleTemplate);
		$this->checkLicenseStatus();

		session_start([
			'cookie_secure' => TRUE,
			'cookie_httponly' => TRUE,
			'cookie_samesite' => 'Strict',
		]);
		if (isset($_SESSION['tx_sgcookieoptin']['configurationChanged'])) {
			unset($_SESSION['tx_sgcookieoptin']['configurationChanged']);

			$this->addFlashMessage(
				LocalizationUtility::translate('backend.hasChanges.message', 'sg_cookie_optin'),
				LocalizationUtility::translate('backend.hasChanges.title', 'sg_cookie_optin'),
				ContextualFeedbackSeverity::INFO
			);
		}

		$pageUid = (int)($this->request->getParsedBody()['id'] ?? $this->request->getQueryParams()['id'] ?? NULL);

		// Check specifically for website Page 0
		$isSiteRoot = ($pageUid === 0);
		$this->moduleTemplate->assign('useEmptyLayout', $isSiteRoot);

		$pageInfo = BackendUtility::readPageAccess($pageUid, $GLOBALS['BE_USER']->getPagePermsClause(1));
		if ($pageInfo && isset($pageInfo['is_siteroot']) && (int)$pageInfo['is_siteroot'] === 1) {
			$optIns = BackendService::getOptins($pageUid);

			if (\count($optIns) > 1) {
				$this->addFlashMessage(
					LocalizationUtility::translate('backend.tooManyRecorsException.description', 'sg_cookie_optin'),
					LocalizationUtility::translate('backend.tooManyRecorsException.header', 'sg_cookie_optin'),
					ContextualFeedbackSeverity::ERROR
				);
			}

			$this->moduleTemplate->assign('isSiteRoot', TRUE);
			$this->moduleTemplate->assign('optins', $optIns);
		}

		$this->moduleTemplate->assign('typo3Version', $typo3Version);
		$this->moduleTemplate->assign('pages', BackendService::getPages());
		$pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
		$pageRenderer->loadJavaScriptModule('@sgalinski/sg-cookie-optin/EditOnClick.js');

		return $this->moduleTemplate->renderResponse('Optin/Index');
	}

	/**
	 * Activates the demo mode for the given instance.
	 */
	public function activateDemoModeAction(): ResponseInterface {
		if (LicenceCheckService::isInDemoMode() || !LicenceCheckService::isDemoModeAcceptable()) {
			return $this->redirect('index');
		}

		LicenceCheckService::activateDemoMode();
		return $this->redirect('index');
	}

	/**
	 * Imports JSON configuration
	 */
	public function importJsonAction(): ?ResponseInterface {
		session_start([
			'cookie_secure' => TRUE,
			'cookie_httponly' => TRUE,
			'cookie_samesite' => 'Strict',
		]);

		$pid = (int) ($this->request->getParsedBody()['id'] ?? $this->request->getQueryParams()['id'] ?? NULL);

		try {
			if (!isset($_SESSION['tx_sgcookieoptin']['importJsonData']['defaultLanguageId'])) {
				throw new JsonImportException(
					LocalizationUtility::translate('jsonImport.error.theStoredImportedDataIsCorrupt', 'sg_cookie_optin'),
					101
				);
			}

			$defaultLanguageId = $_SESSION['tx_sgcookieoptin']['importJsonData']['defaultLanguageId'];
			$jsonImportService = GeneralUtility::makeInstance(JsonImportService::class);

			$defaultLanguageJsonData = $_SESSION['tx_sgcookieoptin']['importJsonData']['languageData'][$defaultLanguageId];
			$defaultLanguageOptinId = $jsonImportService->importJsonData($defaultLanguageJsonData, $pid);

			foreach ($_SESSION['tx_sgcookieoptin']['importJsonData']['languageData'] as $languageId => $jsonData) {
				if ($languageId !== $defaultLanguageId) {
					$jsonImportService->importJsonData($jsonData, $pid, $languageId, $defaultLanguageOptinId);
				}
			}

			unset($_SESSION['tx_sgcookieoptin']['importJsonData']);
			$_SESSION['tx_sgcookieoptin']['configurationChanged'] = TRUE;
			return $this->redirectToUri($this->buildTCAEditUri((int)$defaultLanguageOptinId));
		} catch (Exception $exception) {
			$this->addFlashMessage($exception->getMessage(), '', ContextualFeedbackSeverity::ERROR);
			return $this->redirect('previewImport', 'Optin', 'sg_cookie_optin');
		}
	}

	/**
	 * Displays statistics about the imported data for a preview
	 *
	 * @throws \Doctrine\DBAL\Exception
	 */
	public function previewImportAction(): ResponseInterface {
		session_start([
			'cookie_secure' => TRUE,
			'cookie_httponly' => TRUE,
			'cookie_samesite' => 'Strict',
		]);

		$pageUid = (int) ($this->request->getParsedBody()['id'] ?? $this->request->getQueryParams()['id'] ?? NULL);
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
		$this->initComponents($this->moduleTemplate);
		$pageInfo = BackendUtility::readPageAccess($pageUid, $GLOBALS['BE_USER']->getPagePermsClause(1));
		if ($pageInfo && (int) $pageInfo['is_siteroot'] === 1) {
			$optIns = BackendService::getOptins($pageUid);

			if (\count($optIns) > 0) {
				$this->addFlashMessage(
					LocalizationUtility::translate('backend.tooManyRecorsException.description', 'sg_cookie_optin'),
					LocalizationUtility::translate('backend.tooManyRecorsException.header', 'sg_cookie_optin'),
					ContextualFeedbackSeverity::ERROR
				);
			}

			$this->moduleTemplate->assign('isSiteRoot', TRUE);
			$this->moduleTemplate->assign('optins', $optIns);
		}

		try {
			$languages = LanguageService::getLanguages($pageUid);
		} catch (SiteNotFoundException) {
			$languages = [];
		}

		$jsonImportService = GeneralUtility::makeInstance(JsonImportService::class);
		try {
			if (!isset($_FILES['file'])) {
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
						LocalizationUtility::translate('backend.jsonImport.warnings.language.header', 'sg_cookie_optin'),
						ContextualFeedbackSeverity::WARNING
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
					$groupsCounts[$languageId] = \count($languageData['cookieGroups']);
					foreach ($languageData['cookieGroups'] as $group) {
						if (!isset($cookiesCounts[$languageId])) {
							$cookiesCounts[$languageId] = 0;
							$scriptsCounts[$languageId] = 0;
						}

						$cookiesCounts[$languageId] += isset($group['cookieData']) ? \count($group['cookieData']) : 0;
						$scriptsCounts[$languageId] += isset($group['scriptData']) ? \count($group['scriptData']) : 0;
					}
				}
			}

			if (\count(array_unique($groupsCounts)) > 1) {
				$this->addFlashMessage(
					LocalizationUtility::translate('backend.jsonImport.warnings.groupsCount', 'sg_cookie_optin'),
					LocalizationUtility::translate('backend.jsonImport.warnings.header', 'sg_cookie_optin'),
					ContextualFeedbackSeverity::WARNING
				);
				$warningGroups = TRUE;
			}

			if (\count(array_unique($cookiesCounts)) > 1) {
				$this->addFlashMessage(
					LocalizationUtility::translate('backend.jsonImport.warnings.cookiesCount', 'sg_cookie_optin'),
					LocalizationUtility::translate('backend.jsonImport.warnings.header', 'sg_cookie_optin'),
					ContextualFeedbackSeverity::WARNING
				);
				$warningCookies = TRUE;
			}

			if (\count(array_unique($scriptsCounts)) > 1) {
				$this->addFlashMessage(
					LocalizationUtility::translate('backend.jsonImport.warnings.scriptsCount', 'sg_cookie_optin'),
					LocalizationUtility::translate('backend.jsonImport.warnings.header', 'sg_cookie_optin'),
					ContextualFeedbackSeverity::WARNING
				);
				$warningScripts = TRUE;
			}

			foreach ($languages as $language) {
				$dataSummary[$language['uid']] = [
					'translated' => \array_key_exists($language['uid'], $groupsCounts),
					'groups' => $groupsCounts[$language['uid']] ?? 0,
					'cookies' => $cookiesCounts[$language['uid']] ?? 0,
					'scripts' => $scriptsCounts[$language['uid']] ?? 0,
					'title' => $language['title'] ?? '',
					'locale' => $language['locale'] ?? '',
					'flagIdentifier' => $language['flagIdentifier'] ?? '',
				];
			}
			$this->moduleTemplate->assign('dataSummary', $dataSummary);
			$this->moduleTemplate->assign('warningGroups', $warningGroups);
			$this->moduleTemplate->assign('warningScripts', $warningScripts);
			$this->moduleTemplate->assign('warningCookies', $warningCookies);
			return $this->moduleTemplate->renderResponse('Optin/PreviewImport');
		} catch (Exception $exception) {
			$this->addFlashMessage($exception->getMessage(), '', ContextualFeedbackSeverity::ERROR);
			return $this->redirect('uploadJson', 'Optin', 'sg_cookie_optin');
		}
	}

	/**
	 * Downloads a JSON file containing all the configuration for each language
	 */
	public function exportJsonAction() {
		try {
			$pid = (int) ($this->request->getParsedBody()['id'] ?? $this->request->getQueryParams()['id'] ?? NULL);

			$data = JsonImportService::getDataForExport($pid);
			if ($data->rowCount() !== 1) {
				throw new JsonImportException(
					LocalizationUtility::translate('backend.jsonExport.error.exactlyOneEntry', 'sg_cookie_optin')
				);
			}

			$folder = ExtensionSettingsService::getSetting(ExtensionSettingsService::SETTING_FOLDER);
			$sitePath = \defined('PATH_site') ? PATH_site : Environment::getPublicPath() . DIRECTORY_SEPARATOR;
			$filesPath = $sitePath . $folder . 'siteroot-' . $pid . DIRECTORY_SEPARATOR;
			$jsonData = [];
			foreach (new DirectoryIterator($filesPath) as $file) {
				if (!str_starts_with($file->getFilename(), 'cookieOptinData')) {
					continue;
				}

				$contents = file_get_contents($filesPath . $file->getFilename());
				$locale = LanguageService::getLocaleByFileName(str_replace('.json', '', $file->getFilename()));
				$jsonData[$locale] = json_decode($contents, TRUE);
			}

			header('Content-disposition: attachment; filename=sg_cookie_optin.json');
			header('Content-type: application/json');
			echo json_encode($jsonData, TRUE);
			die();
		} catch (Exception $exception) {
			$this->addFlashMessage(
				LocalizationUtility::translate('backend.jsonExport.error', 'sg_cookie_optin') . $exception->getMessage(),
				LocalizationUtility::translate('backend.exportConfig', 'sg_cookie_optin'),
				ContextualFeedbackSeverity::ERROR
			);
			return $this->redirect('index');
		}
	}

	/**
	 * Displays the user preference statistics
	 */
	public function statisticsAction(): ResponseInterface {
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
		$this->initComponents($this->moduleTemplate);
		return $this->htmlResponse();
	}

	/**
	 * Renders the upload JSON form
	 *
	 * @throws \Doctrine\DBAL\Exception
	 */
	public function uploadJsonAction(): ResponseInterface {
		$this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
		$this->initComponents($this->moduleTemplate);
		$this->moduleTemplate->assign('pages', BackendService::getPages());
		return $this->moduleTemplate->renderResponse('Optin/UploadJson');
	}

	/**
	 * Create an optin entry in the database and redirect to edit action
	 *
	 * @throws RouteNotFoundException
	 */
	public function createAction(): ResponseInterface {
		$pid = (int) ($this->request->getParsedBody()['id'] ?? $this->request->getQueryParams()['id'] ?? NULL);

		// Create with DataHandler
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
			'parent_optin' => $newOptinKey,
		];

		$newCookieKey = StringUtility::getUniqueId('NEW');
		$data['tx_sgcookieoptin_domain_model_cookie'][$newCookieKey] = [
			'pid' => $pid,
			'name' => 'SgCookieOptin.lastPreferences',
			'provider' => '',
			'purpose' => JsonImportService::TEXT_ESSENTIAL_DEFAULT_LAST_PREFERENCES_PURPOSE,
			'lifetime' => '1 Jahr',
			'parent_optin' => $newOptinKey,
		];

		$dataHandler = GeneralUtility::makeInstance(DataHandler::class);
		$dataHandler->start($data, []);
		$dataHandler->process_datamap();

		$newOptinId = $dataHandler->substNEWwithIDs[$newOptinKey];

		return $this->redirectToUri($this->buildTCAEditUri((int) $newOptinId));
	}

	/**
	 * Redirects to the edit action
	 *
	 * @param int $optInId
	 * @return string
	 * @throws RouteNotFoundException
	 */
	protected function buildTCAEditUri(int $optInId): string {
		$pid = (int) ($this->request->getParsedBody()['id'] ?? $this->request->getQueryParams()['id'] ?? NULL);
		$uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
		$params = [
			'edit' => ['tx_sgcookieoptin_domain_model_optin' => [$optInId => 'edit']],
			'returnUrl' => (string) $uriBuilder->buildUriFromRoutePath('/module/web/sg-cookie-optin', ['id' => $pid]),
		];
		return (string) $uriBuilder->buildUriFromRoute('record_edit', $params);
	}

	/**
	 * Checks the license status and displays it
	 */
	protected function checkLicenseStatus(): void {
		if (LicenceCheckService::isTYPO3VersionSupported() && !LicenceCheckService::isInDevelopmentContext()) {
			$licenseStatus = LicenceCheckService::getLicenseCheckResponseData();
			$this->moduleTemplate->assign('licenseError', $licenseStatus['error']);
			$this->moduleTemplate->assign('licenseMessage', $licenseStatus['message']);
			$this->moduleTemplate->assign('licenseTitle', $licenseStatus['title']);
		}
	}
}
