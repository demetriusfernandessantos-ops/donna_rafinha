<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="stylesheet" href="icons/bootstrap-icons.css">
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/jquery.min.js"></script>
    <script src="js/jquery.maskMoney.min.js"></script>
    <style>
        #nome {
            width: 400px;
        }
        .container-fluid {
            width: 1200px;
            margin: 0 auto;
        }
        table tr:hover {
            background-color: #d0d0d0;
        }
        .img-thumb {
            width: 150px;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #ccc;
            box-shadow: 0 1px 4px rgba(0,0,0,0.2);
        }
    </style>
    <script>
        $(document).ready(function(){
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex mt-5 mb-3">

                    <a href="index.html" class="btn btn-primary" style="margin-right: 10px;" title="Início" data-toggle="tooltip">
        <i class="bi bi-house-fill"></i>
    </a>
                
                    <a href="roupas.php" class="d-flex btn btn-primary">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                    
<form method="GET" action="roupas_listagem.php" class="d-flex" style="margin-left: 30px;">
    <input type="text" name="busca" class="form-control" style="width:600px;" placeholder="Buscar" value="<?php echo isset($_GET['busca']) ? htmlspecialchars($_GET['busca']) : ''; ?>">
    <button type="submit" class="btn btn-primary ms-2">Buscar</button>
</form>
                </div>
                <?php
                // Include config file
                require_once "config.php";

  // Pegando o termo de busca
$busca = isset($_GET['busca']) ? $_GET['busca'] : '';

// Montando a query
$sql = "SELECT * FROM roupas";

// Se houver busca, adiciona WHERE com LIKE
if(!empty($busca)){
    // mysqli_real_escape_string previne SQL Injection
    $busca_escaped = mysqli_real_escape_string($link, $busca);
    $sql .= " WHERE nome LIKE '%$busca_escaped%'";
}

$sql .= " ORDER BY id DESC";

if($result = mysqli_query($link, $sql)){
    if(mysqli_num_rows($result) > 0){
        echo '<table class="table table-bordered table-striped align-middle text-center">';
        echo "<thead class='table-primary'>";
        echo "<tr>";
        echo "<th>#Cod</th>";
        echo "<th>Imagem</th>";
        echo "<th id='nome'>Nome</th>";
        echo "<th>Qtd</th>";
        echo "<th>Valor Compra</th>";
        echo "<th>Valor Venda</th>";
        echo "<th>Ações</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        while($row = mysqli_fetch_array($result)){
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";

            if(!empty($row['imagem_base64']) && !empty($row['extensao'])) {
                echo '<td><img src="data:image/' . $row['extensao'] . ';base64,' . $row['imagem_base64'] . '" class="img-thumb" alt="Imagem"></td>';
            } else {
                echo "<td><i class='bi bi-image' style='font-size: 2rem; color: #ccc;'></i></td>";
            }

            echo "<td>" . htmlspecialchars($row['nome']) . "</td>";
            echo "<td>" . $row['qtd'] . "</td>";
            echo "<td>R$ " . number_format($row['valor_compra'], 2, ',', '.') . "</td>";
            echo "<td>R$ " . number_format($row['valor_venda'], 2, ',', '.') . "</td>";

            $nome = htmlspecialchars($row['nome']);
            echo "<td>";
            echo '<a href="editar_roupas.php?id='. $row['id'] .'" class="btn btn-primary" style="margin-right:15px" title="Editar" data-toggle="tooltip"><i class="bi bi-pencil-fill"></i></a>';
            echo '<button class="btn btn-danger" onclick="deletar(\''.$nome.'\', \''.$row['id'].'\');" title="Excluir" data-toggle="tooltip"><i class="bi bi-trash-fill"></i></button>';
            echo "</td>";
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";

        mysqli_free_result($result);
    } else {
        echo '<div class="alert alert-warning"><em>Nenhuma roupa encontrada.</em></div>';
    }
} else {
    echo "Oops! Algo deu errado. Tente novamente mais tarde.";
}

// Close connection
mysqli_close($link);
                ?>
            </div>
        </div>
    </div>

    <!-- Modal de confirmação -->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <p>Deseja deletar a roupa?</p>
                    <p class="modal-body-roupa fw-bold"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()" style="margin-right: 40px">Cancelar</button>
                    <button type="button" class="btn btn-danger" onclick="deletarRoupa()"><i class="bi bi-trash-fill"></i> Deletar</button>
                </div>
            </div>
        </div>
    </div>

<script>
    var idDeletar = '';

    function deletar(nome, id) {
        let myModal = new bootstrap.Modal(document.getElementById('myModal'), {});
        $(".modal-body-roupa").html(nome);
        idDeletar = id;
        myModal.show();
    }

    function deletarRoupa() {
        
        $.post("deletar_roupa.php", { id: idDeletar }, function(response) {
            try {

                console.log('response', response)
                if (response.status === "success") {
                    // Atualiza a página após excluir
                    window.location.reload();
                } else {
                    alert("Você não pode deletar uma roupa que já foi vendida, exclua as vendas primeiro antes de deletar!");
                    window.location.reload();
                }
            } catch (e) {
                alert("Erro inesperado ao deletar.");
            }
        });
    }

    function closeModal() {
        $('#myModal').modal('hide');
    }
</script>
</body>
</html>
<?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
  <div id="successToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body">
        <i class="bi bi-check-circle-fill"></i> Produto atualizado com sucesso!
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fechar"></button>
    </div>
  </div>
</div>
<script>
  document.addEventListener("DOMContentLoaded", function(){
      const toastEl = document.getElementById('successToast');
      const toast = new bootstrap.Toast(toastEl, { delay: 3500 });
      toast.show();
  });
</script>
<?php endif; ?>