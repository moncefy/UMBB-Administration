function submitRequest() {
    const bloc = document.getElementById('bloc').value;
    const room = document.getElementById('room').value;
    const date = document.getElementById('date').value;
    const startTime = document.getElementById('startTime').value;
    const endTime = document.getElementById('endTime').value;

    if (!bloc || !room || !date || !startTime || !endTime) {
        alert("Please fill in all fields.");
        return;
    }

    if (startTime >= endTime) {
        alert("Start time must be before the end time.");
        return;
    }

}