
<?php
/*
|--------------------------------------------------------------------------
| PAINEL DO USUÁRIO - ESCOLA AGRÍCOLA
|--------------------------------------------------------------------------
| - Verifica se o usuário está autenticado.
| - Impede que administradores acessem a área de estudantes.
| - Exibe o nome e as iniciais do usuário.
*/

// ==========================================================
// 1. INICIALIZAÇÃO DA SESSÃO
// ==========================================================

session_start();

// ==========================================================
// 2. VERIFICAÇÃO DE AUTENTICAÇÃO
// ==========================================================

// Se não existir uma sessão de usuário, volta ao login.
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

// ==========================================================
// 3. VERIFICAÇÃO DO TIPO DE USUÁRIO
// ==========================================================

$userRole = $_SESSION['user_role'] ?? '';

if ($userRole !== 'user') {

    // Se for administrador, direciona para o painel administrativo.
    if ($userRole === 'admin') {
        header('Location: mainAdm.php');
        exit;
    }

    // Qualquer perfil desconhecido retorna ao login.
    header('Location: login.html');
    exit;
}

// ==========================================================
// 4. DADOS DO USUÁRIO
// ==========================================================

// Recupera o nome salvo durante o login.
$rawUserName = $_SESSION['user_name'] ?? 'Estudante';

// Protege o nome contra inserção de código HTML.
$userName = htmlspecialchars(
    $rawUserName,
    ENT_QUOTES,
    'UTF-8'
);

// Recupera o e-mail.
$userEmail = htmlspecialchars(
    $_SESSION['user_email'] ?? '',
    ENT_QUOTES,
    'UTF-8'
);

// ==========================================================
// 5. GERAÇÃO DAS INICIAIS DO USUÁRIO
// ==========================================================

$nameParts = preg_split(
    '/\s+/u',
    trim($rawUserName),
    -1,
    PREG_SPLIT_NO_EMPTY
);

$initials = '';

if (!empty($nameParts)) {

    // Primeira letra do primeiro nome.
    $initials .= mb_substr(
        $nameParts[0],
        0,
        1,
        'UTF-8'
    );

    // Primeira letra do último sobrenome.
    if (count($nameParts) > 1) {
        $initials .= mb_substr(
            end($nameParts),
            0,
            1,
            'UTF-8'
        );
    }

}

$initials = htmlspecialchars(
    mb_strtoupper($initials, 'UTF-8'),
    ENT_QUOTES,
    'UTF-8'
);

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Área do Estudante | Escola Agrícola</title>

    <link rel="stylesheet" href="style.css">

</head>

