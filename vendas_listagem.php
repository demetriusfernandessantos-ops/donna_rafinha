<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Listagem de Vendas</title>
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="stylesheet" href="icons/bootstrap-icons.css">
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/jquery.min.js"></script>
    <style>
        table {
            width: 100%;
        }
        .container-fluid {
            width: 900px;
            margin: 0 auto;
        }
        table tr:hover {
            background-color: #d0d0d0;
        }
.detalhes-venda td {
    background-color: #f8f9fa;
    border-top: none;
}
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div style="height: 40px" class="d-flex mt-5 mb-3 clearfix">
                <a href="index.html" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
                <input style="width: 600px; margin-left: 20px;" type="text"
                       class="form-control" placeholder="Buscar venda..." id="buscar">
            </div>

            <?php
            require_once "config.php";

            // Corrigindo SQL: pegamos uma linha por venda
            $sql = "
                SELECT 
                    v.id AS id_venda,
                    v.total_venda,
                    v.forma_pagamento,
                    v.criada_em
                FROM vendas v
                ORDER BY v.criada_em DESC
            ";

            if ($result = mysqli_query($link, $sql)) {
                if (mysqli_num_rows($result) > 0) {
                    echo '<table class="table table-bordered table-striped">';
                    echo '<thead>';
                    echo '<tr>';
                    echo '<th>#Cod</th>';
                    echo '<th>Valor da Venda</th>';
                    echo '<th>Forma de Pagamento</th>';
                    echo '<th>Data</th>';
                    echo '<th style="width: 160px;">Ações</th>';
                    echo '</tr>';
                    echo '</thead>';
                    echo '<tbody>';

                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<tr>';
                        echo '<td>' . $row['id_venda'] . '</td>';
                        echo '<td>R$ ' . number_format($row['total_venda'], 2, ',', '.') . '</td>';
                        echo '<td>' . htmlspecialchars($row['forma_pagamento']) . '</td>';
                        echo '<td>' . date('d/m/Y H:i', strtotime($row['criada_em'])) . '</td>';
                        echo '<td class="d-flex justify-content-around">';
                        echo '<button class="btn btn-primary btn-ver-itens" data-id="' . $row['id_venda'] . '" title="Ver Itens"><i class="bi bi-eye-fill"></i></button>';
                        echo '<button class="btn btn-danger" onclick="deletar(\'' . $row['id_venda'] . '\')" title="Deletar"><i class="bi bi-trash-fill"></i></button>';
                        echo '</td>';
                        echo '</tr>';
                    }

                    echo '</tbody>';
                    echo '</table>';
                    mysqli_free_result($result);
                } else {
                    echo '<div class="alert alert-warning text-center"><em>Nenhuma venda encontrada.</em></div>';
                }
            } else {
                echo '<div class="alert alert-danger text-center">Erro ao carregar vendas. Tente novamente mais tarde.</div>';
            }

            mysqli_close($link);
            ?>
        </div>
    </div>
</div>

<!-- Modal de confirmação -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body text-center">
                <p>Deseja realmente deletar a venda de ID <strong class="modal-body-roupa"></strong>?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="deletarVenda()">
                    <i class="bi bi-trash-fill"></i> Deletar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    var idDeletar = '';

    function deletar(id) {
        let myModal = new bootstrap.Modal(document.getElementById('myModal'), {});
        $(".modal-body-roupa").text(id);
        idDeletar = id;
        myModal.show();
    }

    function deletarVenda() {
        $.post("deletar_venda.php", { id: idDeletar })
            .done(function () {
                window.location.reload();
            })
            .fail(function () {
                alert("Erro ao deletar venda.");
            });
    }

    function closeModal() {
        $('#myModal').modal('hide');
    }

    $(document).ready(function () {
        $("#buscar").on('keyup', function () {
            var value = $(this).val().toLowerCase();
            $("tbody tr").filter(function () {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });
    });

    $(document).on('click', '.btn-ver-itens', function() {
    var id = $(this).data('id');
    var tr = $(this).closest('tr');

    // Se já existe uma linha de detalhes abaixo, remove (colapsa)
    if (tr.next().hasClass('detalhes-venda')) {
        tr.next().remove();
        return;
    }

    // Remove outras linhas abertas
    $('.detalhes-venda').remove();

    // Cria a nova linha de detalhe
    var novaLinha = $('<tr class="detalhes-venda"><td colspan="5" class="text-center p-3">Carregando...</td></tr>');
    tr.after(novaLinha);

    // Carrega via AJAX os itens da venda
    $.get('ver_itens_venda.php', { id: id }, function(data) {
        novaLinha.find('td').html(data);
    }).fail(function() {
        novaLinha.find('td').html('<div class="text-danger">Erro ao carregar itens da venda.</div>');
    });
});
</script>
</body>
</html>
