import MyToast from './class/MyToast.js';

const spinnerContainer = document.getElementById('spinner-container');
const form = document.querySelector('form');
const compareBtn = document.getElementById('tokenBtn');

form.addEventListener('submit', async (e) => {
    e.preventDefault()
    compareBtn.disabled = true;
    spinnerContainer.classList.remove('d-none');
    
    const token = document.getElementById('tokenInput').value.trim();
    try {
        const cleanInput = validateInput(token);
        const response = await fetch('/homepage/checkToken', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                data: cleanInput
            })
        });
    
        if (!response.ok) {
            throw new Error('Server error');
        }

        const data = await response.json();
        if (data.success){
            window.location.href = '/surveys/view';
        } else {
            const toast = new MyToast(data.message, 'danger');
            toast.show();
        }

    } catch (error) {
        console.error('Error:', error);
        alert(error.message);
    } finally {
        spinnerContainer.classList.add('d-none');
        compareBtn.disabled = false;
    }
});



function validateInput(input) {
    if (input.length !== 47) {
        throw new Error('The length must be exactly 47 characters');
    }
    const regex = /^Bearer [a-zA-Z0-9-]+$/;
    if (!regex.test(input)) {
        throw new Error('Unauthorised characters detected');
    }
    // return encodeURIComponent(input);
    return input
}