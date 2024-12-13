import MyToast from './class/MyToast.js';


// selectAll
const selectAllCheckbox = document.getElementById('selectAll');
const tdCheckboxes = document.querySelectorAll('td input[type="checkbox"]');
const spinnerContainer = document.getElementById('spinner-container');
const downloadBtn = document.getElementById('downloadBtn');

selectAllCheckbox.addEventListener('change', function() {
    tdCheckboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
});


document.getElementById('surveyForm').addEventListener('submit', async (event) => {
    event.preventDefault()

    downloadBtn.disabled = true;
    spinnerContainer.classList.remove('d-none');

    const selectedRows = document.querySelectorAll('tbody tr input[type="checkbox"]:checked');
    const data = Array.from(selectedRows).map(checkbox => {
        const row = checkbox.closest('tr');
        return {
            survey_id: checkbox.value,
            site: row.cells[1].textContent.trim(),
            name: row.cells[2].textContent.trim(),
            date_captured: row.cells[2].textContent.trim(),
            organization_id: row.cells[4].textContent.trim(),
            site_id: row.cells[5].textContent.trim()
        };
    });
    
    try {
        const response = await fetch('/surveyList/downloadSurveys', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                data: data
            })
        });
        if (response.ok) {
            // Récupérer le nom du fichier depuis les headers si possible
            const filename = response.headers.get('content-disposition')?.split('filename=')[1]?.replace(/"/g, '') || 'download.zip';
            
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        } else {
            throw new Error('Erreur lors du téléchargement');
        }
    } catch (error) {
        console.error('Error:', error);
        const toast = new MyToast(error.message, 'danger');
        toast.show();
    } finally {
        spinnerContainer.classList.add('d-none');
        downloadBtn.disabled = false;
    }
});
