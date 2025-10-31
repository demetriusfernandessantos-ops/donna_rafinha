<?php
// Include config file
require_once "config.php";

// Define variables
$nome = $valor_compra = $valor_venda = $qtd = $imagem_base64 = $extensao = "";
$mensagem_sucesso = false; // nova variável

// Process form when submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    $nome = trim($_POST["nome"]);
    $qtd = trim($_POST["qtd"]);
    $valor_compra = trim($_POST["valor_compra"]);
    $valor_compra = str_replace(['R$', ' '], '', $valor_compra);
    $valor_compra = str_replace(',', '.', $valor_compra);
    $valor_venda = trim($_POST["valor_venda"]);
    $valor_venda = str_replace(['R$', ' '], '', $valor_venda);
    $valor_venda = str_replace(',', '.', $valor_venda);

    // Se uma imagem for enviada, converte para Base64 e pega a extensão
    if(isset($_FILES["imagem"]) && $_FILES["imagem"]["error"] == 0) {
        $imagem_temp = $_FILES["imagem"]["tmp_name"];
        $imagem_base64 = base64_encode(file_get_contents($imagem_temp));
        
        // Obtém a extensão do arquivo sem o ponto
        $nome_arquivo = $_FILES["imagem"]["name"];
        $extensao = pathinfo($nome_arquivo, PATHINFO_EXTENSION);
    } else {
        $imagem_base64 = null; // imagem não obrigatória
        $extensao = null;
    }

    // Prepare SQL
    $sql = "INSERT INTO roupas (nome, qtd, valor_compra, valor_venda, imagem_base64, extensao) VALUES (?, ?, ?, ?, ?, ?)";

    if($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "siddss", $nome, $qtd, $valor_compra, $valor_venda, $imagem_base64, $extensao);

        if (mysqli_stmt_execute($stmt)) {
            $mensagem_sucesso = true; // exibe toast de sucesso
        } else {
            echo "Alguma coisa deu errado !!";
        }
    }

    mysqli_close($link);
}
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="stylesheet" href="icons/bootstrap-icons.css">
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/jquery.min.js"></script>
    <script src="js/jquery.maskMoney.min.js"></script>
    <title>Donna Rafinha</title>
    <style>
        body {
            background-color: #ddd;
        }
        #corpo {
            height: 100vh;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }
        #botoes {
            margin: 90px;
            position: absolute;
        }
        form {
            background-color: #fff;
            padding: 40px;
        }
        .form-group {
            display: flex;
            width: 800px;
            align-items: center;
            margin-bottom: 15px;
        }
        .pequeno_campo {
            width: 100px;
        }
        label {
            width: 105px;
        }
        #imagem-preview {
            margin-left: 10px;
            max-width: 250px;
            max-height: 550px;
            display: none;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        /* Toast fixo no topo direito */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 2000;
        }
    </style>
</head>
<body>

<!-- TOAST DE SUCESSO -->
<div class="toast-container">
    <div id="toastSucesso" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                Produto cadastrado com sucesso!
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<div id="botoes">
    <a href="index.html" type="button" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
    <a href="roupas_listagem.php" style="margin-left: 26px;" type="button" class="btn btn-primary">
        <i class="bi bi-card-list"></i> Gerenciar Produtos
    </a>
</div>

<div id="corpo">
    <form id="form-roupas" action="roupas.php" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="nome">Nome:</label>
            <input type="text" required autocomplete="off" class="form-control" id="nome" name="nome">
        </div>

        <div class="form-group">
            <label for="qtd">Quantidade:</label>
            <select required class="form-control pequeno_campo" id="qtd" name="qtd">
                <option value="">Selecione...</option>
                <?php
                for ($i = 0; $i <= 100; $i++) {
                    echo "<option value=\"$i\">$i</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label for="valor_compra">Valor compra:</label>
            <input type="text" required class="form-control pequeno_campo" id="valor_compra" name="valor_compra">
        </div>

        <div class="form-group">
            <label for="valor_venda">Valor venda:</label>
            <input type="text" class="form-control pequeno_campo" id="valor_venda" name="valor_venda">
        </div>

        <div class="form-group">
            <label for="imagem"><i class="bi bi-camera"></i> Imagem:</label>
            <input type="file" class="form-control pequeno_campo" id="imagem" name="imagem" accept="image/*" style="display:none;">
            <button type="button" id="btnImagem" class="btn btn-primary">
                <i class="bi bi-image"></i> Selecionar imagem
            </button>
            <img id="imagem-preview" src="#" alt="Prévia da imagem">
        </div>

        <button style="float: right" type="submit" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Cadastrar
        </button>
    </form>
</div>

<script>
    $(function(){
        $('#valor_compra, #valor_venda').maskMoney({
            prefix:'R$ ',
            allowNegative: true,
            thousands:'.', decimal:',',
            affixesStay: true
        });

        $('#btnImagem').click(function(){
            $('#imagem').click();
        });

        $('#imagem').change(function(){
            const file = this.files[0];
            if (file) {
                let reader = new FileReader();
                reader.onload = function(e){
                    $('#imagem-preview').attr('src', e.target.result).show();
                }
                reader.readAsDataURL(file);
            } else {
                $('#imagem-preview').hide();
            }
        });

        <?php if($mensagem_sucesso): ?>
            // Mostra toast e reseta o formulário
            const toastEl = new bootstrap.Toast(document.getElementById('toastSucesso'));
            toastEl.show();

            $('#form-roupas')[0].reset();
            $('#imagem-preview').hide();
        <?php endif; ?>
    });
</script>
</body>
</html>
