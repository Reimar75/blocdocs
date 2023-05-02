import './styles/app.css';
import './bootstrap';

window.asyncRequest = function (url, target) {
    var request = new XMLHttpRequest();
    request.onload = function () {
        target.innerHTML = this.responseText;
        target.querySelector('[autofocus="autofocus"]')?.focus();
    }
    request.open('GET', url);
    request.send();
}

window.openModal = function (id, url = null) {
    var modal = document.getElementById(id);
    if (url) {
        var target = modal.querySelector('.modal-content');
        target.innerHTML = '';
        asyncRequest(url, target);
    }
    showElement(modal);
    return false;
};

window.closeModal = function (element) {
    hideElement(element.closest(".modal"));
}

window.hideElement = function (element) {
    element.classList.add('hidden');
}

window.showElement = function (element) {
    element.classList.remove('hidden');
}

document.onkeydown = function (event) {
    event = event || window.event;
    var isEscape = false;
    if ("key" in event) {
        isEscape = (event.key === "Escape" || event.key === "Esc");
    } else {
        isEscape = (event.keyCode === 27);
    }
    if (isEscape) {
        Array.prototype.forEach.call(document.getElementsByClassName('modal'), function (element) {
            closeModal(element);
        });
    }
};

document.querySelectorAll('form').forEach(function (form) {
    form.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();

            const button = form.querySelector('button[type="submit"]');
            if (button) {
                button.click();
            }
        }
    });
});