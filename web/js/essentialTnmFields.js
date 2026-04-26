const fullTnmCheckbox = document.getElementById('tumour-full_tnm');
const essentialTnmFields = document.getElementById('essential-tnm-fields');

function toggleEssentialFields() {
    if (fullTnmCheckbox.checked) {
        essentialTnmFields.style.display = 'none';
    } else {
        essentialTnmFields.style.display = 'block';
    }
}

// Set initial state on page load
toggleEssentialFields();

// Listen for changes
fullTnmCheckbox.addEventListener('change', toggleEssentialFields);