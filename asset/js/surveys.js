const spinnerContainer = document.getElementById('spinner-container');
const form = document.querySelector('form');
const compareBtn = document.getElementById('compareBtn');

form.addEventListener('submit', (e) => {
    compareBtn.disabled = true;
    spinnerContainer.classList.remove('d-none');
});

