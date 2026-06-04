document.getElementById('login-form').addEventListener('submit', async e => {
    e.preventDefault();
    const form = e.target;
    const error = document.getElementById('login-error');
    error.classList.add('hidden');
    error.textContent = '';

    try {
        const response = await fetch('/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                username: form.username.value,
                password: form.password.value
            })
        });

        const data = await response.json();

        if (!response.ok) {
            error.textContent = data.error || 'Login failed';
            error.classList.remove('hidden');
            return;
        }

        window.location.href = 'media.html';
    } catch (err) {
        error.textContent = 'An error occurred. Please try again.';
        error.classList.remove('hidden');
    }
});