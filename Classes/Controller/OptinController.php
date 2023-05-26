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
class OptinController extends ActionController
{
    use InitControllerComponents;

    /**
     * DocHeaderComponent
     *
     * @var DocHeaderComponent
     */
    protected $docHeaderComponent;

    public function __construct(
        protected readonly ModuleTemplateFactory $moduleTemplateFactory,
    )
    {
    }

    /**
     * Init module state.
     * This isn't done within __construct() since the controller
     * object is only created once in extbase when multiple actions are called in
     * one call. When those change module state, the second action would see old state.
     */
    public function initializeAction(): void
    {
        $this->moduleData = $this->request->getAttribute('moduleData');
        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
//        $this->moduleTemplate->setTitle(LocalizationUtility::translate('LLL:EXT:beuser/Resources/Private/Language/locallang_mod.xlf:mlang_tabs_tab'));
        $this->moduleTemplate->setFlashMessageQueue($this->getFlashMessageQueue());
    }

    /**
     * Starts the module, even opens up a TCEForm, or shows where the domain root is.
     *
     */
    public function indexAction()
    {
        $this->initComponents($this->moduleTemplate);
        $this->checkLicenseStatus($this->moduleTemplate);

        session_start();
        if (isset($_SESSION['tx_sgcookieoptin']['configurationChanged'])) {
            unset($_SESSION['tx_sgcookieoptin']['configurationChanged']);
            $this->addFlashMessage(
                LocalizationUtility::translate('backend.hasChanges.message', 'sg_cookie_optin'),
                LocalizationUtility::translate('backend.hasChanges.title', 'sg_cookie_optin'),
                AbstractMessage::INFO
            );
        }

        $pageUid = (int)GeneralUtility::_GP('id');
        $pageInfo = BackendUtility::readPageAccess($pageUid, $GLOBALS['BE_USER']->getPagePermsClause(1));
        if ($pageInfo && isset($pageInfo['is_siteroot']) && (int)$pageInfo['is_siteroot'] === 1) {
            $optIns = BackendService::getOptins($pageUid);

            if (count($optIns) > 1) {
                $this->addFlashMessage(
                    LocalizationUtility::translate('backend.tooManyRecorsException.description', 'sg_cookie_optin'),
                    LocalizationUtility::translate('backend.tooManyRecorsException.header', 'sg_cookie_optin'),
                    AbstractMessage::ERROR
                );
            }

            $this->moduleTemplate->assign('isSiteRoot', TRUE);
            $this->moduleTemplate->assign('optins', $optIns);
        }

        $currentTypo3Version = VersionNumberUtility::getCurrentTypo3Version();
        $typo3Version = \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger($currentTypo3Version);
        $this->moduleTemplate->assign('typo3Version', $typo3Version);

        $this->moduleTemplate->assign('pages', BackendService::getPages());
        if (version_compare(\TYPO3\CMS\Core\Utility\VersionNumberUtility::getCurrentTypo3Version(), '11.0.0', '<')) {
            return NULL;
        }

        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->loadRequireJsModule('TYPO3/CMS/SgCookieOptin/Backend/EditOnClick');

        return $this->moduleTemplate->renderResponse('Optin/Index');
    }

    /**
     * Activates the demo mode for the given instance.
     *
     */
    public function activateDemoModeAction()
    {
        if (LicenceCheckService::isInDemoMode() || !LicenceCheckService::isDemoModeAcceptable()) {
            $this->redirect('index');
        }

        LicenceCheckService::activateDemoMode();
        $this->redirect('index');
    }

