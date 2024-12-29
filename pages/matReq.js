function submitRequest() {
    const startTime = document.getElementById('startTime').value;
    const endTime = document.getElementById('endTime').value;
    
    if (!startTime || !endTime) {
        alert("Please select both start and end times.");
        return;
    }

    const start = new Date(`2025-01-01T${startTime}:00`);
    const end = new Date(`2025-01-01T${endTime}:00`);
    const duration = (end - start) / (1000 * 60); // Duration in minutes
    
    if (duration <= 0) {
        alert("End time must be after start time.");
    } else if (duration > 90) {
        alert("The selected time interval cannot exceed 1 hour and 30 minutes.");
    } else {
        alert("Request submitted successfully!");
        
    }}
