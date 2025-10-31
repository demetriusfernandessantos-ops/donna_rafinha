<?php
require_once "config.php";

$nome = $qtd = $valor_compra = $valor_venda = $imagem_base64 = $extensao = "";
$id = 0;

// Quando o formulário for enviado
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id"])) {
    $id = $_POST["id"];
    $nome = trim($_POST["nome"]);
    $qtd = trim($_POST["qtd"]);
    $valor_compra = str_replace(['R$', ' '], '', $_POST["valor_compra"]);
    $valor_compra = str_replace(',', '.', $valor_compra);
    $valor_venda = str_replace(['R$', ' '], '', $_POST["valor_venda"]);
    $valor_venda = str_replace(',', '.', $valor_venda);

    // Verifica se uma nova imagem foi enviada
    if (isset($_FILES["imagem"]) && $_FILES["imagem"]["error"] == 0) {
        $imagem_temp = $_FILES["imagem"]["tmp_name"];
        $imagem_base64 = base64_encode(file_get_contents($imagem_temp));

        $sql = "UPDATE roupas SET nome=?, qtd=?, valor_compra=?, valor_venda=?, imagem_base64=? WHERE id=?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "siddsi", $nome, $qtd, $valor_compra, $valor_venda, $imagem_base64, $id);
        }
    } else {
        // Se não houver nova imagem, mantém a anterior
        $sql = "UPDATE roupas SET nome=?, qtd=?, valor_compra=?, valor_venda=? WHERE id=?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "siddi", $nome, $qtd, $valor_compra, $valor_venda, $id);
        }
    }

    if ($stmt && mysqli_stmt_execute($stmt)) {
        header("location: roupas_listagem.php?success=1");
        exit();
    } else {
        echo "Erro ao atualizar o registro.";
    }

    mysqli_close($link);
} else {
    // Carrega os dados existentes
    if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
        $id = trim($_GET["id"]);
        $sql = "SELECT * FROM roupas WHERE id = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $id);
            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                if ($row = mysqli_fetch_assoc($result)) {
                    $nome = $row["nome"];
                    $qtd = $row["qtd"];
                    $valor_compra = $row["valor_compra"];
                    $valor_venda = $row["valor_venda"];
                    $imagem_base64 = $row["imagem_base64"];
                    $extensao = $row["extensao"];
                }
            }
        }
    } else {
        header("location: error.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Roupa</title>
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="stylesheet" href="icons/bootstrap-icons.css">
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/jquery.min.js"></script>
    <script src="js/jquery.maskMoney.min.js"></script>
    <style>
        body { background-color: #ddd; }
        #corpo {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
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
        .pequeno_campo { width: 100px; }
        label { width: 105px; }
        #imagem-preview {
            margin-left: 15px;
            max-width: 250px;
            max-height: 250px;
            border: 2px solid #ccc;
            border-radius: 10px;
            object-fit: cover;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
        }
    </style>
        <script>
        $(document).ready(function(){
            $('[data-toggle="tooltip"]').tooltip({
                   placement: 'bottom'
            });
        });
    </script>
</head>
<body>
<div id="botoes" style="margin: 40px; position: absolute;">
    <a href="index.html" class="btn btn-primary" style="margin-right: 10px;" title="Início" data-toggle="tooltip">
        <i class="bi bi-house-fill"></i>
    </a>
    <a href="roupas_listagem.php" type="button" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
</div>

<div id="corpo">
    <form action="editar_roupas.php" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="nome">Nome:</label>
            <input type="text" required class="form-control" value="<?php echo htmlspecialchars($nome); ?>" id="nome" name="nome">
        </div>
<div class="form-group">
    <label for="qtd">Quantidade:</label>
    <select required class="form-control pequeno_campo" id="qtd" name="qtd">
        <option value="">Selecione...</option>
        <?php
        for ($i = 0; $i <= 100; $i++) {
            $selected = ($i == $qtd) ? "selected" : "";
            echo "<option value=\"$i\" $selected>$i</option>";
        }
        ?>
    </select>
</div>
        <div class="form-group">
            <label for="valor_compra">Valor compra:</label>
            <input type="text" required class="form-control pequeno_campo"
                   value="R$ <?php echo str_replace('.', ',', $valor_compra); ?>" id="valor_compra" name="valor_compra">
        </div>
        <div class="form-group">
            <label for="valor_venda">Valor venda:</label>
            <input type="text" class="form-control pequeno_campo"
                   value="R$ <?php echo str_replace('.', ',', $valor_venda); ?>" id="valor_venda" name="valor_venda">
        </div>

        <!-- Campo de imagem -->
        <div class="form-group">
            <label for="imagem"><i class="bi bi-camera"></i> Imagem:</label>
            <input type="file" class="form-control pequeno_campo" id="imagem" name="imagem" accept="image/*" style="display:none;">
            <button type="button" id="btnImagem" class="btn btn-primary">
                <i class="bi bi-camera"></i> Selecionar imagem
            </button>
            <?php if (!empty($imagem_base64)): ?>
                <img id="imagem-preview" src="data:image/<?php echo $extensao; ?>;base64,<?php echo $imagem_base64; ?>" alt="Imagem atual">
            <?php else: ?>
                <img id="imagem-preview" src="#" alt="Prévia" style="display:none;">
            <?php endif; ?>
        </div>

        <input type="hidden" name="id" value="<?php echo $id; ?>"/>

        <button style="float: right" type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg"></i> Atualizar
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
        }
    });
});
</script>
</body>
</html>
