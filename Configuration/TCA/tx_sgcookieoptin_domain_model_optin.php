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
		'title' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin',
		'label' => 'header',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,
		'searchFields' => 'header, description, accept_all_text, user_hash_text, accept_specific_text, accept_essential_text,
			essential_title, essential_description, extend_box_link_text, extend_box_link_text_close,
			extend_table_link_text, extend_table_link_text_close, cookie_name_text, cookie_provider_text,
			cookie_purpose_text, cookie_lifetime_text, iframe_title, iframe_description, iframe_cookies, iframe_button_allow_all_text,
			iframe_button_allow_one_text, iframe_button_reject_text, iframe_button_load_one_description, iframe_button_load_one_text, iframe_open_settings_text, iframe_whitelist_regex, template_html,
			banner_html, banner_button_accept_text, banner_button_settings_text, banner_description,
			save_confirmation_text, dependent_groups_text',
		'delete' => 'deleted',
		'hideTable' => FALSE,
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'iconfile' => 'EXT:sg_cookie_optin/Resources/Public/Icons/tx_sgcookieoptin_domain_model_optin.svg',
		'requestUpdate' => 'template_selection',
	],
	'interface' => [],
	'types' => [
		'1' => [
			'showitem' => '
				--div--;LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.tab.textAndMenu,
					--palette--;;update_version, header, description, save_confirmation_text, user_hash_text, dependent_groups_text, --palette--;;accept_buttons_texts,
					--palette--;;link_texts, --palette--;;cookie_texts, navigation,
				--div--;LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.tab.color,
					--palette--;;template, --palette--;;color_general, --palette--;;fingerprint, --palette--;;color_notification,
					--palette--;;color_checkbox, --palette--;;color_button, --palette--;;color_list,
					--palette--;;color_table, disable_powered_by,
				--div--;LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.tab.iframes,
					iframe_enabled, iframe_title, iframe_description, iframe_cookies, --palette--;;iframe_texts,
					--palette--;;iframe_colors, --palette--;;iframe_template, --palette--;;iframe_replacement_template, --palette--;;iframe_whitelist,
				--div--;LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.tab.iframe_settings,
					services,
				--div--;LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.tab.banner,
					--palette--;;banner_general, --palette--;;banner_general_colors,
					--palette--;;banner_settings_button, --palette--;;banner_accept_essential_button, --palette--;;banner_accept_button,
					--palette--;;banner_template,
				--div--;LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.tab.essential,
					essential_title, essential_description, essential_scripts, essential_cookies,
				--div--;LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.tab.group,
					groups,
				--div--;LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.tab.settings,
					unified_cookie_name, --palette--;;cookie_lifetime_settings, overwrite_baseurl, minify_generated_data, --palette--;;consent_mode,
					activate_testing_mode, disable_for_this_language, monochrome_enabled, render_assets_inline, consider_do_not_track, --palette--;;multidomain, cookiebanner_whitelist_regex, disable_usage_statistics, auto_action_for_bots',
		],
	],
	'palettes' => [
		'accept_buttons_texts' => [
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.accept_buttons_texts',
			'showitem' => 'accept_all_text, accept_specific_text, accept_essential_text'
		],
		'link_texts' => [
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.link_texts',
			'showitem' => 'extend_box_link_text, extend_box_link_text_close, --linebreak--,
				extend_table_link_text, extend_table_link_text_close'
		],
		'cookie_texts' => [
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.cookie_texts',
			'showitem' => 'cookie_name_text, cookie_provider_text, --linebreak--,
			 	cookie_purpose_text, cookie_lifetime_text'
		],
		'color_general' => [
			'showitem' => 'color_full_box, color_full_headline, color_full_text, --linebreak--, color_box, color_headline, color_text'
		],
		'color_notification' => [
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_notification',
			'showitem' => 'color_confirmation_background, color_confirmation_text'
		],
		'color_checkbox' => [
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.colors_checkboxes',
			'showitem' => 'color_checkbox_required, color_checkbox'
		],
		'color_button' => [
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.colors_buttons',
			'showitem' => 'show_button_close, --linebreak--,
				color_button_close, color_button_close_hover, color_button_close_text, --linebreak--,
				color_full_button_close, color_full_button_close_hover, color_full_button_close_text, --linebreak--,
				color_button_all, color_button_all_hover, color_button_all_text, --linebreak--,
				color_button_specific, color_button_specific_hover, color_button_specific_text, --linebreak--,
				color_button_essential, color_button_essential_hover, color_button_essential_text'
		],
		'color_list' => [
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.colors_lists',
			'showitem' => 'color_list, color_list_text',
		],
		'fingerprint' => [
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.fingerprint',
			'showitem' => 'show_fingerprint, fingerprint_position, --linebreak--,
			color_fingerprint_background, color_fingerprint_image'
		],
		'color_table' => [
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.colors_tables',
			'showitem' => 'color_table_header, color_table, --linebreak--,
				color_table_header_text, color_Table_data_text'
		],
		'template' => [
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.template',
			'showitem' => 'template_selection, template_overwritten, --linebreak--,
				template_warning, --linebreak--,
				template_html'
		],
		'iframe_texts' => [
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_texts',
			'showitem' => 'iframe_button_allow_all_text, iframe_button_allow_one_text, iframe_button_reject_text, --linebreak--,
				iframe_button_load_one_text, iframe_open_settings_text, --linebreak--,
				iframe_button_load_one_description'
		],
		'iframe_colors' => [
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_colors',
			'showitem' => 'iframe_color_consent_box_background, --linebreak--,
				iframe_color_button_load_one, iframe_color_button_load_one_hover, iframe_color_button_load_one_text, --linebreak--,
				iframe_color_open_settings, --linebreak--,
				iframe_replacement_background_image'
		],
		'iframe_template' => [
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_template',
			'showitem' => 'iframe_overwritten, --linebreak--,
				iframe_warning, --linebreak--,
				iframe_html'
		],
		'iframe_whitelist' => [
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_whitelist',
			'description' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_whitelist.description',
			'showitem' => 'iframe_whitelist_regex'
		],
		'iframe_replacement_template' => [
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_replacement_template',
			'showitem' => 'iframe_replacement_overwritten, --linebreak--,
				iframe_replacement_warning, --linebreak--,
				iframe_replacement_html, --linebreak--'
		],
		'banner_general' => [
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.palette.banner_general',
			'showitem' => 'banner_enable, --linebreak--, banner_force_min_width, --linebreak--, banner_position,
				banner_description'
		],
		'banner_general_colors' => [
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.palette.banner_general_colors',
			'showitem' => 'banner_color_box, banner_color_text, banner_color_link_text'
		],
		'banner_settings_button' => [
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.palette.banner_settings_button',
			'showitem' => 'banner_show_settings_button, --linebreak--,
				banner_button_settings_text, --linebreak--,
				banner_color_button_settings, banner_color_button_settings_hover, banner_color_button_settings_text'
		],
		'banner_accept_button' => [
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.palette.banner_accept_button',
			'showitem' => 'banner_button_accept_text, --linebreak--,
				banner_color_button_accept, banner_color_button_accept_hover, banner_color_button_accept_text'
		],
		'banner_accept_essential_button' => [
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.palette.banner_accept_essential_button',
			'showitem' => 'banner_button_accept_essential_text, --linebreak--,
				banner_color_button_accept_essential, banner_color_button_accept_essential_hover, banner_color_button_accept_essential_text'
		],
		'banner_template' => [
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.palette.banner_template',
			'showitem' => 'banner_overwritten, --linebreak--,
				banner_warning, --linebreak--,
				banner_html'
		],
		'cookie_lifetime_settings' => [
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.palette.cookie_lifetime_settings',
			'showitem' => 'cookie_lifetime, session_only_essential_cookies, --linebreak--,
				banner_show_again_interval'
		],
		'update_version' => [
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.palette.update_version',
			'showitem' => 'update_version_checkbox, version'
		],
		'multidomain' => [
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.palette.multidomain_settings',
			'showitem' => 'subdomain_support, set_cookie_for_domain, --linebreak--, domains_to_delete_cookies_for'
		],
		'consent_mode' => [
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.palette.consent_mode',
			'showitem' => 'disable_automatic_loading'
		],
	],
	'columns' => [
		'pid' => [
			'exclude' => FALSE,
			'label' => 'PID',
			'config' => [
				'type' => 'none',
			]
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
				'foreign_table' => 'tx_sgcookieoptin_domain_model_optin',
				'foreign_table_where' => 'AND tx_sgcookieoptin_domain_model_optin.uid=###REC_FIELD_l10n_parent### AND tx_sgcookieoptin_domain_model_optin.sys_language_uid IN (-1,0)',
				'default' => 0
			]
		],
		'l10n_diffsource' => [
			'config' => [
				'type' => 'passthrough',
				'default' => ''
			]
		],
		'header' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.header',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'default' => 'Datenschutzeinstellungen',
				'placeholder' => 'Datenschutzeinstellungen',
				'eval' => 'trim, required'
			],
		],
		'description' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.description',
			'config' => [
				'type' => 'text',
				'default' => \SGalinski\SgCookieOptin\Service\JsonImportService::TEXT_BANNER_DESCRIPTION,
				'placeholder' => \SGalinski\SgCookieOptin\Service\JsonImportService::TEXT_BANNER_DESCRIPTION,
				'eval' => 'trim'
			],
		],
		'save_confirmation_text' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.save_confirmation_text',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'default' => 'Cookie-Einstellungen erfolgreich gespeichert',
				'placeholder' => 'Cookie-Einstellungen erfolgreich gespeichert',
				'eval' => 'trim, required'
			],
		],
		'dependent_groups_text' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.dependent_groups_text',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'default' => 'Abhängig von:',
				'placeholder' => 'Abhängig von:',
				'eval' => 'trim, required'
			],
		],
		'user_hash_text' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.user_hash_text',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'default' => 'User-Hash',
				'placeholder' => 'User-Hash',
				'eval' => 'trim, required'
			],
		],
		'accept_all_text' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.accept_all_text',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'default' => 'Alle akzeptieren',
				'placeholder' => 'Alle akzeptieren',
				'eval' => 'trim, required'
			],
		],
		'accept_specific_text' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.accept_specific_text',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'default' => 'Speichern & schließen',
				'placeholder' => 'Speichern & schließen',
				'eval' => 'trim, required'
			],
		],
		'accept_essential_text' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.accept_essential_text',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'default' => 'Nur essenzielle Cookies akzeptieren',
				'placeholder' => 'Nur essenzielle Cookies akzeptieren',
				'eval' => 'trim, required'
			],
		],
		'extend_box_link_text' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.extend_box_link_text',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'default' => 'Weitere Informationen anzeigen',
				'placeholder' => 'Weitere Informationen anzeigen',
				'eval' => 'trim, required'
			],
		],
		'extend_box_link_text_close' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.extend_box_link_text_close',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'default' => 'Weitere Informationen verstecken',
				'placeholder' => 'Weitere Informationen verstecken',
				'eval' => 'trim, required'
			],
		],
		'extend_table_link_text' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.extend_table_link_text',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'default' => 'Cookie-Informationen anzeigen',
				'placeholder' => 'Cookie-Informationen anzeigen',
				'eval' => 'trim, required'
			],
		],
		'extend_table_link_text_close' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.extend_table_link_text_close',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'default' => 'Cookie-Informationen verstecken',
				'placeholder' => 'Cookie-Informationen verstecken',
				'eval' => 'trim, required'
			],
		],
		'cookie_name_text' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.cookie_name_text',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'default' => 'Name',
				'placeholder' => 'Name',
				'eval' => 'trim, required'
			],
		],
		'cookie_provider_text' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.cookie_provider_text',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'default' => 'Anbieter',
				'placeholder' => 'Anbieter',
				'eval' => 'trim, required'
			],
		],
		'cookie_purpose_text' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.cookie_purpose_text',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'default' => 'Zweck',
				'placeholder' => 'Zweck',
				'eval' => 'trim, required'
			],
		],
		'cookie_lifetime_text' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.cookie_lifetime_text',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'default' => 'Laufzeit',
				'placeholder' => 'Laufzeit',
				'eval' => 'trim, required'
			],
		],
		'navigation' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.navigation',
			'config' => [
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'pages',
				'minitems' => 0,
				'maxitems' => 2,
				'wizards' => [
					'suggest' => [
						'type' => 'suggest'
					]
				],
			],
		],
		'groups' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.groups',
			'config' => [
				'type' => 'inline',
				'foreign_table' => 'tx_sgcookieoptin_domain_model_group',
				'foreign_field' => 'parent_optin',
				'foreign_sortby' => 'sorting',
				'appearance' => [
					'showPossibleLocalizationRecords' => TRUE,
					'showRemovedLocalizationRecords' => FALSE,
					'showAllLocalizationLink' => TRUE,
				],
				'maxitems' => 99999,
			],
		],
		'color_box' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_box',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#FFFFFF',
				'placeholder' => '#FFFFFF',
				'eval' => 'trim, required'
			],
		],
		'color_headline' => [
			'displayCond' => 'FIELD:template_selection:=:0',
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_headline',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#373737',
				'placeholder' => '#373737',
				'eval' => 'trim, required'
			],
		],
		'color_text' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_text',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#373737',
				'placeholder' => '#373737',
				'eval' => 'trim, required'
			],
		],
		'color_confirmation_background' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_confirmation_background',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#2E6B96',
				'placeholder' => '#2E6B96',
				'eval' => 'trim, required'
			]
		],
		'color_confirmation_text' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_confirmation_text',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#FFFFFF',
				'placeholder' => '#FFFFFF',
				'eval' => 'trim, required'
			]
		],
		'color_checkbox' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_checkbox',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#143D59',
				'placeholder' => '#143D59',
				'eval' => 'trim, required'
			],
		],
		'color_checkbox_required' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_checkbox_required',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#575757',
				'placeholder' => '#575757',
				'eval' => 'trim, required'
			],
		],
		'color_button_all' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_button_all',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#143D59',
				'placeholder' => '#143D59',
				'eval' => 'trim, required'
			],
		],
		'color_button_all_hover' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_button_all_hover',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#2E6B96',
				'placeholder' => '#2E6B96',
				'eval' => 'trim, required'
			],
		],
		'color_button_all_text' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_button_all_text',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#FFFFFF',
				'placeholder' => '#FFFFFF',
				'eval' => 'trim, required'
			],
		],
		'color_button_specific' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_button_specific',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#575757',
				'placeholder' => '#575757',
				'eval' => 'trim, required'
			],
		],
		'color_button_specific_hover' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_button_specific_hover',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#929292',
				'placeholder' => '#929292',
				'eval' => 'trim, required'
			],
		],
		'color_button_specific_text' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_button_specific_text',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#FFFFFF',
				'placeholder' => '#FFFFFF',
				'eval' => 'trim, required'
			],
		],
		'color_button_essential' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_button_essential',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#575757',
				'placeholder' => '#575757',
				'eval' => 'trim, required'
			],
		],
		'color_button_essential_hover' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_button_essential_hover',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#929292',
				'placeholder' => '#929292',
				'eval' => 'trim, required'
			],
		],
		'color_button_essential_text' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_button_essential_text',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#FFFFFF',
				'placeholder' => '#FFFFFF',
				'eval' => 'trim, required'
			],
		],
		'color_list' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_list',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#575757',
				'placeholder' => '#575757',
				'eval' => 'trim, required'
			],
			'displayCond' => 'FIELD:template_selection:=:0',
		],
		'color_list_text' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_list_text',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#FFFFFF',
				'placeholder' => '#FFFFFF',
				'eval' => 'trim, required'
			],
			'displayCond' => 'FIELD:template_selection:=:0',
		],
		'color_table' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_table',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#FFFFFF',
				'placeholder' => '#FFFFFF',
				'eval' => 'trim, required'
			],
		],
		'color_table_header' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_table_header',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#F3F3F3',
				'placeholder' => '#F3F3F3',
				'eval' => 'trim, required'
			],
		],
		'color_table_header_text' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_table_header_text',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#373737',
				'placeholder' => '#373737',
				'eval' => 'trim, required'
			],
		],
		'color_Table_data_text' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_Table_data_text',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#373737',
				'placeholder' => '#373737',
				'eval' => 'trim, required'
			],
		],
		'color_button_close' => [
			'displayCond' => 'FIELD:template_selection:=:0',
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_button_close',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#FFFFFF',
				'placeholder' => '#FFFFFF',
				'eval' => 'trim, required'
			],
		],
		'color_button_close_hover' => [
			'displayCond' => 'FIELD:template_selection:=:0',
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_button_close_hover',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#FFFFFF',
				'placeholder' => '#FFFFFF',
				'eval' => 'trim, required'
			],
		],
		'color_button_close_text' => [
			'displayCond' => 'FIELD:template_selection:=:0',
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_button_close_text',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#373737',
				'placeholder' => '#373737',
				'eval' => 'trim, required'
			],
		],
		'monochrome_enabled' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.monochrome_enabled.title',
			'description' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.monochrome_enabled.description',
			'config' => [
				'type' => 'check',
				'default' => '0',
			],
		],
		'essential_title' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.essential_title',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'default' => 'Essenziell',
				'placeholder' => 'Essenziell',
				'eval' => 'trim, required'
			],
		],
		'essential_description' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.essential_description',
			'config' => [
				'type' => 'text',
				'default' => \SGalinski\SgCookieOptin\Service\JsonImportService::TEXT_ESSENTIAL_DESCRIPTION,
				'placeholder' => \SGalinski\SgCookieOptin\Service\JsonImportService::TEXT_ESSENTIAL_DESCRIPTION,
				'eval' => 'trim'
			],
		],
		'essential_scripts' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.essential_scripts',
			'description' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.essential_scripts.description',
			'config' => [
				'type' => 'inline',
				'foreign_table' => 'tx_sgcookieoptin_domain_model_script',
				'foreign_field' => 'parent_optin',
				'foreign_sortby' => 'sorting',
				'appearance' => [
					'showPossibleLocalizationRecords' => TRUE,
					'showRemovedLocalizationRecords' => FALSE,
					'showAllLocalizationLink' => TRUE,
				],
				'maxitems' => 99999,
			],
		],
		'essential_cookies' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.essential_cookies',
			'config' => [
				'type' => 'inline',
				'foreign_table' => 'tx_sgcookieoptin_domain_model_cookie',
				'foreign_field' => 'parent_optin',
				'foreign_sortby' => 'sorting',
				'appearance' => [
					'showPossibleLocalizationRecords' => TRUE,
					'showRemovedLocalizationRecords' => FALSE,
					'showAllLocalizationLink' => TRUE,
				],
				'maxitems' => 99999,
				'minitems' => 1,
			],
		],
		'iframe_enabled' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_enabled',
			'config' => [
				'type' => 'check',
				'default' => '0',
			],
		],
		'iframe_title' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_title',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'default' => 'Externe Inhalte',
				'placeholder' => 'Externe Inhalte',
				'eval' => 'trim, required'
			],
		],
		'iframe_description' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_description',
			'config' => [
				'type' => 'text',
				'default' => \SGalinski\SgCookieOptin\Service\JsonImportService::TEXT_IFRAME_DESCRIPTION,
				'placeholder' => \SGalinski\SgCookieOptin\Service\JsonImportService::TEXT_IFRAME_DESCRIPTION,
				'eval' => 'trim'
			],
		],
		'iframe_cookies' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_cookies',
			'config' => [
				'type' => 'inline',
				'foreign_table' => 'tx_sgcookieoptin_domain_model_cookie',
				'foreign_field' => 'parent_iframe',
				'foreign_sortby' => 'sorting',
				'appearance' => [
					'showPossibleLocalizationRecords' => TRUE,
					'showRemovedLocalizationRecords' => FALSE,
					'showAllLocalizationLink' => TRUE,
				],
				'maxitems' => 99999,
			],
		],
		'iframe_button_allow_all_text' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_button_allow_all_text',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'default' => 'Alle externen Inhalte erlauben',
				'placeholder' => 'Alle externen Inhalte erlauben',
				'eval' => 'trim, required'
			],
		],
		'iframe_button_allow_one_text' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_button_allow_one_text',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'default' => 'Einmalig erlauben',
				'placeholder' => 'Einmalig erlauben',
				'eval' => 'trim, required'
			],
		],
		'iframe_button_reject_text' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_button_reject_text',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'default' => 'Nicht erlauben',
				'placeholder' => 'Nicht erlauben',
				'eval' => 'trim, required'
			],
		],
		'iframe_button_load_one_description' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_button_load_one_description',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'default' => '',
				'placeholder' => '',
				'eval' => 'trim'
			],
		],

		'iframe_button_load_one_text' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_button_load_one_text',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'default' => 'Externen Inhalt laden',
				'placeholder' => 'Externen Inhalt laden',
				'eval' => 'trim, required'
			],
		],
		'iframe_open_settings_text' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_open_settings_text',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'default' => 'Einstellungen anzeigen',
				'placeholder' => 'Einstellungen anzeigen',
				'eval' => 'trim, required'
			],
		],
		'iframe_color_consent_box_background' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_color_consent_box_background',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#D6D6D6',
				'placeholder' => '#D6D6D6',
				'eval' => 'trim, required'
			],
		],
		'iframe_color_button_load_one' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_color_button_load_one',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#143D59',
				'placeholder' => '#143D59',
				'eval' => 'trim, required'
			],
		],
		'iframe_color_button_load_one_hover' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_color_button_load_one_hover',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#2E6B96',
				'placeholder' => '#2E6B96',
				'eval' => 'trim, required'
			],
		],
		'iframe_color_button_load_one_text' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_color_button_load_one_text',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#FFFFFF',
				'placeholder' => '#FFFFFF',
				'eval' => 'trim, required'
			],
		],
		'iframe_color_open_settings' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_color_open_settings',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#373737',
				'placeholder' => '#373737',
				'eval' => 'trim, required'
			],
		],
		'iframe_html' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_html',
			'description' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_html.description',
			'config' => [
				'type' => 'text',
				'renderType' => 't3editor',
				'format' => 'html',
				'eval' => 'trim',
				'behaviour' => [
					'allowLanguageSynchronization' => TRUE
				],
			],
		],
		'iframe_overwritten' => [
			'exclude' => TRUE,
			'onChange' => 'reload',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_overwritten',
			'description' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_overwritten.description',
			'config' => [
				'type' => 'check',
				'default' => '0',
				'behaviour' => [
					'allowLanguageSynchronization' => TRUE
				],
			],
		],
		'iframe_warning' => [
			'displayCond' => 'FIELD:iframe_overwritten:=:0',
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'config' => [
				'type' => 'none',
				'renderType' => 'SgCookieOptinTCAWarningField'
			]
		],
		'iframe_replacement_html' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_replacement_html',
			'description' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_replacement_html.description',
			'config' => [
				'type' => 'text',
				'renderType' => 't3editor',
				'format' => 'html',
				'eval' => 'trim',
				'default' => '',
				'behaviour' => [
					'allowLanguageSynchronization' => TRUE
				],
			],
		],
		'iframe_replacement_overwritten' => [
			'exclude' => TRUE,
			'onChange' => 'reload',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_replacement_overwritten',
			'description' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_overwritten.description',
			'config' => [
				'type' => 'check',
				'default' => '0',
				'behaviour' => [
					'allowLanguageSynchronization' => TRUE
				],
			],

		],
		'iframe_replacement_warning' => [
			'displayCond' => 'FIELD:iframe_replacement_overwritten:=:0',
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'config' => [
				'type' => 'none',
				'renderType' => 'SgCookieOptinTCAWarningField'
			]
		],
		'iframe_replacement_background_image' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_replacement_background_image',
			'description' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_replacement_background_image.description',
			'config' => [
				'type' => 'input',
				'eval' => 'trim',
				'behaviour' => [
					'allowLanguageSynchronization' => TRUE
				],
				'default' => '',
			],
		],
		'cookie_lifetime' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.cookie_lifetime',
			'l10n_mode' => 'exclude',
			'config' => [
				'type' => 'input',
				'default' => '365',
				'placeholder' => '365',
				'eval' => 'trim, int, required'
			],
		],
		'session_only_essential_cookies' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.session_only_essential_cookies',
			'config' => [
				'type' => 'check',
				'default' => '0',
			],
		],
		'overwrite_baseurl' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.overwrite_baseurl',
			'description' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.overwrite_baseurl.description',
			'config' => [
				'type' => 'input',
				'default' => '',
				'eval' => 'trim'
			],
		],
		'minify_generated_data' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.minify_generated_data',
			'config' => [
				'type' => 'check',
				'default' => '1',
			],
		],
		'auto_action_for_bots' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.auto_action_for_bots',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'minitems' => 1,
				'items' => [
					['LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.auto_action_for_bots.0', 0],
					['LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.auto_action_for_bots.1', 1],
					['LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.auto_action_for_bots.2', 2],
				],
				'default' => '0'
			],
		],
		'template_html' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.template_html',
			'description' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.template_html.description',
			'config' => [
				'type' => 'text',
				'renderType' => 't3editor',
				'format' => 'html',
				'eval' => 'trim',
				'fieldWizard' => [
					'templatePreviewLinkWizard' => [
						'renderType' => 'templatePreviewLinkWizard',
					],
				],
				'default' => '',
				'behaviour' => [
					'allowLanguageSynchronization' => TRUE
				],
			],
		],
		'template_overwritten' => [
			'exclude' => TRUE,
			'onChange' => 'reload',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.template_overwritten',
			'description' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.template_overwritten.description',
			'config' => [
				'type' => 'check',
				'default' => '0',
				'behaviour' => [
					'allowLanguageSynchronization' => TRUE
				],
			],
		],
		'template_warning' => [
			'displayCond' => 'FIELD:template_overwritten:=:0',
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'config' => [
				'type' => 'none',
				'renderType' => 'SgCookieOptinTCAWarningField'
			]
		],
		'template_selection' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.template_selection',
			'description' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.template_selection.description',
			'onChange' => 'reload',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'minitems' => 1,
				'default' => 0,
				'items' => [
					['LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.template_selection.0', 0],
					['LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.template_selection.1', 1],
				],
				'behaviour' => [
					'allowLanguageSynchronization' => TRUE
				],
			],
		],
		'disable_powered_by' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.disable_powered_by',
			'config' => [
				'type' => 'check',
				'default' => '0',
			],
		],
		'disable_for_this_language' => [
			'exclude' => FALSE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.disable_for_this_language',
			'config' => [
				'type' => 'check',
				'default' => '0',
			],
		],
		'render_assets_inline' => [
			'exclude' => FALSE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.render_assets_inline',
			'description' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.render_assets_inline.description',
			'config' => [
				'type' => 'check',
				'default' => '0',
			],
		],
		'consider_do_not_track' => [
			'exclude' => FALSE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.consider_do_not_track',
			'config' => [
				'type' => 'check',
				'default' => '0',
			],
		],
		'subdomain_support' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.subdomain_support',
			'description' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.subdomain_support.description',
			'config' => [
				'type' => 'check',
				'default' => '0',
			],
		],
		'set_cookie_for_domain' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.set_cookie_for_domain',
			'description' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.set_cookie_for_domain.description',
			'config' => [
				'type' => 'input',
				'default' => '',
				'eval' => 'trim,domainname'
			],
		],
		'banner_show_again_interval' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.banner_show_again_interval',
			'config' => [
				'type' => 'input',
				'default' => '14',
				'eval' => 'trim, int'
			],
		],
		'banner_enable' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.banner_enable',
			'config' => [
				'type' => 'check',
				'default' => '0',
			],
		],
		'banner_force_min_width' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.banner_force_min_width',
			'config' => [
				'type' => 'input',
				'default' => '0',
				'eval' => 'trim, int',
				'size' => 5,
			],
		],
		'banner_html' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.banner_html',
			'config' => [
				'type' => 'text',
				'renderType' => 't3editor',
				'format' => 'html',
				'eval' => 'trim',
				'default' => '',
				'fieldWizard' => [
					'templatePreviewLinkWizard' => [
						'renderType' => 'templatePreviewLinkWizard'
					],
				],
				'behaviour' => [
					'allowLanguageSynchronization' => TRUE
				],
			],
		],
		'banner_overwritten' => [
			'exclude' => TRUE,
			'onChange' => 'reload',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.banner_overwritten',
			'description' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_overwritten.description',
			'config' => [
				'type' => 'check',
				'default' => '0',
				'behaviour' => [
					'allowLanguageSynchronization' => TRUE
				],
			],
		],
		'banner_warning' => [
			'displayCond' => 'FIELD:banner_overwritten:=:0',
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'config' => [
				'type' => 'none',
				'renderType' => 'SgCookieOptinTCAWarningField'
			]
		],
		'banner_show_settings_button' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.banner_show_settings_button',
			'description' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.banner_show_settings_button.description',
			'config' => [
				'type' => 'check',
				'default' => '1',
			],
		],
		'banner_position' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.banner_position',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'minitems' => 1,
				'items' => [
					['LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.banner_position.0', 0],
					['LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.banner_position.1', 1],
				],
			],
		],
		'banner_color_box' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.banner_color_box',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#DDDDDD',
				'placeholder' => '#DDDDDD',
				'eval' => 'trim, required'
			],
		],
		'banner_color_text' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.banner_color_text',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#373737',
				'placeholder' => '#373737',
				'eval' => 'trim, required'
			],
		],
		'banner_color_link_text' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.banner_color_link_text',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#373737',
				'placeholder' => '#373737',
				'eval' => 'trim, required'
			],
		],
		'banner_color_button_settings' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.banner_color_button_settings',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#575757',
				'placeholder' => '#575757',
				'eval' => 'trim, required'
			],
		],
		'banner_color_button_settings_hover' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.banner_color_button_settings_hover',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#929292',
				'placeholder' => '#929292',
				'eval' => 'trim, required'
			],
		],
		'banner_color_button_settings_text' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.banner_color_button_settings_text',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#FFFFFF',
				'placeholder' => '#FFFFFF',
				'eval' => 'trim, required'
			],
		],
		'banner_color_button_accept' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.banner_color_button_accept',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#143D59',
				'placeholder' => '#143D59',
				'eval' => 'trim, required'
			],
		],
		'banner_color_button_accept_hover' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.banner_color_button_accept_hover',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#2E6B96',
				'placeholder' => '#2E6B96',
				'eval' => 'trim, required'
			],
		],
		'banner_color_button_accept_text' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.banner_color_button_accept_text',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#FFFFFF',
				'placeholder' => '#FFFFFF',
				'eval' => 'trim, required'
			],
		],
		'banner_button_accept_text' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.banner_button_accept_text',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'default' => 'Akzeptieren',
				'placeholder' => 'Akzeptieren',
				'eval' => 'trim, required'
			],
		],
		'banner_color_button_accept_essential' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.banner_color_button_accept_essential',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#575757',
				'placeholder' => '#575757',
				'eval' => 'trim, required'
			],
		],
		'banner_color_button_accept_essential_hover' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.banner_color_button_accept_essential_hover',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#929292',
				'placeholder' => '#929292',
				'eval' => 'trim, required'
			],
		],
		'banner_color_button_accept_essential_text' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.banner_color_button_accept_essential_text',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#FFFFFF',
				'placeholder' => '#FFFFFF',
				'eval' => 'trim, required'
			],
		],
		'banner_button_accept_essential_text' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.banner_button_accept_essential_text',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'default' => 'Ablehnen',
				'placeholder' => 'Ablehnen',
				'eval' => 'trim, required'
			],
		],
		'banner_button_settings_text' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.banner_button_settings_text',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'default' => 'Einstellungen',
				'placeholder' => 'Einstellungen',
				'eval' => 'trim, required'
			],
		],
		'banner_description' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.banner_description',
			'config' => [
				'type' => 'text',
				'default' => \SGalinski\SgCookieOptin\Service\JsonImportService::TEXT_BANNER_DESCRIPTION,
				'placeholder' => \SGalinski\SgCookieOptin\Service\JsonImportService::TEXT_BANNER_DESCRIPTION,
				'eval' => 'trim'
			],
		],
		'show_button_close' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.show_button_close',
			'config' => [
				'type' => 'check',
				'default' => '0',
			],
		],
		'activate_testing_mode' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.activate_testing_mode',
			'description' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.activate_testing_mode.description',
			'config' => [
				'type' => 'check',
				'default' => '0',
			],
		],
		'color_full_box' => [
			'displayCond' => 'FIELD:template_selection:=:1',
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_full_box',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#143D59',
				'placeholder' => '#143D59',
				'eval' => 'trim, required'
			],
		],
		'color_full_headline' => [
			'displayCond' => 'FIELD:template_selection:=:1',
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_full_headline',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#FFFFFF',
				'placeholder' => '#FFFFFF',
				'eval' => 'trim, required'
			],
		],
		'color_full_text' => [
			'displayCond' => 'FIELD:template_selection:=:1',
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_full_text',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#FFFFFF',
				'placeholder' => '#FFFFFF',
				'eval' => 'trim, required'
			],
		],
		'color_full_button_close' => [
			'displayCond' => 'FIELD:template_selection:=:1',
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_button_close',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#143D59',
				'placeholder' => '#143D59',
				'eval' => 'trim, required'
			],
		],
		'color_full_button_close_hover' => [
			'displayCond' => 'FIELD:template_selection:=:1',
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_button_close_hover',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#143D59',
				'placeholder' => '#143D59',
				'eval' => 'trim, required'
			],
		],
		'color_full_button_close_text' => [
			'displayCond' => 'FIELD:template_selection:=:1',
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_button_close_text',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#FFFFFF',
				'placeholder' => '#FFFFFF',
				'eval' => 'trim, required'
			],
		],
		'iframe_whitelist_regex' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_whitelist_regex',
			'description' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_whitelist_regex.description',
			'config' => [
				'type' => 'text',
				'renderType' => 't3editor',
				'eval' => 'trim',
			],
		],
		'domains_to_delete_cookies_for' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.domains_to_delete_cookies_for',
			'config' => [
				'type' => 'text',
				'renderType' => 't3editor',
				'eval' => 'trim',
				'default' => ''
			],
		],
		'cookiebanner_whitelist_regex' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.cookiebanner_whitelist_regex',
			'description' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.cookiebanner_whitelist_regex.description',
			'config' => [
				'type' => 'text',
				'renderType' => 't3editor',
				'eval' => 'trim',
				'default' => ''
			],
		],
		'version' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.version',
			'config' => [
				'type' => 'input',
				'default' => '1',
				'readOnly' => '1',
				'eval' => 'trim, int, required'
			],
		],
		'update_version_checkbox' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.update_version',
			'config' => [
				'type' => 'check',
				'default' => '0',
			],
		],
		'unified_cookie_name' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.unified_cookie_name',
			'description' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.unified_cookie_name.description',
			'config' => [
				'type' => 'check',
				'default' => '1',
			],
		],
		'disable_usage_statistics' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.disable_usage_statistics',
			'config' => [
				'type' => 'check',
				'default' => '0',
			],
		],
		'show_fingerprint' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.show_fingerprint',
			'config' => [
				'type' => 'check',
				'default' => '1',
			],
		],
		'fingerprint_position' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.fingerprint_position',
			'default' => 1,
			'l10n_mode' => 'exclude',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'default' => 1,
				'items' => [
					[
						'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.fingerprint_position.0',
						0,
					],
					[
						'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.fingerprint_position.1',
						1,
					],
					[
						'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.fingerprint_position.2',
						2,
					],
					[
						'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.fingerprint_position.3',
						3,
					],
					[
						'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.fingerprint_position.4',
						4,
					],
				]
			]
		],
		'color_fingerprint_background' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_fingerprint_background',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#143D59',
				'placeholder' => '#208A20',
				'eval' => 'trim, required'
			]
		],
		'color_fingerprint_image' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_fingerprint_image',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#FFFFFF',
				'placeholder' => '#208A20',
				'eval' => 'trim, required'
			]
		],
		'disable_automatic_loading' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.disable_automatic_loading',
			'description' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.disable_automatic_loading.description',
			'config' => [
				'type' => 'check',
				'default' => '0',
			],
		],
		'services' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.services',
			'config' => [
				'type' => 'inline',
				'foreign_table' => 'tx_sgcookieoptin_domain_model_service',
				'foreign_field' => 'parent_optin',
				'foreign_sortby' => 'sorting',
				'appearance' => [
					'showPossibleLocalizationRecords' => TRUE,
					'showRemovedLocalizationRecords' => FALSE,
					'showAllLocalizationLink' => TRUE,
				],
				'maxitems' => 99999,
			],
		],
	],
];

