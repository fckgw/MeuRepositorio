<?php
require_once 'session_check.php';
if ($user['role'] !== 'admin') {
    die("Acesso negado. Apenas administradores podem aceder a esta página.");
}
require_once 'config.php'; // Inclui as constantes como APP_NAME e BASE_URL
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="main-wrapper">
        <!-- Inclui o menu lateral de administração -->
        <?php include 'admin_sidebar.php'; ?>

        <div class="page-content">
            <header class="header">
                <div class="header-placeholder"></div>
                <div class="user-menu">
                    <span>Olá, <?php echo htmlspecialchars($user['username']); ?>! (Admin)</span>
                    <a href="index.php">Ver o meu Driver</a>
                    <a href="logout.php">Sair</a>
                </div>
            </header>
            <main class="admin-panel">
                <h2>Dashboard Geral</h2>
                <div class="dashboard-grid">
                    <div class="chart-container">
                        <h3>Utilizadores por Módulo</h3>
                        <canvas id="usersPerModuleChart"></canvas>
                    </div>
                    <!-- Pode adicionar mais caixas de estatísticas aqui no futuro -->
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Script do gráfico
            fetch('get_stats.php')
                .then(response => response.json())
                .then(result => {
                    if(result.status === 'success') {
                        const ctx = document.getElementById('usersPerModuleChart').getContext('2d');
                        new Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                labels: result.data.labels,
                                datasets: [{
                                    label: 'Utilizadores',
                                    data: result.data.values,
                                    backgroundColor: ['#4285F4', '#EA4335', '#FBBC05', '#34A853', '#A85334', '#FF6384', '#36A2EB'],
                                    hoverOffset: 4
                                }]
                            },
                            options: { responsive: true, maintainAspectRatio: false }
                        });
                    }
                });
        });
    </script>
</body>
</html>