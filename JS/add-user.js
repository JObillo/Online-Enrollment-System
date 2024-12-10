
// JavaScript function to show/hide fields based on user type
function toggleFields() {
    var userType = document.getElementById('user_type').value;
    var departmentField = document.getElementById('department-field');
    var positionField = document.getElementById('position-field');
    var departmentSelect = document.getElementById('department');
    var positionSelect = document.getElementById('position');

    // Show department if Dean is selected
    if (userType === 'Dean') {
        departmentField.style.display = 'block';
        positionField.style.display = 'none'; // Hide position field
        departmentSelect.setAttribute('required', 'true'); // Make department field required
        positionSelect.removeAttribute('required'); // Remove required from position field
    }
    // Show position if Admin is selected
    else if (userType === 'Admin') {
        positionField.style.display = 'block';
        departmentField.style.display = 'none'; // Hide department field
        positionSelect.setAttribute('required', 'true'); // Make position field required
        departmentSelect.removeAttribute('required'); // Remove required from department field
    }
    else {
        departmentField.style.display = 'none';
        positionField.style.display = 'none';
        positionSelect.removeAttribute('required');
        departmentSelect.removeAttribute('required');
    }
}

// Ensure the toggleFields function is called when the user type is selected
document.getElementById('user_type').addEventListener('change', toggleFields);

// Initially call the function to set the correct visibility on page load
