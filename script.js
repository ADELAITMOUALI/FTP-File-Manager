document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const errorMessage = document.getElementById('errorMessage');

    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;

        // Simple client-side validation
        if (username.trim() === '' || password.trim() === '') {
            errorMessage.textContent = 'Veuillez remplir tous les champs.';
            return;
        }

        // Send the form data to the server
        fetch('login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = 'dashboard.php'; // Redirect to dashboard on successful login
            } else {
                errorMessage.textContent = data.message || 'Échec de la connexion. Veuillez réessayer.';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            errorMessage.textContent = 'Une erreur s\'est produite. Veuillez réessayer plus tard.';
        });
    });
});