/*
 * Copyright notice
 *
 * (c) sgalinski Internet Services (https://www.sgalinski.de)
 *
 * Commercial license
 * You can buy a license key on the following site:
 * https://www.sgalinski.de/en/typo3-produkte-webentwicklung/sgalinski-cookie-optin/
 */

const SgCookieOptin = {

	COOKIE_NAME: 'cookie_optin',
	LAST_PREFERENCES_COOKIE_NAME: 'cookie_optin_last_preferences',
	LAST_PREFERENCES_LOCAL_STORAGE_NAME: 'SgCookieOptin.lastPreferences',
	COOKIE_GROUP_EXTERNAL_CONTENT: 'iframes',
	COOKIE_GROUP_ESSENTIAL: 'essential',

	/**
	 * The CSS selector to match possible external content
	 * @type {string}
	 */
	EXTERNAL_CONTENT_ELEMENT_SELECTOR: 'iframe, object, video, audio, [data-external-content-protection], .frame-external-content-protection',
	/**
	 * The CSS selector to match whitelisted external content
	 * @type {string}
	 */
	EXTERNAL_CONTENT_ALLOWED_ELEMENT_SELECTOR: '[data-external-content-no-protection], [data-iframe-allow-always], .frame-external-content-no-protection',

	externalContentObserver: null,
	protectedExternalContents: [],
	lastOpenedExternalContentId: 0,
	jsonData: {},
	isExternalGroupAccepted: false,
	fingerprintIcon: null,

	/**
	 * Executes the script
	 */
	run: function() {
		SgCookieOptin.closestPolyfill();
		SgCookieOptin.customEventPolyfill();

		SgCookieOptin.jsonData = JSON.parse(document.getElementById('cookieOptinData').innerHTML);
		if (SgCookieOptin.jsonData) {
			SgCookieOptin.setCookieNameBasedOnLanguage();
			// https://plainjs.com/javascript/events/running-code-when-the-document-is-ready-15/
			document.addEventListener('DOMContentLoaded', function() {
				SgCookieOptin.initialize();
			});
			SgCookieOptin.isExternalGroupAccepted = SgCookieOptin.checkIsExternalGroupAccepted();
			SgCookieOptin.checkForExternalContents();

			if (SgCookieOptin.jsonData.settings.iframe_enabled && SgCookieOptin.isExternalGroupAccepted) {
				document.addEventListener("DOMContentLoaded", function() {
					SgCookieOptin.checkForDataAttributeExternalContents();
				});
			}
		}
	},

	/**
	 * Initializes the whole functionality.
	 *
	 * @return {void}
	 */
	initialize: function() {
		SgCookieOptin.handleScriptActivations();

		const optInContentElements = document.querySelectorAll('.sg-cookie-optin-plugin-uninitialized');
		for (let index = 0; index < optInContentElements.length; ++index) {
			const optInContentElement = optInContentElements[index];
			SgCookieOptin.openCookieOptin(optInContentElement, {hideBanner: true});
			optInContentElement.classList.remove('sg-cookie-optin-plugin-uninitialized');
			optInContentElement.classList.add('sg-cookie-optin-plugin-initialized');
		}

		// noinspection EqualityComparisonWithCoercionJS
		const disableOptIn = SgCookieOptin.getParameterByName('disableOptIn') == true;
		if (disableOptIn) {
			return;
		}

		// noinspection EqualityComparisonWithCoercionJS
		const showOptIn = SgCookieOptin.getParameterByName('showOptIn') == true;
		const cookieValue = SgCookieOptin.getCookie(SgCookieOptin.COOKIE_NAME);
		if (showOptIn || !SgCookieOptin.jsonData.settings.activate_testing_mode &&
			(SgCookieOptin.shouldShowBannerBasedOnLastPreferences(cookieValue))
		) {
			SgCookieOptin.openCookieOptin(null, {hideBanner: false});
		} else {
			if (!SgCookieOptin.jsonData.settings.activate_testing_mode) {
				SgCookieOptin.showFingerprint();
			}
		}
	},

	/**
	 * Sets the sg_cookie_optin cookie name accordingly based on the current language settings
	 */
	setCookieNameBasedOnLanguage: function() {
		if (!SgCookieOptin.jsonData.settings.unified_cookie_name) {
			SgCookieOptin.COOKIE_NAME += '_' + SgCookieOptin.jsonData.settings.identifier + '_'
				+ SgCookieOptin.jsonData.settings.language;
			SgCookieOptin.LAST_PREFERENCES_COOKIE_NAME += '_' + SgCookieOptin.jsonData.settings.identifier + '_'
				+ SgCookieOptin.jsonData.settings.language;
		}
	},

	/**
	 * Checks if the external cookie group has been accepted
	 * @returns {boolean}
	 */
	checkIsExternalGroupAccepted: function() {
		return SgCookieOptin.checkIsGroupAccepted(SgCookieOptin.COOKIE_GROUP_EXTERNAL_CONTENT);
	},

	/**
	 * Checks whether the given group has been accepted or not
	 *
	 * @param {string} groupName
	 * @param {string=} cookieValue
	 * @returns {boolean}
	 */
	checkIsGroupAccepted: function(groupName, cookieValue) {
		if (typeof cookieValue === 'undefined') {
			cookieValue = SgCookieOptin.getCookie(SgCookieOptin.COOKIE_NAME);
		}
		if (cookieValue) {
			const splitedCookieValue = cookieValue.split('|');
			for (const splitedCookieValueIndex in splitedCookieValue) {
				if (!splitedCookieValue.hasOwnProperty(splitedCookieValueIndex)) {
					continue;
				}

				const splitedCookieValueEntry = splitedCookieValue[splitedCookieValueIndex];
				const groupAndStatus = splitedCookieValueEntry.split(':');
				if (!groupAndStatus.hasOwnProperty(0) || !groupAndStatus.hasOwnProperty(1)) {
					continue;
				}

				const group = groupAndStatus[0];
				const status = parseInt(groupAndStatus[1]);
				if (group === groupName) {
					if (status === 1) {
						return true;
					}
					break;
				}
			}
		}
		return false;
	},

	/**
	 * Checks whether the given group has been accepted or not
	 *
	 * @param {string} cookieValue
	 * @returns {*[]}
	 */
	readCookieValues: function(cookieValue) {
		if (typeof cookieValue === 'undefined') {
			cookieValue = SgCookieOptin.getCookie(SgCookieOptin.COOKIE_NAME);
		}

		const cookieValues = [];

		if (cookieValue) {
			const splitedCookieValue = cookieValue.split('|');
			for (const splitedCookieValueIndex in splitedCookieValue) {
				if (!splitedCookieValue.hasOwnProperty(splitedCookieValueIndex)) {
					continue;
				}

				const splitedCookieValueEntry = splitedCookieValue[splitedCookieValueIndex];
				const groupAndStatus = splitedCookieValueEntry.split(':');
				if (!groupAndStatus.hasOwnProperty(0) || !groupAndStatus.hasOwnProperty(1)) {
					continue;
				}

				const group = groupAndStatus[0];
				cookieValues[group] = parseInt(groupAndStatus[1]);
			}
		}

		return cookieValues;
	},

	/**
	 * Handles the scripts of the allowed cookie groups.
	 *
	 * @return {void}
	 */
	handleScriptActivations: function() {
		const cookieValue = SgCookieOptin.getCookie(SgCookieOptin.COOKIE_NAME);
		if (!cookieValue) {
			return;
		}

		const splitedCookieValue = cookieValue.split('|');
		for (const splitedCookieValueIndex in splitedCookieValue) {
			if (!splitedCookieValue.hasOwnProperty(splitedCookieValueIndex)) {
				continue;
			}

			const splitedCookieValueEntry = splitedCookieValue[splitedCookieValueIndex];
			const groupAndStatus = splitedCookieValueEntry.split(':');
			if (!groupAndStatus.hasOwnProperty(0) || !groupAndStatus.hasOwnProperty(1)) {
				continue;
			}

			const group = groupAndStatus[0];
			const status = parseInt(groupAndStatus[1]);
			if (!status) {
				continue;
			}

			for (const groupIndex in SgCookieOptin.jsonData.cookieGroups) {
				if (!SgCookieOptin.jsonData.cookieGroups.hasOwnProperty(groupIndex) || SgCookieOptin.jsonData.cookieGroups[groupIndex]['groupName'] !== group) {
					continue;
				}

				if (
					SgCookieOptin.jsonData.cookieGroups[groupIndex]['loadingHTML'] &&
					SgCookieOptin.jsonData.cookieGroups[groupIndex]['loadingHTML'] !== ''
				) {
					const head = document.getElementsByTagName('head')[0];
					if (head) {
						const range = document.createRange();
						range.selectNode(head);
						head.appendChild(range.createContextualFragment(SgCookieOptin.jsonData.cookieGroups[groupIndex]['loadingHTML']));
						// Emit event
						const addedLoadingHTMLEvent = new CustomEvent('addedLoadingHTML', {
							bubbles: true,
							detail: {
								src: SgCookieOptin.jsonData.cookieGroups[groupIndex]['loadingHTML']
							}
						});
						head.dispatchEvent(addedLoadingHTMLEvent);
					}
				}

				if (
					SgCookieOptin.jsonData.cookieGroups[groupIndex]['loadingJavaScript'] &&
					SgCookieOptin.jsonData.cookieGroups[groupIndex]['loadingJavaScript'] !== ''
				) {
					const script = document.createElement('script');
					script.setAttribute('src', SgCookieOptin.jsonData.cookieGroups[groupIndex]['loadingJavaScript']);
					script.setAttribute('type', 'text/javascript');
					document.body.appendChild(script);

					// Emit event
					const addedLoadingScriptEvent = new CustomEvent('addedLoadingScript', {
						bubbles: true,
						detail: {
							src: SgCookieOptin.jsonData.cookieGroups[groupIndex]['loadingJavaScript']
						}
					});
					script.dispatchEvent(addedLoadingScriptEvent);
				}
			}
		}
	},

	/**
	 * Opens the cookie optin box.
	 *
	 * Supported options:
	 * {boolean} hideBanner Whether to show the cookie banner or not, if it's enabled
	 *
	 * @param {Element=} contentElement
	 * @param {object=} options
	 *
	 * @return {void}
	 */
	openCookieOptin: function(contentElement, options) {
		if (!SgCookieOptin.shouldShowOptinBanner()) {
			return;
		}

		SgCookieOptin.hideFingerprint();
		const hideBanner = typeof options == 'object' && options.hideBanner === true;
		const fromBanner = typeof options == 'object' && options.fromBanner === true;

		const wrapper = document.createElement('DIV');
		wrapper.setAttribute('data-nosnippet', '');
		wrapper.id = 'SgCookieOptin';

		let forceBanner;
		if (SgCookieOptin.jsonData.settings.banner_force_min_width && parseInt(SgCookieOptin.jsonData.settings.banner_force_min_width) > 0) {
			forceBanner = window.matchMedia('(max-width: ' + parseInt(SgCookieOptin.jsonData.settings.banner_force_min_width) + 'px)').matches;
		} else {
			forceBanner = false;
		}

		if (!fromBanner && !hideBanner && ((!contentElement && SgCookieOptin.jsonData.settings.banner_enable && !hideBanner) || forceBanner)) {
			wrapper.classList.add('sg-cookie-optin-banner-wrapper');
			wrapper.insertAdjacentHTML('afterbegin', SgCookieOptin.jsonData.mustacheData.banner.markup);
		} else {
			wrapper.insertAdjacentHTML('afterbegin', SgCookieOptin.jsonData.mustacheData.template.markup);
		}

		SgCookieOptin.insertUserUuid(wrapper);

		SgCookieOptin.addListeners(wrapper, contentElement);

		if (!contentElement) {
			document.body.insertAdjacentElement('beforeend', wrapper);
		} else {
			contentElement.appendChild(wrapper);
		}

		setTimeout(function() {
			SgCookieOptin.adjustDescriptionHeight(wrapper, contentElement);
			SgCookieOptin.updateCookieList();
			// Emit event
			const cookieOptinShownEvent = new CustomEvent('cookieOptinShown', {
				bubbles: true,
				detail: {}
			});
			document.body.dispatchEvent(cookieOptinShownEvent);

			// check if there is a cookie optin plugin on the page - then don't focus the checkboxes
			if (document.getElementsByClassName('sg-cookie-optin-plugin-initialized').length > 0) {
				return;
			}

			const checkboxes = document.getElementsByClassName('sg-cookie-optin-checkbox');
			if (checkboxes.length > 1) {
				if (checkboxes[1].focus) {
					checkboxes[1].focus();
				}
			}

		}, 10);
	},

	/**
	 * Checks if the cookie banner should be shown
	 *
	 * @returns {boolean}
	 */
	shouldShowOptinBanner: function() {
		// check doNotTrack
		if (typeof SgCookieOptin.jsonData.settings.consider_do_not_track !== 'undefined'
			&& SgCookieOptin.jsonData.settings.consider_do_not_track
			&& typeof navigator.doNotTrack !== 'undefined' && navigator.doNotTrack === '1') {
			console.log('Cookie Consent: DoNotTrack detected - Auto-OptOut');
			return false;
		}

		// check if there is a cookie optin plugin on the page
		if (document.getElementsByClassName('sg-cookie-optin-plugin-initialized').length > 0) {
			return false;
		}

		// test if the current URL matches one of the whitelist regex
		if (typeof SgCookieOptin.jsonData.settings.cookiebanner_whitelist_regex !== 'undefined'
			&& SgCookieOptin.jsonData.settings.cookiebanner_whitelist_regex.trim() !== ''
		) {
			const regularExpressions = SgCookieOptin.jsonData.settings.cookiebanner_whitelist_regex.trim()
				.split(/\r?\n/).map(function(value) {
					return new RegExp(value);
				});
			if (typeof regularExpressions === 'object' && regularExpressions.length > 0) {
				for (const regExIndex in regularExpressions) {
					if (regularExpressions[regExIndex].test(window.location)) {
						return false;
					}
				}
			}
		}

		return true;
	},

	/**
	 * Adjusts the description height for each element, for the new design.
	 *
	 * @param {HTMLElement} container
	 * @param {Element} contentElement
	 * @return {void}
	 */
	adjustDescriptionHeight: function(container, contentElement) {
		let index;
		let columnsPerRow = 4;
		if (contentElement === null) {
			if (window.innerWidth <= 750) {
				return;
			} else if (window.innerWidth <= 1050) {
				columnsPerRow = 2;
			} else if (window.innerWidth <= 1250) {
				columnsPerRow = 3;
			}
		} else {
			columnsPerRow = 2;
		}

		const maxHeightPerRow = [];
		let maxHeightPerRowIndex = 0;
		const descriptions = container.querySelectorAll('.sg-cookie-optin-box-new .sg-cookie-optin-checkbox-description');
		for (index = 0; index < descriptions.length; ++index) {
			if (!(index % columnsPerRow)) {
				++maxHeightPerRowIndex;
			}

			const descriptionHeight = descriptions[index].getBoundingClientRect().height;
			const maxHeight = (maxHeightPerRow[maxHeightPerRowIndex] ? maxHeightPerRow[maxHeightPerRowIndex] : 0);
			if (descriptionHeight > maxHeight) {
				maxHeightPerRow[maxHeightPerRowIndex] = descriptionHeight;
			}
		}

		maxHeightPerRowIndex = 0;
		for (index = 0; index < descriptions.length; ++index) {
			if (!(index % columnsPerRow)) {
				++maxHeightPerRowIndex;
			}

			descriptions[index].style.height = maxHeightPerRow[maxHeightPerRowIndex] + 'px';
		}
	},

	/**
	 * Adjusts the description height for each element, for the new design.
	 *
	 * @param {HTMLElement} container
	 * @param {HTMLElement} contentElement
	 * @return {void}
	 */
	adjustReasonHeight: function(container, contentElement) {
		let columnsPerRow = 3;
		if (contentElement === null) {
			if (window.innerWidth <= 750) {
				return;
			} else if (window.innerWidth <= 1050) {
				columnsPerRow = 2;
			}
		} else {
			columnsPerRow = 2;
		}

		const listItems = container.querySelectorAll('.sg-cookie-optin-box-new .sg-cookie-optin-box-cookie-list-item');
		for (let listItemIndex = 0; listItemIndex < listItems.length; ++listItemIndex) {
			let index;
			const maxHeightPerRow = [];
			let maxHeightPerRowIndex = 0;

			const reasons = listItems[listItemIndex].querySelectorAll('.sg-cookie-optin-box-table-reason');
			for (index = 0; index < reasons.length; ++index) {
				if (!(index % columnsPerRow)) {
					++maxHeightPerRowIndex;
				}

				let reasonHeight = reasons[index].getBoundingClientRect().height;
				reasonHeight -= parseInt(window.getComputedStyle(reasons[index], null).getPropertyValue('padding-top'));
				reasonHeight -= parseInt(window.getComputedStyle(reasons[index], null).getPropertyValue('padding-bottom'));
				const maxHeight = (maxHeightPerRow[maxHeightPerRowIndex] ? maxHeightPerRow[maxHeightPerRowIndex] : 0);
				if (reasonHeight > maxHeight) {
					maxHeightPerRow[maxHeightPerRowIndex] = reasonHeight;
				}
			}

			maxHeightPerRowIndex = 0;
			for (index = 0; index < reasons.length; ++index) {
				if (!(index % columnsPerRow)) {
					++maxHeightPerRowIndex;
				}

				reasons[index].style.height = maxHeightPerRow[maxHeightPerRowIndex] + 'px';
			}
		}
	},

	/**
	 * Checks whether we must show the cookie banner based on the last preferences of the user.
	 * This may be necessary if there are new groups or new cookies since the last preferences save,
	 * or the user didn't select all preferences and the configured interval has expired
	 *
	 * @param {String} cookieValue
	 * @returns {boolean}
	 */
	shouldShowBannerBasedOnLastPreferences: function(cookieValue) {
		if (!cookieValue) {
			return true;
		}

		const lastPreferences = SgCookieOptin.getLastPreferences();

		if (typeof lastPreferences.timestamp === 'undefined') {
			return true;
		}

		if (lastPreferences.version !== SgCookieOptin.jsonData.settings.version) {
			return true;
		}

		for (const groupIndex in SgCookieOptin.jsonData.cookieGroups) {
			if (!SgCookieOptin.jsonData.cookieGroups.hasOwnProperty(groupIndex)) {
				continue;
			}

			// is the group new
			if (typeof SgCookieOptin.jsonData.cookieGroups[groupIndex].crdate !== 'undefined' &&
				SgCookieOptin.jsonData.cookieGroups[groupIndex].crdate > lastPreferences.timestamp) {
				return true;
			}

			// is there a new cookie in this group
			let cookieIndex;
			for (cookieIndex in SgCookieOptin.jsonData.cookieGroups[groupIndex].cookieData) {
				if (!SgCookieOptin.jsonData.cookieGroups[groupIndex].cookieData.hasOwnProperty(cookieIndex)) {
					continue;
				}

				if (typeof SgCookieOptin.jsonData.cookieGroups[groupIndex].cookieData[cookieIndex].crdate !== 'undefined'
					&& SgCookieOptin.jsonData.cookieGroups[groupIndex].cookieData[cookieIndex].crdate > lastPreferences.timestamp) {
					return true;
				}
			}

			// if the user didn't select all group last time, check if the group was not accepted and the configured interval has expired
			if (!lastPreferences.isAll &&
				typeof SgCookieOptin.jsonData.cookieGroups[groupIndex].groupName !== 'undefined' &&
				!SgCookieOptin.checkIsGroupAccepted(SgCookieOptin.jsonData.cookieGroups[groupIndex].groupName,
					lastPreferences.cookieValue)
				&& (new Date().getTime() / 1000) > (lastPreferences.timestamp + 24 * 60 * 60
					* SgCookieOptin.jsonData.settings.banner_show_again_interval)) {
				return true;
			}
		}
		return false;
	},

	/**
	 * Gets the current user uuid
	 *
	 * @param {boolean} setIfEmpty
	 * @returns {string}
	 */
	getUserUuid: function(setIfEmpty) {
		const lastPreferences = SgCookieOptin.getLastPreferences();
		let userUuid = lastPreferences.uuid;
		if (setIfEmpty && !userUuid && !SgCookieOptin.jsonData.settings.disable_usage_statistics) {
			userUuid = SgCookieOptin.generateUUID();
			lastPreferences.uuid = userUuid;
			window.localStorage.setItem(SgCookieOptin.LAST_PREFERENCES_LOCAL_STORAGE_NAME, JSON.stringify(lastPreferences));
		}

		return userUuid;
	},

	/**
	 * Shows the UserUuid in the cookie banner
	 *
	 * @param {HTMLElement} wrapper
	 */
	insertUserUuid: function(wrapper) {
		const hashContainer = wrapper.querySelector('.sg-cookie-optin-box-footer-user-hash');
		const hashContainerParent = wrapper.querySelector('.sg-cookie-optin-box-footer-user-hash-container');

		// The banner does not have a hash container
		if (!hashContainer || !hashContainerParent) {
			return;
		}

		if (SgCookieOptin.jsonData.settings.disable_usage_statistics) {
			hashContainerParent.style.display = 'none';
			return;
		}

		const uuid = SgCookieOptin.getUserUuid(false);

		if (typeof hashContainer.innerText === 'string') {
			if (uuid) {
				hashContainer.innerText = uuid;
			} else {
				hashContainerParent.style.display = 'none';
			}
		}
	},

	/**
	 * Stores the last saved preferences in the localstorage
	 *
	 * @param {string} cookieValue
	 */
	saveLastPreferences: function(cookieValue) {
		let isAll = true;
		for (let groupIndex in SgCookieOptin.jsonData.cookieGroups) {
			if (!SgCookieOptin.jsonData.cookieGroups.hasOwnProperty(groupIndex)) {
				continue;
			}
			if (!SgCookieOptin.checkIsGroupAccepted(SgCookieOptin.jsonData.cookieGroups[groupIndex].groupName)) {
				isAll = false;
			}
		}

		const lastPreferences = SgCookieOptin.getLastPreferences();

		lastPreferences.timestamp = Math.floor(new Date().getTime() / 1000);
		lastPreferences.cookieValue = cookieValue;
		lastPreferences.isAll = isAll;
		lastPreferences.version = SgCookieOptin.jsonData.settings.version;
		lastPreferences.identifier = SgCookieOptin.jsonData.settings.identifier;

		if (SgCookieOptin.lastPreferencesFromCookie()) {
			SgCookieOptin.setCookie(SgCookieOptin.LAST_PREFERENCES_COOKIE_NAME, JSON.stringify(lastPreferences), '365')
		} else {
			window.localStorage.setItem(SgCookieOptin.LAST_PREFERENCES_LOCAL_STORAGE_NAME, JSON.stringify(lastPreferences));
		}

		SgCookieOptin.saveLastPreferencesForStats(lastPreferences);
	},

	/**
	 * Decides whether to read the last preferences from cookie or from local storage
	 *
	 * @returns {boolean}
	 */
	lastPreferencesFromCookie: function() {
		return (SgCookieOptin.jsonData.settings.subdomain_support || SgCookieOptin.jsonData.settings.set_cookie_for_domain);
	},

	/**
	 * Saves the preferences for statistics via AJAX
	 *
	 * @param lastPreferences
	 */
	saveLastPreferencesForStats: function(lastPreferences) {
		if (SgCookieOptin.jsonData.settings.disable_usage_statistics) {
			return;
		}

		lastPreferences.uuid = SgCookieOptin.getUserUuid(true);

		const request = new XMLHttpRequest();
		const formData = new FormData();
		formData.append('lastPreferences', JSON.stringify(lastPreferences));
		const url = SgCookieOptin.jsonData.settings.save_history_webhook;

		if (!url) {
			return;
		}

		request.open('POST', url);
		request.send(formData);
	},

	/**
	 * Generates a RFC4122 v4 compliant UUID
	 *
	 * @returns {string}
	 */
	generateUUID: function() { // Public Domain/MIT
		let d = new Date().getTime();//Timestamp
		let d2 = (performance && performance.now && (performance.now() * 1000)) || 0;//Time in microseconds since page-load or 0 if unsupported
		return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
			let r = Math.random() * 16;//random number between 0 and 16
			if (d > 0) {//Use timestamp until depleted
				r = (d + r) % 16 | 0;
				d = Math.floor(d / 16);
			} else {//Use microseconds since page-load if supported
				r = (d2 + r) % 16 | 0;
				d2 = Math.floor(d2 / 16);
			}
			return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
		});
	},

	/**
	 * Delete all cookies that match the regex of the cookie name if a given group has been unselected by the user
	 */
	deleteCookiesForUnsetGroups: function() {
		for (const groupIndex in SgCookieOptin.jsonData.cookieGroups) {
			if (!SgCookieOptin.jsonData.cookieGroups.hasOwnProperty(groupIndex)) {
				continue;
			}
			const documentCookies = document.cookie.split('; ');
			if (!SgCookieOptin.checkIsGroupAccepted(SgCookieOptin.jsonData.cookieGroups[groupIndex].groupName)) {
				for (const cookieIndex in SgCookieOptin.jsonData.cookieGroups[groupIndex].cookieData) {
					if (isNaN(parseInt(cookieIndex))) {
						continue;
					}

					for (const documentCookieIndex in documentCookies) {
						if (isNaN(parseInt(documentCookieIndex))) {
							continue;
						}

						const cookieName = documentCookies[documentCookieIndex].split('=')[0];
						let regExString = SgCookieOptin.jsonData.cookieGroups[groupIndex].cookieData[cookieIndex]
							.Name.trim();

						if (!regExString) {
							continue;
						}

						if (!regExString.includes('^')) {
							regExString = '^' + regExString;
						}

						if (!regExString.includes('$')) {
							regExString += '$';
						}

						const regEx = new RegExp(regExString);
						if (regEx.test(cookieName)) {
							// delete the cookie
							SgCookieOptin.deleteGroupCookie(cookieName);
						}
					}
				}
			}
		}
	},

	/**
	 * Delete the cookie from the current domain and the additional domains
	 *
	 * @param cookieName
	 */
	deleteGroupCookie: function(cookieName) {
		let cookie = cookieName + '=; path=/; Max-Age=-99999999;';
		document.cookie = cookie; // This is important in case the configuration that we test below has been changed
		const currentHost = window.location.hostname;

		if (SgCookieOptin.jsonData.settings.set_cookie_for_domain && SgCookieOptin.jsonData.settings.set_cookie_for_domain.length > 0) {
			cookie += ';domain=' + SgCookieOptin.jsonData.settings.set_cookie_for_domain;
		} else if (SgCookieOptin.jsonData.settings.subdomain_support) {
			const domainParts = currentHost.split('.');
			if (domainParts.length > 2) {
				domainParts.shift();
				const hostnameToFirstDot = '.' + domainParts.join('.');
				cookie += ';domain=' + hostnameToFirstDot;
			}
		}

		document.cookie = cookie;

		const additionalDomains = SgCookieOptin.jsonData.settings.domains_to_delete_cookies_for.trim()
			.split(/\r?\n/).map(function(value) {
				return value.trim();
			});

		for (const additionalDomainIndex in additionalDomains) {
			if (!additionalDomains.hasOwnProperty(additionalDomainIndex)) {
				continue;
			}

			document.cookie = cookieName + '=; path=/; ' + 'domain=' + additionalDomains[additionalDomainIndex] + '; Max-Age=-99999999;';
		}
	},

	/**
	 * Adds the listeners to the given element.
	 *
	 * @param {HTMLElement} element
	 * @param {Element} contentElement
	 *
	 * @return {void}
	 */
	addListeners: function(element, contentElement) {
		const closeButtons = element.querySelectorAll('.sg-cookie-optin-box-close-button');
		SgCookieOptin.addEventListenerToList(closeButtons, 'click', function() {
			SgCookieOptin.acceptEssentialCookies();
			SgCookieOptin.updateCookieList();
			SgCookieOptin.handleScriptActivations();

			if (!contentElement) {
				SgCookieOptin.hideCookieOptIn();
			}
		});

		const openMoreLinks = element.querySelectorAll('.sg-cookie-optin-box-open-more-link');
		SgCookieOptin.addEventListenerToList(openMoreLinks, 'click', SgCookieOptin.openCookieDetails);

		const openSubListLink = element.querySelectorAll('.sg-cookie-optin-box-sublist-open-more-link');
		SgCookieOptin.addEventListenerToList(openSubListLink, 'click', function(event) {
			SgCookieOptin.openSubList(event, contentElement);
		});

		const acceptAllButtons = element.querySelectorAll(
			'.sg-cookie-optin-box-button-accept-all, .sg-cookie-optin-banner-button-accept'
		);
		SgCookieOptin.addEventListenerToList(acceptAllButtons, 'click', function() {
			SgCookieOptin.acceptAllCookies();
			SgCookieOptin.updateCookieList();
			SgCookieOptin.handleScriptActivations();

			if (!contentElement) {
				SgCookieOptin.hideCookieOptIn();
			} else {
				SgCookieOptin.showSaveConfirmation(contentElement);
			}
		});

		const acceptSpecificButtons = element.querySelectorAll('.sg-cookie-optin-box-button-accept-specific');
		SgCookieOptin.addEventListenerToList(acceptSpecificButtons, 'click', function() {
			SgCookieOptin.acceptSpecificCookies(this);
			SgCookieOptin.updateCookieList(this);
			SgCookieOptin.handleScriptActivations();

			if (!contentElement) {
				SgCookieOptin.hideCookieOptIn();
			} else {
				SgCookieOptin.showSaveConfirmation(contentElement);
			}
		});

		const acceptEssentialButtons = element.querySelectorAll('.sg-cookie-optin-box-button-accept-essential, .sg-cookie-optin-banner-button-accept-essential');
		SgCookieOptin.addEventListenerToList(acceptEssentialButtons, 'click', function() {
			SgCookieOptin.acceptEssentialCookies();
			SgCookieOptin.updateCookieList();
			SgCookieOptin.handleScriptActivations();

			if (!contentElement) {
				SgCookieOptin.hideCookieOptIn();
			} else {
				SgCookieOptin.showSaveConfirmation(contentElement);
			}
		});

		const openSettingsButtons = element.querySelectorAll('.sg-cookie-optin-banner-button-settings');
		SgCookieOptin.addEventListenerToList(openSettingsButtons, 'click', function() {
			SgCookieOptin.hideCookieOptIn();
			SgCookieOptin.openCookieOptin(null, {hideBanner: true, fromBanner: true});
		});
	},

	/**
	 * Adds an event to a given node list.
	 *
	 * @param {NodeListOf<Element>} list
	 * @param {string} event
	 * @param {function} assignedFunction
	 *
	 * @return {void}
	 */
	addEventListenerToList: function(list, event, assignedFunction) {
		for (let index = 0; index < list.length; ++index) {
			list[index].addEventListener(event, assignedFunction, false);
		}
	},

	/**
	 * Hides the cookie opt in.
	 *
	 * @return {void}
	 */
	hideCookieOptIn: function() {
		// The content element cookie optins aren't removed, because querySelector gets only the first entry and it's
		// always the modular one.
		const optins = document.querySelectorAll('#SgCookieOptin');
		for (const index in optins) {
			if (!optins.hasOwnProperty(index)) {
				continue;
			}

			// Because of the IE11 no .remove();
			const optin = optins[index];
			const parentNode = optin.parentNode;
			if (!parentNode || parentNode.classList.contains('sg-cookie-optin-plugin')) {
				continue;
			}

			parentNode.removeChild(optin);

			// Emit event
			const cookieOptinHiddenEvent = new CustomEvent('cookieOptinHidden', {
				bubbles: true,
				detail: {}
			});
			parentNode.dispatchEvent(cookieOptinHiddenEvent);
		}

		SgCookieOptin.showFingerprint();
	},

	/**
	 * Returns the cookie list DOM.
	 *
	 * @param {Element=} triggerElement
	 *
	 * @return {void}
	 */
	updateCookieList: function(triggerElement) {
		let checkBoxesContainer;
		const statusMap = {};
		const cookieValue = SgCookieOptin.getCookie(SgCookieOptin.COOKIE_NAME);
		if (cookieValue) {
			const splitedCookieValue = cookieValue.split('|');
			for (const valueIndex in splitedCookieValue) {
				if (!splitedCookieValue.hasOwnProperty(valueIndex)) {
					continue;
				}

				const splitedCookieValueEntry = splitedCookieValue[valueIndex];
				const groupAndStatus = splitedCookieValueEntry.split(':');
				if (!groupAndStatus.hasOwnProperty(0) || !groupAndStatus.hasOwnProperty(1)) {
					continue;
				}

				statusMap[groupAndStatus[0]] = parseInt(groupAndStatus[1]);
			}
		}

		for (const groupIndex in SgCookieOptin.jsonData.cookieGroups) {
			if (!SgCookieOptin.jsonData.cookieGroups.hasOwnProperty(groupIndex)) {
				continue;
			}

			const groupName = SgCookieOptin.jsonData.cookieGroups[groupIndex]['groupName'];
			if (!groupName) {
				continue;
			}

			if (!statusMap.hasOwnProperty(groupName)) {
				continue;
			}

			if (!triggerElement) {
				checkBoxesContainer = document;
			} else {
				checkBoxesContainer = triggerElement.closest('.sg-cookie-optin-box');
			}

			// fallback to document if not found
			if (!checkBoxesContainer) {
				checkBoxesContainer = document;
			}
			const cookieList = checkBoxesContainer.querySelectorAll('.sg-cookie-optin-checkbox[value="' + groupName + '"]');
			for (let index = 0; index < cookieList.length; ++index) {
				cookieList[index].checked = (statusMap[groupName] === 1);
			}
		}
	},

	/**
	 * Opens the cookie details box.
	 *
	 * @param {Event} event
	 * @return {void}
	 */
	openCookieDetails: function(event) {
		event.preventDefault();

		const openMoreElement = event.target.parentNode;
		if (!openMoreElement) {
			return;
		}

		const cookieDetailList = openMoreElement.previousElementSibling;
		if (!cookieDetailList) {
			return;
		}

		if (cookieDetailList.classList.contains('sg-cookie-optin-visible')) {
			cookieDetailList.classList.remove('sg-cookie-optin-visible');
			event.target.innerHTML = SgCookieOptin.jsonData.textEntries.extend_box_link_text;
		} else {
			cookieDetailList.classList.add('sg-cookie-optin-visible');
			event.target.innerHTML = SgCookieOptin.jsonData.textEntries.extend_box_link_text_close;
		}
	},

	/**
	 * Opens the subList box.
	 *
	 * @param event
	 * @param {Element} contentElement
	 * @return {void}
	 */
	openSubList: function(event, contentElement) {
		event.preventDefault();

		// todo remove redundant code.
		let height = 0;
		const cookieSubList = event.target.previousElementSibling;
		if (!cookieSubList || !cookieSubList.classList.contains('sg-cookie-optin-box-cookie-detail-sublist')) {
			const cookieOptin = event.target.closest('#SgCookieOptin');
			const cookieBoxes = cookieOptin.querySelectorAll('.sg-cookie-optin-box-new');
			for (const index in cookieBoxes) {
				if (!cookieBoxes.hasOwnProperty(index)) {
					continue;
				}

				let visible = true;
				const cookieBox = cookieBoxes[index];
				if (cookieBox.classList.contains('sg-cookie-optin-visible')) {
					visible = false;
					cookieBox.classList.remove('sg-cookie-optin-visible');
					cookieBox.classList.add('sg-cookie-optin-invisible');
					event.target.innerHTML = SgCookieOptin.jsonData.textEntries.extend_table_link_text;
				} else {
					cookieBox.classList.remove('sg-cookie-optin-invisible');
					cookieBox.classList.add('sg-cookie-optin-visible');
					SgCookieOptin.adjustReasonHeight(cookieOptin, contentElement);
					event.target.innerHTML = SgCookieOptin.jsonData.textEntries.extend_table_link_text_close;
				}

				const descriptions = cookieBox.querySelectorAll('.sg-cookie-optin-checkbox-description');
				for (const descriptionIndex in descriptions) {
					if (!descriptions.hasOwnProperty(descriptionIndex)) {
						continue;
					}

					const description = descriptions[descriptionIndex];
					if (visible) {
						description.setAttribute('data-optimal-height', description.style.height);
						description.style.height = 'auto';
					} else {
						description.style.height = description.getAttribute('data-optimal-height');
					}
				}
			}
		} else {
			if (cookieSubList.classList.contains('sg-cookie-optin-visible')) {
				cookieSubList.classList.remove('sg-cookie-optin-visible');
				cookieSubList.style.height = '';
				event.target.innerHTML = SgCookieOptin.jsonData.textEntries.extend_table_link_text;
			} else {
				cookieSubList.classList.add('sg-cookie-optin-visible');
				cookieSubList.style.height = 'auto';
				height = cookieSubList.getBoundingClientRect().height + 'px';
				cookieSubList.style.height = '';
				requestAnimationFrame(function() {
					setTimeout(function() {
						cookieSubList.style.height = height;
					}, 10);
				});

				event.target.innerHTML = SgCookieOptin.jsonData.textEntries.extend_table_link_text_close;
			}
		}
	},

	/**
	 * Accepts all cookies and saves them.
	 *
	 * @return {void}
	 */
	acceptAllCookies: function() {
		let cookieData = '';
		for (const index in SgCookieOptin.jsonData.cookieGroups) {
			if (!SgCookieOptin.jsonData.cookieGroups.hasOwnProperty(index)) {
				continue;
			}

			const groupName = SgCookieOptin.jsonData.cookieGroups[index]['groupName'];
			if (!groupName) {
				continue;
			}

			if (cookieData.length > 0) {
				cookieData += '|';
			}
			cookieData += groupName + ':' + 1;

			if (groupName === SgCookieOptin.COOKIE_GROUP_EXTERNAL_CONTENT) {
				SgCookieOptin.acceptAllExternalContents();
			}
		}
		SgCookieOptin.setCookieWrapper(cookieData);
	},

	/**
	 * Accepts specific cookies and saves them.
	 *
	 * @param {HTMLElement} triggerElement
	 * @return {void}
	 */
	acceptSpecificCookies: function(triggerElement) {
		let externalContentGroupFoundAndActive = false;
		let cookieData = '';
		let checkBoxesContainer;
		if (!triggerElement) {
			checkBoxesContainer = document;
		} else {
			checkBoxesContainer = triggerElement.closest('.sg-cookie-optin-box');
		}
		const checkboxes = checkBoxesContainer.querySelectorAll('.sg-cookie-optin-checkbox:checked');
		for (const index in SgCookieOptin.jsonData.cookieGroups) {
			if (!SgCookieOptin.jsonData.cookieGroups.hasOwnProperty(index)) {
				continue;
			}

			const groupName = SgCookieOptin.jsonData.cookieGroups[index]['groupName'];
			if (!groupName) {
				continue;
			}

			let status = 0;
			for (let subIndex = 0; subIndex < checkboxes.length; ++subIndex) {
				if (checkboxes[subIndex].value === groupName) {
					status = 1;
					break;
				}
			}

			if (groupName === SgCookieOptin.COOKIE_GROUP_EXTERNAL_CONTENT && status === 1) {
				externalContentGroupFoundAndActive = true;
			}

			if (cookieData.length > 0) {
				cookieData += '|';
			}
			cookieData += groupName + ':' + status;
		}

		SgCookieOptin.setCookieWrapper(cookieData);

		if (SgCookieOptin.jsonData.settings.iframe_enabled) {
			if (externalContentGroupFoundAndActive) {
				SgCookieOptin.acceptAllExternalContents();
			} else {
				SgCookieOptin.checkForExternalContents();
			}
		}
	},

	/**
	 * Accepts essential cookies and saves them.
	 *
	 * @return {void}
	 */
	acceptEssentialCookies: function() {
		let cookieData = '';
		for (const index in SgCookieOptin.jsonData.cookieGroups) {
			if (!SgCookieOptin.jsonData.cookieGroups.hasOwnProperty(index)) {
				continue;
			}

			const groupName = SgCookieOptin.jsonData.cookieGroups[index]['groupName'];
			if (!groupName) {
				continue;
			}

			let status = 0;
			if (SgCookieOptin.jsonData.cookieGroups[index]['required']) {
				status = 1;
			}

			if (cookieData.length > 0) {
				cookieData += '|';
			}
			cookieData += groupName + ':' + status;
		}

		SgCookieOptin.setCookieWrapper(cookieData);
	},

	/**
	 * Checks if external content elements are added to the dom and replaces them with a consent, if the cookie isn't accepted, or set.
	 *
	 * @return {void}
	 */
	checkForExternalContents: function() {
		if (!SgCookieOptin.jsonData.settings.iframe_enabled) {
			return;
		}

		// noinspection EqualityComparisonWithCoercionJS
		const showOptIn = SgCookieOptin.getParameterByName('showOptIn') == true;
		if (SgCookieOptin.jsonData.settings.activate_testing_mode && !showOptIn) {
			return;
		}

		if (SgCookieOptin.isExternalGroupAccepted) {
			return;
		}

		if (!SgCookieOptin.externalContentObserver) {
			const externalContents = document.querySelectorAll(SgCookieOptin.EXTERNAL_CONTENT_ELEMENT_SELECTOR);
			if (externalContents.length > 0) {
				for (const externalContentIndex in externalContents) {
					if (!externalContents.hasOwnProperty(externalContentIndex)) {
						continue;
					}
					SgCookieOptin.replaceExternalContentWithConsent(externalContents[externalContentIndex]);
				}
			}
		}

		// Create an observer instance linked to the callback function
		SgCookieOptin.externalContentObserver = new MutationObserver(function(mutationsList) {
			// Use traditional 'for loops' for IE 11
			for (const index in mutationsList) {
				if (!mutationsList.hasOwnProperty(index)) {
					continue;
				}

				const mutation = mutationsList[index];
				if (mutation.type !== 'childList' || mutation.addedNodes.length <= 0) {
					continue;
				}

				for (const addedNodeIndex in mutation.addedNodes) {
					if (!mutation.addedNodes.hasOwnProperty(addedNodeIndex)) {
						continue;
					}

					const addedNode = mutation.addedNodes[addedNodeIndex];
					if (typeof addedNode.matches === 'function' && addedNode.matches(SgCookieOptin.EXTERNAL_CONTENT_ELEMENT_SELECTOR)) {
						SgCookieOptin.replaceExternalContentWithConsent(addedNode);
					} else if (addedNode.querySelectorAll && typeof addedNode.querySelectorAll === 'function') {
						// check if there is an external content in the subtree
						const externalContents = addedNode.querySelectorAll(SgCookieOptin.EXTERNAL_CONTENT_ELEMENT_SELECTOR);
						if (externalContents.length > 0) {
							for (const externalContentIndex in externalContents) {
								if (!externalContents.hasOwnProperty(externalContentIndex)) {
									continue;
								}
								SgCookieOptin.replaceExternalContentWithConsent(externalContents[externalContentIndex]);
							}
						}
					}
				}
			}
		});

		// Start observing the target node for configured mutations
		SgCookieOptin.externalContentObserver.observe(document, {subtree: true, childList: true});
	},

	/**
	 * Checks whether this element matches the rules in the whitelist
	 *
	 * @param {Element} externalContent
	 * @return {boolean}
	 */
	isContentWhiteListed: function(externalContent) {
		const regularExpressions = SgCookieOptin.jsonData.mustacheData.iframeWhitelist.markup.trim()
			.split(/\r?\n/).map(function(value) {
				return new RegExp(value);
			});
		if (typeof regularExpressions === 'object' && regularExpressions.length < 1) {
			return false;
		}

		switch (externalContent.tagName) {
			case 'IFRAME':
				return SgCookieOptin.matchRegexElementSource(externalContent, 'src', regularExpressions, true);
			case 'OBJECT':
				return SgCookieOptin.matchRegexElementSource(externalContent, 'data', regularExpressions, true);
			case 'VIDEO':
			case 'AUDIO':
				return SgCookieOptin.matchRegexAudioVideo(externalContent, regularExpressions, true);
			default:
				return false;
		}
	},

	/**
	 * Tests whether a dom element attribute matches the given regular expressions
	 *
	 * @param {HTMLElement} externalContent
	 * @param {string} attribute
	 * @param {RegExp[]} regularExpressions
	 * @param {boolean} checkForLocal
	 * @return {boolean}
	 */
	matchRegexElementSource: function(externalContent, attribute, regularExpressions, checkForLocal) {
		if (typeof externalContent.getAttribute !== 'function') {
			return false;
		}

		if (checkForLocal && SgCookieOptin.isUrlLocal(externalContent.dataset['contentReplaceSrc'] || externalContent.getAttribute(attribute))) {
			return true;
		}

		for (const regExIndex in regularExpressions) {
			if (typeof externalContent.getAttribute === 'function' && regularExpressions[regExIndex].test(externalContent.getAttribute(attribute))) {
				return true;
			}
		}
		return false;
	},

	/**
	 * Tests if an audio or video element's source matches the given regular expressions
	 *
	 * @param {Element} externalContent
	 * @param {RegExp[]} regularExpressions
	 * @param {boolean} checkForLocal
	 * @return {boolean}
	 */
	matchRegexAudioVideo: function(externalContent, regularExpressions, checkForLocal) {
		if (externalContent.hasAttribute('src')) {
			return SgCookieOptin.matchRegexElementSource(externalContent, 'src', regularExpressions, checkForLocal);
		}

		const sources = externalContent.querySelectorAll('source');
		let foundMatches = false;
		for (const sourceIndex in sources) {
			if (!sources.hasOwnProperty(sourceIndex)) {
				continue;
			}

			if (!sources[sourceIndex].getAttribute('src')) {
				continue;
			}

			// noinspection JSUnfilteredForInLoop
			if (!SgCookieOptin.matchRegexElementSource(sources[sourceIndex], 'src', regularExpressions, checkForLocal)) {
				foundMatches = true;
			}
		}

		return !foundMatches;
	},

	/**
	 * Checks whether the given element is in a container that is allowed to always render external content
	 * @param {HTMLElement} externalContent
	 * @returns {boolean}
	 */
	isElementInAllowedNode: function(externalContent) {
		const potentialParents = document.querySelectorAll(SgCookieOptin.EXTERNAL_CONTENT_ALLOWED_ELEMENT_SELECTOR);
		for (let i in potentialParents) {
			if (typeof potentialParents[i].contains === 'function' && potentialParents[i].contains(externalContent)) {
				return true;
			}
		}
		return false;
	},

	/**
	 * Checks if the given URL is a local or relative path
	 * @param {string} url
	 */
	isUrlLocal: function(url) {
		const tempA = document.createElement('a');
		tempA.setAttribute('href', url);
		return window.location.protocol === tempA.protocol && window.location.host === tempA.host;
	},

	/**
	 * Adds a consent for externalContents, which needs to be accepted.
	 *
	 * @param {HTMLElement} externalContent
	 *
	 * @return {void}
	 */
	replaceExternalContentWithConsent: function(externalContent) {
		// Skip allowed elements and whitelisted sources
		// noinspection EqualityComparisonWithCoercionJS
		if (externalContent.matches(SgCookieOptin.EXTERNAL_CONTENT_ALLOWED_ELEMENT_SELECTOR)
			|| SgCookieOptin.isElementInAllowedNode(externalContent)
			|| SgCookieOptin.isContentWhiteListed(externalContent)) {
			return;
		}

		// Skip detached elements
		const parent = externalContent.parentElement;
		if (!parent) {
			return;
		}

		const src = externalContent.dataset['contentReplaceSrc'] || externalContent.src;
		// Skip iframes with no source
		if (externalContent.tagName === 'IFRAME'
			&& (!src || externalContent.src.indexOf('chrome-extension') >= 0)) {
			return;
		}

		// Get the position of the element within its parent
		let positionIndex = 0;
		let child = externalContent;
		while ((child = child.previousSibling) != null) {
			positionIndex++;
		}
		externalContent.setAttribute('data-iframe-position-index', positionIndex.toString());

		// Got problems with the zero.
		let externalContentId = SgCookieOptin.protectedExternalContents.length + 1;
		if (externalContentId === 0) {
			externalContentId = 1;
		}
		SgCookieOptin.protectedExternalContents[externalContentId] = externalContent;

		// Create the external content consent box DIV container
		const container = document.createElement('DIV');
		container.setAttribute('data-iframe-id', externalContentId.toString());
		container.setAttribute('data-content-replace-src', src);
		container.setAttribute('style', 'height: ' + externalContent.offsetHeight + 'px;');
		container.classList.add('sg-cookie-optin-iframe-consent');
		SgCookieOptin.insertExternalContentReplacementHTML(externalContent, container);

		// Add event Listeners to the consent buttons
		const externalContentConsentAccept = container.querySelectorAll('.sg-cookie-optin-iframe-consent-accept');
		SgCookieOptin.addEventListenerToList(externalContentConsentAccept, 'click', function() {
			SgCookieOptin.acceptExternalContent(externalContentId)
		});

		// Set custom accept text if available
		if (externalContent.getAttribute('data-consent-button-text')) {
			for (let acceptButtonIndex = 0; acceptButtonIndex < externalContentConsentAccept.length; ++acceptButtonIndex) {
				externalContentConsentAccept[acceptButtonIndex].innerText = externalContent.getAttribute('data-consent-button-text');
			}
		}

		SgCookieOptin.setExternalContentDescriptionText(container, externalContent);

		const externalContentConsentLink = container.querySelectorAll('.sg-cookie-optin-iframe-consent-link');
		SgCookieOptin.addEventListenerToList(externalContentConsentLink, 'click', SgCookieOptin.openExternalContentConsent);

		// Replace the element
		if (parent.childNodes.length > 0) {
			parent.insertBefore(container, parent.childNodes[positionIndex]);
		} else {
			parent.appendChild(container);
		}

		// Accept child nodes as well when it is accepted
		externalContent.addEventListener('externalContentAccepted', function() {
			SgCookieOptin.acceptContainerChildNodes(externalContent);
			return true;
		});

		// Because of the IE11 no .remove();
		parent.removeChild(externalContent);

		// Emit event for replaced content
		const externalContentReplacedEvent = new CustomEvent('externalContentReplaced', {
			bubbles: true,
			detail: {
				positionIndex: positionIndex,
				parent: parent,
				externalContent: externalContent,
				container: container
			}
		});
		container.dispatchEvent(externalContentReplacedEvent);
	},

	/**
	 * Inserts the external content replacement HTML
	 *
	 * @param {HTMLElement} externalContent
	 * @param {HTMLElement} container
	 */
	insertExternalContentReplacementHTML: function(externalContent, container) {
		let serviceUsed = false;
		let service = null;

		// check if the service template is explicitly set
		if (typeof externalContent.dataset.sgCookieOptinService !== 'undefined') {
			service = SgCookieOptin.jsonData.mustacheData.services[
				externalContent.dataset.sgCookieOptinService
			];

			if (service) {
				serviceUsed = true;
				SgCookieOptin.emitBeforeExternalContentReplacedEvent(parent, externalContent, container, service);
				container.insertAdjacentHTML('afterbegin', service.rendered);
			} else {
				console.log('Sg Cookie Optin: Template ' + externalContent.dataset.sgCookieOptinReplacementTemplate
					+ ' not found!');
			}
		} else {
			// try to match service regex
			if (typeof SgCookieOptin.jsonData.mustacheData.services !== 'undefined') {
				for (const serviceIndex in SgCookieOptin.jsonData.mustacheData.services) {
					if (!SgCookieOptin.jsonData.mustacheData.services.hasOwnProperty(serviceIndex)) {
						continue;
					}

					if (SgCookieOptin.jsonData.mustacheData.services[serviceIndex].regex.trim() === '') {
						continue;
					}

					const regularExpressions = SgCookieOptin.jsonData.mustacheData.services[serviceIndex].regex.trim()
						.split(/\r?\n/).map(function(value) {
							return new RegExp(value);
						});

					let serviceMatched = false;
					if (typeof regularExpressions === 'object' && regularExpressions.length > 0) {
						switch (externalContent.tagName) {
							case 'IFRAME':
								serviceMatched = SgCookieOptin.matchRegexElementSource(externalContent, 'src', regularExpressions, false);
								break;
							case 'OBJECT':
								serviceMatched = SgCookieOptin.matchRegexElementSource(externalContent, 'data', regularExpressions, false);
								break;
							case 'VIDEO':
							case 'AUDIO':
								serviceMatched = SgCookieOptin.matchRegexAudioVideo(externalContent, regularExpressions, false);
								break;
						}
					}

					if (serviceMatched) {
						serviceUsed = true;
						service = SgCookieOptin.jsonData.mustacheData.services[serviceIndex];
						SgCookieOptin.emitBeforeExternalContentReplacedEvent(parent, externalContent, container, service);
						container.insertAdjacentHTML('afterbegin', service.rendered);
						break;
					}

				}
			}
		}

		// No service matched - use the default template and pass undefined as the service argument
		if (!serviceUsed) {
			SgCookieOptin.emitBeforeExternalContentReplacedEvent(parent, externalContent, container, service);
			container.insertAdjacentHTML('afterbegin', SgCookieOptin.jsonData.mustacheData.iframeReplacement.markup);
		}

		// Set the backgrond image
		let backgroundImage;
		if (typeof externalContent.dataset.sgCookieOptinBackgroundImage !== 'undefined') {
			backgroundImage = externalContent.dataset.sgCookieOptinBackgroundImage;
		} else if (service && service.replacement_background_image !== '') {
			backgroundImage = service.replacement_background_image;
		} else if (typeof SgCookieOptin.jsonData.settings.iframe_replacement_background_image !== 'undefined'
			&& SgCookieOptin.jsonData.settings.iframe_replacement_background_image !== '') {
			backgroundImage = SgCookieOptin.jsonData.settings.iframe_replacement_background_image;
		}

		if (backgroundImage) {
			container.style.backgroundImage = 'url(' + backgroundImage + ')';
			container.style.backgroundSize = 'cover';
		}
	},

	/**
	 * Emits the event that takes place right before we replace the external content with the replacement
	 *
	 * @param {Object} parent
	 * @param {HTMLElement} externalContent
	 * @param {HTMLElement} container
	 * @param {null|Object} service
	 */
	emitBeforeExternalContentReplacedEvent: function(parent, externalContent, container, service) {
		// Emit event for replaced content
		const beforeExternalContentReplaced = new CustomEvent('beforeExternalContentReplaced', {
			bubbles: true,
			detail: {
				parent: parent,
				externalContent: externalContent,
				container: container,
				service: service
			}
		});
		document.dispatchEvent(beforeExternalContentReplaced);
	},

	/**
	 * Sets external content description text bellow the button and in the settings description
	 * @param {HTMLElement} wrapper
	 * @param {HTMLElement} externalContent
	 */
	setExternalContentDescriptionText: function(wrapper, externalContent) {
		const flashMessageContainer = wrapper.querySelector('.sg-cookie-optin-box-flash-message');
		if (flashMessageContainer !== null) {
			let flashMessageText = externalContent.getAttribute('data-consent-description');
			// fallback to default if no data attribute has been set
			if (!flashMessageText) {
				flashMessageText = SgCookieOptin.jsonData.textEntries.iframe_button_load_one_description;
			}

			if (flashMessageText) {
				flashMessageContainer.appendChild(document.createTextNode(flashMessageText));
			} else {
				flashMessageContainer.remove();
			}
		}
	},

	/**
	 * Adds a consent for iFrames, which needs to be accepted.
	 *
	 * @return {void}
	 */
	openExternalContentConsent: function() {
		const parent = this.parentElement;
		if (!parent) {
			return;
		}

		const externalContentId = parent.getAttribute('data-iframe-id');
		if (!externalContentId) {
			return;
		}

		SgCookieOptin.lastOpenedExternalContentId = externalContentId;
		const externalContent = SgCookieOptin.protectedExternalContents[externalContentId];
		if (!externalContent) {
			return;
		}

		const wrapper = document.createElement('DIV');
		wrapper.id = 'SgCookieOptin';
		wrapper.insertAdjacentHTML('afterbegin', SgCookieOptin.jsonData.mustacheData.iframe.markup);

		SgCookieOptin.setExternalContentDescriptionText(wrapper, externalContent);
		SgCookieOptin.addExternalContentListeners(wrapper);

		document.body.insertAdjacentElement('afterbegin', wrapper);

		// focus the first button for better accessability
		const buttons = document.getElementsByClassName('sg-cookie-optin-box-button-accept-all');
		if (buttons.length > 0) {
			if (buttons[0].focus) {
				buttons[0].focus();
			}
		}

		// Emit event
		const externalContentConsentDisplayedEvent = new CustomEvent('externalContentConsentDisplayed', {
			bubbles: true,
			detail: {}
		});
		wrapper.dispatchEvent(externalContentConsentDisplayedEvent);
	},

	/**
	 * Adds the listeners to the given element.
	 *
	 * @param {dom} element
	 *
	 * @return {void}
	 */
	addExternalContentListeners: function(element) {
		const closeButtons = element.querySelectorAll('.sg-cookie-optin-box-close-button');
		SgCookieOptin.addEventListenerToList(closeButtons, 'click', SgCookieOptin.hideCookieOptIn);

		const rejectButtons = element.querySelectorAll('.sg-cookie-optin-box-button-iframe-reject');
		SgCookieOptin.addEventListenerToList(rejectButtons, 'click', SgCookieOptin.hideCookieOptIn);

		const acceptAllButtons = element.querySelectorAll('.sg-cookie-optin-box-button-accept-all');
		SgCookieOptin.addEventListenerToList(acceptAllButtons, 'click', function() {
			SgCookieOptin.acceptAllExternalContents();
			SgCookieOptin.acceptExternalConentGroup();
			SgCookieOptin.updateCookieList();
			SgCookieOptin.handleScriptActivations();
			SgCookieOptin.hideCookieOptIn();
		});

		const acceptSpecificButtons = element.querySelectorAll('.sg-cookie-optin-box-button-accept-specific');
		SgCookieOptin.addEventListenerToList(acceptSpecificButtons, 'click', function() {
			SgCookieOptin.acceptExternalContent(SgCookieOptin.lastOpenedExternalContentId);
			SgCookieOptin.updateCookieList();
			SgCookieOptin.handleScriptActivations();
			SgCookieOptin.hideCookieOptIn();
		});
	},

	/**
	 * Replaces all external content consent containers with the corresponding external content and adapts the cookie for further requests.
	 *
	 * @return {void}
	 */
	acceptAllExternalContents: function() {
		if (SgCookieOptin.jsonData.settings.iframe_enabled && SgCookieOptin.externalContentObserver) {
			SgCookieOptin.externalContentObserver.disconnect();
		}

		for (let index in SgCookieOptin.protectedExternalContents) {
			index = parseInt(index);
			if (!document.querySelector('div[data-iframe-id="' + index + '"]')) {
				continue;
			}

			SgCookieOptin.acceptExternalContent(index)
		}
	},

	/**
	 * Accepts the external content group and updates the cookie value if needed
	 *
	 * @return {void}
	 */
	acceptExternalConentGroup: function() {
		const cookieValue = SgCookieOptin.getCookie(SgCookieOptin.COOKIE_NAME);

		let groupFound = false;
		let newCookieValue = '';
		const splitedCookieValue = cookieValue.split('|');
		for (const splitedCookieValueIndex in splitedCookieValue) {
			if (!splitedCookieValue.hasOwnProperty(splitedCookieValueIndex)) {
				continue;
			}

			const splitedCookieValueEntry = splitedCookieValue[splitedCookieValueIndex];
			const groupAndStatus = splitedCookieValueEntry.split(':');
			if (!groupAndStatus.hasOwnProperty(0) || !groupAndStatus.hasOwnProperty(1)) {
				continue;
			}

			const group = groupAndStatus[0];
			let status = parseInt(groupAndStatus[1]);
			if (group === SgCookieOptin.COOKIE_GROUP_EXTERNAL_CONTENT) {
				groupFound = true;
				status = 1;
			}

			if (newCookieValue.length > 0) {
				newCookieValue += '|';
			}
			newCookieValue += group + ':' + status;
		}

		if (!groupFound) {
			newCookieValue += '|' + SgCookieOptin.COOKIE_GROUP_EXTERNAL_CONTENT + ':' + 1;
		}

		if (cookieValue !== newCookieValue) {
			SgCookieOptin.setCookieWrapper(newCookieValue);
		}
	},

	/**
	 * Replaces an external content consent container with the external content element.
	 *
	 * @param {string} externalContentId
	 * @return {void}
	 */
	acceptExternalContent: function(externalContentId) {
		if (!externalContentId) {
			externalContentId = parent.getAttribute('data-iframe-id');
			if (!externalContentId) {
				return;
			}
		}

		const container = document.querySelector('div[data-iframe-id="' + externalContentId + '"]');
		const externalContent = SgCookieOptin.protectedExternalContents[externalContentId];
		if (!externalContent || !container) {
			return;
		}

		externalContent.setAttribute('data-iframe-allow-always', 1);
		const positionIndex = externalContent.getAttribute('data-iframe-position-index');
		externalContent.removeAttribute('data-iframe-position-index');

		// Because of the IE11 no .replaceWith();
		const parentNode = container.parentNode;
		parentNode.removeChild(container);
		if (parentNode.childNodes.length > 0) {
			parentNode.insertBefore(externalContent, parentNode.childNodes[positionIndex]);
		} else {
			parentNode.appendChild(externalContent);
		}

		SgCookieOptin.fixExternalContentAttributes(externalContent);
		SgCookieOptin.emitExternalContentAcceptedEvent(externalContent);
	},

	/**
	 * Emit event when the external content element has been accepted
	 * @param {HTMLElement} externalContent
	 */
	emitExternalContentAcceptedEvent: function(externalContent) {
		const externalContentAcceptedEvent = new CustomEvent('externalContentAccepted', {
			bubbles: true,
			detail: {
				externalContent: externalContent
			}
		});
		externalContent.dispatchEvent(externalContentAcceptedEvent);
	},

	/**
	 * Accept the external contents nested into the given container
	 *
	 * @param {HTMLElement} container
	 */
	acceptContainerChildNodes: function(container) {
		const replacedChildren = container.querySelectorAll('[data-iframe-id]');
		if (replacedChildren.length > 0) {
			for (const childIndex in replacedChildren) {
				const childElement = replacedChildren[childIndex];
				if (typeof childElement.getAttribute !== 'function') {
					continue;
				}
				const externalContentId = childElement.getAttribute('data-iframe-id');
				if (!externalContentId) {
					continue;
				}
				SgCookieOptin.acceptExternalContent(externalContentId);
			}
		}
	},

	/**
	 * todo Optimize the cookie handling with hasSetting, addSetting, removeSetting and use it everywhere.
	 *
	 * Returns the cookie, found with the given name, or null.
	 *
	 * @param {string} name
	 * @return {string}
	 */
	getCookie: function(name) {
		const v = document.cookie.match('(^|;) ?' + name + '=([^;]*)(;|$)');
		return v ? v[2] : null;
	},

	/**
	 * Sets the given cookie with the given value for X days.
	 *
	 * @param {string} name
	 * @param {string} value
	 * @param {string} days
	 */
	setCookie: function(name, value, days) {
		const d = new Date;
		d.setTime(d.getTime() + 24 * 60 * 60 * 1000 * days);
		let cookie = name + '=' + value + ';path=/';

		const currentHost = window.location.hostname;
		const cookieStringEnd = ';expires=' + d.toUTCString() + '; SameSite=None; Secure';

		if (SgCookieOptin.jsonData.settings.set_cookie_for_domain && SgCookieOptin.jsonData.settings.set_cookie_for_domain.length > 0) {
			cookie += ';domain=' + SgCookieOptin.jsonData.settings.set_cookie_for_domain;
		} else if (SgCookieOptin.jsonData.settings.subdomain_support) {
			const domainParts = currentHost.split('.');
			if (domainParts.length > 2) {
				domainParts.shift();
				const hostnameToFirstDot = '.' + domainParts.join('.');
				cookie += ';domain=' + hostnameToFirstDot;
			}
		}

		document.cookie = cookie + cookieStringEnd;
	},

	/**
	 * Sets the given cookie with the given value only for the current session.
	 *
	 * @param {string} name
	 * @param {string} value
	 */
	setSessionCookie: function(name, value) {
		let cookie = name + '=' + value + '; path=/';

		const currentHost = window.location.hostname;
		const cookieStringEnd = ';SameSite=None; Secure';

		if (SgCookieOptin.jsonData.settings.set_cookie_for_domain && SgCookieOptin.jsonData.settings.set_cookie_for_domain.length > 0) {
			cookie += ';domain=' + SgCookieOptin.jsonData.settings.set_cookie_for_domain;
		} else if (SgCookieOptin.jsonData.settings.subdomain_support) {
			const domainParts = currentHost.split('.');
			if (domainParts.length > 2) {
				domainParts.shift();
				const hostnameToFirstDot = '.' + domainParts.join('.');
				cookie += ';domain=' + hostnameToFirstDot;
			}
		}

		document.cookie = cookie + cookieStringEnd;
	},

	/**
	 * Cookie is set with lifetime if the user has accepted a non-essential group that exists.
	 *
	 * @param {string} cookieValue
	 */
	setCookieWrapper: function(cookieValue) {
		let setCookieForSessionOnly = false;
		if (SgCookieOptin.jsonData.settings.session_only_essential_cookies) {
			let hasNonEssentialGroups = false;
			let hasAcceptedNonEssentials = false;
			const splitCookieValue = cookieValue.split('|');
			for (const cookieValueIndex in splitCookieValue) {
				if (!splitCookieValue.hasOwnProperty(cookieValueIndex)) {
					continue;
				}

				const valueEntry = splitCookieValue[cookieValueIndex];
				if (valueEntry.indexOf(SgCookieOptin.COOKIE_GROUP_ESSENTIAL) === 0 || valueEntry.indexOf(SgCookieOptin.COOKIE_GROUP_EXTERNAL_CONTENT) === 0) {
					continue;
				}

				hasNonEssentialGroups = true;
				if (valueEntry.indexOf(':1') > 0) {
					hasAcceptedNonEssentials = true;
					break;
				}
			}

			setCookieForSessionOnly = hasNonEssentialGroups && !(hasNonEssentialGroups && hasAcceptedNonEssentials);
		}

		if (setCookieForSessionOnly) {
			SgCookieOptin.setSessionCookie(SgCookieOptin.COOKIE_NAME, cookieValue);
		} else {
			SgCookieOptin.setCookie(SgCookieOptin.COOKIE_NAME, cookieValue, SgCookieOptin.jsonData.settings.cookie_lifetime);
		}

		SgCookieOptin.saveLastPreferences(cookieValue);
		SgCookieOptin.deleteCookiesForUnsetGroups();

		// Emit event
		const consentCookieSetEvent = new CustomEvent('consentCookieSet', {
			bubbles: true,
			detail: {
				cookieValue: cookieValue
			}
		});
		document.body.dispatchEvent(consentCookieSetEvent);
	},

	/**
	 * Displays a notification as a box as first element in the given contentElement
	 *
	 * @param {Element} contentElement
	 */
	showSaveConfirmation: function(contentElement) {
		const oldNotification = contentElement.firstChild;
		if (!oldNotification.classList.contains('sg-cookie-optin-save-confirmation')) {
			const notification = document.createElement('DIV');
			notification.classList.add('sg-cookie-optin-save-confirmation');
			notification.insertAdjacentText('afterbegin', SgCookieOptin.jsonData.textEntries.save_confirmation_text);
			contentElement.insertBefore(notification, contentElement.firstChild);
		}
	},

	/**
	 * Returns the value of a query parameter as a string, or null on error.
	 *
	 * @param {string} name
	 * @param {string} url
	 * @return {string|null}
	 */
	getParameterByName: function(name, url) {
		if (!url) {
			url = window.location.href;
		}

		name = name.replace(/[\[\]]/g, '\\$&');
		const regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
			results = regex.exec(url);
		if (!results) {
			return null;
		}

		if (!results[2]) {
			return '';
		}

		return decodeURIComponent(results[2].replace(/\+/g, ' '));
	},

	/**
	 * Returns all the protected and unaccepted elements that match the given selector
	 *
	 * @param {string} selector
	 * @returns {[]}
	 */
	findProtectedElementsBySelector: function(selector) {
		const foundElements = [];
		for (const elementIndex in SgCookieOptin.protectedExternalContents) {
			if (typeof SgCookieOptin.protectedExternalContents[elementIndex].matches === 'function'
				&& SgCookieOptin.protectedExternalContents[elementIndex].matches(selector)) {
				foundElements.push(SgCookieOptin.protectedExternalContents[elementIndex]);
			}
		}
		return foundElements;
	},

	/**
	 * Adds a trigger function to all protected elements that will be fired when the respective element has been granted
	 * consent. You may filter the elements by a CSS selector.
	 *
	 * @param {function} callback
	 * @param {string} selector
	 */
	addAcceptHandlerToProtectedElements: function(callback, selector) {
		if (typeof callback !== 'function') {
			throw new Error('Required argument "callback" has not been passed.');
		}

		if (!selector) {
			selector = '*';
		}

		const elements = SgCookieOptin.findProtectedElementsBySelector(selector);
		if (elements.length > 0) {
			SgCookieOptin.addEventListenerToList(elements, 'externalContentAccepted', callback);
		} else {
			// workaround for when the external group has been accepted and we have no protected elements
			if (SgCookieOptin.isExternalGroupAccepted) {
				SgCookieOptin.triggerAcceptedEventListenerToExternalContentElements(callback, selector);
			}
		}
	},

	/**
	 * Filters the elements that would have been replaced by the external protection by the given selector and triggers
	 * the ExternalContentAccepted Event for each of the elements found
	 *
	 * @param {function} callback
	 * @param {string} selector
	 */
	triggerAcceptedEventListenerToExternalContentElements: function(callback, selector) {
		let index;
		const elements = [];
		const externalContentElements = document.querySelectorAll(SgCookieOptin.EXTERNAL_CONTENT_ELEMENT_SELECTOR);
		for (index = 0; index < externalContentElements.length; ++index) {
			if (typeof externalContentElements[index].matches === 'function' && externalContentElements[index].matches(selector)) {
				elements.push(externalContentElements[index]);
			}
		}

		if (elements.length > 0) {
			// add the listener with the same callback to keep the same API
			SgCookieOptin.addEventListenerToList(elements, 'externalContentAccepted', callback);
			// trigger the event immediately
			for (index = 0; index < elements.length; ++index) {
				SgCookieOptin.emitExternalContentAcceptedEvent(elements[index]);
			}
		}
	},

	/**
	 * Polyfill for the Element.matches and Element.closest
	 */
	closestPolyfill: function() {
		const ElementPrototype = window.Element.prototype;

		if (typeof ElementPrototype.matches !== 'function') {
			ElementPrototype.matches = ElementPrototype.msMatchesSelector || ElementPrototype.mozMatchesSelector || ElementPrototype.webkitMatchesSelector || function matches(selector) {
				const element = this;
				const elements = (element.document || element.ownerDocument).querySelectorAll(selector);
				let index = 0;

				while (elements[index] && elements[index] !== element) {
					++index;
				}

				return Boolean(elements[index]);
			};
		}

		if (typeof ElementPrototype.closest !== 'function') {
			ElementPrototype.closest = function closest(selector) {
				let element = this;

				while (element && element.nodeType === 1) {
					if (element.matches(selector)) {
						return element;
					}

					element = element.parentNode;
				}

				return null;
			};
		}
	},

	/**
	 * Adds the Polyfill for the Window.CustomEvent
	 * @returns {boolean}
	 */
	customEventPolyfill: function() {
		if (typeof window.CustomEvent === "function") {
			return false;
		}

		function CustomEvent(event, params) {
			params = params || {bubbles: false, cancelable: false, detail: null};
			const evt = document.createEvent('CustomEvent');
			evt.initCustomEvent(event, params.bubbles, params.cancelable, params.detail);
			return evt;
		}

		window.CustomEvent = CustomEvent;
	},

	/**
	 * Get the last preferences object in the local storage
	 *
	 * @return {Object}|undefined
	 */
	getLastPreferences: function() {
		let lastPreferences;
		if (SgCookieOptin.lastPreferencesFromCookie()) {
			lastPreferences = SgCookieOptin.getCookie(SgCookieOptin.LAST_PREFERENCES_COOKIE_NAME);
		} else {
			lastPreferences = window.localStorage.getItem(SgCookieOptin.LAST_PREFERENCES_LOCAL_STORAGE_NAME);
		}

		if (!lastPreferences) {
			return {};
		}

		try {
			lastPreferences = JSON.parse(lastPreferences);
			return lastPreferences;
		} catch (e) { // we don't want to break the rest of the code if the JSON is malformed for some reason
			return {};
		}
	},

	/**
	 * Replaces the data- attribute placeholders for source URLs with actual attributes depending on the element type
	 *
	 * @param {HTMLElement}externalContent
	 * @return {void}
	 */
	fixExternalContentAttributes: function(externalContent) {
		switch (externalContent.tagName) {
			case 'IFRAME':
				return SgCookieOptin.fixExternalContentAttribute(externalContent, 'src');
			case 'OBJECT':
				return SgCookieOptin.fixExternalContentAttribute(externalContent, 'data');
			default:
				return;
		}
	},

	/**
	 * Replaces the data-src and data-data with actual src and data attributes
	 *
	 * @param {HTMLElement} externalContent
	 * @param {string} attribute
	 */
	fixExternalContentAttribute: function(externalContent, attribute) {
		if (externalContent.dataset['contentReplaceSrc']) {
			externalContent.setAttribute(attribute, externalContent.dataset['contentReplaceSrc']);
		}
	},

	/**
	 * Checks if there are any external contents with data-src attributes and sets their real attributes to the
	 * data-attribute value
	 */
	checkForDataAttributeExternalContents: function() {
		if (SgCookieOptin.jsonData.settings.iframe_enabled && SgCookieOptin.isExternalGroupAccepted) {
			const externalContents = document.querySelectorAll(SgCookieOptin.EXTERNAL_CONTENT_ELEMENT_SELECTOR);
			if (externalContents.length > 0) {
				for (const externalContentToFixIndex in externalContents) {
					if (!externalContents.hasOwnProperty(externalContentToFixIndex)) {
						continue;
					}

					SgCookieOptin.fixExternalContentAttributes(externalContents[externalContentToFixIndex]);
				}
			}
		}
	},

	/**
	 * Shows the fingerprint and creates the element if necessary
	 */
	showFingerprint: function() {
		// noinspection EqualityComparisonWithCoercionJS
		// checkbox and position must be set properly
		if (!(SgCookieOptin.jsonData.settings.show_fingerprint
			&& SgCookieOptin.jsonData.settings.fingerprint_position > 0)) {
			return;
		}

		if (SgCookieOptin.fingerprintIcon === null) {
			SgCookieOptin.fingerprintIcon = SgCookieOptin.createFingeprint();
		}

		SgCookieOptin.fingerprintIcon.style.display = 'block';
	},

	/**
	 * Hides the fingerprint
	 */
	hideFingerprint: function() {
		if (SgCookieOptin.fingerprintIcon === null || !SgCookieOptin.shouldShowOptinBanner()) {
			return;
		}

		SgCookieOptin.fingerprintIcon.style.display = 'none';
	},

	/**
	 * Creates the fingerprint HTML element
	 * @return {HTMLDivElement}
	 */
	createFingeprint: function() {
		const fingerprintContainer = document.createElement('div');
		let iconPositionClass = '';

		switch (SgCookieOptin.jsonData.settings.fingerprint_position) {
			case 1:
				iconPositionClass = 'bottom-left';
				break;
			case 2:
				iconPositionClass = 'bottom-right';
				break;
			case 3:
				iconPositionClass = 'top-left';
				break;
			case 4:
				iconPositionClass = 'top-right';
				break;
		}

		fingerprintContainer.classList.add('sg-cookie-optin-fingerprint');
		fingerprintContainer.classList.add('sg-cookie-optin-fingerprint-' + iconPositionClass);
		fingerprintContainer.addEventListener('click', function(e) {
			SgCookieOptin.openCookieOptin(null, {hideBanner: true});
		});

		fingerprintContainer.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" xml:space="preserve" fill="currentColor" class="sg-cookie-optin-fingerprint-icon" viewBox="0 0 512 512"><path d="M275.334 511c-5.207 0-8.678-4.339-8.678-8.678 6.075-182.237-16.488-256.868-17.356-257.736-1.736-4.339.868-9.546 5.207-11.281 4.339-1.736 9.546.868 11.281 5.207.868 3.471 24.298 77.234 17.356 262.942.868 6.075-3.471 9.546-7.81 9.546zM491.415 294.051c-4.339 0-7.81-3.471-8.678-7.81-6.075-58.142-14.753-104.136-25.166-128.434C423.727 77.102 345.625 25.034 257.978 25.034c-28.637 0-57.275 5.207-83.308 16.488-14.753 6.075-28.637 13.885-41.654 21.695-4.339 2.603-9.546 1.736-12.149-2.603-2.603-4.339-1.736-9.546 2.603-12.149 13.885-8.678 28.637-16.488 45.125-23.431 27.769-11.281 58.142-17.356 89.383-17.356 94.59 0 178.766 56.407 216.081 143.186 13.885 33.844 22.563 91.119 26.034 133.641.868 5.207-2.604 8.678-8.678 9.546.868 0 0 0 0 0zM43.632 363.475c-3.471 0-6.942-2.603-8.678-6.075-43.39-145.79 2.603-231.702 49.464-277.695 3.471-3.471 8.678-3.471 12.149 0s3.471 8.678 0 12.149C34.086 152.6 18.466 242.851 51.442 352.193c1.736 4.339-.868 9.546-6.075 10.414 0 .868-.867.868-1.735.868z" style="fill:currentColor" transform="translate(1 1)"/><path d="M100.906 450.254c-5.207 0-8.678-3.471-8.678-8.678 0-61.614 0-101.532-3.471-122.359-1.736-9.546-4.339-19.092-6.075-27.769-25.165-88.516 19.96-182.238 105.004-217.817 24.298-10.414 50.332-14.753 77.234-13.885 69.424 2.603 144.922 60.746 168.353 130.169 19.092 56.407 19.092 150.129 17.356 234.305 0 5.207-4.339 8.678-8.678 8.678-5.207 0-8.678-4.339-8.678-8.678 2.603-82.441 1.736-174.427-16.488-228.23-20.827-62.481-91.119-116.285-152.732-118.888-24.298-.868-47.729 3.471-69.424 13.017-77.234 32.108-118.02 117.153-95.458 196.99 2.603 9.546 5.207 19.959 6.942 29.505 3.471 22.563 3.471 62.481 3.471 124.963 0 5.206-3.471 8.677-8.678 8.677z" style="fill:currentColor" transform="translate(1 1)"/><path d="M388.147 476.288c-5.207 0-8.678-4.339-8.678-8.678 5.207-139.715 3.471-219.553-17.356-269.017-17.356-41.654-58.142-68.556-104.136-68.556-7.81 0-16.488.868-24.298 2.603-4.339.868-9.546-1.736-10.414-6.942-.868-4.339 1.736-9.546 6.942-10.414 9.546-1.736 18.224-2.603 27.77-2.603 52.068 0 99.797 31.241 119.756 79.837 22.563 52.936 24.298 131.905 19.092 276.827 0 3.472-4.339 6.943-8.678 6.943zm-226.495-17.356c-5.207 0-8.678-3.471-8.678-8.678 0-99.797-17.356-164.014-18.224-164.881-15.62-58.142 3.471-118.888 48.597-150.129 4.339-2.603 9.546-1.736 12.149 1.736 2.603 4.339 1.736 9.546-1.736 12.149-39.051 26.902-55.539 79.837-41.654 131.037.868 2.603 18.224 66.82 18.224 169.22 0 6.075-3.471 9.546-8.678 9.546zM336.079 502.322c-5.207 0-8.678-4.339-8.678-8.678v-42.522c0-10.414-.868-19.959 0-26.902 0-4.339 4.339-8.678 8.678-8.678 5.207 0 8.678 4.339 8.678 8.678v26.034c0 14.753.868 32.108 0 43.39 0 5.207-4.339 8.678-8.678 8.678z" style="fill:currentColor" transform="translate(1 1)"/><path d="M213.72 502.322c-5.207 0-8.678-3.471-8.678-8.678 0-103.268-5.207-207.403-12.149-227.363-12.149-36.447 4.339-74.631 38.183-88.515 8.678-3.471 17.356-5.207 26.902-5.207 27.77 0 52.936 16.488 64.217 42.522 17.356 40.786 22.563 112.814 23.431 165.749 0 5.207-3.471 8.678-8.678 8.678-4.339 0-8.678-3.471-8.678-8.678-.868-51.2-6.075-120.624-22.563-158.807-7.81-19.959-26.902-32.108-47.729-32.108-6.942 0-13.885 1.736-19.959 4.339-25.166 10.414-37.315 39.051-28.637 66.82 7.81 24.298 13.017 139.715 13.017 232.569-.001 5.208-3.472 8.679-8.679 8.679z" style="fill:currentColor" transform="translate(1 1)"/><path d="M267.523 511c-5.207 0-8.678-4.339-8.678-8.678 6.075-183.105-16.488-265.546-17.356-266.414-1.736-4.339 1.736-9.546 6.075-10.414 4.339-1.736 9.546 1.736 10.414 6.075.868 3.471 24.298 85.912 17.356 271.62.867 4.34-3.472 7.811-7.811 7.811zM483.605 285.373c-4.339 0-7.81-3.471-8.678-7.81-3.471-33.844-11.281-95.458-25.166-128.434C415.917 68.424 337.815 16.356 250.167 16.356c-28.637 0-57.275 5.207-83.308 16.488-14.753 6.075-28.637 13.885-41.654 21.695-4.339 2.603-9.546 1.736-12.149-2.603-2.603-4.339-1.736-9.546 2.603-12.149 13.885-8.678 28.637-16.488 45.125-23.431C188.554 5.075 218.927-1 250.167-1c94.59 0 178.766 56.407 216.081 143.186 13.885 33.844 22.563 91.119 26.034 133.641.001 5.207-3.471 8.678-8.677 9.546.868 0 0 0 0 0zM35.822 354.797c-3.471 0-6.942-2.603-8.678-6.075-43.39-145.79 2.603-231.702 49.464-277.695 3.471-3.471 8.678-3.471 12.149 0 3.471 3.471 3.471 8.678 0 12.149-62.481 60.746-78.102 150.997-45.125 260.339 1.736 4.339-.868 9.546-6.075 10.414 0 .868-.868.868-1.735.868z" style="fill:currentColor" transform="translate(1 1)"/><path d="M93.096 441.576c-5.207 0-8.678-3.471-8.678-8.678 0-61.614 0-101.532-3.471-122.359-1.736-9.546-3.471-18.224-6.075-27.77-25.166-88.515 19.96-182.237 105.004-217.816 24.298-10.414 50.332-14.753 77.234-13.885 69.424 2.603 144.922 60.746 168.353 130.169 19.092 56.407 19.092 150.129 17.356 234.305 0 5.207-4.339 8.678-8.678 8.678-5.207 0-8.678-4.339-8.678-8.678 2.603-82.441 1.736-174.427-16.488-228.23-20.827-62.481-91.119-116.285-152.732-118.888-24.298-.868-47.729 3.471-69.424 13.017-77.234 32.108-118.02 117.153-95.458 196.99 2.603 9.546 5.207 19.959 6.942 29.505 3.471 22.563 3.471 62.481 3.471 124.963 0 5.206-3.471 8.677-8.678 8.677z" style="fill:currentColor" transform="translate(1 1)"/><path d="M380.337 467.61c-5.207 0-8.678-4.339-8.678-8.678 5.207-139.715 3.471-219.553-17.356-269.017-17.356-41.654-58.142-68.556-104.136-68.556-7.81 0-16.488.868-24.298 2.603-4.339.868-9.546-1.736-10.414-6.942-.868-4.339 1.736-9.546 6.942-10.414 9.546-1.736 18.224-2.603 27.77-2.603 52.068 0 99.797 31.241 119.756 79.837 22.563 52.936 24.298 131.905 19.092 276.827 0 3.472-4.339 6.943-8.678 6.943zm-226.495-17.356c-5.207 0-8.678-3.471-8.678-8.678 0-99.797-17.356-164.014-18.224-164.881-15.62-58.142 3.471-118.888 48.597-150.129 4.339-2.603 9.546-1.736 12.149 1.736 2.603 4.339 1.736 9.546-1.736 12.149-39.051 26.902-55.539 79.837-41.654 131.037.868 3.471 18.224 67.688 18.224 170.088 0 5.207-3.471 8.678-8.678 8.678zM328.269 493.644c-5.207 0-8.678-4.339-8.678-8.678v-42.522c0-10.414-.868-19.959 0-26.034 0-4.339 4.339-8.678 8.678-8.678 5.207 0 8.678 4.339 8.678 8.678v26.034c0 15.62.868 32.108 0 43.39 0 4.339-4.339 7.81-8.678 7.81z" style="fill:currentColor" transform="translate(1 1)"/><path d="M205.91 493.644c-5.207 0-8.678-3.471-8.678-8.678 0-103.268-5.207-207.403-12.149-227.363-12.149-36.447 4.339-74.631 38.183-88.515 8.678-3.471 17.356-5.207 26.902-5.207 27.769 0 52.936 16.488 64.217 42.522 17.356 40.786 22.563 112.814 23.431 165.749 0 5.207-3.471 8.678-8.678 8.678-4.339 0-8.678-3.471-8.678-8.678-.868-51.2-6.075-120.624-22.563-159.675-7.81-19.092-26.902-32.108-47.729-32.108-6.942 0-13.885 1.736-19.959 4.339-25.166 10.414-37.315 39.051-28.637 66.82 7.81 25.166 13.017 140.583 13.017 233.437-.001 5.208-3.472 8.679-8.679 8.679z" style="fill:currentColor" transform="translate(1 1)"/></svg>`;
		document.body.appendChild(fingerprintContainer);

		// Emit event
		const fingerprintCreatedEvent = new CustomEvent('fingerprintCreated', {
			bubbles: true,
			detail: {}
		});
		document.dispatchEvent(fingerprintCreatedEvent);

		return fingerprintContainer;
	}
};

SgCookieOptin.run();
