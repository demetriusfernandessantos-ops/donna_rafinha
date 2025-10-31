<?php

            require_once "config.php";
// Captura os valores dos filtros logo no início
$dia = $_GET['dia'] ?? '';
$mes = $_GET['mes'] ?? '';
$ano = $_GET['ano'] ?? date('Y');


$condicoesDashboard = [];
if (!empty($ano)) {
    if (!empty($mes)) {
        if (!empty($dia)) {
            $condicoesDashboard[] = "DAY(v.criada_em) = $dia AND MONTH(v.criada_em) = $mes AND YEAR(v.criada_em) = $ano";
        } else {
            $condicoesDashboard[] = "MONTH(v.criada_em) = $mes AND YEAR(v.criada_em) = $ano";
        }
    } else {
        $condicoesDashboard[] = "YEAR(v.criada_em) = $ano";
    }
}
$whereDashboard = $condicoesDashboard ? "WHERE " . implode(" AND ", $condicoesDashboard) : "";

// Total de vendas
$sqlVendas = "SELECT SUM(v.total_venda) AS faturamento_total, COUNT(v.id) AS total_vendas FROM vendas v $whereDashboard";
$resVendas = mysqli_query($link, $sqlVendas);
$dadosVendas = mysqli_fetch_assoc($resVendas);
$faturamentoTotal = $dadosVendas['faturamento_total'] ?? 0;
$totalVendas = $dadosVendas['total_vendas'] ?? 0;

// Quantidade total de produtos vendidos
$sqlQtd = "
    SELECT SUM(vp.qtd_produto) AS total_produtos
    FROM venda_produto vp
    INNER JOIN vendas v ON v.id = vp.id_venda
    $whereDashboard
";
$resQtd = mysqli_query($link, $sqlQtd);
$dadosQtd = mysqli_fetch_assoc($resQtd);
$totalProdutos = $dadosQtd['total_produtos'] ?? 0;

// Valor gasto total (custo de compra das roupas)
$sqlCusto = "
    SELECT SUM(r.valor_compra * vp.qtd_produto) AS total_gasto
    FROM venda_produto vp
    INNER JOIN roupas r ON r.id = vp.id_produto
    INNER JOIN vendas v ON v.id = vp.id_venda
    $whereDashboard
";
$resCusto = mysqli_query($link, $sqlCusto);
$dadosCusto = mysqli_fetch_assoc($resCusto);
$totalGasto = $dadosCusto['total_gasto'] ?? 0;

// ---------------------------------------------
// 🔹 FAIXA DE VALOR MAIS VENDIDA (de 50 em 50)
// ---------------------------------------------

$intervalo = 50;
$faixaInicialMaisVendida = null;
$faixaFinalMaisVendida = null;
$totalMaisVendido = 0;

// Vamos percorrer as faixas de 0 até, digamos, R$ 2000
for ($i = 0; $i <= 2000; $i += $intervalo) {
    $faixaInicial = $i;
    $faixaFinal = $i + $intervalo;

    $sqlFaixa = "
        SELECT SUM(vp.qtd_produto) AS total_vendido
        FROM venda_produto vp
        INNER JOIN roupas r ON r.id = vp.id_produto
        INNER JOIN vendas v ON v.id = vp.id_venda
        " . ($whereDashboard ? $whereDashboard . " AND" : "WHERE") . "
        r.valor_venda BETWEEN $faixaInicial AND $faixaFinal
    ";

    $resFaixa = mysqli_query($link, $sqlFaixa);
    $rowFaixa = mysqli_fetch_assoc($resFaixa);

    $totalVendidoFaixa = $rowFaixa['total_vendido'] ?? 0;

    if ($totalVendidoFaixa > $totalMaisVendido) {
        $totalMaisVendido = $totalVendidoFaixa;
        $faixaInicialMaisVendida = $faixaInicial;
        $faixaFinalMaisVendida = $faixaFinal;
    }
}

// ---------------------------------------------
// 🔹 Cálculo da média dentro da faixa campeã
// ---------------------------------------------
$valorMaisVendidoMedio = 0;

if ($faixaInicialMaisVendida !== null) {
    $sqlMedia = "
        SELECT AVG(r.valor_venda) AS media_faixa
        FROM venda_produto vp
        INNER JOIN roupas r ON r.id = vp.id_produto
        INNER JOIN vendas v ON v.id = vp.id_venda
        " . ($whereDashboard ? $whereDashboard . " AND" : "WHERE") . "
        r.valor_venda BETWEEN $faixaInicialMaisVendida AND $faixaFinalMaisVendida
    ";

    $resMedia = mysqli_query($link, $sqlMedia);
    $rowMedia = mysqli_fetch_assoc($resMedia);
    $valorMaisVendidoMedio = $rowMedia['media_faixa'] ?? 0;
}

// Lucro líquido (faturamento - custo)
$lucroLiquido = $faturamentoTotal - $totalGasto;
?>


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
        .filtros {
            display: flex;
            align-items: flex-end;
            gap: 10px;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">

            <!-- Campo de busca -->
            <div style="height: 40px" class="d-flex mb-3 mt-4 clearfix">
<div class="filtros ml-3">
    <a href="index.html" class="btn btn-secondary" style="margin-right: 50px">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
    <select id="filtro-dia" class="form-select" style="width: 100px;">
        <option value="">Dia</option>
        <?php
        for ($i = 1; $i <= 31; $i++) {
            $sel = ($i == $dia) ? 'selected' : '';
            echo "<option value='$i' $sel>$i</option>";
        }
        ?>
    </select>

    <select id="filtro-mes" class="form-select" style="width: 160px;">
        <option value="">Mês</option>
        <?php
        $meses = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
        ];
        foreach ($meses as $num => $nome) {
            $sel = ($num == $mes) ? 'selected' : '';
            echo "<option value='$num' $sel>$nome</option>";
        }
        ?>
    </select>

    <select id="filtro-ano" class="form-select" style="width: 120px;">
        <?php
        for ($a = 2025; $a <= 2099; $a++) {
            $sel = ($a == $ano) ? 'selected' : '';
            echo "<option value='$a' $sel>$a</option>";
        }
        ?>
    </select>

    <button id="btn-filtrar" class="btn btn-primary">
        <i class="bi bi-funnel"></i> Filtrar
    </button>

