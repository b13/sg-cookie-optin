CREATE TABLE tx_sgcookieoptin_domain_model_optin (
	uid                                        int(11)                                                NOT NULL auto_increment,
	pid                                        int(11) unsigned    DEFAULT '0'                        NOT NULL,

	-- general columns
	header                                     varchar(255)        DEFAULT 'Datenschutzeinstellungen' NOT NULL,
	description                                text                                                   NOT NULL,
	navigation                                 varchar(255)        DEFAULT ''                         NOT NULL,
	groups                                     int(11)             DEFAULT '0'                        NOT NULL,

	-- Warning fields
	iframe_warning                             varchar(255)                                           NOT NULL DEFAULT '',
	iframe_whitelist_warning                   varchar(255)                                           NOT NULL DEFAULT '',
	iframe_replacement_warning                 varchar(255)                                           NOT NULL DEFAULT '',
	banner_warning                             varchar(255)                                           NOT NULL DEFAULT '',
	template_warning                           varchar(255)                                           NOT NULL DEFAULT '',

	-- general texts
	accept_all_text                            text                                                   NOT NULL,
	accept_specific_text                       text                                                   NOT NULL,
	accept_essential_text                      text                                                   NOT NULL,
	extend_box_link_text                       text                                                   NOT NULL,
	extend_box_link_text_close                 text                                                   NOT NULL,
	extend_table_link_text                     text                                                   NOT NULL,
	extend_table_link_text_close               text                                                   NOT NULL,
	cookie_name_text                           text                                                   NOT NULL,
	cookie_provider_text                       text                                                   NOT NULL,
	cookie_purpose_text                        text                                                   NOT NULL,
	cookie_lifetime_text                       text                                                   NOT NULL,
	save_confirmation_text                     text                                                   NOT NULL,
	dependent_groups_text                      varchar(255)        DEFAULT 'Abhängig von:'            NOT NULL,
	user_hash_text                             varchar(255)        DEFAULT 'User-Hash'                NOT NULL,

	-- template
	template_html                              text                                                   NOT NULL,
	template_overwritten                       tinyint(4) unsigned DEFAULT '0'                        NOT NULL,
	template_selection                         int(11)             DEFAULT '0'                        NOT NULL,
	disable_powered_by                         tinyint(4) unsigned DEFAULT '0'                        NOT NULL,
	monochrome_enabled                         tinyint(4) unsigned DEFAULT '0'                        NOT NULL,

	-- banner
	banner_enable                              tinyint(4) unsigned DEFAULT '0'                        NOT NULL,
	banner_html                                text                                                   NOT NULL,
	banner_overwritten                         tinyint(4) unsigned DEFAULT '0'                        NOT NULL,
	banner_show_settings_button                tinyint(4) unsigned DEFAULT '1'                        NOT NULL,
	banner_position                            int(11)             DEFAULT '0'                        NOT NULL,
	banner_color_box                           varchar(10)         DEFAULT '#DDDDDD'                  NOT NULL,
	banner_color_text                          varchar(10)         DEFAULT '#373737'                  NOT NULL,
	banner_color_link_text                     varchar(10)         DEFAULT '#373737'                  NOT NULL,
	banner_color_button_settings               varchar(10)         DEFAULT '#575757'                  NOT NULL,
	banner_color_button_settings_hover         varchar(10)         DEFAULT '#929292'                  NOT NULL,
	banner_color_button_settings_text          varchar(10)         DEFAULT '#FFFFFF'                  NOT NULL,
	banner_color_button_accept                 varchar(10)         DEFAULT '#143D59'                  NOT NULL,
	banner_color_button_accept_hover           varchar(10)         DEFAULT '#2E6B96'                  NOT NULL,
	banner_color_button_accept_text            varchar(10)         DEFAULT '#FFFFFF'                  NOT NULL,
	banner_color_button_accept_essential       varchar(10)         DEFAULT '#575757'                  NOT NULL,
	banner_color_button_accept_essential_hover varchar(10)         DEFAULT '#929292'                  NOT NULL,
	banner_color_button_accept_essential_text  varchar(10)         DEFAULT '#FFFFFF'                  NOT NULL,
	banner_button_accept_text                  text                                                   NOT NULL,
	banner_button_accept_essential_text        text                                                   NOT NULL,
	banner_button_settings_text                text                                                   NOT NULL,
	banner_description                         text                                                   NOT NULL,
	banner_force_min_width                     int(11)             DEFAULT '0'                        NOT NULL,

	-- template colors
	color_box                                  varchar(10)         DEFAULT '#FFFFFF'                  NOT NULL,
	color_headline                             varchar(10)         DEFAULT '#373737'                  NOT NULL,
	color_text                                 varchar(10)         DEFAULT '#373737'                  NOT NULL,
	color_confirmation_background              varchar(10)         DEFAULT '#2E6B96'                  NOT NULL,
	color_confirmation_text                    varchar(10)         DEFAULT '#FFFFFF'                  NOT NULL,
	color_checkbox                             varchar(10)         DEFAULT '#143D59'                  NOT NULL,
	color_checkbox_required                    varchar(10)         DEFAULT '#575757'                  NOT NULL,
	color_button_all                           varchar(10)         DEFAULT '#143D59'                  NOT NULL,
	color_button_all_hover                     varchar(10)         DEFAULT '#2E6B96'                  NOT NULL,
	color_button_all_text                      varchar(10)         DEFAULT '#FFFFFF'                  NOT NULL,
	color_button_specific                      varchar(10)         DEFAULT '#575757'                  NOT NULL,
	color_button_specific_hover                varchar(10)         DEFAULT '#929292'                  NOT NULL,
	color_button_specific_text                 varchar(10)         DEFAULT '#FFFFFF'                  NOT NULL,
	color_button_essential                     varchar(10)         DEFAULT '#575757'                  NOT NULL,
	color_button_essential_hover               varchar(10)         DEFAULT '#929292'                  NOT NULL,
	color_button_essential_text                varchar(10)         DEFAULT '#FFFFFF'                  NOT NULL,
	color_list                                 varchar(10)         DEFAULT '#575757'                  NOT NULL,
	color_list_text                            varchar(10)         DEFAULT '#FFFFFF'                  NOT NULL,
	color_table                                varchar(10)         DEFAULT '#FFFFFF'                  NOT NULL,
	color_Table_data_text                      varchar(10)         DEFAULT '#373737'                  NOT NULL,
	color_table_header                         varchar(10)         DEFAULT '#F3F3F3'                  NOT NULL,
	color_table_header_text                    varchar(10)         DEFAULT '#373737'                  NOT NULL,
	color_button_close                         varchar(10)         DEFAULT '#FFFFFF'                  NOT NULL,
	color_button_close_hover                   varchar(10)         DEFAULT '#FFFFFF'                  NOT NULL,
	color_button_close_text                    varchar(10)         DEFAULT '#373737'                  NOT NULL,

	-- Template Full
	color_full_box                             varchar(10)         DEFAULT '#143D59'                  NOT NULL,
	color_full_headline                        varchar(10)         DEFAULT '#FFFFFF'                  NOT NULL,
	color_full_text                            varchar(10)         DEFAULT '#FFFFFF'                  NOT NULL,
	color_full_button_close                    varchar(10)         DEFAULT '#143D59'                  NOT NULL,
	color_full_button_close_hover              varchar(10)         DEFAULT '#143D59'                  NOT NULL,
	color_full_button_close_text               varchar(10)         DEFAULT '#FFFFFF'                  NOT NULL,

	-- Fingerpring settings
	color_fingerprint_background               varchar(10)         DEFAULT '#143D59'                  NOT NULL,
	color_fingerprint_image                    varchar(10)         DEFAULT '#FFFFFF'                  NOT NULL,
	fingerprint_position                       tinyint(1)          DEFAULT '1'                        NOT NULL,
	show_fingerprint                           tinyint(1)          DEFAULT '1'                        NOT NULL,

	-- Essential group specific columns
	essential_title                            text                                                   NOT NULL,
	essential_description                      text                                                   NOT NULL,
	essential_scripts                          int(11)             DEFAULT '0'                        NOT NULL,
	essential_cookies                          int(11)             DEFAULT '0'                        NOT NULL,

	-- IFrame group specific columns
	iframe_enabled                             tinyint(1) unsigned DEFAULT '0'                        NOT NULL,
	iframe_title                               text                                                   NOT NULL,
	iframe_description                         text                                                   NOT NULL,
	iframe_cookies                             int(11)             DEFAULT '0'                        NOT NULL,

	iframe_html                                text                                                   NOT NULL,
	iframe_overwritten                         tinyint(4) unsigned DEFAULT '0'                        NOT NULL,

	iframe_replacement_html                    text                                                   NOT NULL,
	iframe_replacement_overwritten             tinyint(4) unsigned DEFAULT '0'                        NOT NULL,
	iframe_replacement_background_image        text                                                   NOT NULL,

	iframe_whitelist_regex                     text                                                   NOT NULL,

	iframe_button_allow_all_text               text                                                   NOT NULL,
	iframe_button_allow_one_text               text                                                   NOT NULL,
	iframe_button_reject_text                  text                                                   NOT NULL,
	iframe_button_load_one_text                text                                                   NOT NULL,
	iframe_open_settings_text                  text                                                   NOT NULL,
	iframe_button_load_one_description         text                                                   NOT NULL,

	iframe_color_consent_box_background        varchar(10)         DEFAULT '#D6D6D6'                  NOT NULL,
	iframe_color_button_load_one               varchar(10)         DEFAULT '#143D59'                  NOT NULL,
	iframe_color_button_load_one_hover         varchar(10)         DEFAULT '#2E6B96'                  NOT NULL,
	iframe_color_button_load_one_text          varchar(10)         DEFAULT '#FFFFFF'                  NOT NULL,
	iframe_color_open_settings                 varchar(10)         DEFAULT '#373737'                  NOT NULL,
	services                                   int(11)             DEFAULT '0'                        NOT NULL,

	-- Settings
	cookie_lifetime                            int(11)             DEFAULT '365'                      NOT NULL,
	session_only_essential_cookies             tinyint(4) unsigned DEFAULT '0'                        NOT NULL,
	minify_generated_data                      tinyint(4) unsigned DEFAULT '1'                        NOT NULL,
	show_button_close                          tinyint(4) unsigned DEFAULT '0'                        NOT NULL,
	activate_testing_mode                      tinyint(4) unsigned DEFAULT '0'                        NOT NULL,
	banner_show_again_interval                 int(11) unsigned    DEFAULT '14'                       NOT NULL,
	disable_for_this_language                  tinyint(4) unsigned DEFAULT '0'                        NOT NULL,
	set_cookie_for_domain                      varchar(255)        DEFAULT ''                         NOT NULL,
	cookiebanner_whitelist_regex               text                                                   NOT NULL,
	version                                    int(11) unsigned    DEFAULT '1'                        NOT NULL,
	update_version_checkbox                    tinyint(4) unsigned DEFAULT '0'                        NOT NULL,
	render_assets_inline                       tinyint(4) unsigned DEFAULT '0'                        NOT NULL,
	consider_do_not_track                      tinyint(4) unsigned DEFAULT '0'                        NOT NULL,
	domains_to_delete_cookies_for              TEXT,
	subdomain_support                          tinyint(4)          DEFAULT '0',
	overwrite_baseurl                          TEXT,
	unified_cookie_name                        tinyint(4)          DEFAULT '1',
	disable_usage_statistics                   tinyint(4)          DEFAULT '0',
	disable_automatic_loading                  tinyint(4)          DEFAULT '0',
	auto_action_for_bots                       tinyint(4)          DEFAULT '0',

	-- TYPO3 related columns
	tstamp                                     int(11) unsigned    DEFAULT '0'                        NOT NULL,
	crdate                                     int(11) unsigned    DEFAULT '0'                        NOT NULL,
	cruser_id                                  int(11) unsigned    DEFAULT '0'                        NOT NULL,
	deleted                                    tinyint(4) unsigned DEFAULT '0'                        NOT NULL,

	sys_language_uid                           int(11)             DEFAULT '0'                        NOT NULL,
	l10n_parent                                int(11)             DEFAULT '0'                        NOT NULL,
	l10n_diffsource                            mediumblob,

	PRIMARY KEY (uid),
	KEY parent(pid),
	KEY language(l10n_parent, sys_language_uid)
);

