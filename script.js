// Sistema de Login com JavaScript
const passwordInput = document.querySelector('#password');
const togglePassword = document.querySelector('#toggle-password');
const loginForm = document.querySelector('#login-form');
const status = document.querySelector('#status');

togglePassword.addEventListener('click', () => {
    const isPassword = passwordInput.type === 'password';
    passwordInput.type = isPassword ? 'text' : 'password';
    togglePassword.textContent = isPassword ? 'Ocultar' : 'Mostrar';
    togglePassword.setAttribute('aria-label', isPassword ? 'Ocultar senha' : 'Mostrar senha');
});

loginForm.addEventListener('submit', (event) => {
    event.preventDefault();
    status.textContent = 'Acesso pronto para ser conectado ao sistema.';
});
