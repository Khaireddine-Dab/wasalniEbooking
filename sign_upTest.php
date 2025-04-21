// Validate forms for both admin and client
document.addEventListener('DOMContentLoaded', function () {
    // Function to validate alphabetic names
    function isAlphabetic(value) {
        return /^[A-Za-zÀ-ÖØ-öø-ÿ]+$/.test(value);
    }

    // Function to validate an 8-digit phone number
    function isValidPhoneNumber(value) {
        return /^\d{8}$/.test(value);
    }

    // Validate client form
    const clientForm = document.getElementById('clientForm');
    if (clientForm) {
        clientForm.addEventListener('submit', function (event) {
            const firstName = document.getElementById('clientFirstName').value.trim();
            const lastName = document.getElementById('clientLastName').value.trim();
            const phone = document.getElementById('clientPhone').value.trim();

            let isValid = true;

            if (!isAlphabetic(firstName)) {
                alert('Le prénom doit être alphabétique.');
                isValid = false;
            }

            if (!isAlphabetic(lastName)) {
                alert('Le nom doit être alphabétique.');
                isValid = false;
            }

            if (!isValidPhoneNumber(phone)) {
                alert('Le numéro de téléphone doit contenir exactement 8 chiffres.');
                isValid = false;
            }

            if (!isValid) {
                event.preventDefault(); // Prevent form submission
            }
        });
    }

    // Validate admin form
    const adminForm = document.getElementById('adminForm');
    if (adminForm) {
        adminForm.addEventListener('submit', function (event) {
            const firstName = document.getElementById('adminFirstName').value.trim();
            const lastName = document.getElementById('adminLastName').value.trim();
            const phone = document.getElementById('adminPhone').value.trim();

            let isValid = true;

            if (!isAlphabetic(firstName)) {
                alert('Le prénom doit être alphabétique.');
                isValid = false;
            }

            if (!isAlphabetic(lastName)) {
                alert('Le nom doit être alphabétique.');
                isValid = false;
            }

            if (!isValidPhoneNumber(phone)) {
                alert('Le numéro de téléphone doit contenir exactement 8 chiffres.');
                isValid = false;
            }

            if (!isValid) {
                event.preventDefault(); // Prevent form submission
            }
        });
    }
});// Validate that the name fields are alphabetic
function isAlphabetic(value) {
    const regex = /^[A-Za-zÀ-ÖØ-öø-ÿ]+$/; // Supports accented characters
    return regex.test(value);
}

// Validate that the phone number is exactly 8 digits
function isValidPhoneNumber(value) {
    const regex = /^\d{8}$/;
    return regex.test(value);
}

// Validate the client form
function validateClientForm(event) {
    const firstName = document.getElementById('clientFirstName').value.trim();
    const lastName = document.getElementById('clientLastName').value.trim();
    const phone = document.getElementById('clientPhone').value.trim();

    let isValid = true;
    let errorMessage = '';

    if (!isAlphabetic(firstName)) {
        isValid = false;
        errorMessage += 'Le prénom doit être alphabétique.\n';
    }

    if (!isAlphabetic(lastName)) {
        isValid = false;
        errorMessage += 'Le nom doit être alphabétique.\n';
    }

    if (!isValidPhoneNumber(phone)) {
        isValid = false;
        errorMessage += 'Le numéro de téléphone doit contenir exactement 8 chiffres.\n';
    }

    if (!isValid) {
        alert(errorMessage);
        event.preventDefault(); // Prevent form submission
    }
}

// Validate the admin form
function validateAdminForm(event) {
    const firstName = document.getElementById('adminFirstName').value.trim();
    const lastName = document.getElementById('adminLastName').value.trim();
    const phone = document.getElementById('adminPhone').value.trim();

    let isValid = true;
    let errorMessage = '';

    if (!isAlphabetic(firstName)) {
        isValid = false;
        errorMessage += 'Le prénom doit être alphabétique.\n';
    }

    if (!isAlphabetic(lastName)) {
        isValid = false;
        errorMessage += 'Le nom doit être alphabétique.\n';
    }

    if (!isValidPhoneNumber(phone)) {
        isValid = false;
        errorMessage += 'Le numéro de téléphone doit contenir exactement 8 chiffres.\n';
    }

    if (!isValid) {
        alert(errorMessage);
        event.preventDefault(); // Prevent form submission
    }
}

// Attach validation to form submissions
document.getElementById('clientForm').addEventListener('submit', validateClientForm);
document.getElementById('adminForm').addEventListener('submit', validateAdminForm);