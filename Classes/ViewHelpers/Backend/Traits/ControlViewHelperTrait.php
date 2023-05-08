<?php
namespace SGalinski\SgCookieOptin\ViewHelpers\Backend\Traits;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Backend\RecordList\DatabaseRecordList;

/**
 * Trade for code sharing between versions
 */
trait ControlViewHelperTrait {
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
        $pageRenderer->loadRequireJsModule('TYPO3/CMS/Backend/AjaxDataHandler');
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