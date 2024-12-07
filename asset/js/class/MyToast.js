export default class MyToast {
    constructor(message, type) {
        this.message = message;
        this.type = type;
        this.toastContainer = document.getElementById('toast-container');
        this.toastElement = document.createElement('div');
    }

    show() {
        this.toastElement.className = `toast align-items-center text-white bg-${this.type} border-0`;
        this.toastElement.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    ${this.message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>`;

        this.toastContainer.appendChild(this.toastElement);
        const toast = new bootstrap.Toast(this.toastElement);
        toast.show();
    }
}
