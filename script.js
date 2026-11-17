
document.addEventListener('DOMContentLoaded', () => {

    // =====================================================
    // 1. ELEMENTOS DA PÁGINA
    // =====================================================

    const passwordInput = document.getElementById('password');
    const togglePassword = document.getElementById('toggle-password');
    const status = document.getElementById('status');

    // =====================================================
    // 2. MOSTRAR E OCULTAR SENHA
    // =====================================================

    if (passwordInput && togglePassword) {

        togglePassword.addEventListener('click', () => {

            // Verifica se a senha está escondida.
            const isPassword = passwordInput.type === 'password';

            // Alterna entre os tipos password e text.
            passwordInput.type = isPassword ? 'text' : 'password';

            // Atualiza o texto do botão.
            togglePassword.textContent = isPassword
                ? 'Ocultar'
                : 'Mostrar';

            // Atualiza a descrição acessível do botão.
            togglePassword.setAttribute(
                'aria-label',
                isPassword ? 'Ocultar senha' : 'Mostrar senha'
            );

        });

    }

    // =====================================================
    // 3. LEITURA DOS PARÂMETROS DA URL
    // =====================================================

    const params = new URLSearchParams(window.location.search);

    const error = params.get('error');
    const registered = params.get('registered');
    const success = params.get('success');

    // =====================================================
    // 4. MENSAGENS DE ERRO
    // =====================================================

    const errorMessages = {

        invalid:
            'E-mail ou senha inválidos. Verifique seus dados.',

        service:
            'Não foi possível conectar ao sistema. Tente novamente.',

        security:
            'Não foi possível validar sua solicitação. Atualize a página.',

        name:
            'Informe um nome válido.',

        email:
            'Informe um endereço de e-mail válido.',

        password:
            'A senha precisa ter pelo menos 8 caracteres.',

        match:
            'As senhas informadas não coincidem.',

        exists:
            'Este e-mail já está cadastrado.'

    };

    if (status && error) {

        status.textContent = errorMessages[error] ||
            'Ocorreu um erro. Verifique os dados e tente novamente.';

        status.classList.remove('success');
        status.classList.add('error');

    }

    // =====================================================
    // 5. MENSAGEM DE CADASTRO CONCLUÍDO
    // =====================================================

    if (status && (registered === '1' || success === 'registered')) {

        status.textContent =
            'Conta criada com sucesso! Faça login para continuar.';

        status.classList.remove('error');
        status.classList.add('success');

    }

});
