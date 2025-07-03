<?php
    session_start();

    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        header("Location: login.html");
        exit();
    }

    include_once __DIR__ . '/../../api/config/database.php';

    $database = new Database();
    $db = $database->getConnection();
    $query = "SELECT full_name, cpf, birth_date, phone, email FROM users WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Picpay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/pages/pagina_inicial.css">
    <link rel="stylesheet" href="../css/pages/dashboard.css">
</head>
<body class="dashboard-body">

    <div class="dashboard-container">
        <aside id="sidebar">
            <div class="sidebar-header">
                <a class="navbar-brand" href="../../index.html">
                    <svg width="90" height="30" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g clip-path="url(#a)">
                            <path d="M18.504 23.928h4.212V11.661h-4.212v12.267Zm5.661-20.61H21.33v2.835h2.835V3.318ZM8.271 4.695H4.212V8.25h3.771c2.394 0 3.771 1.161 3.771 3.339S10.377 15 7.983 15H4.212V8.322H0v15.606h4.212v-5.373h3.996c4.86 0 7.695-2.61 7.695-7.11-.009-4.212-2.763-6.75-7.632-6.75ZM27 .483h-8.496v8.496H27V.483Zm-1.377 7.038h-5.661V1.86h5.661v5.661Zm24.678-2.826h-3.843V8.25h3.627c2.394 0 3.771 1.161 3.771 3.339S52.479 15 50.085 15h-3.627V8.322h-4.212v15.606h4.212v-5.373h3.843c4.86 0 7.695-2.61 7.695-7.11 0-4.212-2.835-6.75-7.695-6.75Zm35.343 4.5-3.627 9.144-3.627-9.144h-4.356l5.805 14.733-2.25 5.589h4.428L90 9.195h-4.356Zm-18.873-.072c-2.538 0-4.5.585-6.678 1.665l1.305 2.907c1.521-.873 3.051-1.305 4.428-1.305 2.034 0 3.051.873 3.051 2.466v.288h-4.068c-3.627 0-5.589 1.665-5.589 4.428 0 2.682 1.89 4.572 5.085 4.572 2.034 0 3.483-.729 4.644-1.962v1.593h4.14v-9.576c-.144-3.123-2.394-5.076-6.318-5.076Zm2.475 9.864c-.432 1.233-1.665 2.25-3.411 2.25-1.449 0-2.322-.729-2.322-1.89s.801-1.665 2.394-1.665h3.339v1.305Zm-35.784 1.746c-2.034 0-3.483-1.593-3.483-3.996 0-2.322 1.449-3.915 3.483-3.915 1.449 0 2.538.585 3.339 1.593l2.835-2.034c-1.305-1.962-3.555-3.123-6.39-3.123-4.428-.072-7.479 2.979-7.479 7.479s3.051 7.479 7.479 7.479c3.051 0 5.301-1.233 6.534-3.267l-2.907-1.962c-.729 1.161-1.89 1.746-3.411 1.746Z" fill="var(--primary-color)"></path>
                            <defs>
                                <clipPath id="a">
                                    <path fill="#fff" transform="translate(0 .483)" d="M0 0h90v29.034H0z" />
                                </clipPath>
                            </defs>
                        </g>
                    </svg>
                </a>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="#" class="nav-link active" data-target="home"><i class="bi bi-house-door-fill"></i> <span>Início</span></a></li>
                    <li><a href="#" class="nav-link" data-target="extrato"><i class="bi bi-receipt"></i> <span>Extrato</span></a></li>
                    <li><a href="#" class="nav-link" data-target="pix"><i class="bi bi-x-diamond-fill"></i> <span>Área Pix</span></a></li>
                    <li><a href="#" class="nav-link" data-target="cartoes"><i class="bi bi-credit-card-2-front-fill"></i> <span>Cartões</span></a></li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <ul>
                    <li><a href="#" class="nav-link" data-target="perfil"><i class="bi bi-person-fill"></i> <span>Meu Perfil</span></a></li>
                    <li><a href="/Breno/Bank/api/logout.php" class="nav-link"><i class="bi bi-box-arrow-left"></i> <span>Sair</span></a></li>
                </ul>
            </div>
        </aside>

        <main id="main-content">

            <div id="home" class="page-content active">
                <div class="page-header">
                    <h1>
                        Olá, <?php
                                // Pega o nome completo da sessão
                                $fullName = $_SESSION['user_name'];
                                // Separa o nome em partes usando o espaço e pega apenas a primeira parte
                                $firstName = explode(' ', $fullName)[0];
                                // Imprime o primeiro nome de forma segura
                                echo htmlspecialchars($firstName);
                                ?>!
                    </h1>
                    <p>Seu resumo financeiro em um só lugar.</p>
                </div>

                <div class="balance-card">
                    <span>Saldo disponível</span>
                    <div class="d-flex align-items-center">
                        <h2 id="balance-value">R$ 1.234,56</h2>
                        <i class="bi bi-eye-fill ms-3" id="toggle-balance" style="cursor: pointer;"></i>
                    </div>
                </div>

                <h3 class="section-subtitle">Ações Rápidas</h3>
                <div class="quick-actions">
                    <a href="#" class="action-card"><i class="bi bi-x-diamond-fill"></i><span>Fazer um Pix</span></a>
                    <a href="#" class="action-card"><i class="bi bi-upc-scan"></i><span>Pagar Boleto</span></a>
                    <a href="#" class="action-card"><i class="bi bi-arrow-down-up"></i><span>Transferir</span></a>
                    <a href="#" class="action-card"><i class="bi bi-phone-fill"></i><span>Recarga</span></a>
                </div>

                <h3 class="section-subtitle mt-5">Atividade Recente</h3>
                <ul class="transaction-list">
                    <li>
                        <div class="icon-wrapper"><i class="bi bi-cart-fill"></i></div>
                        <div class="info"><strong>Supermercado Pague Menos</strong><span>Hoje</span></div>
                        <div class="amount expense">- R$ 157,80</div>
                    </li>
                    <li>
                        <div class="icon-wrapper income"><i class="bi bi-cash-coin"></i></div>
                        <div class="info"><strong>Salário Empresa Ltda</strong><span>Ontem</span></div>
                        <div class="amount income">+ R$ 4.500,00</div>
                    </li>
                    <li>
                        <div class="icon-wrapper"><i class="bi bi-fuel-pump-fill"></i></div>
                        <div class="info"><strong>Posto Shell</strong><span>25/06/2025</span></div>
                        <div class="amount expense">- R$ 100,00</div>
                    </li>
                </ul>
                <a href="#" id="view-statement-link" class="link-more-center">Ver extrato completo</a>
            </div>

            <div id="extrato" class="page-content d-none">
                <div class="page-header">
                    <h1>Extrato Completo</h1>
                    <p>Todas as suas movimentações financeiras.</p>
                </div>
                <ul class="transaction-list full">
                    <li>
                        <div class="icon-wrapper"><i class="bi bi-cart-fill"></i></div>
                        <div class="info"><strong>Supermercado Pague Menos</strong><span>Hoje</span></div>
                        <div class="amount expense">- R$ 157,80</div>
                    </li>
                    <li>
                        <div class="icon-wrapper income"><i class="bi bi-cash-coin"></i></div>
                        <div class="info"><strong>Salário Empresa Ltda</strong><span>Ontem</span></div>
                        <div class="amount income">+ R$ 4.500,00</div>
                    </li>
                    <li>
                        <div class="icon-wrapper"><i class="bi bi-fuel-pump-fill"></i></div>
                        <div class="info"><strong>Posto Shell</strong><span>25/06/2025</span></div>
                        <div class="amount expense">- R$ 100,00</div>
                    </li>
                    <li>
                        <div class="icon-wrapper income"><i class="bi bi-x-diamond-fill"></i></div>
                        <div class="info"><strong>Pix Recebido - Maria S.</strong><span>24/06/2025</span></div>
                        <div class="amount income">+ R$ 250,00</div>
                    </li>
                    <li>
                        <div class="icon-wrapper"><i class="bi bi-cup-hot-fill"></i></div>
                        <div class="info"><strong>Padaria Pão Quente</strong><span>24/06/2025</span></div>
                        <div class="amount expense">- R$ 22,50</div>
                    </li>
                </ul>
            </div>

            <div id="pix" class="page-content d-none">
                <div class="page-header">
                    <h1>Área Pix</h1>
                    <p>Envie e receba dinheiro a qualquer hora, de graça.</p>
                </div>
                <div class="quick-actions">
                    <a href="#" class="action-card lg"><i class="bi bi-send-fill"></i><span>Enviar</span></a>
                    <a href="#" class="action-card lg"><i class="bi bi-qr-code"></i><span>Receber</span></a>
                    <a href="#" class="action-card lg"><i class="bi bi-clipboard-check-fill"></i><span>Pix Copia e Cola</span></a>
                </div>
            </div>

            <div id="cartoes" class="page-content d-none">
                <div class="page-header">
                    <h1>Meus Cartões</h1>
                    <p>Gerencie seus cartões físico e virtual.</p>
                </div>
                <div class="credit-card-display">
                    <img src="https://picpay.com/cartao-de-credito/_next/static/media/cartao-de-credito-picpay-platinum.d2d3a8d2.webp" alt="Cartão de Crédito">
                </div>
                <div class="quick-actions">
                    <a href="#" class="action-card"><i class="bi bi-eye-fill"></i><span>Ver dados</span></a>
                    <a href="#" class="action-card"><i class="bi bi-lock-fill"></i><span>Bloquear</span></a>
                    <a href="#" class="action-card"><i class="bi bi-sliders"></i><span>Ajustar limite</span></a>
                    <a href="#" class="action-card"><i class="bi bi-receipt-cutoff"></i><span>Ver faturas</span></a>
                </div>
            </div>

            <div id="perfil" class="page-content d-none">
                <div class="page-header">
                    <h1>Meu Perfil</h1>
                    <p>Gerencie seus dados e configurações de segurança.</p>
                </div>

                <div class="content-panel">

                    <div class="panel-section">
                        <h4>Dados Pessoais e de Contato</h4>
                        <p class="text-secondary">Mantenha seu e-mail e telefone sempre atualizados para receber nossas comunicações.</p>
                        <hr>
                        <form id="profile-data-form" class="mt-4">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nome Completo</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">CPF</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['cpf']) ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="profile-phone" class="form-label">Celular</label>
                                    <input type="text" class="form-control" id="profile-phone" value="<?= htmlspecialchars($user['phone']) ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="profile-email" class="form-label">E-mail</label>
                                    <input type="email" class="form-control" id="profile-email" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                                </div>
                            </div>
                            <div class="text-end mt-2">
                                <button type="button" id="edit-profile-btn" class="btn btn-outline-light">Editar Contato</button>
                                <button type="submit" id="save-profile-btn" class="btn btn-glow d-none">Salvar Alterações</button>
                            </div>
                        </form>
                    </div>

                    <div class="panel-section">
                        <h4>Segurança</h4>
                        <p class="text-secondary">Recomendamos alterar sua senha periodicamente para manter sua conta segura.</p>
                        <hr>
                        <form id="password-change-form" class="mt-4">
                            <div class="mb-3">
                                <label for="current-password" class="form-label">Senha Atual</label>
                                <input type="password" class="form-control" id="current-password" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="new-password-profile" class="form-label">Nova Senha</label>
                                    <input type="password" class="form-control" id="new-password-profile" placeholder="Mínimo 8 caracteres" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="confirm-new-password" class="form-label">Confirme a Nova Senha</label>
                                    <input type="password" class="form-control" id="confirm-new-password" required>
                                </div>
                            </div>
                            <div class="text-end mt-2">
                                <button type="submit" class="btn btn-glow">Alterar Senha</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <button id="ai-fab" class="ai-fab" title="Converse com a NexaAI">
        <i class="bi bi-stars"></i>
    </button>

    <div id="ai-chat-window" class="ai-chat-window">
        <div class="chat-header">
            <h5><i class="bi bi-stars"></i> PicPay Assistant</h5>
            <button id="close-chat-btn" class="close-chat-btn" title="Fechar">&times;</button>
        </div>
        <div id="chat-body" class="chat-body">
        </div>
        <form id="chat-form" class="chat-footer">
            <input type="text" id="chat-input" placeholder="Pergunte sobre CDB, Pix...">
            <button type="submit" id="send-chat-btn" title="Enviar"><i class="bi bi-send-fill"></i></button>
        </form>
    </div>

    <script src="../scripts/commons/dashboard.js"></script>
</body>

</html>