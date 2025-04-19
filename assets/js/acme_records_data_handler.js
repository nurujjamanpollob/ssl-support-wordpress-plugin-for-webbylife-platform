// page load finish listener
let snackbarEl;
let snackbar;

document.addEventListener("DOMContentLoaded", function () {

    snackbarEl = document.querySelector('.mdc-snackbar');
    snackbar = new mdc.snackbar.MDCSnackbar(snackbarEl);

    // add event listeners to buttons
    const buttons = document.querySelectorAll('button[action]');

    buttons.forEach(button => {
        button.addEventListener('click', function () {
            const action = this.getAttribute('action');
            const key = this.getAttribute('key');
            if (action === 'edit') {
                editRecord(key);
            }

            // action for applied button
            if (action === 'apply') {
                applyRecord(key);
            }

            // handle delete action
            if (action === 'delete') {
                deleteRecord(key);
            }
        });
    });

    // listen on search form submit
    const searchForm = document.querySelector('form[name="search_form"]');

    if (searchForm) {
        searchForm.addEventListener('submit', function (event) {
            event.preventDefault();
            searchEntity(this);
        });
    }
});


// gets an input field with key="value" and also, type="type"
function getInputField(key, type) {
    return document.querySelector(`input[key="${key}"][type="${type}"]`);
}

// this method handles edit action
function editRecord(key) {
    const inputField = getInputField(key, 'text');
    inputField.disabled = false;
    inputField.focus();
    inputField.select();

    // disable edit button
    const editButton = document.querySelector(`button[key="${key}"][action="edit"]`);
    // change edit button type to apply
    editButton.setAttribute('action', 'apply');
    editButton.innerText = 'Apply';

}

// this method handles apply action
function applyRecord(key) {
    const inputField = getInputField(key, 'text');
    const value = inputField.value;

    // gey action button by apply
    const applyButton = document.querySelector(`button[key="${key}"][action="apply"]`);
    // change apply button type to edit
    applyButton.setAttribute('action', 'edit');
    applyButton.innerText = 'Edit';
    inputField.disabled = true;

    // save data to server
    // submit post request to: /api/v1/edit-acme-challenge-data
    // needs: challenge_key, new_value, nonce

    // create form data
    const formData = new FormData();
    formData.append('challenge_key', key);
    formData.append('new_value', value);
    formData.append('nonce', getNonce());

    // open snackbar
    openSnackbar('Updating record...', true, -1).then(r => {});

    // make request to server
    fetch('/api/v1/edit-acme-challenge-data', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        // if response is 200
        if (response.status === 200) {
            // close snackbar
            closeSnackbar().then(r => {});
            // SHOW RECORD update message
            openSnackbar('Record updated successfully', false, 5000).then(r => {});
        }
        // if response is not 200
        else {
            // get the response text
            response.text().then(text => {
                // close snackbar
                closeSnackbar().then(r => {});
                // open snackbar with error message
                openSnackbar('Error updating record, server responds: ' + text, false, 10000).then(r => {});
            });
        }
    })


}

// this method handles delete action
function deleteRecord(key) {
    const row = document.querySelector(`tr[key="${key}"]`);
    if (row) {

        // handle single entry delete acme challenge data: pattern: /api/v1/delete-single-acme-challenge-data
        // needs: challenge_key, nonce
        // create form data
        const formData = new FormData();
        formData.append('challenge_key', key);
        formData.append('nonce', getNonce());

        // open snackbar
        openSnackbar('Deleting record...', true, -1).then(r => {});
        // make request to server
        fetch('/api/v1/delete-single-acme-challenge-data', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            // if response is 200
            if (response.status === 200) {
                // close snackbar
                closeSnackbar().then(r => {});
                // SHOW RECORD delete message
                openSnackbar('Record deleted successfully', false, 5000).then(r => {});

                // remove the row from the table
                row.remove();

            }
            // if response is not 200
            else {
                // get the response text
                response.text().then(text => {
                    // close snackbar
                    closeSnackbar().then(r => {});
                    // open snackbar with error message
                    openSnackbar('Error deleting record, server responds: ' + text, false, 10000).then(r => {});
                });
            }
        })
    }
}

