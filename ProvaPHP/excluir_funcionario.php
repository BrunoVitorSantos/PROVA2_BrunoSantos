<?php
session_start();
require_once 'conexao.php';

// Verifica se o usuário tem permissão (apenas admin pode excluir)
if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] != 1) {
    echo "<script>alert('Acesso negado!'); window.location.href='principal.php';</script>";
    exit;
}

// Inicializa variáveis
$erro = '';
$sucesso = '';
$funcionario = null;
$funcionarios = [];
$mostrar_form_busca = true;

// Processa a busca de funcionários
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['busca'])) {
    $busca = trim($_POST['busca']);
    
    try {
        if (is_numeric($busca)) {
            $sql = "SELECT * FROM funcionario WHERE id_funcionario = :busca";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':busca', $busca, PDO::PARAM_INT);
        } else {
            $sql = "SELECT * FROM funcionario WHERE nome_funcionario LIKE :busca";
            $stmt = $pdo->prepare($sql);
            $param_busca = '%' . $busca . '%';
            $stmt->bindParam(':busca', $param_busca, PDO::PARAM_STR);
        }
        
        $stmt->execute();
        $funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($funcionarios)) {
            $erro = "Nenhum funcionário encontrado com os critérios informados.";
        }
    } catch (PDOException $e) {
        $erro = "Erro na busca: " . $e->getMessage();
    }
}

// Processa a seleção para exclusão
if (isset($_GET['selecionar']) && is_numeric($_GET['selecionar'])) {
    $id_funcionario = $_GET['selecionar'];
    
    try {
        $sql = "SELECT * FROM funcionario WHERE id_funcionario = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id_funcionario, PDO::PARAM_INT);
        $stmt->execute();
        $funcionario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$funcionario) {
            $erro = "Funcionário não encontrado!";
            $mostrar_form_busca = true;
        } else {
            $mostrar_form_busca = false;
        }
    } catch (PDOException $e) {
        $erro = "Erro ao buscar funcionário: " . $e->getMessage();
        $mostrar_form_busca = true;
    }
}

// Processa a confirmação de exclusão
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirmar_exclusao'])) {
    $id_funcionario = $_POST['id_funcionario'];
    
    try {
        // Verifica se o funcionário existe
        $sql_verifica = "SELECT id_funcionario FROM funcionario WHERE id_funcionario = :id";
        $stmt_verifica = $pdo->prepare($sql_verifica);
        $stmt_verifica->bindParam(':id', $id_funcionario, PDO::PARAM_INT);
        $stmt_verifica->execute();
        
        if ($stmt_verifica->rowCount() > 0) {
            // Tenta excluir
            $sql_excluir = "DELETE FROM funcionario WHERE id_funcionario = :id";
            $stmt_excluir = $pdo->prepare($sql_excluir);
            $stmt_excluir->bindParam(':id', $id_funcionario, PDO::PARAM_INT);
            
            if ($stmt_excluir->execute()) {
                $sucesso = "Funcionário excluído com sucesso!";
                // Redireciona após 2 segundos
                echo "<script>
                    setTimeout(function() {
                        window.location.href = 'excluir_funcionario.php';
                    }, 2000);
                </script>";
            } else {
                $erro = "Erro ao excluir funcionário!";
            }
        } else {
            $erro = "Funcionário não encontrado!";
        }
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'foreign key constraint') !== false) {
            $erro = "Este funcionário não pode ser excluído porque está vinculado a outros registros.";
        } else {
            $erro = "Erro no banco de dados: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excluir Funcionário</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/geral.css">
    <style>
        .confirmation-box {
            background-color: var(--light);
            border-radius: 12px;
            padding: 1.5rem;
            margin: 1.5rem 0;
            box-shadow: var(--shadow-sm);
        }
        
        .confirmation-box h3 {
            color: var(--danger);
            margin-bottom: 1rem;
        }
        
        .confirmation-box ul {
            margin: 1rem 0 1.5rem 1.5rem;
        }
        
        .confirmation-box li {
            margin-bottom: 0.75rem;
            list-style-type: none;
            display: flex;
            gap: 0.75rem;
        }
        
        .confirmation-box li::before {
            content: "•";
            color: var(--primary);
        }
        
        .results-table {
            width: 100%;
            margin: 1.5rem 0;
        }
        
        .results-table th {
            background-color: var(--primary);
            color: white;
        }
        
        .action-select {
            background-color: var(--danger);
            color: white;
        }
        
        .action-select:hover {
            background-color:rgb(90, 3, 3);
        }
    </style>
</head>
<body>
    <div class="container">
        <h2><i class="fas fa-user-times"></i> Excluir Funcionário</h2>
        
        <?php if($sucesso): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $sucesso; ?>
            </div>
        <?php endif; ?>
        
        <?php if($erro): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $erro; ?>
            </div>
        <?php endif; ?>

        <?php if($mostrar_form_busca): ?>
            <!-- Formulário de busca -->
            <form action="excluir_funcionario.php" method="POST">
                <div class="form-group">
                    <label for="busca"><i class="fas fa-search"></i> Buscar Funcionário:</label>
                    <input type="text" id="busca" name="busca" required placeholder="Digite nome ou ID">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Pesquisar
                    </button>
                    <a href="principal.php" class="btn btn-back">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>
            </form>
            
            <!-- Resultados da busca -->
            <?php if(!empty($funcionarios)): ?>
                <div class="table-responsive">
                    <table class="results-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Telefone</th>
                                <th>Email</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($funcionarios as $func): ?>
                                <tr>
                                    <td><?= htmlspecialchars($func['id_funcionario']) ?></td>
                                    <td><?= htmlspecialchars($func['nome_funcionario']) ?></td>
                                    <td><?= htmlspecialchars($func['telefone']) ?></td>
                                    <td><?= htmlspecialchars($func['email']) ?></td>
                                    <td>
                                        <a href="excluir_funcionario.php?selecionar=<?= $func['id_funcionario'] ?>" class="btn action-select">
                                            <i class="fas fa-trash-alt"></i> Selecionar
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
            
        <?php elseif(isset($funcionario)): ?>
            <!-- Confirmação de exclusão -->
            <div class="confirmation-box">
                <h3><i class="fas fa-exclamation-triangle"></i> Confirmar Exclusão</h3>
                <p>Você está prestes a excluir o seguinte funcionário:</p>
                
                <ul>
                    <li><strong>ID:</strong> <?= htmlspecialchars($funcionario['id_funcionario']) ?></li>
                    <li><strong>Nome:</strong> <?= htmlspecialchars($funcionario['nome_funcionario']) ?></li>
                    <li><strong>Email:</strong> <?= htmlspecialchars($funcionario['email']) ?></li>
                    <li><strong>Telefone:</strong> <?= htmlspecialchars($funcionario['telefone']) ?></li>
                </ul>
                
                <p><strong>Atenção:</strong> Esta ação não pode ser desfeita!</p>
                
                <form action="excluir_funcionario.php" method="POST">
                    <input type="hidden" name="id_funcionario" value="<?= htmlspecialchars($funcionario['id_funcionario']) ?>">
                    
                    <div class="form-actions">
                        <a href="excluir_funcionario.php" class="btn btn-back">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" name="confirmar_exclusao" class="btn btn-danger">
                            <i class="fas fa-trash-alt"></i> Confirmar Exclusão
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>