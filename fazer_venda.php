<?php
require_once "config.php";
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Sanitiza entradas
    $forma_pag = trim($_POST["forma_pag"]);
    $idProdutos = isset($_POST['idProdutos']) ? $_POST['idProdutos'] : [];
    $idProdutos = array_map('intval', $idProdutos); // garante inteiros

    $valor_venda = trim($_POST["valor_venda"]);
    $valor_venda = preg_replace('/[^\d,.-]/', '', $valor_venda); // remove R$, espaços
    $valor_venda = str_replace(',', '.', $valor_venda);
    $valor_venda = floatval($valor_venda);

    $qtd_produto = 1;

    // Insere a venda principal
    $sqlVenda = "INSERT INTO vendas (forma_pagamento, total_venda) VALUES (?, ?)";
    if ($stmtVenda = mysqli_prepare($link, $sqlVenda)) {
        mysqli_stmt_bind_param($stmtVenda, "sd", $forma_pag, $valor_venda);
        if (mysqli_stmt_execute($stmtVenda)) {

            $ultima_venda = $link->insert_id;

            // Prepara consultas auxiliares
            $sqlInsertVP = "INSERT INTO venda_produto (id_venda, id_produto, qtd_produto) VALUES (?, ?, ?)";
            $sqlSelect = "SELECT qtd, vendido FROM roupas WHERE id = ?";
            $sqlUpdate = "UPDATE roupas SET qtd = ?, vendido = ? WHERE id = ?";

            $stmtVP = mysqli_prepare($link, $sqlInsertVP);
            $stmtSel = mysqli_prepare($link, $sqlSelect);
            $stmtUpd = mysqli_prepare($link, $sqlUpdate);

            foreach ($idProdutos as $idProduto) {

                // Insere relação na tabela venda_produto
                mysqli_stmt_bind_param($stmtVP, "iii", $ultima_venda, $idProduto, $qtd_produto);
                mysqli_stmt_execute($stmtVP);

                // Busca estoque atual e vendidos
                mysqli_stmt_bind_param($stmtSel, "i", $idProduto);
                mysqli_stmt_execute($stmtSel);
                $result = mysqli_stmt_get_result($stmtSel);
                $row = mysqli_fetch_assoc($result);

                if ($row) {
                    $nova_qtd = max(0, $row['qtd'] - $qtd_produto);
                    $novo_vendido = $row['vendido'] + $qtd_produto;

                    // Atualiza estoque e vendidos
                    mysqli_stmt_bind_param($stmtUpd, "iii", $nova_qtd, $novo_vendido, $idProduto);
                    mysqli_stmt_execute($stmtUpd);
                }
            }

            // Fecha os statements
            mysqli_stmt_close($stmtVP);
            mysqli_stmt_close($stmtSel);
            mysqli_stmt_close($stmtUpd);
            mysqli_stmt_close($stmtVenda);

            mysqli_close($link);

            // Redireciona para listagem
            header("Location: vendas_listagem.php");
            exit;

        } else {
            echo "Erro ao registrar venda.";
        }
    } else {
        echo "Erro ao preparar statement da venda.";
    }

}
?>
