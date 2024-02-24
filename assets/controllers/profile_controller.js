import { Controller } from '@hotwired/stimulus';
import * as api from './../api.js';

export default class extends Controller {
    static targets = [
        "profileWrapper",
        "profileFormFirstName",
        "profileFormLastName",
        "profileFormEmail",
        "submitFormBtn",
        "changePasswordBtn",
    ]

    profileWrapperDefaultHTML = null;

    async submitProfileForm() {
        const data = {
            firstName: this.profileFormFirstNameTarget.value,
            lastName: this.profileFormLastNameTarget.value,
            email: this.profileFormEmailTarget.value
        }

        await api.post('/profile/edit', data).then((response) => {
            if (response.firstName != undefined) {
                this.profileWrapperTarget.childNodes[3].textContent = 'Profile updated';
                this.profileFormFirstNameTarget.value = response.firstName;
                this.profileFormLastNameTarget.value = response.lastName;
                this.profileFormEmailTarget.value = response.email;
            } else {
                if (response.title === 'Validation Failed') {
                    console.log(response)
                    this.profileWrapperTarget.childNodes[3].textContent = response.detail.split(':')[1].split('(')[0];
                } else {
                    this.profileWrapperTarget.childNodes[3].textContent = response.detail;
                }
            }
        })
    }

    changePasswordMode() {
        this.profileWrapperDefaultHTML = this.profileWrapperTarget.innerHTML;

        const profilePictureNode = this.profileWrapperTarget.childNodes[1];
        const errorFiled = document.createElement('div');
        const textField = document.createElement('input');
        const saveBtn = document.createElement('button');
        const backBtn = document.createElement('button');

        errorFiled.classList.add('error-message');

        textField.placeholder = 'New password';
        textField.id = 'password-change-field';
        textField.name = 'new-password';
        textField.type = 'password';

        saveBtn.dataset.action = 'profile#changePasswordSubmit';
        saveBtn.innerHTML = 'Change my password';
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
        this.profileWrapperTarget.append(profilePictureNode, errorFiled, textField, bottom, message);
    }

    defaultMode() {
        this.profileWrapperTarget.innerHTML = this.profileWrapperDefaultHTML;
    }

    async changePasswordSubmit() {
        const data = {
            password: this.profileWrapperTarget.childNodes[2].value
        }

        await api.post('/profile/password', data).then((response) => {
            if (response === 'Your password has been updated successfully') {
                this.profileWrapperTarget.childNodes[2].value = "";
                this.profileWrapperTarget.childNodes[1].textContent = response;
            } else {
                if (response.title === 'Validation Failed') {
                    this.profileWrapperTarget.childNodes[3].textContent = response.detail.split(':')[1].split('(')[0];
                } else {
                    this.profileWrapperTarget.childNodes[3].textContent = response.detail;
                }
            }
        })
    }
}