CREATE TABLE tx_sgcookieoptin_domain_model_group (
	uid              int(11)                         NOT NULL auto_increment,
	pid              int(11) unsigned    DEFAULT '0' NOT NULL,

	title            varchar(255)        DEFAULT ''  NOT NULL,
	group_name       varchar(30)         DEFAULT ''  NOT NULL,
	description      text                            NOT NULL,
	parent_optin     int(11)             DEFAULT '0' NOT NULL,
	scripts          int(11)             DEFAULT '0' NOT NULL,
	cookies          int(11)             DEFAULT '0' NOT NULL,
	google_name      text                            NOT NULL,
	google_service   tinyint(4)          DEFAULT '0' NOT NULL,
	dependent_groups text                            NOT NULL,

	sorting          int(11) unsigned    DEFAULT '0' NOT NULL,
	tstamp           int(11) unsigned    DEFAULT '0' NOT NULL,
	crdate           int(11) unsigned    DEFAULT '0' NOT NULL,
	cruser_id        int(11) unsigned    DEFAULT '0' NOT NULL,
	deleted          tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden           tinyint(4) unsigned DEFAULT '0' NOT NULL,

	sys_language_uid int(11)             DEFAULT '0' NOT NULL,
	l10n_parent      int(11)             DEFAULT '0' NOT NULL,
	l10n_diffsource  mediumblob,

	PRIMARY KEY (uid),
	KEY parent(pid),
	KEY parent_optin(parent_optin),
	KEY language(l10n_parent, sys_language_uid)
);

