// Function to toggle transferee, requirements, and education sections based on year level and transferee checkbox
function toggleSections() {
    const isTransferee = document.getElementById("is_transferee").checked;
    const yearLevel = document.getElementById("year_level").value;
    const transfereeSection = document.getElementById("transfereeSection");
    const requirementsSection = document.getElementById("requirementsSection");
    const educationSection = document.getElementById("educationSection");

    // Toggle transferee section visibility
    if (isTransferee) {
        transfereeSection.style.display = "block";
        requirementsSection.style.display = "none"; // Hide requirements if transferee is checked
        document.getElementById("transfer_last_school").setAttribute("required", "required");
        document.getElementById("transfer_last_year").setAttribute("required", "required");
        document.getElementById("transfer_course").setAttribute("required", "required");
        document.getElementById("requirement_file_grade").removeAttribute("required");
        document.getElementById("requirement_file_pic").removeAttribute("required");
    } else {
        transfereeSection.style.display = "none";
        document.getElementById("transfer_last_school").removeAttribute("required");
        document.getElementById("transfer_last_year").removeAttribute("required");
        document.getElementById("transfer_course").removeAttribute("required");

        // Only show requirements if the year level is "First Year"
        if (yearLevel === "1st Year") {
            requirementsSection.style.display = "block";
            document.getElementById("requirement_file_grade").setAttribute("required", "required");
            document.getElementById("requirement_file_pic").setAttribute("required", "required");
        } else {
            requirementsSection.style.display = "none";
            document.getElementById("requirement_file_grade").removeAttribute("required");
            document.getElementById("requirement_file_pic").removeAttribute("required");
        }
    }

    // Show education section only for First Year students
    if (yearLevel === "1st Year" && !isTransferee) {
        educationSection.style.display = "block";
        document.getElementById("last_school_attended").setAttribute("required", "required");
        document.getElementById("strand").setAttribute("required", "required");
        document.getElementById("year_graduated").setAttribute("required", "required");
        document.getElementById("general_average").setAttribute("required", "required");
    } else {
        educationSection.style.display = "none";
        document.getElementById("last_school_attended").removeAttribute("required");
        document.getElementById("strand").removeAttribute("required");
        document.getElementById("year_graduated").removeAttribute("required");
        document.getElementById("general_average").removeAttribute("required");
    }
}


const emailInput = document.getElementById('email');
const emailErrorMessage = document.getElementById('email-error-message');
const phoneInput = document.getElementById('student_phoneNo');
const phoneErrorMessage = document.getElementById('error-message');
const fatherPhone = document.getElementById('father_phone_no');
const fatherErrorMessage = document.getElementById('error-father-phoneno');
const motherPhone = document.getElementById('mother_phone_no')
const motherErrorMessage = document.getElementById('error-mother-phoneno')

emailInput.addEventListener('blur', function () {
    const emailValue = this.value;

    // Regex to check valid email format
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    // If invalid email format is detected
    if (emailValue && !emailRegex.test(emailValue)) {
        emailErrorMessage.textContent = "Invalid Email Address";
        emailErrorMessage.style.color = "red"; // Error message in red
        emailInput.style.border = "2px solid red"; // Input border in red

        // Add vibration effect
        emailInput.classList.add('vibrate');
        setTimeout(() => {
            emailInput.classList.remove('vibrate');
        }, 300); // Remove the class after animation ends
    } else {
        emailErrorMessage.textContent = "";
        emailInput.style.border = ""; // Reset input border
    }
});



phoneInput.addEventListener('input', function () {
    const originalValue = this.value;

    // Sanitize input by allowing only numeric characters
    const sanitizedValue = originalValue.replace(/[^0-9]/g, '');

    // Check if sanitization removed any invalid characters
    if (sanitizedValue !== originalValue) {
        phoneErrorMessage.textContent = "Number Only";
        phoneErrorMessage.style.color = "red"; // Error message in red
        phoneInput.style.border = "2px solid red"; // Input border in red

        // Add vibration effect
        phoneInput.classList.add('vibrate');
        setTimeout(() => {
            phoneInput.classList.remove('vibrate');
        }, 300); // Remove the class after animation ends
    } else {
        phoneErrorMessage.textContent = "";
        phoneInput.style.border = ""; // Reset input border
    }

    // Update the input value to sanitized value
    this.value = sanitizedValue;
});


fatherPhone.addEventListener('input', function () {
    const originalValue = this.value;

    
    this.value = originalValue.replace(/[^0-9]/g, '');

    if (/[a-zA-Z]/.test(originalValue)) {
        fatherErrorMessage.textContent = "Number Only";
        fatherErrorMessage.style.color = "red";
        fatherPhone.style.border = "2px solid red";

        
        fatherPhone.classList.add('vibrate');
        setTimeout(() => {
            fatherPhone.classList.remove('vibrate');
        }, 300);
    } else {
        fatherErrorMessage.textContent = "";
        fatherPhone.style.border = ""; 
    }
});

motherPhone.addEventListener('input', function(){
    const originalValue = this.value;

    this.value = originalValue.replace(/[^0-9]/g, '');

    if (/[a-zA-Z]/.test(originalValue)) {
        motherErrorMessage.textContent = "Number Only";
        motherErrorMessage.style.color = "red";
        motherPhone.style.border = "2px solid red";

        motherPhone.classList.add('vibrate');
        setTimeout(() => {
            motherPhone.classList.remove('vibrate');
        }, 300);
    } else {
        motherErrorMessage.textContent = "";
        motherPhone.style.border = ""; 
    } 

});