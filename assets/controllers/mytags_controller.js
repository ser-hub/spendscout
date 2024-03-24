import { Controller } from '@hotwired/stimulus';
import * as api from '../api.js';

export default class extends Controller {
    static targets = [
        "tagsGrid",
        "tagsFormStatus",
        "tagsFormName",
        "formError",
        "tagEditBtn"
    ];

    connect() {
        this.endpoint = this.element.dataset.endpoint;
    }

    displayTags(tags, scrollToId) {
        this.tagsGridTarget.innerHTML = "";
        if (tags.length > 0) {
            tags.forEach((tag) => {
                this.tagsGridTarget.appendChild(this.createTagElement(tag));
            })

            if (scrollToId != undefined || scrollToId > 0) {
                this.tagsGridTarget.childNodes.forEach(element => {
                    if (element.dataset.id == scrollToId) {
                        element.scrollIntoView();
                        element.classList.add('new-animate');
                    }
                });
            }
        } else {
            this.tagsGridTarget.innerHTML = "No tags to show";
        }
    }

    async refreshTags(scrollToId) {
        let allTags = await api.get(this.endpoint).then((result) => {
            return result;
        });
        this.displayTags(allTags, scrollToId);
    }

    async tagFormSubmit() {
        const tagData = {
            name: this.tagsFormNameTarget.value
        };

        let response = await api.post(this.endpoint, tagData).then((tag) => {
            return tag;
        });

        if (response.status && response.status != 200) {
            if (response.title == "Validation Failed") {
                response.detail = response.detail.split(':')[1];
            }

            this.formErrorTarget.textContent = response.detail;
        } else {
            this.refreshTags(response.id);
            this.clearTagForm();
        }
    }

    async editMode(event) {
        const tagElement = event.currentTarget.parentNode.parentNode;
        this.tagsGridTarget.childNodes.forEach((tagWrapper) => {
            if (tagWrapper.nodeName == "DIV" && tagWrapper.classList.contains('select') && tagWrapper !== tagElement) {
                tagWrapper.classList.remove('select');
            }
        });

        if (tagElement.classList.contains('select')) {
            tagElement.classList.remove('select');
            this.clearTagForm();
        } else {
            tagElement.classList.add('select');
            this.tagsFormStatusTarget.textContent = "Edit tag";

            const tagToEdit = await api.get(this.endpoint + '/' + tagElement.dataset.id).then((tag) => {
                return tag;
            });

            this.tagsFormNameTarget.value = tagToEdit.name;

            this.tagEditBtnTarget.dataset.action = "mytags#editTag";
            this.tagEditBtnTarget.dataset.targetId = tagToEdit.id;
        }
    }

    async editTag(event) {
        const id = event.currentTarget.dataset.targetId;

        const tagData = {
            name: this.tagsFormNameTarget.value
        };

        let response = await api.put(this.endpoint + '/' + id, tagData).then((response) => {
            return response;
        });

        if (response.status && response.status != 200) {
            if (response.title == "Validation Failed") {
                response.detail = response.detail.split(':')[1];
            }

            this.formErrorTarget.textContent = response.detail;
        } else {
            this.refreshTags(id);
            this.clearTagForm();
        }
    }

    async deleteTag(event) {
        const id = event.currentTarget.parentNode.parentNode.dataset.id;
        const response = await api.del(this.endpoint + '/' + id).then((result) => {
            return result;
        })

        if (response.status && response.status != 200) {
            this.displayGeneralError();
        } else {
            this.refreshTags();
            this.clearTagForm();
        }
    }

    createTagElement(tag) {
        const tagWrapper = document.createElement('div');
        tagWrapper.classList.add('tag-wrapper');
        tagWrapper.dataset.id = tag.id;

        const controlsWrapper = document.createElement('div');

        const editBtn = document.createElement('i');
        editBtn.classList.add('fa-solid');
        editBtn.classList.add('fa-pencil');
        editBtn.classList.add('fa-xl');
        editBtn.dataset.action = 'click->mytags#editMode';

        const deleteBtn = document.createElement('i');
        deleteBtn.classList.add('fa-solid');
        deleteBtn.classList.add('fa-trash');
        deleteBtn.classList.add('fa-xl');
        deleteBtn.dataset.action = 'click->mytags#deleteTag';

        tagWrapper.textContent = tag.name;
        controlsWrapper.append(editBtn, deleteBtn);

        tagWrapper.append(
            controlsWrapper
        );

        return tagWrapper;
    }

    clearTagForm() {
        this.tagsGridTarget.childNodes.forEach((tagWrapper) => {
            if (tagWrapper.nodeName == "DIV" && tagWrapper.classList.contains('select')) {
                tagWrapper.classList.remove('select');
            }
        });

        this.tagsFormStatusTarget.textContent = "Create Tag";
        this.tagsFormNameTarget.value = "";
        this.formErrorTarget.textContent = "";
        this.tagEditBtnTarget.dataset.action = "mytags#tagFormSubmit";
    }
}