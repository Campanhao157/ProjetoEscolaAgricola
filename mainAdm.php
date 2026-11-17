```php
<?php

// Inicia a sessão.
session_start();

// Verifica se existe um usuário autenticado.
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

// Somente administradores podem acessar este painel.
if (($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: mainUsuario.php');
    exit;
}

// Recupera o nome do administrador.
$adminName = htmlspecialchars(
    $_SESSION['user_name'] ?? 'Administrador',
    ENT_QUOTES,
    'UTF-8'
);

// Gera as iniciais do administrador.
$nameParts = preg_split(
    '/\s+/u',
    trim($_SESSION['user_name'] ?? 'Administrador')
);

$initials = '';

if (!empty($nameParts[0])) {
    $initials .= mb_substr($nameParts[0], 0, 1, 'UTF-8');
}

if (count($nameParts) > 1) {
    $initials .= mb_substr(end($nameParts), 0, 1, 'UTF-8');
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
    <meta name="theme-color" content="#183b2c">

    <title>Painel administrativo | Escola Agrícola</title>

    <link rel="stylesheet" href="style.css">

    <!-- Estilos específicos do cabeçalho administrativo -->
    <style>
        .admin-header-profile {
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: flex-end;
            flex-wrap: nowrap;
            gap: 14px;
            flex-shrink: 0;
            width: auto;
        }

        .admin-header-profile .profile-link {
            display: inline-flex;
            flex-direction: row;
            align-items: center;
            gap: 10px;
            white-space: nowrap;
            text-decoration: none;
            color: inherit;
            width: auto;
        }

        .admin-header-profile .profile-avatar {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .admin-header-profile .profile-name {
            white-space: nowrap;
        }

        .admin-header-profile .logout-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            white-space: nowrap;

            padding: 9px 14px;
            border: 1px solid #d8ded5;
            border-radius: 8px;

            background: transparent;
            color: #647064;

            font-size: 13px;
            font-weight: 600;
            text-decoration: none;

            transition: background 0.2s ease,
                        color 0.2s ease,
                        border-color 0.2s ease;
        }

        .admin-header-profile .logout-button:hover {
            background: #fff0ed;
            border-color: #e8c5bf;
            color: #c0392b;
        }

        @media (max-width: 1100px) {
            .dashboard-header {
                flex-wrap: wrap;
            }

            .admin-header-profile {
                margin-left: auto;
            }
        }

        @media (max-width: 600px) {
            .admin-header-profile {
                gap: 9px;
            }

            .admin-header-profile .profile-name {
                display: none;
            }
        }
    </style>
</head>

<body class="dashboard-page admin-page">

    <div class="dashboard-shell">

        <!-- Cabeçalho administrativo -->
        <header class="dashboard-header">

            <a
                class="brand dashboard-brand"
                href="mainAdm.php"
                aria-label="Escola Agrícola, painel administrativo"
            >
                <span class="brand-mark" aria-hidden="true">⌁</span>
                <span>Escola Agrícola</span>
            </a>

            <nav class="dashboard-nav" aria-label="Navegação administrativa">

                <a class="nav-link active" href="#inicio" aria-current="page">
                    Visão geral
                </a>

                <a class="nav-link" href="#estudantes">
                    Estudantes
                </a>

                <a class="nav-link" href="#cursos">
                    Cursos
                </a>

                <a class="nav-link" href="#relatorios">
                    Relatórios
                </a>

            </nav>

            <!-- Perfil e logout na mesma linha -->
            <div class="admin-header-profile">

                <a class="profile-link" href="#perfil">

                    <span class="profile-avatar" aria-hidden="true">
                        <?= $initials ?>
                    </span>

                    <span class="profile-name">
                        <?= $adminName ?>
                    </span>

                </a>

                <a href="logout.php" class="logout-button">
                    Sair
                </a>

            </div>

        </header>

        <main class="dashboard-main" id="inicio">

            <!-- Apresentação -->
            <section
                class="dashboard-hero admin-hero"
                aria-labelledby="admin-title"
            >

                <img
                    class="hero-image"
                    src="https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=1800&q=85"
                    alt="Plantação verde ao nascer do sol"
                >

                <div class="hero-content">

                    <p class="eyebrow">
                        PAINEL ADMINISTRATIVO
                    </p>

                    <h1 id="admin-title">
                        Bem-vindo, <?= $adminName ?>.
                    </h1>

                    <p>
                        Acompanhe a comunidade escolar e mantenha
                        cursos e atividades em movimento.
                    </p>

                    <div class="admin-hero-actions">

                        <a class="hero-button" href="#estudantes">
                            Gerenciar estudantes
                            <span aria-hidden="true">→</span>
                        </a>

                        <a class="admin-secondary-link" href="#cursos">
                            Ver cursos
                        </a>

                    </div>

                </div>

                <p class="hero-caption">
                    ETEC Orlando Quagliato · Gestão escolar
                </p>

            </section>

            <!-- Indicadores gerais -->
            <section class="admin-overview" aria-labelledby="overview-title">

                <div class="section-heading">

                    <div>
                        <p class="section-kicker">RESUMO DA ESCOLA</p>
                        <h2 id="overview-title">Visão geral</h2>
                    </div>

                    <span class="admin-data-note">
                        Dados de demonstração
                    </span>

                </div>

                <div class="admin-stat-grid">

                    <article class="admin-stat">
                        <span class="admin-stat-label">Estudantes ativos</span>
                        <strong>248</strong>
                        <span class="admin-stat-note">em todas as unidades</span>
                    </article>

                    <article class="admin-stat">
                        <span class="admin-stat-label">Cursos publicados</span>
                        <strong>12</strong>
                        <span class="admin-stat-note">2 em preparação</span>
                    </article>

                    <article class="admin-stat">
                        <span class="admin-stat-label">Solicitações pendentes</span>
                        <strong>08</strong>
                        <span class="admin-stat-note">aguardando análise</span>
                    </article>

                    <article class="admin-stat">
                        <span class="admin-stat-label">Frequência média</span>
                        <strong>94<span class="stat-percent">%</span></strong>
                        <span class="admin-stat-note">neste mês</span>
                    </article>

                </div>

            </section>

            <div class="admin-workspace">

                <!-- Estudantes -->
                <section
                    class="admin-section"
                    id="estudantes"
                    aria-labelledby="students-title"
                >

                    <div class="section-heading">

                        <div>
                            <p class="section-kicker">COMUNIDADE</p>
                            <h2 id="students-title">Estudantes recentes</h2>
                        </div>

                        <a class="text-link" href="#estudantes">
                            Ver lista <span aria-hidden="true">→</span>
                        </a>

                    </div>

                    <div class="admin-list">

                        <article class="admin-list-row">
                            <span class="admin-list-avatar">MC</span>

                            <div class="admin-list-copy">
                                <h3>Marina Costa</h3>
                                <p>Unidade Rural · Técnico em Agropecuária</p>
                            </div>

                            <span class="admin-pill pill-active">Ativo</span>
                        </article>

                        <article class="admin-list-row">
                            <span class="admin-list-avatar avatar-gold">JP</span>

                            <div class="admin-list-copy">
                                <h3>João Pedro Lima</h3>
                                <p>Unidade Urbana · Técnico em Meio Ambiente</p>
                            </div>

                            <span class="admin-pill pill-pending">Pendente</span>
                        </article>

                        <article class="admin-list-row">
                            <span class="admin-list-avatar avatar-blue">LS</span>

                            <div class="admin-list-copy">
                                <h3>Luiza Santos</h3>
                                <p>Unidade Rural · Técnico em Agropecuária</p>
                            </div>

                            <span class="admin-pill pill-active">Ativo</span>
                        </article>

                    </div>

                </section>

                <!-- Cursos -->
                <section
                    class="admin-section"
                    id="cursos"
                    aria-labelledby="courses-title"
                >

                    <div class="section-heading">

                        <div>
                            <p class="section-kicker">ENSINO</p>
                            <h2 id="courses-title">Cursos em destaque</h2>
                        </div>

                        <a class="text-link" href="#cursos">
                            Ver cursos <span aria-hidden="true">→</span>
                        </a>

                    </div>

                    <div class="admin-list">

                        <article class="admin-course-row">
                            <div class="admin-course-icon icon-leaf" aria-hidden="true">✳</div>

                            <div class="admin-list-copy">
                                <h3>Técnico em Agropecuária</h3>
                                <p>Unidade Rural · 86 estudantes</p>
                            </div>

                            <span class="course-state"><span></span>Publicado</span>
                        </article>

                        <article class="admin-course-row">
                            <div class="admin-course-icon icon-sun" aria-hidden="true">◉</div>

                            <div class="admin-list-copy">
                                <h3>Técnico em Meio Ambiente</h3>
                                <p>Unidade Urbana · 54 estudantes</p>
                            </div>

                            <span class="course-state"><span></span>Publicado</span>
                        </article>

                        <article class="admin-course-row">
                            <div class="admin-course-icon icon-water" aria-hidden="true">≈</div>

                            <div class="admin-list-copy">
                                <h3>Gestão de Recursos Hídricos</h3>
                                <p>Unidade Rural · 32 estudantes</p>
                            </div>

                            <span class="course-state state-draft"><span></span>Rascunho</span>
                        </article>

                    </div>

                </section>

            </div>

            <!-- Atividade recente -->
            <section
                class="admin-activity"
                id="relatorios"
                aria-labelledby="activity-title"
            >

                <div class="section-heading">

                    <div>
                        <p class="section-kicker">ACOMPANHAMENTO</p>
                        <h2 id="activity-title">Atividade recente</h2>
                    </div>

                    <a class="text-link" href="#relatorios">
                        Abrir relatórios <span aria-hidden="true">→</span>
                    </a>

                </div>

                <div class="activity-row">
                    <span class="activity-marker marker-green" aria-hidden="true"></span>

                    <p>
                        <strong>Nova solicitação de matrícula</strong>
                        <span>João Pedro Lima · Técnico em Meio Ambiente</span>
                    </p>

                    <time datetime="2026-10-28T09:14">09:14</time>
                </div>

                <div class="activity-row">
                    <span class="activity-marker marker-gold" aria-hidden="true"></span>

                    <p>
                        <strong>Curso atualizado</strong>
                        <span>Técnico em Agropecuária · Unidade Rural</span>
                    </p>

                    <time datetime="2026-10-28T08:42">08:42</time>
                </div>

            </section>

        </main>

        <footer class="dashboard-footer">
            ETEC Orlando Quagliato
            <span aria-hidden="true">·</span>
            Unidades Rurais e Urbanas
        </footer>

    </div>

</body>
</html>
```