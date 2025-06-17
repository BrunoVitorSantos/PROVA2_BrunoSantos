<?php
session_start();
require 'conexao.php';

// Verifica se o usuário tem permissão de ADM
if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] != 1) {
    echo "<script>alert('Acesso negado!'); window.location.href='principal.php';</script>";
    exit();
}

// Inicializa variáveis
$funcionario = null;
$erro = '';
$sucesso = '';

// Se o formulário for enviado, busca o funcionário pelo ID ou nome
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['busca_funcionario']) && !empty(trim($_POST['busca_funcionario']))) {
        $busca = trim($_POST['busca_funcionario']);

        try {
            // Verifica se a busca começa com número seguido de hífen (ex: "6 - João")
            if (preg_match('/^(\d+)\s*-\s*(.+)/', $busca, $matches)) {
                $id_busca = $matches[1];
                $nome_busca = trim($matches[2]);
                
                // Busca tanto por ID quanto por nome
                $sql = "SELECT * FROM funcionario WHERE id_funcionario = :id OR nome_funcionario LIKE :nome";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $id_busca, PDO::PARAM_INT);
                $nome_param = "%" . $nome_busca . "%";
                $stmt->bindParam(':nome', $nome_param, PDO::PARAM_STR);
            }
            // Verifica se a busca é apenas um número (ID)
            elseif (is_numeric($busca)) {
                $sql = "SELECT * FROM funcionario WHERE id_funcionario = :busca";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':busca', $busca, PDO::PARAM_INT);
            }
            // Busca apenas por nome
            else {
                $sql = "SELECT * FROM funcionario WHERE nome_funcionario LIKE :busca";
                $stmt = $pdo->prepare($sql);
                $nome_param = "%" . $busca . "%";
                $stmt->bindParam(':busca', $nome_param, PDO::PARAM_STR);
            }

            $stmt->execute();
            $funcionario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$funcionario) {
                $erro = "Funcionário não encontrado! Verifique o ID ou nome digitado.";
            }
        } catch (PDOException $e) {
            $erro = "Erro no banco de dados: " . $e->getMessage();
            error_log("PDOException: " . $e->getMessage());
        }
    }
    
    // Se for um submit de atualização
    if (isset($_POST['id_funcionario'])) {
        $id_funcionario = $_POST['id_funcionario'];
        $nome = trim($_POST['nome']);
        $telefone = trim($_POST['telefone']);
        $email = trim($_POST['email']);
        $endereco = trim($_POST['endereco']);
        
        try {
            $sql = "UPDATE funcionario SET 
                    nome_funcionario = :nome,
                    telefone = :telefone,
                    email = :email,
                    endereco = :endereco
                    WHERE id_funcionario = :id";
            
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':telefone', $telefone);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':endereco', $endereco);
            $stmt->bindParam(':id', $id_funcionario);
            
            if ($stmt->execute()) {
                $sucesso = "Funcionário atualizado com sucesso!";
                // Atualiza os dados do funcionário exibido
                $funcionario = [
                    'id_funcionario' => $id_funcionario,
                    'nome_funcionario' => $nome,
                    'telefone' => $telefone,
                    'email' => $email,
                    'endereco' => $endereco
                ];
            } else {
                $erro = "Erro ao atualizar funcionário!";
            }
        } catch (PDOException $e) {
            $erro = "Erro no banco de dados: " . $e->getMessage();
            error_log("PDOException (update): " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Alterar Funcionário</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/geral.css">
</head>
<body>
    <div class="container">
        <h3>Desenvolvido por: Bruno Vitor dos Santos</h3>
        <h2><i class="fas fa-user-edit"></i> Alterar Funcionário</h2>
        
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
        
        <!-- Formulário para buscar funcionário -->
        <form action="alterar_funcionario.php" method="POST" id="buscaForm">
            <div class="form-group">
                <label for="busca_funcionario"><i class="fas fa-search"></i> Digite o ID ou Nome do funcionário:</label>
                <input type="text" id="busca_funcionario" name="busca_funcionario" required 
                       value="<?php echo isset($_POST['busca_funcionario']) ? htmlspecialchars($_POST['busca_funcionario']) : ''; ?>">
                <div id="sugestoes"></div>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Buscar
            </button>
        </form>
        
        <?php if(isset($funcionario) && $funcionario): ?>
        <!-- Formulário para alterar funcionário -->
        <div class="form-section">
            <form action="alterar_funcionario.php" method="POST" id="editarForm">
                <input type="hidden" name="id_funcionario" value="<?= htmlspecialchars($funcionario['id_funcionario']) ?>">
                
                <div class="form-group">
                    <label for="nome"><i class="fas fa-user"></i> Nome:</label>
                    <input type="text" id="nome" name="nome" 
                           value="<?= htmlspecialchars($funcionario['nome_funcionario']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="telefone"><i class="fas fa-phone"></i> Telefone:</label>
                    <input type="tel" id="telefone" name="telefone" 
                           value="<?= htmlspecialchars($funcionario['telefone']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> E-mail:</label>
                    <input type="email" id="email" name="email" 
                           value="<?= htmlspecialchars($funcionario['email']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="endereco"><i class="fas fa-map-marker-alt"></i> Endereço:</label>
                    <input type="text" id="endereco" name="endereco" 
                           value="<?= htmlspecialchars($funcionario['endereco']) ?>" required>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Salvar Alterações
                </button>
            </form>
        </div>
        <?php endif; ?>
        
        <a href="principal.php" class="btn btn-back">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
    $(document).ready(function() {
        // ... (outros códigos existentes)

        // Limpa os campos após alteração bem-sucedida
        <?php if($sucesso): ?>
            // Limpa o formulário de busca
            $('#busca_funcionario').val('');
            
            // Limpa o formulário de edição
            $('#editarForm')[0].reset();
            
            // Esconde a seção de edição
            $('.form-section').hide();
            
            // Foca no campo de busca novamente
            $('#busca_funcionario').focus();
        <?php endif; ?>

        // ... (outros códigos existentes)
    });
</script>
</body>
</html>