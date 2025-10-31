<!DOCTYPE html>
<html lang="en">
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
            width: 700px;
        }
        .container-fluid {
            width: 1000px;
            margin: 0 auto;
        }
        table tr:hover{
            background-color: #d0d0d0;
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
                <div style="height: 40px" class="d-flex mt-5 mb-3 clearfix">
                    <a href="roupas.php" class="d-flex btn btn-primary pull-right"><i class="bi bi-arrow-left"></i> Voltar</a>
                    <input style="width: 600px; margin-left: 20px;" type="text" class="form-control" placeholder="buscar" id="buscar">
                </div>
                <?php
                // Include config file
                require_once "config.php";

                $id = $_GET["id"];

                // Attempt select query execution
                $sql = "select * from venda_produto vp, roupas r where vp.id_venda = $id and vp.id_produto = r.id";
                if($result = mysqli_query($link, $sql)){
                    if(mysqli_num_rows($result) > 0){
                        echo '<table class="table table-bordered table-striped">';
                            echo "<thead>";
                                echo "<tr>";
                                    echo "<th>#</th>";
                                    echo "<th id='nome'>Nome</th>";
                                    echo "<th>Quantidade</th>";
                                    echo "<th>Valor Compra</th>";
                                    echo "<th>Valor venda</th>";
                                echo "</tr>";
                            echo "</thead>";
                            echo "<tbody>";
                            while($row = mysqli_fetch_array($result)){
                                echo "<tr>";
                                    echo "<td>" . $row['id'] . "</td>";
                                    echo "<td>" . $row['nome'] . "</td>";
                                    echo "<td>" . $row['qtd'] . "</td>";
                                    echo "<td>" . $row['valor_compra'] . "</td>";
                                    echo "<td>" . $row['valor_venda'] . "</td>";
                                echo "</tr>";
                            }
                            echo "</tbody>";
                        echo "</table>";
                        // Free result set
                        mysqli_free_result($result);
                    } else{
                        echo '<div class="alert alert-danger"><em>No records were found.</em></div>';
                    }
                } else{
                    echo "Oops! Something went wrong. Please try again later.";
                }

                // Close connection
                mysqli_close($link);
                ?>
            </div>
        </div>
    </div>

    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <p>Deseja deletar a roupa ?</p>
                    <p class="modal-body-roupa"></p>
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
        $.post( "deletar_roupa.php", { id: idDeletar } ).done(function() {
            window.location.reload();
        })
    }

    function closeModal() {
        $('#myModal').modal('hide');
    }

    $(document).ready(function () {
        $("#buscar").on('keyup', function () {
            var value = $(this).val().toLowerCase();
            $("tbody tr").filter(function () {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
    });
</script>
</body>
</html>