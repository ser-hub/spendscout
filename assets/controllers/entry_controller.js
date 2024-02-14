import { Controller } from '@hotwired/stimulus';
import * as api from './../api.js'

export default class extends Controller {
    static targets = [
        "entryFormStatus",
        "entriesGrid",
        "entry",
        "filterBtn",
        "sortBtn",
        "byText",
        "dateBtn",
        "control2",
        "control3",
        "incomeRadio",
        "incomeLabel",
        "expenseRadio",
        "expenseLabel",
        "entryControls",
        "entryFormError",
        "entryFormName",
        "entryFormTag",
        "entryFormAmount",
        "entryFormCurrency",
        "entryFormDate",
        "entryEditBtn",
        "generalError"
    ]

    entriesState = this.refreshEntries;
    endpoint = '/api/entries';

    connect() {
        this.entriesState();
        this.clearEntryForm();
    }

    async getEntries() {
        return await api.get(this.endpoint).then((result) => {
            if (result.length == 0) {
                this.entryControlsTarget.style.display = 'none';
                this.entriesGridTarget.innerHTML = "No entries to show";
            }

            return result;
        });
    }

    displayEntries(entries, scrollToId) {
        this.generalErrorTarget.innerHTML = "";

        if (entries.length > 0) {
            this.entriesGridTarget.innerHTML = "";
            this.entriesGridTarget.parentNode.style.justifyContent = "flex-end";
            this.entryControlsTarget.style.display = 'flex';
            this.entriesGridTarget.style.overflowY = 'auto';

            entries.forEach((entry) => {
                this.entriesGridTarget.appendChild(this.createEntryElement(entry));
            })

            if (scrollToId == undefined || scrollToId < 0) {
                this.entriesGridTarget.scrollTo(0, this.entriesGridTarget.scrollHeight);
            } else {
                this.entriesGridTarget.childNodes.forEach(element => {
                    if (element.dataset.id == scrollToId) {
                        element.scrollIntoView();
                        element.classList.add('new-animate');
                    }
                });
            }
        } else {
            this.entriesGridTarget.innerHTML = "No entries to show";
        }
    }

    async refreshEntries(scrollToId) {
        let allEntries = await this.getEntries().then((entriesResult) => {
            return entriesResult;
        });
        this.displayEntries(allEntries, scrollToId);
    }

    async entryFormSubmit() {
        const entryData = this.getentryDataFromEntryForm();
        let response = await api.post(this.endpoint, entryData).then((entry) => {
            return entry;
        });

        if (response.title && response.title === "Validation Failed") {
            this.displayValidationErrors(response.detail);
        } else if (response.status && response.status != 422) {
            this.displayGeneralError();
        } else {
            const newEntry = this.createEntryElement(response);
            this.entriesState(newEntry.dataset.id, true);
            this.clearEntryForm();
        }
    }

    async editMode(event) {
        const entryElement = event.currentTarget.parentNode;
        this.entriesGridTarget.childNodes.forEach((entryWrapper) => {
            if (entryWrapper.classList.contains('select') && entryWrapper !== entryElement) {
                entryWrapper.classList.remove('select');
            }
        });
        if (entryElement.classList.contains('select')) {
            entryElement.classList.remove('select');
            this.clearEntryForm();
        } else {
            entryElement.classList.add('select');
            this.entryFormStatusTarget.style.display = "block";

            const entryToEdit = await api.get(this.endpoint + '/' + entryElement.dataset.id).then((entry) => {
                return entry;
            });

            this.expenseRadioTarget.checked = entryToEdit.isExpense;
            this.incomeRadioTarget.checked = !entryToEdit.isExpense;
            this.entryFormNameTarget.value = entryToEdit.name;
            this.entryFormAmountTarget.value = entryToEdit.amount;
            this.entryFormDateTarget.value = entryToEdit.date.split('T')[0];
            this.entryFormTagTarget.value = this.entryFormTagTarget.options.namedItem(entryToEdit.tagId).value;
            this.entryFormCurrencyTarget.value = this.entryFormCurrencyTarget.options.namedItem(entryToEdit.currencyId).value;

            this.entryEditBtnTarget.dataset.action = "entry#editEntry";
            this.entryEditBtnTarget.dataset.targetId = entryElement.dataset.id;
        }
    }

    async editEntry(event) {
        const id = event.currentTarget.dataset.targetId;

        const entryData = this.getentryDataFromEntryForm();

        let response = await api.put(this.endpoint + '/' + id, entryData).then((response) => {
            return response;
        });

        if (response.title && response.title === "Validation Failed") {
            this.displayValidationErrors(response.detail);
        } else if (response.status && response.status != 422) {
            this.displayGeneralError();
        } else {
            this.entriesState(id, true);
            this.clearEntryForm();
        }
    }