CREATE TABLE tx_sgcookieoptin_domain_model_script (
	uid              int(11)                         NOT NULL auto_increment,
	pid              int(11) unsigned    DEFAULT '0' NOT NULL,

	title            varchar(255)        DEFAULT ''  NOT NULL,
	script           text                            NOT NULL,
	html             text                            NOT NULL,
	parent_group     int(11)             DEFAULT '0' NOT NULL,
	parent_optin     int(11)             DEFAULT '0' NOT NULL,

	sorting          int(11) unsigned    DEFAULT '0' NOT NULL,
	tstamp           int(11) unsigned    DEFAULT '0' NOT NULL,
	crdate           int(11) unsigned    DEFAULT '0' NOT NULL,
	cruser_id        int(11) unsigned    DEFAULT '0' NOT NULL,
	deleted          tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden           tinyint(4) unsigned DEFAULT '0' NOT NULL,

	sys_language_uid int(11)             DEFAULT '0' NOT NULL,
	l10n_parent      int(11)             DEFAULT '0' NOT NULL,
	l10n_diffsource  mediumblob,

	PRIMARY KEY (uid),
	KEY parent(pid),
	KEY parent_group(parent_group),
	KEY language(l10n_parent, sys_language_uid)
);

CREATE TABLE tx_sgcookieoptin_domain_model_cookie (
	uid              int(11)                         NOT NULL auto_increment,
	pid              int(11) unsigned    DEFAULT '0' NOT NULL,

	name             varchar(255)        DEFAULT ''  NOT NULL,
	provider         varchar(255)        DEFAULT ''  NOT NULL,
	purpose          text                            NOT NULL,
	lifetime         varchar(255)        DEFAULT ''  NOT NULL,
	parent_group     int(11)             DEFAULT '0' NOT NULL,
	parent_optin     int(11)             DEFAULT '0' NOT NULL,
	parent_iframe    int(11)             DEFAULT '0' NOT NULL,

	sorting          int(11) unsigned    DEFAULT '0' NOT NULL,
	tstamp           int(11) unsigned    DEFAULT '0' NOT NULL,
	crdate           int(11) unsigned    DEFAULT '0' NOT NULL,
	cruser_id        int(11) unsigned    DEFAULT '0' NOT NULL,
	deleted          tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden           tinyint(4) unsigned DEFAULT '0' NOT NULL,

	sys_language_uid int(11)             DEFAULT '0' NOT NULL,
	l10n_parent      int(11)             DEFAULT '0' NOT NULL,
	l10n_diffsource  mediumblob,

	PRIMARY KEY (uid),
	KEY parent(pid),
	KEY parent_group(parent_group),
	KEY language(l10n_parent, sys_language_uid)
);

