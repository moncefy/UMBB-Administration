// Prevent form submission and handle document requests
function submitRequest(event) {
    if (event) {
        event.preventDefault(); 
    }

    console.log("submitRequest() triggered.");

    
    const documentTypeElement = document.getElementById('documentType');
    if (!documentTypeElement) {
        console.error("Element with ID 'documentType' not found.");
        return;
    }

    const documentType = documentTypeElement.value;
    console.log("Selected Document Type:", documentType);

    // Validate the selected document type
    if (documentType === "") {
        alert("Please choose a document type.");
    } else {
        alert(`Your ${documentType} request has been successfully submitted!`);
    }
}

// Load this script when the DOM is fully loaded
document.addEventListener("DOMContentLoaded", () => {
    console.log("docreq.js loaded successfully.");

    // Attach the event listener to the form
    const form = document.getElementById("requestForm");
    if (form) {
        form.addEventListener("submit", submitRequest);
    } else {
        console.error("Form with ID 'requestForm' not found.");
    }
});