if (version_compare(\TYPO3\CMS\Core\Utility\VersionNumberUtility::getCurrentTypo3Version(), '10.3.0', '<')) {
	$configuration['interface']['showRecordFieldList'] = 'sys_language_uid, l10n_parent, l10n_diffsource, header,'
		. 'description, cookie_lifetime, overwrite_baseurl, minify_generated_data, navigation, accept_all_text, user_hash_text, accept_specific_text,'
		. 'accept_essential_text, groups, template_html, template_overwritten, template_warning, template_selection, color_text,'
		. 'color_box, color_headline, color_checkbox, color_checkbox_required, color_button_all, color_button_all_text,'
		. 'color_button_specific, color_button_specific_text, color_button_essential, color_button_essential_text,'
		. 'color_list, color_list_text, essential_title, essential_description, essential_scripts, essential_cookies,'
		. 'extend_box_link_text, extend_box_link_text_close, extend_table_link_text, extend_table_link_text_close,'
		. 'color_button_all_hover, color_button_specific_hover, color_button_essential_hover, color_table,'
		. 'color_table_header_text, color_Table_data_text, color_button_close, color_button_close_hover,'
		. 'color_button_close_text, cookie_name_text, cookie_provider_text, cookie_purpose_text, cookie_lifetime_text,'
		. 'iframe_enabled, iframe_title, iframe_description, iframe_cookies, iframe_button_allow_all_text,'
		. 'iframe_button_allow_one_text, iframe_button_reject_text, iframe_button_load_one_description, iframe_button_load_one_text, iframe_open_settings_text,'
		. 'iframe_color_consent_box_background, iframe_color_button_load_one, iframe_color_button_load_one_hover,'
		. 'iframe_color_button_load_one_text, iframe_color_open_settings, iframe_html, iframe_overwritten, iframe_warning, '
		. 'iframe_replacement_html, iframe_replacement_overwritten, iframe_replacement_warning, '
		. 'banner_enable, banner_force_min_width, banner_position, banner_overwritten, banner_warning, banner_html,'
		. 'banner_show_settings_button, banner_color_box, banner_color_text, banner_color_button_settings,'
		. 'banner_color_button_settings_hover, banner_color_button_settings_text, banner_color_button_accept,'
		. 'banner_color_button_accept_hover, banner_color_button_accept_text, banner_color_link_text,'
		. 'banner_button_accept_text, banner_button_settings_text, banner_description, show_button_close,'
		. 'activate_testing_mode, color_full_box, color_full_headline, color_full_text, color_full_button_close,'
		. 'color_full_button_close_hover, color_full_button_close_text, color_table_header, save_confirmation_text,'
		. 'color_confirmation_background, color_confirmation_text, session_only_essential_cookies, iframe_whitelist,'
		. 'iframe_whitelist_regex, dependent_groups_text,'
		. 'subdomain_support, set_cookie_for_domain, domains_to_delete_cookies_for, cookiebanner_whitelist_regex,'
		. 'disable_powered_by, disable_for_this_language, render_assets_inline, consider_do_not_track,'
		. 'banner_show_again_interval, version, unified_cookie_name, disable_usage_statistics, fingerprint_position,'
		. 'color_fingerprint_background, color_fingerprint_image, services, iframe_replacement_background_image,'
		. 'monochrome_enabled, show_fingerprint, auto_action_for_bots';
}

return $configuration;
