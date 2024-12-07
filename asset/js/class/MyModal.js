export default class MyModal {
    constructor(message, buttonText) {
        this.message = message;
        this.buttonText = buttonText;
        this.modalElement = document.getElementById('confirmModal');
        this.modalBody = this.modalElement.querySelector('.modal-body');
        this.confirmButton = this.modalElement.querySelector('#confirmButton');
        this.confirmModal = new bootstrap.Modal(this.modalElement);
    }

    show() {
        this.modalBody.textContent = this.message;
        this.confirmButton.textContent = this.buttonText;

        this.confirmModal.show();
    }

    hide() {
        this.confirmModal.hide();
    }
}