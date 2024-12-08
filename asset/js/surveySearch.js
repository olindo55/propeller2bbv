const spinnerContainer = document.getElementById('spinner-container');
const form = document.querySelector('form');
const compareBtn = document.getElementById('findBtn');

const dateFromInput = document.getElementById('dateFromInput');
const dateToInput = document.getElementById('dateToInput');

// click on button Find surveys
form.addEventListener('submit', (e) => {
    compareBtn.disabled = true;
    spinnerContainer.classList.remove('d-none');
});



// check date
dateFromInput.addEventListener('change', function() {
    dateToInput.min = dateFromInput.value;
    if (dateToInput.value && dateToInput.value < dateFromInput.value) {
        dateToInput.value = dateFromInput.value;
    }
});
dateToInput.addEventListener('change', function() {
    dateFromInput.max = dateToInput.value;
    if (dateFromInput.value && dateFromInput.value > dateToInput.value) {
        dateFromInput.value = dateToInput.value;
    }
});

