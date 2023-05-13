import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        const showBlocks = localStorage.getItem('filter-toggle-blocks') === 'true';
        const showTime = localStorage.getItem('filter-toggle-time') === 'true';

        document.querySelectorAll('.blocks').forEach(element => {
            element.classList.toggle('hidden', !showBlocks);
        });

        document.querySelectorAll('.time').forEach(element => {
            element.classList.toggle('hidden', !showTime);
        });

        document.querySelector('#btn-toggle-blocks').classList.toggle('active', showBlocks);
        document.querySelector('#btn-toggle-time').classList.toggle('active', showTime);
    }

    fetch(event) {
        const url = this.getAttributeFromElementOrParents(event.target, 'data-url');

        if (url) {
            openModal('modal-calendar', url);
        }

        event.stopPropagation();
    }

    chooseColorPicker(event) {
        const radioButtons = event.target.closest('form').querySelectorAll('input[name$="[color]"]');
        const value = event.target.getAttribute('data-value');

        radioButtons.forEach(radioButton => {
            if (radioButton.value === value) {
                radioButton.closest('label').classList.add('active');
                radioButton.checked = true;
            } else {
                radioButton.closest('label').classList.remove('active');
            }
        });
    }

    toggleStar(event) {
        event.stopPropagation();
        this.preventScrolling();
        
        const form = document.getElementById('form-star');
        const dateField = form.querySelector('#form_date');
        const date = this.getAttributeFromElementOrParents(event.target, 'data-date');        

        dateField.setAttribute('value', date);
        form.requestSubmit();
    }

    preventScrolling() {        
        function freezePosition() {
            window.Turbo.navigator.currentVisit.scrolled = true;
            document.removeEventListener('turbo:render', freezePosition);
        }

        document.addEventListener('turbo:render', freezePosition);
    }

    formSubmit(event) {
        event.preventDefault();
        this.preventScrolling();

        const formId = event.target.getAttribute('data-form');

        document.getElementById(formId).requestSubmit();

        // todo: create listener for that
        if ('form-block' === formId) {
            localStorage.setItem('filter-toggle-block', true);
        }
    }

    getAttributeFromElementOrParents(element, attribute) {
        if (element.getAttribute(attribute)) {
            return element.getAttribute(attribute);
        } else if (element.parentNode) {
            return this.getAttributeFromElementOrParents(element.parentNode, attribute);
        }

        return null;
    }

    filterToggle(event) {
        const button = event.target.closest('.btn-filter');
        const items = this.getAttributeFromElementOrParents(button, 'data-items');
        const className = '.' + items;
        var isActive = null;

        document.querySelectorAll(className).forEach(element => {
            element.classList.toggle('hidden');
            isActive = !element.classList.contains('hidden');
        });

        button.classList.toggle('active');
        localStorage.setItem('filter-toggle-' + items, isActive);
    }

    test(event) {
        alert('It works');
    }
}