    /**
     * Imports JSON configuration
     *
     */
    public function importJsonAction()
    {
        session_start();
        $pid = (int)GeneralUtility::_GP('id');
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
            $jsonImportService = GeneralUtility::makeInstance(JsonImportService::class);

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
            return $this->redirectToUri($this->buildTCAEditUri((int)$defaultLanguageOptinId));
        } catch (Exception $exception) {
            $this->addFlashMessage(
                $exception->getMessage(),
                '',
                AbstractMessage::ERROR
            );
            return $this->redirect('previewImport', 'Optin', 'sg_cookie_optin');
        }
    }

    /**
     * Redirects to the edit action
     *
     * @param int $optInId
     * @throws RouteNotFoundException
     */
    protected function buildTCAEditUri(int $optInId)
    {
        $pid = (int)GeneralUtility::_GP('id');
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $params = [
            'edit' => ['tx_sgcookieoptin_domain_model_optin' => [$optInId => 'edit']],
            'returnUrl' => (string)$uriBuilder->buildUriFromRoutePath('/module/web/sg-cookie-optin', ['id' => $pid])
        ];
        return (string)$uriBuilder->buildUriFromRoute('record_edit', $params);
    }

    /**
     * Displays statistics about the imported data for a  preview
     *
     * @throws StopActionException
     * @throws \TYPO3\CMS\Extbase\Object\Exception
     */
    public function previewImportAction()
    {
        session_start();
        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $this->initComponents($this->moduleTemplate);
        $pageUid = (int)GeneralUtility::_GP('id');
        $pageInfo = BackendUtility::readPageAccess($pageUid, $GLOBALS['BE_USER']->getPagePermsClause(1));
        if ($pageInfo && (int)$pageInfo['is_siteroot'] === 1) {
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

            $this->moduleTemplate->assign('isSiteRoot', TRUE);
            $this->moduleTemplate->assign('optins', $optIns);
        }
        try {
            $languages = LanguageService::getLanguages($pageUid);
        } catch (SiteNotFoundException $e) {
            $languages = [];
        }
        $jsonImportService = GeneralUtility::makeInstance(JsonImportService::class);
        try {
            if (version_compare(\TYPO3\CMS\Core\Utility\VersionNumberUtility::getCurrentTypo3Version(), '12.0.0', '<')) {
                if (!isset($_FILES['tx_sgcookieoptin_web_sgcookieoptinoptin'])) {
                    throw new JsonImportException(
                        LocalizationUtility::translate('frontend.error.noFileUploaded', 'sg_cookie_optin'),
                        104
                    );
                }
            } else if (!isset($_FILES['file'])) {
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
            $this->moduleTemplate->assign('dataSummary', $dataSummary);
            $this->moduleTemplate->assign('warningGroups', $warningGroups);
            $this->moduleTemplate->assign('warningScripts', $warningScripts);
            $this->moduleTemplate->assign('warningCookies', $warningCookies);
            return $this->moduleTemplate->renderResponse('Optin/PreviewImport');
        } catch (Exception $exception) {
            $this->addFlashMessage(
                $exception->getMessage(),
                '',
                AbstractMessage::ERROR
            );
            return $this->redirect('uploadJson', 'Optin', 'sg_cookie_optin');
        }
    }

    /**
     * Downloads a JSON file containing all the configuration for each language
     *
     * @throws StopActionException
     */
    public function exportJsonAction()
    {
        try {
            $pid = (int)GeneralUtility::_GP('id');

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
                LocalizationUtility::translate('backend.jsonExport.error', 'sg_cookie_optin') . $exception->getMessage(),
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
    public function statisticsAction()
    {
        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $this->initComponents($this->moduleTemplate);
    }

    /**
     * Renders the upload JSON form
     */
    public function uploadJsonAction()
    {
        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $this->initComponents($this->moduleTemplate);
        $this->moduleTemplate->assign('pages', BackendService::getPages());
        return $this->moduleTemplate->renderResponse('Optin/UploadJson');
    }

    /**
     * Create an optin entry in the database and redirect to edit action
     *
     * @throws RouteNotFoundException
     * @throws SiteNotFoundException
     */
    public function createAction()
    {
        $pid = (int)GeneralUtility::_GP('id');
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

        return $this->redirectToUri($this->buildTCAEditUri((int)$newOptinId));
    }

    /**
     * Checks the license status and displays it
     * @param \TYPO3\CMS\Backend\Template\ModuleTemplate $moduleTemplate
     */
    protected function checkLicenseStatus(ModuleTemplate $moduleTemplate): void
    {
        if (LicenceCheckService::isTYPO3VersionSupported() && !LicenceCheckService::isInDevelopmentContext()) {
            $licenseStatus = LicenceCheckService::getLicenseCheckResponseData();
            $this->moduleTemplate->assign('licenseError', $licenseStatus['error']);
            $this->moduleTemplate->assign('licenseMessage', $licenseStatus['message']);
            $this->moduleTemplate->assign('licenseTitle', $licenseStatus['title']);
        }
    }
}
