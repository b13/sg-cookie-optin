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
import Chart from 'TYPO3/CMS/SgCookieOptin/Backend/Chart.js/Chart.min';
import Formatter from 'TYPO3/CMS/SgCookieOptin/Backend/Chart.js/datalabels.min';

const Statistics = {
	chart: null,

	params: {
		from_date: document.getElementById('from_date').value,
		to_date: document.getElementById('to_date').value,
		version: document.getElementById('version').value,
		pid: 0,
	},

	maxPages: 10,

	init() {
		this.setDefaultValues();
		this.setEventListeners();
		const params = this.getParameterValuesFromForm();
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
			version: document.getElementById('version').value.trim(),
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

	updateCharts(data) {
		this.removeCharts();
		const chartsContainer = document.getElementById('consent-statistics-charts-container');
		for (let identifier in data) {
			if (data.hasOwnProperty(identifier)) {
				this.addChart(chartsContainer, data[identifier], identifier);
			}
		}
	},

	addChart(container, dataEntry, identifier) {
		const chartDivContainer = document.createElement('DIV');
		chartDivContainer.className = 'consent-statistics-chart-div-container';
		const chartContainer = document.createElement('CANVAS');
		chartDivContainer.append(chartContainer);
		container.append(chartDivContainer);
		chartContainer.setAttribute('width', 200);
		chartContainer.setAttribute('height', 100);

		const labels = [];
		const datasets = [];
		const colors = [];
		for (let label in dataEntry) {
			if (dataEntry.hasOwnProperty(label)) {
				labels.push(label);
				datasets.push(dataEntry[label].value);
				colors.push(dataEntry[label].color);
			}
		}

		const chartData = {
			datasets: [{
				data: datasets,
				backgroundColor: colors,
			}],
			labels: labels,
		};

		this.chart = new Chart(chartContainer, {
			type: 'pie',
			data: chartData,
			options: {
				title: {
					text: identifier,
					display: true,
					position: 'top'
				},
				tooltips: {
					enabled: true,
					callbacks: {
						label: function(tooltipItem, data) {
							let label = data.labels[tooltipItem.index] || '';
							if (label) {
								label += ': ' + data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
							}
							const total = data.datasets[tooltipItem.datasetIndex].data.reduce((a, b) => a + b, 0);
							if (total > 0) {
								label += ` (${Math.round(data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index] / total * 100)}%)`;
							}
							return label;
						}
					}
				},
				plugins: {
					datalabels: {
						formatter: (value, ctx) => {
							const sum = ctx.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
							const percentage = (value * 100 / sum).toFixed(2) + '%';
							return percentage;
						},
						color: '#FFF',
					}
				},
				responsive: true
			}
		});
	},

	removeCharts() {
		if (this.chart) {
			this.chart.destroy();
		}
		const chartsContainer = document.getElementById('consent-statistics-charts-container');
		while (chartsContainer.firstChild) {
			chartsContainer.firstChild.remove();
		}
	},

	performSearch(params) {
		const request = new XMLHttpRequest();
		request.open('POST', TYPO3.settings.ajaxUrls['sg_cookie_optin::searchUserPreferenceHistoryChart'], true);
		const formData = new FormData();
		formData.append('params', JSON.stringify(params));
		request.that = this;

		request.onload = function() {
			if (this.status >= 200 && this.status < 400) {
				const data = JSON.parse(this.response);
				if (Object.keys(data).length > 0) {
					document.getElementById('statistics-no-data-found').style.display = 'none';
					setTimeout(function() {
						this.that.updateCharts(data);
					}.bind(this), 100);
				} else {
					this.that.removeCharts();
					document.getElementById('statistics-no-data-found').style.display = 'block';
				}
			} else {
				this.onSearchError();
			}
		};

		request.onerror = this.onSearchError;
		request.send(formData);
	},

	onSearchError(error) {
		console.log(error);
	},
};

Statistics.init();

export default Statistics;
