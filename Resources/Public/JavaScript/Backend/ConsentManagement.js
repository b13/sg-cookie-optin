/*
 *
 * Copyright notice
 *
 * (c) sgalinski Internet Services (https://www.sgalinski.de)
 *
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 */

import $ from 'jquery';

const ConsentManagement = {
	params: {
		from_date: document.getElementById('from_date').value,
		to_date: document.getElementById('to_date').value,
		user_hash: document.getElementById('user_hash').value.trim(),
		item_identifier: document.getElementById('item_identifier').value,
		page: 1,
		pid: 0,
		per_page: 25
	},

	maxPages: 10,

	init() {
		this.setDefaultValues();
		this.setEventListeners();
		let params = this.getParameterValuesFromForm();
		const url = new URL(document.location);
		params.pid = parseInt(url.searchParams.get('id'));
		this.setParams(params);
		this.performSearch(this.getParams());
	},

	refreshSearch() {
		this.setParams(this.getParameterValuesFromForm());
		this.performSearch(this.getParams());
	},

	getParameterValuesFromForm() {
		return {
			from_date: document.getElementById('from_date').value,
			to_date: document.getElementById('to_date').value,
			user_hash: document.getElementById('user_hash').value.trim(),
			item_identifier: document.getElementById('item_identifier').value,
			page: 1,
			per_page: this.params.per_page,
			pid: this.params.pid
		};
	},

	getParams() {
		return this.params;
	},

	setParams(params) {
		this.params = params;
	},

	setEventListeners() {
		$('#consent-statistics-submit').on('click', this.refreshSearch.bind(this));

		document.getElementById('consent-statistics-form').addEventListener('submit', function(event) {
			event.preventDefault();
			this.refreshSearch();
		}.bind(this), false);
	},

	setDefaultValues() {
		const today = new Date();
		document.getElementById('to_date').value = `${today.getFullYear()}-${(today.getMonth() + 1).toString().padStart(2, '0')}-${today.getDate().toString().padStart(2, '0')}`;

		const prevMonth = new Date();
		prevMonth.setMonth(prevMonth.getMonth() - 1);
		document.getElementById('from_date').value = `${prevMonth.getFullYear()}-${(prevMonth.getMonth() + 1).toString().padStart(2, '0')}-${prevMonth.getDate().toString().padStart(2, '0')}`;
	},

	performSearch(params) {
		const request = new XMLHttpRequest();
		request.open('POST', TYPO3.settings.ajaxUrls['sg_cookie_optin::searchUserPreferenceHistory'], true);
		const formData = new FormData();
		formData.append('params', JSON.stringify(params));

		request.onload = function() {
			if (this.status >= 200 && this.status < 400) {
				const data = JSON.parse(this.response);
				if (data.count > 0) {
					document.getElementById('statistics-no-data-found').style.display = 'none';
					document.getElementById('consent-statistics-grid').style.display = 'block';
					ConsentManagement.updateTable(data);
				} else {
					document.getElementById('statistics-no-data-found').style.display = 'block';
					document.getElementById('consent-statistics-grid').style.display = 'none';
				}
			} else {
				ConsentManagement.onSearchError();
			}
		};

		request.onerror = ConsentManagement.onSearchError;
		request.send(formData);
	},

	onSearchError(error) {
		console.log(error);
	},

	updateTable(data) {
		const tableBody = document.querySelector('#consent-statistics-grid tbody');
		while (tableBody.firstChild) {
			tableBody.firstChild.remove();
		}

		data.data.forEach(row => {
			this.addTableRow(tableBody, row);
		});

		this.addPagination(data.count);
	},

	addTableRow(tableBody, dataRow) {
		const tr = document.createElement('TR');
		['tstamp', 'user_hash', 'item_identifier'].forEach(field => {
			const td = document.createElement('TD');
			td.innerText = dataRow[field];
			tr.append(td);
		});

		const td = document.createElement('TD');
		const iconString = dataRow.is_accepted ? '✅' : '❌';
		const icon = document.createTextNode(iconString);
		td.append(icon);
		tr.append(td);

		tableBody.append(tr);
	},

	addPagination(count) {
		const params = this.getParams();
		const pageCount = Math.ceil(count / params.per_page);
		const paginationDiv = document.getElementById('consent-statistics-grid-page-select');

		while (paginationDiv.firstChild) {
			paginationDiv.firstChild.remove();
		}

		let lowerLimit = Math.min(params.page, pageCount);
		let upperLimit = lowerLimit;

		for (let i = 1; i < this.maxPages && i < pageCount;) {
			if (lowerLimit > 1) {
				lowerLimit--;
				i++;
			}
			if (i < this.maxPages && upperLimit < pageCount) {
				upperLimit++;
				i++;
			}
		}

		if (lowerLimit > 1) {
			paginationDiv.append(this.createPaginationItem(1));
			paginationDiv.append(this.createPaginationSpacer(true));
		}

		for (let i = lowerLimit; i <= upperLimit; i++) {
			paginationDiv.append(this.createPaginationItem(i, i === params.page));
		}

		if (upperLimit < pageCount) {
			paginationDiv.append(this.createPaginationSpacer());
			paginationDiv.append(this.createPaginationItem(pageCount));
		}

		$('.page-link').on('click', (event) => {
			const num = parseInt(event.target.innerText);
			this.showPage(num);
		});
	},

	createPaginationItem(number, isActive = false) {
		const li = document.createElement('LI');
		li.className = `page-item${isActive ? ' active' : ''}`;
		const a = document.createElement('A');
		a.className = 'page-link';
		a.href = '#';
		a.innerText = number;
		li.append(a);
		return li;
	},

	createPaginationSpacer(isLeft = false) {
		const li = document.createElement('LI');
		li.className = 'page-item';
		const a = document.createElement('A');
		a.href = '#';
		a.className = 'page-link disabled';
		a.innerHTML = isLeft ? '&laquo;' : '&raquo;';
		li.append(a);
		return li;
	},

	showPage(num) {
		const params = this.getParams();
		params.page = num;
		this.setParams(params);
		this.performSearch(params);
	},
};

// Initialize ConsentManagement
ConsentManagement.init();

export default ConsentManagement;