// this method handles delete selected action
function deleteSelected() {



    const selectedRows = getSelectedRows();

    //submit post request to /api/v1/delete-acme-challenge-data
    // needs: challenge_keys[comma separated], nonce

    // create form data
    const formData = new FormData();
    formData.append('challenge_keys', selectedRows.join(','));
    formData.append('nonce', getNonce());

    // open snackbar
    openSnackbar('Deleting selected records...', true, -1).then(r => {});

    // make request to server
    fetch('/api/v1/delete-acme-challenge-data', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        // if response is 200
        if (response.status === 200) {

            // close snackbar
            closeSnackbar().then(r => {});
            // SHOW RECORD delete message
            openSnackbar('Selected records deleted successfully', false, 5000).then(r => {});

            // remove selected rows from table
            selectedRows.forEach(key => {
                const row = document.querySelector(`tr[key="${key}"]`);
                if (row) {
                    row.remove();
                }
            });
        }
        // if response is not 200
        else {
            // close snackbar
            closeSnackbar().then(r => {});
            // open snackbar with an error message
            openSnackbar('Error deleting records, server responds: ' + response.statusText, false, 10000).then(r => {});
            // get the response text
            response.text().then(text => {
                console.error('Error deleting records, server responds: ', text);
            });
        }
    })
}

// get rows selected, which has defined key property, it returns an array of keys, from the selected rows
function getSelectedRows() {
    const selectedRows = [];
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        if (checkbox.checked) {
            const key = checkbox.getAttribute('key');
            selectedRows.push(key);
        }
    });
    return selectedRows;
}

// perform the search action
function searchEntity($elm) {

   // get the search input field
    const searchInput = $elm.querySelector('input[type="text"]');

    // get the search value
    const searchValue = searchInput.value;

    // url encode the search value
    const encodedSearchValue = encodeURIComponent(searchValue);

    // get domain name containing http or https
    const domainName = window.location.origin;
    // now add wp-admin/admin.php?page=manage-acme-challenge-data
    // redirect to the url
    let url = `${domainName}/wp-admin/admin.php?page=manage-acme-challenge-data&q=${encodedSearchValue}`;

    // create a button and click it
    const button = document.createElement('a');
    button.href = url;
    button.click();
    // remove the button
    button.remove();



}

// performs the clean all action
function cleanAll() {

    // Here you can add the logic to clean all records from the server or database //TODO

    // make call to clean all records
    // need to provide action and its value as delete_all_acme_challenge_data

    const action = 'delete_all_acme_challenge_data';

    const nonce = getNonce();

    /// make form data
    const formData = new FormData();
    formData.append('action', action);
    formData.append('nonce', nonce);

    // open snackbar
    openSnackbar('Cleaning all records...', true, -1).then(r => {});

    // make request to server /api/v1/clean-all-acme-challenge-data
    fetch('/api/v1/clean-all-acme-challenge-data', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })


    .then(response => {
        // if response is 200
        if (response.status === 200) {

            // close snackbar
            closeSnackbar().then(r => {});

            // SHOW RECORD delete message
            openSnackbar('All records deleted successfully', false, 5000).then(r => {});

            // remove all rows from table
            const tableBody = document.querySelector('table tbody');
            if (tableBody) {
                tableBody.innerHTML = '';
            }


        }
        // if response is not 200
        else {
            // get the response text
            response.text().then(text => {
                // close snackbar
                closeSnackbar().then(r => {});
                // open snackbar with error message
                openSnackbar('Error cleaning records, server responds: ' + text, false, 10000).then(r => {});
            });
        }
    })




}

// get nonce
function getNonce() {
    // get nonce by id ssl_support_for_webbylife_platform_nonce
    const nonce = document.querySelector('#ssl_support_for_webbylife_platform_nonce');
    if (nonce) {
        return nonce.value;
    }
    return null;
}

/**
 * Method to open the snackbar. You can pass the snackbar content and hide a close button flag to this method.
 * You can also define snackbar timeout in this method. -1 means no timeout. The unit is in milliseconds.
 * @param snackBarContent Snackbar content
 * @param isHideCloseButton Hide close button flag
 * @param timeout Snackbar timeout
 */
async function openSnackbar(snackBarContent, isHideCloseButton, timeout) {

    snackbar.open();
    snackbar.timeoutMs = timeout;

    // Set the snackbar content
    const snackbarContent = document.getElementById('snackbar-content');
    // Empty the snackbar content
    snackbarContent.innerHTML = '';
    snackbarContent.innerHTML = snackBarContent;

    // Hide the close button if the flag is true
    if (isHideCloseButton) {
        const snackbarCancelButton = document.getElementById('snackbar-cancel-button');
        snackbarCancelButton.style.visibility = 'hidden';
    } else {
        const snackbarCancelButton = document.getElementById('snackbar-cancel-button');
        snackbarCancelButton.style.visibility = 'visible';
    }

}

/**
 * Method to close the snackbar
 */
async function closeSnackbar() {

    snackbar.close();
}
