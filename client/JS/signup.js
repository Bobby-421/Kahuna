const BASE_URI = "http://localhost:8000/kahuna/api/";

function showMessage(message, type = 'danger') {
    const form = document.getElementById('signupForm');
    const existingAlert = form.querySelector('.alert');
    if (existingAlert) {
        existingAlert.remove();
    }
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} mt-3`;
    alert.textContent = message;
    form.appendChild(alert);
}

function addUser() {
    document.getElementById('signupForm').addEventListener('submit', async (evt) => {
        evt.preventDefault();
        
        const formData = new FormData(evt.target);
        
        if (!formData.get('username') || !formData.get('email') || !formData.get('password')) {
            showMessage('Please fill in all fields');
            return;
        }
        
        const submitButton = evt.target.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        
        try {
            const response = await fetch(`${BASE_URI}user`, {
                mode: 'cors',
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (!response.ok || data.error) {
                throw new Error(data.error || 'Failed to create user');
            }

            showMessage('User created successfully! Redirecting to login...', 'success');
            
            setTimeout(() => {
                window.location.href = 'index.html';
            }, 2000);

        } catch (err) {
            showMessage(err.message || 'Failed to create user. Please try again.');
        } finally {
            submitButton.disabled = false;
        }
    });
}

document.addEventListener('DOMContentLoaded', addUser);
