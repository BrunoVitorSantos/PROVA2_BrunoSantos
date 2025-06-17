<?php
session_start();
require_once 'conexao.php';

// Verifica se o usuário tem permissão (perfis 1, 2 ou 4 podem cadastrar funcionários)
$perfis_permitidos = [1, 2, 4];
if (!isset($_SESSION['perfil']) || !in_array($_SESSION['perfil'], $perfis_permitidos)) {
    echo "<script>alert('Acesso negado!'); window.location.href='principal.php';</script>";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obter dados do formulário
    $nome_funcionario = trim($_POST['nome_funcionario']);
    $email = trim($_POST['email']);
    $telefone = trim($_POST['telefone']);
    $endereco = trim($_POST['endereco']);
    
    // Validação básica
    if (empty($nome_funcionario)) {
        $erro = "O nome do funcionário é obrigatório!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "E-mail inválido!";
    } else {
        try {
            $sql = "INSERT INTO funcionario (nome_funcionario, email, telefone, endereco) 
                    VALUES (:nome_funcionario, :email, :telefone, :endereco)";
            
            $stmt = $pdo->prepare($sql);
            
            $stmt->bindParam(':nome_funcionario', $nome_funcionario);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':telefone', $telefone);
            $stmt->bindParam(':endereco', $endereco);

            if ($stmt->execute()) {
                $sucesso = "Funcionário cadastrado com sucesso!";
                // Limpa os campos do formulário após cadastro bem-sucedido
                $nome_funcionario = $email = $telefone = $endereco = '';
            } else {
                $erro = "Erro ao cadastrar funcionário!";
            }
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $erro = "Este e-mail já está cadastrado no sistema!";
            } else {
                $erro = "Erro no banco de dados: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Funcionário</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/geral.css">
</head>
<body>
    <div class="container">
        <h3>Desenvolvido por: Bruno Vitor dos Santos</h3>
        <h2><i class="fas fa-user-plus"></i> Cadastrar Funcionário</h2>
        
        <?php if(isset($sucesso)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $sucesso; ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($erro)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $erro; ?>
            </div>
        <?php endif; ?>
        
        <form action="cadastro_funcionario.php" method="POST" class="form-cliente">
            <div class="form-group full-width">
                <label for="nome_funcionario"><i class="fas fa-user"></i> Nome Completo:</label>
                <input type="text" id="nome_funcionario" name="nome_funcionario" required 
                       value="<?php echo isset($nome_funcionario) ? htmlspecialchars($nome_funcionario) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="telefone"><i class="fas fa-phone"></i> Telefone:</label>
                <input type="tel" id="telefone" name="telefone" required 
                       placeholder="(00) 00000-0000" 
                       value="<?php echo isset($telefone) ? htmlspecialchars($telefone) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> E-mail:</label>
                <input type="email" id="email" name="email" required 
                       value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
            </div>
            
            <div class="form-group full-width">
                <label for="endereco"><i class="fas fa-map-marker-alt"></i> Endereço:</label>
                <input type="text" id="endereco" name="endereco" required 
                       value="<?php echo isset($endereco) ? htmlspecialchars($endereco) : ''; ?>">
            </div>

            <div class="form-actions">
                <a href="principal.php" class="btn btn-back">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
                <button type="reset" class="btn btn-secondary">
                    <i class="fas fa-broom"></i> Limpar
                </button>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Salvar
                </button>
            </div>
        </form>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script>
        $(document).ready(function(){
            $('#telefone').mask('(00) 00000-0000');
            
            // Foco automático no primeiro campo
            $('#nome_funcionario').focus();
            
            // Validação básica do formulário
            $('form').on('submit', function(e) {
                let isValid = true;
                
                // Verifica se todos os campos obrigatórios estão preenchidos
                $(this).find('[required]').each(function() {
                    if (!$(this).val().trim()) {
                        $(this).css('border-color', 'var(--danger-color)');
                        isValid = false;
                    } else {
                        $(this).css('border-color', '');
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    alert('Por favor, preencha todos os campos obrigatórios!');
                }
            });
        });
    </script>
</body>
</html>