CREATE TABLE tx_sgcookieoptin_domain_model_user_preference (
	uid             int(11)                      NOT NULL auto_increment,
	pid             int(11) unsigned DEFAULT '0' NOT NULL,

	date            DATE             DEFAULT NULL,
	tstamp          DATETIME                     NOT NULL,
	user_hash       VARCHAR(255)                 NOT NULL,
	preference_hash CHAR(22)                     NOT NULL,
	version         int(11) unsigned             NOT NULL,
	item_identifier varchar(30)                  NOT NULL,
	item_type       int(11) unsigned             NOT NULL,
	is_accepted     tinyint(4) unsigned          NOT NULL,
	is_all          tinyint(4) unsigned          NOT NULL,

	PRIMARY KEY (uid),
	KEY consent(pid, user_hash, item_type, item_identifier, date),
	KEY statistics(pid, item_type, item_identifier, is_accepted, version, date),
	KEY version(version, pid)
);

CREATE TABLE tx_sgcookieoptin_domain_model_service (
	uid                          int(11)                         NOT NULL auto_increment,
	pid                          int(11) unsigned    DEFAULT '0' NOT NULL,

	identifier                   varchar(255)        DEFAULT ''  NOT NULL,
	replacement_html_overwritten tinyint(4)          DEFAULT '0' NOT NULL,
	replacement_html             text                            NOT NULL,
	replacement_background_image text                            NOT NULL,
	source_regex                 text                            NOT NULL,
	parent_optin                 int(11)             DEFAULT '0' NOT NULL,

	sorting                      int(11) unsigned    DEFAULT '0' NOT NULL,
	tstamp                       int(11) unsigned    DEFAULT '0' NOT NULL,
	crdate                       int(11) unsigned    DEFAULT '0' NOT NULL,
	cruser_id                    int(11) unsigned    DEFAULT '0' NOT NULL,
	deleted                      tinyint(4) unsigned DEFAULT '0' NOT NULL,

	sys_language_uid             int(11)             DEFAULT '0' NOT NULL,
	l10n_parent                  int(11)             DEFAULT '0' NOT NULL,
	l10n_diffsource              mediumblob,

	PRIMARY KEY (uid),
	KEY parent(pid),
	KEY parent_optin(parent_optin),
	KEY language(l10n_parent, sys_language_uid)
);
