// Elementos usados pelo controle da senha e pelo status do login.
const passwordInput = document.querySelector('#password');
const togglePassword = document.querySelector('#toggle-password');
const status = document.querySelector('#status');

if (togglePassword && passwordInput) {
    togglePassword.addEventListener('click', () => {
        const isPassword = passwordInput.type === 'password';
        passwordInput.type = isPassword ? 'text' : 'password';
        togglePassword.textContent = isPassword ? 'Ocultar' : 'Mostrar';
        togglePassword.setAttribute('aria-label', isPassword ? 'Ocultar senha' : 'Mostrar senha');
    });
}

const loginError = new URLSearchParams(window.location.search).get('error');
if (status && loginError) {
    status.textContent = loginError === 'service'
        ? 'Não foi possível conectar ao serviço de login. Tente novamente.'
        : 'E-mail ou senha inválidos.';
    status.classList.add('error');
}
