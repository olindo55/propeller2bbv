export default class TableSort {

    constructor(table) {
        this.table = table;
    }

    isNumeric(value) {
        return !isNaN(value) && !isNaN(parseFloat(value));
    }

    isCheckboxColumn(columnIndex) {
        const firstRow = this.table.tBodies[0].rows[0];
        const cell = firstRow.cells[columnIndex];
        return cell.querySelector('input[type="checkbox"]') !== null;
    }

    isSelectColumn(columnIndex) {
        const firstRow = this.table.tBodies[0].rows[0];
        const cell = firstRow.cells[columnIndex];
        return cell.querySelector('select') !== null;
    }

    isIconColumn(columnIndex) {
        const firstRow = this.table.tBodies[0].rows[0];
        const cell = firstRow.cells[columnIndex];
        return cell.querySelector('img') !== null;
    }

    sortColumn(columnIndex) {
        const tbody = this.table.tBodies[0];
        const rows = Array.from(tbody.rows);

        const icons = document.querySelectorAll('table th img');
        const iconIndex = columnIndex;
        const icon = icons[iconIndex];
        let ascending = icon.src.includes('/sort-down.svg');

        const firstCell = rows[0].cells[columnIndex].textContent.trim().toLowerCase();
        let dataType = 'string';

        if (this.isNumeric(firstCell)) {
            dataType = 'number';
        } else if (this.isCheckboxColumn(columnIndex)) {
            dataType = 'checkbox';
        } else if (this.isSelectColumn(columnIndex)) {
            dataType = 'select';
        } else if (this.isIconColumn(columnIndex)) {
            dataType = 'icon';
        }

        rows.sort((a, b) => {
            let cellA, cellB;

            if (dataType === 'select') {
                cellA = a.cells[columnIndex].querySelector('select').selectedOptions[0].textContent.toLowerCase();
                cellB = b.cells[columnIndex].querySelector('select').selectedOptions[0].textContent.toLowerCase();
            } else if (dataType === 'checkbox') {
                cellA = a.cells[columnIndex].querySelector('input[type="checkbox"]').checked;
                cellB = b.cells[columnIndex].querySelector('input[type="checkbox"]').checked;
            } else if (dataType === 'icon') {
                cellA = a.cells[columnIndex].querySelector('img').alt.toLowerCase();
                cellB = b.cells[columnIndex].querySelector('img').alt.toLowerCase();
            }else {
                cellA = a.cells[columnIndex].textContent.trim().toLowerCase();
                cellB = b.cells[columnIndex].textContent.trim().toLowerCase();
            }

            return ascending ? cellA.localeCompare(cellB) : cellB.localeCompare(cellA);
        });

        rows.forEach(row => tbody.appendChild(row));

        if (ascending) {
            icon.src = icon.src.replace('/sort-down.svg', '/sort-down-alt.svg');
        } else {
            icon.src = icon.src.replace('/sort-down-alt.svg', '/sort-down.svg');

        }
    }
}
