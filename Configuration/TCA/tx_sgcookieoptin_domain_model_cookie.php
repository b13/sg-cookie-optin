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

$configuration = [
	'ctrl' => [
		'title' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_cookie',
		'label' => 'name',
		'label_alt' => 'provider',
		'label_alt_force' => 1,
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,
		'searchFields' => 'name, provider, purpose, lifetime',
		'delete' => 'deleted',
		'enablecolumns' => [
			'disabled' => 'hidden',
		],
		'default_sortby' => 'ORDER BY sorting DESC',
		'hideTable' => TRUE,
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'iconfile' => 'EXT:sg_cookie_optin/Resources/Public/Icons/tx_sgcookieoptin_domain_model_cookie.svg'
	],
	'interface' => [],
	'types' => [
		'1' => [
			'showitem' => 'hidden, name, provider, lifetime, purpose',
		],
	],
	'palettes' => [

	],
	'columns' => [
		'pid' => [
			'exclude' => FALSE,
			'label' => 'PID',
			'config' => [
				'type' => 'none',
			]
		],
		'hidden' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:hidden.I.0',
			'config' => [
				'type' => 'check',
			],
		],
		'sys_language_uid' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'special' => 'languages',
				'default' => 0,
				'items' => [
					[
						'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.allLanguages',
						-1,
						'flags-multiple'
					]
				]
			]
		],
		'l10n_parent' => [
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'items' => [
					['', 0]
				],
				'foreign_table' => 'tx_sgcookieoptin_domain_model_cookie',
				'foreign_table_where' => 'AND tx_sgcookieoptin_domain_model_cookie.uid=###REC_FIELD_l10n_parent### AND tx_sgcookieoptin_domain_model_cookie.sys_language_uid IN (-1,0)',
				'default' => 0
			]
		],
		'l10n_diffsource' => [
			'config' => [
				'type' => 'passthrough',
				'default' => ''
			]
		],
		'name' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_cookie.name',
			'description' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_cookie.name.description',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'placeholder' => '_ga',
				'eval' => 'trim, required'
			],
		],
		'provider' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_cookie.provider',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'placeholder' => 'Google Adwords',
				'eval' => 'trim, required'
			],
		],
		'purpose' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_cookie.purpose',
			'config' => [
				'type' => 'text',
				'placeholder' => 'Cookie von Google zur Steuerung der erweiterten Script- und Ereignisbehandlung.',
				'eval' => 'trim, required'
			],
		],
		'lifetime' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_cookie.lifetime',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'placeholder' => '1 Jahr',
				'eval' => 'trim, required'
			],
		],
		'parent_group' => [
			'exclude' => TRUE,
			'displayCond' => 'FIELD:parent_optin:<=:0',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_cookie.parent_group',
			'config' => [
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'tx_sgcookieoptin_domain_model_group',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
				'autoSizeMax' => 1,
				'default' => 0,
			],
		],
		'parent_optin' => [
			'exclude' => TRUE,
			'displayCond' => 'FIELD:parent_group:<=:0',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_cookie.parent_optin',
			'config' => [
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'tx_sgcookieoptin_domain_model_optin',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
				'autoSizeMax' => 1,
				'default' => 0,
			],
		],
	],
];

if (version_compare(\TYPO3\CMS\Core\Utility\VersionNumberUtility::getCurrentTypo3Version(), '10.3.0', '<')) {
	$configuration['interface']['showRecordFieldList'] = 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, name, provider, purpose, lifetime, parent_group, parent_optin';
}

return $configuration;