    async deleteEntry(event) {
        const id = event.currentTarget.parentNode.dataset.id;
        const response = await api.del(this.endpoint + '/' + id).then((result) => {
            return result;
        })

        if (response.status && response.status != 200) {
            this.displayGeneralError();
        } else {
            this.entriesState();
        }
    }

    clearEntryForm() {
        this.entriesGridTarget.childNodes.forEach((entryWrapper) => {
            if (entryWrapper.nodeName == "DIV" && entryWrapper.classList.contains('select')) {
                entryWrapper.classList.remove('select');
            }
        });

        this.entryFormStatusTarget.style.display = "none";
        this.incomeRadioTarget.checked = false;
        if (this.incomeLabelTarget.classList.contains('underline')) {
            this.incomeLabelTarget.classList.remove('underline');
        }
        this.expenseRadioTarget.checked = false;
        if (this.expenseLabelTarget.classList.contains('underline')) {
            this.expenseLabelTarget.classList.remove('underline');
        }
        this.entryFormNameTarget.value = "";
        this.entryFormTagTarget.value = this.entryFormTagTarget.options[0].value;
        this.entryFormAmountTarget.value = "";
        this.entryFormCurrencyTarget.value = this.entryFormCurrencyTarget.options[0].value;
        this.entryFormErrorTargets[0].textContent = "";
        this.entryFormErrorTargets[1].textContent = "";

        const dateObject = new Date();
        const day = ("0" + dateObject.getDate()).slice(-2);
        const month = ("0" + (dateObject.getMonth() + 1)).slice(-2);
        const date = dateObject.getFullYear() + "-" + month + "-" + day;
        this.entryFormDateTarget.value = date;
    }

    selectExpense() {
        this.expenseLabelTarget.classList.toggle("underline");
        if (this.incomeLabelTarget.classList.contains('underline')) {
            this.incomeLabelTarget.classList.remove('underline');
        }
    }

    selectIncome() {
        this.incomeLabelTarget.classList.toggle("underline");
        if (this.expenseLabelTarget.classList.contains('underline')) {
            this.expenseLabelTarget.classList.remove('underline');
        }
    }

    sortBtnClick() {
        this.sortBtnTarget.classList.toggle("underline");
        if (this.filterBtnTarget.classList.contains('underline')) {
            this.filterBtnTarget.classList.remove('underline');
        }

        if (this.dateBtnTarget.classList.contains('underline')) {
            this.dateBtnTarget.classList.remove('underline');
        }

        if (this.control2Target.classList.contains('underline')) {
            this.control2Target.classList.remove('underline');
        }

        if (!this.sortBtnTarget.classList.contains('underline')) {
            this.byTextTarget.innerHTML = "";
            this.dateBtnTarget.innerHTML = "";
            this.control2Target.innerHTML = "";
            this.control3Target.innerHTML = "";
            this.entriesState = this.refreshEntries;
            this.entriesState();
        } else {
            const ascBtn = document.createElement('button');
            ascBtn.textContent = "asc";
            ascBtn.style.marginRight = ".6rem"
            ascBtn.onclick = () => {
                if (ascBtn.classList.contains('underline')) {
                    this.control3Target.dataset.mode = ''
                } else {
                    this.control3Target.dataset.mode = 'asc';
                    ascBtn.classList.add('underline');
                }

                if (descBtn.classList.contains('underline')) {
                    descBtn.classList.remove('underline');
                }
            }
            const descBtn = document.createElement('button');
            descBtn.textContent = "desc";
            descBtn.onclick = () => {
                if (descBtn.classList.contains('underline')) {
                    this.control3Target.dataset.mode = ''
                } else {
                    this.control3Target.dataset.mode = 'desc';
                    descBtn.classList.add('underline');
                }

                if (ascBtn.classList.contains('underline')) {
                    ascBtn.classList.remove('underline');
                }
            }

            this.control2Target.textContent = "amount";
            this.control2Target.dataset.action = "entry#sortByAmountClick";

            this.control3Target.innerHTML = ""
            this.control3Target.appendChild(ascBtn);
            this.control3Target.appendChild(descBtn);
            this.control3Target.dataset.action = "entry#sortByDateClick";

            this.byTextTarget.textContent = "by";
            this.dateBtnTarget.textContent = "date";
            this.dateBtnTarget.dataset.action = "entry#sortByDateClick";
        }
    }