<a href="relatorios.php?ano=<?= date('Y') ?>" class="btn btn-secondary">
    <i class="bi bi-x-circle"></i> Limpar
</a>
</div>

            </div>

<div class="alert alert-success mt-4">
    <h5 class="mb-3"><i class="bi bi-graph-up-arrow"></i> Resumo do Período</h5>
    <div class="row text-center">
        <div class="col-md-3">
            <strong>💰 Valor Gasto:</strong><br>
            R$ <?= number_format($totalGasto, 2, ',', '.') ?>
        </div>
        <div class="col-md-3">
            <strong>🧾 Faturamento:</strong><br>
            R$ <?=  number_format($faturamentoTotal, 2, ',', '.') ?>
        </div>
        <div class="col-md-3">
            <strong>✅ Lucro Líquido:</strong><br>
            R$ <?= number_format($lucroLiquido, 2, ',', '.') ?>
        </div>
        <div class="col-md-3">
            <strong>📦 Produtos Vendidos:</strong><br>
            <?= $totalProdutos ?>
        </div>
    </div>
     <?php
    // 🔹 Contagem por forma de pagamento
    $sqlPagamentos = "
        SELECT forma_pagamento, COUNT(*) AS total
        FROM vendas v
        $whereDashboard
        GROUP BY forma_pagamento
    ";
    $resPag = mysqli_query($link, $sqlPagamentos);
    $pix = $cartao = $dinheiro = $fiado = 0;
    while ($p = mysqli_fetch_assoc($resPag)) {
        switch (strtolower($p['forma_pagamento'])) {
            case 'pix':
                $pix = $p['total'];
                break;
            case 'cartão':
            case 'cartao': // para segurança, caso venha sem acento
                $cartao = $p['total'];
                break;
            case 'dinheiro':
                $dinheiro = $p['total'];
                break;
        }
    }
    ?>

    <div class="row text-center mt-2">
        <div class="col-md-3">
            <strong>💎 Valor Mais Vendido:</strong><br>
            R$ <?= number_format($valorMaisVendidoMedio, 2, ',', '.') ?> (média)
        </div>
        <div class="col-md-3">
            <strong>💳 Cartão:</strong><br>
            <?= $cartao ?> venda<?= $cartao == 1 ? '' : 's' ?>
        </div>
        <div class="col-md-3">
            <strong>💸 Dinheiro:</strong><br>
            <?= $dinheiro ?> venda<?= $dinheiro == 1 ? '' : 's' ?>
        </div>
        <div class="col-md-3">
            <strong>⚡ Pix:</strong><br>
            <?= $pix ?> venda<?= $pix == 1 ? '' : 's' ?>
        </div>
    </div>
</div>

            <?php

            $sql = "
                SELECT 
                    v.id AS id_venda,
                    v.total_venda,
                    v.forma_pagamento,
                    v.criada_em
                FROM vendas v
            ";

            // Filtro dinâmico de data
            $condicoes = [];
            if (!empty($ano)) {
                if (!empty($mes)) {
                    if (!empty($dia)) {
                        $condicoes[] = "DAY(v.criada_em) = $dia AND MONTH(v.criada_em) = $mes AND YEAR(v.criada_em) = $ano";
                    } else {
                        $condicoes[] = "MONTH(v.criada_em) = $mes AND YEAR(v.criada_em) = $ano";
                    }
                } else {
                    $condicoes[] = "YEAR(v.criada_em) = $ano";
                }
            }

            if ($condicoes) {
                $sql .= " WHERE " . implode(" AND ", $condicoes);
            }

            $sql .= " ORDER BY v.criada_em DESC";

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

// Busca de texto
$(document).ready(function () {
    $("#buscar").on('keyup', function () {
        var value = $(this).val().toLowerCase();
        $("tbody tr").filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    // Clique no botão de filtro
    $("#btn-filtrar").click(function() {
        let dia = $("#filtro-dia").val();
        let mes = $("#filtro-mes").val();
        let ano = $("#filtro-ano").val();

        // Regras de seleção
        if (dia && !mes) {
            alert("Selecione um mês quando escolher um dia.");
            return;
        }

        // Garante que apenas combinações válidas sejam aplicadas
        let url = "relatorios.php?";
        if (ano) url += "ano=" + ano;
        if (mes) url += "&mes=" + mes;
        if (dia) url += "&dia=" + dia;

        window.location.href = url;
    });
});

// Mostrar itens via AJAX
$(document).on('click', '.btn-ver-itens', function() {
    var id = $(this).data('id');
    var tr = $(this).closest('tr');

    if (tr.next().hasClass('detalhes-venda')) {
        tr.next().remove();
        return;
    }

    $('.detalhes-venda').remove();

    var novaLinha = $('<tr class="detalhes-venda"><td colspan="5" class="text-center p-3">Carregando...</td></tr>');
    tr.after(novaLinha);

    $.get('ver_itens_venda.php', { id: id }, function(data) {
        novaLinha.find('td').html(data);
    }).fail(function() {
        novaLinha.find('td').html('<div class="text-danger">Erro ao carregar itens da venda.</div>');
    });
});
</script>
</body>
</html>
