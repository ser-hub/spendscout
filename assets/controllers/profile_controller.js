import { Controller } from '@hotwired/stimulus';
import * as api from './../api.js';

export default class extends Controller {
    static targets = [
        "profileWrapper",
        "profileFormFirstName",
        "profileFormLastName",
        "profileFormEmail",
        "submitFormBtn",
    ]

    connect() {
        this.endpointInfo = this.element.dataset.endpointInfo;
        this.endpointCredentials = this.element.dataset.endpointCredentials;
        this.profileWrapperDefaultHTML = null;
    }

    async submitProfileForm() {
        const data = {
            firstName: this.profileFormFirstNameTarget.value,
            lastName: this.profileFormLastNameTarget.value,
        }

        await api.post(this.endpointInfo, data).then((response) => {
            if (response.firstName != undefined) {
                this.profileWrapperTarget.childNodes[3].textContent = response.message;
                this.profileFormFirstNameTarget.value = response.firstName;
                this.profileFormLastNameTarget.value = response.lastName;
            } else {
                if (response.title === 'Validation Failed') {
                    this.profileWrapperTarget.childNodes[3].textContent = response.detail.split(':')[1].split('(')[0];
                } else {
                    this.profileWrapperTarget.childNodes[3].textContent = response.detail;
                }
            }
        })
    }

    changeCredentialsMode(event) {
        this.profileWrapperDefaultHTML = this.profileWrapperTarget.innerHTML;

        const profilePictureNode = this.profileWrapperTarget.childNodes[1];
        const errorFiled = document.createElement('div');
        const emailField = document.createElement('input');
        const passwordField = document.createElement('input');
        const saveBtn = document.createElement('button');
        const backBtn = document.createElement('button');

        errorFiled.classList.add('error-message');

        passwordField.placeholder = 'New password';
        passwordField.id = 'password-change-field';
        passwordField.name = 'new-password';
        passwordField.type = 'password';

        emailField.value = event.currentTarget.dataset.email;
        emailField.placeholder = 'New email';
        emailField.id = 'email-change-field';
        emailField.name = 'new-email';
        emailField.type = 'email';

        saveBtn.dataset.action = 'profile#newCredentialsSubmit';
        saveBtn.innerHTML = 'Change my credentials';
        saveBtn.classList.remove('yellow-btn');
        saveBtn.classList.add('white-btn');

        backBtn.innerHTML = '<';
        backBtn.classList.add('yellow-btn');
        backBtn.style.padding = '.45rem .5rem';
        backBtn.style.marginRight = '.5rem';
        backBtn.dataset.action = 'profile#defaultMode';

        const bottom = document.createElement('div');
        bottom.append(backBtn, saveBtn);

        const message = document.createElement('div');
        message.style.fontSize = '.9rem';
        message.innerHTML = 'Your new password must be at least 8 characters long and must contain: <ul><li>an uppercase and a lowercase letter,</li><li>a special symbol,</li><li>and a number.</li></ul>';

        this.profileWrapperTarget.innerHTML = '';
        this.profileWrapperTarget.append(profilePictureNode, errorFiled, emailField, passwordField, bottom, message);
    }

    defaultMode() {
        this.profileWrapperTarget.innerHTML = this.profileWrapperDefaultHTML;
    }

    async newCredentialsSubmit() {
        const data = {
            password: this.profileWrapperTarget.childNodes[3].value,
            email: this.profileWrapperTarget.childNodes[2].value
        }

        await api.post(this.endpointCredentials, data).then((response) => {
            if (response.email != undefined) {
                this.profileWrapperTarget.childNodes[2].value = response.email;
                this.profileWrapperTarget.childNodes[1].textContent = response.message;
            } else {
                if (response.title === 'Validation Failed') {
                    this.profileWrapperTarget.childNodes[1].textContent = response.detail;
                } else {
                    this.profileWrapperTarget.childNodes[1].textContent = response.detail;
                }
            }
        })
    }
}