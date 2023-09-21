function closeMessage(index) {
    // Ciblez l'élément avec l'ID unique
    var messageElement = document.getElementById('message_' + index);
    var errorElement = document.getElementById('error_' + index);

    // Masquez l'élément
    if (messageElement) {
        messageElement.style.display = 'none';
    }
    if (errorElement) {
        errorElement.style.display = 'none';
    }
}