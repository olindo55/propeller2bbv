import MyToast from './class/MyToast.js';

const spinnerContainer = document.getElementById('spinner-container');
const form = document.querySelector('form');
const findBtn = document.getElementById('findBtn');

const dateFromInput = document.getElementById('dateFromInput');
const dateToInput = document.getElementById('dateToInput');

// click on button Find surveys
form.addEventListener('submit', async function(e) {
    e.preventDefault();

    findBtn.disabled = true;
    spinnerContainer.classList.remove('d-none');

    const formData = new FormData(form);
    
    try {
        const response = await fetch('/surveySearch/find', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        console.log(data)
        if (data.success){
            window.location.href = 'surveyList/view';
            return;
        } else {
            const toast = new MyToast(data.message, 'danger');
            toast.show();
        }
    } catch (error) {
        console.error('Error:', error);
        const toast = new MyToast(error.message, 'danger');
        toast.show();
    } finally {
        spinnerContainer.classList.add('d-none');
        findBtn.disabled = false;
    }
});



// Validation dates
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

