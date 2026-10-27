// Elementos usados pelo controle da senha e pelo formulário.
const passwordInput = document.querySelector('#password');
const togglePassword = document.querySelector('#toggle-password');
const loginForm = document.querySelector('#login-form');
const status = document.querySelector('#status');
// Usuários locais usados apenas para simular a autenticação durante os testes.
const testUsers = [
    {
        email: 'admin@escolaagricola.com',
        password: 'Admin@123',
        name: 'Administrador',
        role: 'admin'
    },
    {
        email: 'usuario@escolaagricola.com',
        password: 'User@123',
        name: 'Usuário de Teste',
        role: 'user'
    }
];
// Alterna entre senha visível e senha protegida.
togglePassword.addEventListener('click', () => {
    const isPassword = passwordInput.type === 'password';
    passwordInput.type = isPassword ? 'text' : 'password';
    togglePassword.textContent = isPassword ? 'Ocultar' : 'Mostrar';
    togglePassword.setAttribute('aria-label', isPassword ? 'Ocultar senha' : 'Mostrar senha');
});

// Valida as credenciais e mostra o perfil encontrado.
loginForm.addEventListener('submit', (event) => {
    event.preventDefault();

    // FormData coleta os valores atuais sem precisar acessar cada campo separadamente.
    const formData = new FormData(loginForm);
    const email = formData.get('email').trim().toLowerCase();
    const password = formData.get('password');
    const user = testUsers.find((testUser) => testUser.email === email && testUser.password === password);

    if (!user) {
        status.textContent = 'E-mail ou senha inválidos.';
        status.classList.add('error');
        return;
    }

    status.classList.remove('error');
    status.textContent = `Olá, ${user.name}! Login ${user.role === 'admin' ? 'de administrador' : 'de usuário'} realizado.`;
});
