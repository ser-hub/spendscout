import { Controller } from '@hotwired/stimulus';
import * as api from './../api.js';

export default class extends Controller {
    static targets = [
        "userGrid",
        "searchField",
        "entriesGrid"
    ];

    selectUser(event, target) {
        this.userGridTarget.childNodes.forEach(node => {
            if (node.nodeName == 'DIV' && node.classList.contains('select-user') && node != event.currentTarget) {
                node.classList.remove('select-user');
            }
        });

        if (target != undefined) {
            target.classList.toggle('select-user');
        } else {
            event.currentTarget.classList.toggle('select-user');
        }
    }

    async searchUsers() {
        const keyword = this.searchFieldTarget.value;
        if (keyword != '') {
            this.clearSelection();
            this.displayUsers(await this.getUsers(keyword));
        }
    }

    async getUsers(keyword) {
        return await api.get('/admin/search?keyword=' + keyword).then((response) => {
            return response;
        });
    }

    displayUsers(users) {
        this.userGridTarget.innerHTML = '';

        if (users.length == 0) {
            this.userGridTarget.innerHTML = 'No users were found';
        } else {
            users.forEach((user) => {
                this.userGridTarget.appendChild(this.createUserElement(user));
            })
        }
    }

    createUserElement(user) {
        const userWrapper = document.createElement('div');
        userWrapper.classList.add('user-wrapper');
        userWrapper.dataset.id = user.id;
        userWrapper.dataset.action = "click->admin#selectUser click->entry#adminLoadUserData";

        const username = document.createElement('div');
        username.classList.add('username');
        username.textContent = `${user.firstName} ${user.lastName}`;

        const email = document.createElement('div');
        email.classList.add('email');
        email.textContent = user.email;

        userWrapper.append(username, email);
        return userWrapper;
    }

    clearSelection() {
        this.entriesGridTarget.innerHTML = 'No entries to show';
    }
}