<body class="dashboard-page user-page">

    <div class="dashboard-shell">

        <!-- =================================================
             CABEÇALHO E NAVEGAÇÃO
        ================================================== -->

        <header class="dashboard-header">

            <a href="mainUsuario.php" class="dashboard-brand">

                <span class="brand-icon">EA</span>

                <span class="brand-name">
                    Escola Agrícola
                </span>

            </a>

            <nav class="dashboard-nav">

                <a href="#inicio" class="active">
                    Início
                </a>

                <a href="#cursos">
                    Meus cursos
                </a>

                <a href="#atividades">
                    Atividades
                </a>

            </nav>

            <!-- Perfil e botão de saída -->

            <div class="profile-actions">

                <a href="#perfil" class="profile-link">

                    <span class="profile-avatar" aria-hidden="true">
                        <?= $initials ?>
                    </span>

                    <span class="profile-name">
                        <?= $userName ?>
                    </span>

                </a>

                <a href="logout.php" class="logout-button">
                    Sair
                </a>

            </div>

        </header>

        <!-- =================================================
             CONTEÚDO PRINCIPAL
        ================================================== -->

        <main>

            <!-- Apresentação do estudante -->

            <section class="dashboard-welcome" id="inicio">

                <div class="welcome-content">

                    <span class="dashboard-eyebrow">
                        ÁREA DO ESTUDANTE
                    </span>

                    <h1>
                        Olá, <?= $userName ?>!
                    </h1>

                    <p>
                        Bem-vindo à sua área de estudos.
                        Acompanhe seus cursos e atividades
                        em um só lugar.
                    </p>

                    <a href="#cursos" class="primary-button">
                        Acessar meus cursos
                    </a>

                </div>

                <div class="welcome-decoration">

                    <span class="welcome-decoration-icon">
                        EA
                    </span>

                </div>

            </section>

            <!-- =================================================
                 RESUMO DO ESTUDANTE
            ================================================== -->

            <section class="dashboard-section">

                <div class="section-heading">

                    <div>

                        <span class="dashboard-eyebrow">
                            SEU DESEMPENHO
                        </span>

                        <h2>Resumo acadêmico</h2>

                    </div>

                </div>

                <div class="stats-grid">

                    <article class="stat-card">

                        <span class="stat-label">
                            Cursos matriculados
                        </span>

                        <strong class="stat-value">
                            02
                        </strong>

                        <span class="stat-description">
                            Cursos em andamento
                        </span>

                    </article>

                    <article class="stat-card">

                        <span class="stat-label">
                            Atividades pendentes
                        </span>

                        <strong class="stat-value">
                            03
                        </strong>

                        <span class="stat-description">
                            Atividades para concluir
                        </span>

                    </article>

                    <article class="stat-card">

                        <span class="stat-label">
                            Frequência
                        </span>

                        <strong class="stat-value">
                            94%
                        </strong>

                        <span class="stat-description">
                            Presença nas aulas
                        </span>

                    </article>

                </div>

            </section>

            <!-- =================================================
                 CURSOS DO ESTUDANTE
            ================================================== -->

            <section class="dashboard-section" id="cursos">

                <div class="section-heading">

                    <div>

                        <span class="dashboard-eyebrow">
                            APRENDIZADO
                        </span>

                        <h2>Meus cursos</h2>

                    </div>

                    <a href="#cursos" class="section-link">
                        Ver todos
                    </a>

                </div>

                <div class="courses-grid">

                    <!-- Curso 1 -->

                    <article class="course-card">

                        <div class="course-image course-image-agro">

                            <span>
                                AGROPECUÁRIA
                            </span>

                        </div>

                        <div class="course-content">

                            <span class="course-category">
                                CURSO TÉCNICO
                            </span>

                            <h3>
                                Técnico em Agropecuária
                            </h3>

                            <p>
                                Aprenda sobre produção agrícola,
                                manejo e desenvolvimento rural.
                            </p>

                            <div class="course-progress-info">

                                <span>
                                    Progresso
                                </span>

                                <strong>68%</strong>

                            </div>

                            <div
                                class="progress-bar"
                                role="progressbar"
                                aria-label="Progresso do curso de Agropecuária"
                                aria-valuenow="68"
                                aria-valuemin="0"
                                aria-valuemax="100"
                            >

                                <span style="width: 68%;"></span>

                            </div>

                        </div>

                    </article>

                    <!-- Curso 2 -->

                    <article class="course-card">

                        <div class="course-image course-image-meio-ambiente">

                            <span>
                                MEIO AMBIENTE
                            </span>

                        </div>

                        <div class="course-content">

                            <span class="course-category">
                                CURSO TÉCNICO
                            </span>

                            <h3>
                                Técnico em Meio Ambiente
                            </h3>

                            <p>
                                Estude sustentabilidade, conservação
                                e preservação ambiental.
                            </p>

                            <div class="course-progress-info">

                                <span>
                                    Progresso
                                </span>

                                <strong>42%</strong>

                            </div>

                            <div
                                class="progress-bar"
                                role="progressbar"
                                aria-label="Progresso do curso de Meio Ambiente"
                                aria-valuenow="42"
                                aria-valuemin="0"
                                aria-valuemax="100"
                            >

                                <span style="width: 42%;"></span>

                            </div>

                        </div>

                    </article>

                </div>

            </section>

            <!-- =================================================
                 ATIVIDADES RECENTES
            ================================================== -->

            <section class="dashboard-section" id="atividades">

                <div class="section-heading">

                    <div>

                        <span class="dashboard-eyebrow">
                            ORGANIZAÇÃO
                        </span>

                        <h2>Atividades recentes</h2>

                    </div>

                </div>

                <div class="activity-list">

                    <article class="activity-item">

                        <div class="activity-icon">
                            01
                        </div>

                        <div class="activity-info">

                            <h3>
                                Atividade de Agropecuária
                            </h3>

                            <p>
                                Revisar o conteúdo sobre produção agrícola.
                            </p>

                        </div>

                        <span class="activity-status pending">
                            Pendente
                        </span>

                    </article>

                    <article class="activity-item">

                        <div class="activity-icon">
                            02
                        </div>

                        <div class="activity-info">

                            <h3>
                                Trabalho de Meio Ambiente
                            </h3>

                            <p>
                                Pesquisa sobre sustentabilidade.
                            </p>

                        </div>

                        <span class="activity-status pending">
                            Pendente
                        </span>

                    </article>

                    <article class="activity-item">

                        <div class="activity-icon">
                            03
                        </div>

                        <div class="activity-info">

                            <h3>
                                Avaliação de conhecimentos
                            </h3>

                            <p>
                                Conteúdo introdutório do curso.
                            </p>

                        </div>

                        <span class="activity-status completed">
                            Concluída
                        </span>

                    </article>

                </div>

            </section>

        </main>

        <!-- =================================================
             RODAPÉ
        ================================================== -->

        <footer class="dashboard-footer">

            <p>
                Escola Agrícola
            </p>

            <span>
                Plataforma educacional
            </span>

        </footer>

    </div>

</body>
</html>
