import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        // document.getElementById('today').focus();
    }

    eventCreate(event) {
        openModal('modal-event', event.target.getAttribute('data-url'));
    }

    eventEdit(event) {
        var url = this.getAttributeFromElementOrParents(event.target, 'data-url');

        if (url) {
            openModal('modal-event', url);
        }

        event.stopPropagation();
    }

    chooseColorPicker(event) {
        var radioButtons = document.querySelectorAll(`input[name="event[color]"]`);
        var value = event.target.getAttribute('data-value');

        radioButtons.forEach(radioButton => {
            if (radioButton.value === value) {
                radioButton.closest("label").classList.add('active');
                radioButton.checked = true;
            } else {
                radioButton.closest("label").classList.remove('active');
            }
        });
    }

    formSubmit(event) {
        event.preventDefault();

        // prevent scrolling after form submit
        const freezePosition = () => {
            window.Turbo.navigator.currentVisit.scrolled = true;
            document.removeEventListener("turbo:render", freezePosition);
        };

        document.addEventListener("turbo:render", freezePosition);
        document.getElementById(event.target.getAttribute('data-form')).requestSubmit();
    }

    getAttributeFromElementOrParents(element, attribute) {
        if (element.getAttribute(attribute)) {
            return element.getAttribute(attribute);
        } else if (element.parentNode) {
            return this.getAttributeFromElementOrParents(element.parentNode, attribute);
        }

        return null;
    }

    test(event) {
        alert('It works');
    }
}