    filterBtnClick() {
        this.filterBtnTarget.classList.toggle("underline");
        if (this.sortBtnTarget.classList.contains('underline')) {
            this.sortBtnTarget.classList.remove('underline');
        }

        if (this.dateBtnTarget.classList.contains('underline')) {
            this.dateBtnTarget.classList.remove('underline');
        }

        if (this.control2Target.classList.contains('underline')) {
            this.control2Target.classList.remove('underline');
        }

        if (this.filterBtnTarget.classList.contains('underline')) {
            this.byTextTarget.textContent = "by";
        } else {
            this.byTextTarget.textContent = "by";
        }

        if (!this.filterBtnTarget.classList.contains('underline')) {
            this.byTextTarget.innerHTML = "";
            this.dateBtnTarget.innerHTML = "";
            this.control2Target.innerHTML = "";
            this.control3Target.innerHTML = "";
            this.entriesState = this.refreshEntries;
            this.entriesState();
        } else {
            this.control3Target.innerHTML = "";
            this.control2Target.innerHTML = "tag";
            this.control2Target.dataset.action = "entry#filterByTagClick";

            this.byTextTarget.textContent = "by";
            this.dateBtnTarget.textContent = "date";
            this.dateBtnTarget.dataset.action = "entry#filterByDateClick";
        }
    }

    sortByAmountClick() {
        this.control2Target.classList.toggle("underline");
        if (this.dateBtnTarget.classList.contains('underline')) {
            this.dateBtnTarget.classList.remove('underline');
        }

        const sortBtns = this.control3Target.children;
        sortBtns[0].dataset.action = "entry#sortByAmount";
        sortBtns[1].dataset.action = "entry#sortByAmount";
    }

    sortByDateClick() {
        this.dateBtnTarget.classList.toggle("underline");
        if (this.control2Target.classList.contains('underline')) {
            this.control2Target.classList.remove('underline');
        }

        const sortBtns = this.control3Target.children;
        sortBtns[0].dataset.action = "entry#sortByDate";
        sortBtns[1].dataset.action = "entry#sortByDate";
    }

    async sortByDate(scrollToId) {
        const sortMode = this.control3Target.dataset.mode;
        const data = await this.getEntries();
        this.entriesState = this.sortByDate;

        const dateSortFuncAsc = (objA, objB) => {
            const objADate = new Date(objA.date);
            const objBDate = new Date(objB.date);
            if (objADate > objBDate) {
                return -1;
            } else if (objBDate > objADate) {
                return 1;
            }

            return 0;
        };

        const dateSortFuncDesc = (objA, objB) => {
            const objADate = new Date(objA.date);
            const objBDate = new Date(objB.date);
            if (objADate < objBDate) {
                return -1;
            } else if (objBDate < objADate) {
                return 1;
            }

            return 0;
        };

        if (sortMode == 'asc') {
            this.displayEntries(data.sort(dateSortFuncAsc), scrollToId);
        } else {
            this.displayEntries(data.sort(dateSortFuncDesc), scrollToId);
        }
    }

    async sortByAmount(scrollToId) {
        const sortMode = this.control3Target.dataset.mode;
        const data = await this.getEntries();
        this.entriesState = this.sortByAmount;

        const amountSortFuncAsc = (objA, objB) => {
            if (objA.amount > objB.amount) {
                return -1;
            } else if (objB.amount > objA.amount) {
                return 1;
            }

            return 0;
        };

        const amountSortFuncDesc = (objA, objB) => {
            return !amountSortFuncAsc(objA, objB);
        }

        if (sortMode == 'asc') {
            this.displayEntries(data.sort(amountSortFuncAsc), scrollToId);
        } else {
            this.displayEntries(data.sort(amountSortFuncDesc), scrollToId);
        }

    }

    filterByDateClick() {
        this.dateBtnTarget.classList.toggle("underline");
        if (this.control2Target.classList.contains('underline')) {
            this.control2Target.classList.remove('underline');
        }

        const dateFromElement = document.createElement('input');
        dateFromElement.type = 'date';
        dateFromElement.dataset.action = "entry#filterByDate";

        const dateToElement = document.createElement('input');
        dateToElement.type = "date";
        dateToElement.dataset.action = "entry#filterByDate";

        this.control3Target.innerHTML = "from ";
        this.control3Target.appendChild(dateFromElement);
        this.control3Target.innerHTML += ' to ';
        this.control3Target.appendChild(dateToElement);
        this.control3Target.classList.add('underline');
    }

