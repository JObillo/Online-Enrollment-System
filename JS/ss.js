document.getElementById('save-button').addEventListener('click', function () {
    const form = document.getElementById('subject-form');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());

    // Send AJAX request
    fetch('sched-sub.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams(data).toString()
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert('Subject added successfully!');
            form.reset();
        } else {
            alert('Error: ' + result.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An unexpected error occurred.');
    });
});
