<?php
session_start();
require_once 'conexao.php';

// Verifica se o usuário tem permissão (perfil 1 = admin, perfil 2 = secretaria)
if (!isset($_SESSION['perfil']) || ($_SESSION['perfil'] != 1 && $_SESSION['perfil'] != 2)) {
    echo "<script>alert('Acesso negado!');window.location.href='principal.php';</script>";
    exit;
}

$funcionarios = []; // Inicializa a variável
$mostrando_todos = false;

// Verifica se foi solicitado listar todos os funcionários
if (isset($_GET['listar_todos'])) {
    try {
        $sql = "SELECT * FROM funcionario ORDER BY nome_funcionario ASC";
        $stmt = $pdo->query($sql);
        $funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $mostrando_todos = true;
    } catch (PDOException $e) {
        $erro = "Erro ao listar funcionários: " . $e->getMessage();
    }
} 
// Processa a busca específica
elseif ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['busca'])) {
    $busca = trim($_POST['busca']);

    if (is_numeric($busca)) {
        $sql = "SELECT * FROM funcionario WHERE id_funcionario = :busca ORDER BY nome_funcionario ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':busca', $busca, PDO::PARAM_INT);
    } else {
        $sql = "SELECT * FROM funcionario WHERE nome_funcionario LIKE :busca_nome ORDER BY nome_funcionario ASC";
        $stmt = $pdo->prepare($sql);
        $busca_nome = "%$busca%";
        $stmt->bindValue(':busca_nome', $busca_nome, PDO::PARAM_STR);
    }

    $stmt->execute();
    $funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Funcionários</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    :root {
        --primary-color: #3498db;
        --secondary-color: #2980b9;
        --success-color: #2ecc71;
        --danger-color: #e74c3c;
        --warning-color: #f39c12;
        --info-color: #17a2b8;
        --light-color: #f8f9fa;
        --dark-color: #343a40;
        --border-color: #dee2e6;
        --text-color: #212529;
        --text-muted: #6c757d;
        --white: #ffffff;
        --table-hover: rgba(0, 0, 0, 0.03);
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
        background-color: #f5f7fa;
        color: var(--text-color);
        line-height: 1.6;
        padding: 20px;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        background-color: var(--white);
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    }

    h2 {
        color: var(--primary-color);
        margin-bottom: 25px;
        font-size: 28px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .action-buttons {
        margin-bottom: 30px;
    }

    .search-form {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        align-items: flex-end;
    }

    .form-group {
        flex: 1;
        min-width: 250px;
    }

    label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: var(--text-color);
    }

    input[type="text"] {
        width: 100%;
        padding: 10px 15px;
        border: 1px solid var(--border-color);
        border-radius: 4px;
        font-size: 16px;
        transition: border-color 0.3s;
    }

    input[type="text"]:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
    }

    button[type="submit"] {
        background-color: var(--primary-color);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        font-weight: 600;
        transition: background-color 0.3s;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    button[type="submit"]:hover {
        background-color: var(--secondary-color);
    }

    .list-all-btn {
        background-color: var(--info-color);
        color: white;
        border: none;
        padding: 8px 20px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        font-weight: 600;
        transition: background-color 0.3s;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .list-all-btn:hover {
        background-color: #138496;
    }

    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 4px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .alert-info {
        background-color: #d1ecf1;
        color: #0c5460;
        border: 1px solid #bee5eb;
    }

    .results-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 30px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .results-table th {
        background-color: var(--primary-color);
        color: white;
        padding: 12px 15px;
        text-align: left;
        font-weight: 600;
    }

    .results-table td {
        padding: 12px 15px;
        border-bottom: 1px solid var(--border-color);
        vertical-align: middle;
    }

    .results-table tr:hover {
        background-color: var(--table-hover);
    }

    .action-links {
        display: flex;
        gap: 15px;
    }

    .edit-link, .delete-link {
        text-decoration: none;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 5px;
        padding: 5px 10px;
        border-radius: 4px;
        transition: all 0.3s;
    }

    .edit-link {
        color: var(--warning-color);
    }

    .edit-link:hover {
        background-color: rgba(243, 156, 18, 0.1);
    }

    .delete-link {
        color: var(--danger-color);
    }

    .delete-link:hover {
        background-color: rgba(231, 76, 60, 0.1);
    }

    .no-results {
        padding: 20px;
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
        border-radius: 4px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .back-button {
        display: inline-block;
        padding: 10px 20px;
        background-color: var(--light-color);
        color: var(--text-color);
        text-decoration: none;
        border-radius: 4px;
        font-weight: 600;
        transition: background-color 0.3s;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .back-button:hover {
        background-color: #e2e6ea;
    }

    /* Responsividade */
    @media (max-width: 768px) {
        .container {
            padding: 20px;
        }
        
        .search-form {
            flex-direction: column;
            align-items: stretch;
        }
        
        .action-links {
            flex-direction: column;
            gap: 8px;
        }
        
        .results-table {
            display: block;
            overflow-x: auto;
        }
    }
</style>
</head>
<body>
    <div class="container">
        <h3>Desenvolvido por: Bruno Vitor dos Santos</h3>
        <h2><i class="fas fa-users"></i> Lista de Funcionários</h2>
        
<div class="action-buttons">
    <form action="buscar_funcionario.php" method="POST" class="search-form">
        <div class="form-group">
            <label for="busca"><i class="fas fa-search"></i> Buscar Funcionário:</label>
            <input type="text" id="busca" name="busca" placeholder="Digite nome ou ID">
        </div>
        <button type="submit">Pesquisar</button>

        <a href="buscar_funcionario.php?listar_todos=1" class="list-all-btn">
        <i class="fas fa-list"></i> Listar Todos
        </a>

    </form>
        </form>
        </div>
            
            

        <?php if (!empty($funcionarios)): ?>
            <?php if($mostrando_todos): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Exibindo todos os funcionários cadastrados.
                </div>
            <?php endif; ?>
            
            <table class="results-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Endereço</th>
                        <th>Telefone</th>
                        <th>Email</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($funcionarios as $funcionario): ?>
                        <tr>
                            <td><?= htmlspecialchars($funcionario['id_funcionario']) ?></td>
                            <td><?= htmlspecialchars($funcionario['nome_funcionario']) ?></td>
                            <td><?= htmlspecialchars($funcionario['endereco']) ?></td>
                            <td><?= htmlspecialchars($funcionario['telefone']) ?></td>
                            <td><?= htmlspecialchars($funcionario['email']) ?></td>
                            <td class="action-links">
                                <a href="alterar_funcionario.php?id=<?= htmlspecialchars($funcionario['id_funcionario']) ?>" class="edit-link">
                                    <i class="fas fa-edit"></i> Alterar
                                </a>
                                <a href="excluir_funcionario.php?id=<?= htmlspecialchars($funcionario['id_funcionario']) ?>" class="delete-link">
                                    <i class="fas fa-trash-alt"></i> Excluir
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
            <div class="no-results">
                <i class="fas fa-info-circle"></i> Nenhum funcionário encontrado com os critérios informados.
            </div>
        <?php endif; ?>

        <a href="principal.php" class="back-button"><i class="fas fa-arrow-left"></i> Voltar</a>
    </div>
</body>
</html>