    filterByTagClick() {
        this.control2Target.classList.toggle("underline");
        if (this.dateBtnTarget.classList.contains('underline')) {
            this.dateBtnTarget.classList.remove('underline');
        }

        const tagsElement = document.createElement('select');
        Array.from(this.entryFormTagTarget.options).forEach((option) => {
            tagsElement.add(new Option(option.textContent));
        })
        tagsElement.dataset.action = "entry#filterByTag";

        this.control3Target.innerHTML = "";
        this.control3Target.appendChild(tagsElement);
    }

    async filterByDate(scrollToId, refresh = false) {
        const dateFrom = this.control3Target.children[0].value;
        const dateTo = this.control3Target.children[1].value;

        if (refresh || dateFrom && dateTo) {
            const data = await this.getEntries();
            this.entriesState = this.filterByDate;

            const filteredData = data.filter((entry) => entry.date >= dateFrom && entry.date <= dateTo);
            this.displayEntries(filteredData, scrollToId);

            const mappedData = filteredData.map((data) => data.id);

            if (!(scrollToId instanceof Event) && !mappedData.includes(parseInt(scrollToId))) {
                this.generalErrorTarget.innerHTML = "Entry added.";
            }
        }
    }

    async filterByTag(scrollToId) {
        const tagSelect = this.control3Target.children[0];
        const tagName = tagSelect[tagSelect.selectedIndex].textContent.trim();

        const data = await this.getEntries();
        this.entriesState = this.filterByTag;

        const filteredData = data.filter((entry) => entry.tagId == tagName);
        this.displayEntries(filteredData, scrollToId);

        const mappedData = filteredData.map((data) => data.id);

        if (!(scrollToId instanceof Event) && !mappedData.includes(parseInt(scrollToId))) {
            this.generalErrorTarget.innerHTML = "Entry added.";
        }
    }

    createEntryElement(entry) {
        const entryWrapper = document.createElement('div');
        entryWrapper.classList.add('entry-wrapper');
        entryWrapper.dataset.id = entry.id;

        const entryBtn = document.createElement('div');
        entryBtn.classList.add('entry-btn');

        if (entry.isExpense) {
            entryBtn.classList.add('red');
        } else {
            entryBtn.classList.add('green');
        }

        const entryCurrency = document.createElement('div');
        const userLocale =
            navigator.languages && navigator.languages.length
                ? navigator.languages[0]
                : navigator.language;

        entryCurrency.textContent = Intl.NumberFormat(userLocale, { style: 'currency', currency: entry.currencyId }).format(
            entry.amount,
        );

        const entryData = document.createElement('div');
        entryData.classList.add('entry-data');
        const dataTag = document.createElement('span');
        dataTag.classList.add('accent');
        dataTag.textContent = entry.tagId;
        const dataName = document.createElement('span');
        dataName.textContent = entry.name;
        entryData.append(dataTag, dataName);

        const entryDate = document.createElement('div');
        entryDate.classList.add("entry-date");
        entryDate.textContent = entry.date.split('T')[0];

        const editBtn = document.createElement('i');
        editBtn.classList.add('fa-solid');
        editBtn.classList.add('fa-pencil');
        editBtn.classList.add('fa-xl');
        editBtn.dataset.action = 'click->entry#editMode';

        const deleteBtn = document.createElement('i');
        deleteBtn.classList.add('fa-solid');
        deleteBtn.classList.add('fa-trash');
        deleteBtn.classList.add('fa-xl');
        deleteBtn.dataset.action = 'click->entry#deleteEntry';

        entryWrapper.append(
            entryBtn,
            entryCurrency,
            entryData,
            entryDate,
            editBtn,
            deleteBtn
        );

        return entryWrapper;
    }

    getentryDataFromEntryForm() {
        return {
            isExpense: this.expenseRadioTarget.checked,
            name: this.entryFormNameTarget.value,
            tagId: this.entryFormTagTarget.value,
            amount: parseFloat(this.entryFormAmountTarget.value),
            currencyId: this.entryFormCurrencyTarget.value,
            date: this.entryFormDateTarget.value
        }
    }

    displayValidationErrors(detail) {
        console.log(detail);
        const detailSplitted = detail.split(':');

        const field = detailSplitted[0].trim();

        if (field == 'name') {
            this.entryFormErrorTargets[0].textContent = "Too long";
        } else if (field == 'amount') {
            this.entryFormErrorTargets[1].textContent = "Invalid format";
        }
    }

    displayGeneralError() {
        this.generalErrorTarget.innerHTML = "Something went wrong.";
    }
}