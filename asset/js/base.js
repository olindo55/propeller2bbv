import MyToast from './class/MyToast.js';

// toast flashMessage
document.addEventListener('DOMContentLoaded', function() {
    console.log('Creating toast');
    if (flashMessage) {
        const toast = new MyToast(flashMessage, flashAlert);
        toast.show();
    }
});