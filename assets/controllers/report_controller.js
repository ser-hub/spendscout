import { Controller } from '@hotwired/stimulus';
import * as api from './../api.js';
import Chart from 'chart.js/auto';

export default class extends Controller {
    static targets = [
        "reportChartTitle",
        "reportChart",
        "chartBorder",
        "reportFormCurrency",
        "reportFormTag",
        "reportFormDateFrom",
        "reportFormDateTo",
    ];

    connect() {
        this.endpoint = this.element.dataset.endpoint;
    }

    async applyFilters() {
        const pie = Chart.getChart('report-chart');
        const filters = {
            currencyId: this.reportFormCurrencyTarget.value,
            tagId: this.reportFormTagTarget.value == "Select tag" ? null : this.reportFormTagTarget.value,
            dateFrom: this.reportFormDateFromTarget.value,
            dateTo: this.reportFormDateToTarget.value 
        };

        const currencyCode = this.reportFormCurrencyTarget.options[this.reportFormCurrencyTarget.selectedIndex].id;
        let chartTitle = `[${currencyCode}] Report`;

        let queryString = this.endpoint + '?currencyId=' + filters.currencyId;

        if (filters.tagId != '') {
            queryString += '&tagId=' + filters.tagId;
            const tagName = this.reportFormTagTarget[this.reportFormTagTarget.selectedIndex].textContent;
            chartTitle += ` for ${tagName}`
        }

        if (filters.dateFrom != '') {
            queryString += '&dateFrom=' + filters.dateFrom;
        }

        if (filters.dateTo != '') {
            queryString += '&dateTo=' + filters.dateTo;
        }

        if (filters.dateFrom != '' || filters.dateTo != '') {
            const dateFromHumanized = filters.dateFrom == '' ? "until now" : filters.dateFrom;
            const dateToHumanized = filters.dateTo == '' ? "up to" : filters.dateTo;
            const separator = filters.dateFrom != '' && filters.dateTo != '' ? '-' : '';
            chartTitle += `(${dateFromHumanized} ${separator} ${dateToHumanized})`;
        }

        await api.get(queryString).then((result) => {
            this.reportChartTitleTarget.textContent = chartTitle;

            if (result == "No data") {
                this.chartBorderTarget.style.display = 'none';
                pie.data.labels.pop();
                pie.data.datasets[0].data.pop();
                pie.data.labels[0] = result;
                pie.data.datasets[0].data[0] = 0;

                pie.update();
            } else {
                this.chartBorderTarget.style.display = 'block';
                pie.data.labels = [
                    'Expenses',
                    'Income'
                ]
                pie.data.datasets[0].data = [
                    result.expenses,
                    result.income
                ];

                pie.update();
            }
        })
    }
}