<?php
require_once "config.php";

if($_SERVER["REQUEST_METHOD"] == "POST"){

    $id_venda = trim($_POST["id"]);

    $sql_produtos = "SELECT id_produto, qtd_produto FROM venda_produto WHERE id_venda = ?";
    $sql_delete_venda_produto = "DELETE FROM venda_produto WHERE id_venda = ?";
    $sql_delete_venda = "DELETE FROM vendas WHERE id = ?";
    $sql_update_estoque = "UPDATE roupas SET qtd = qtd + ? WHERE id = ?";

    mysqli_begin_transaction($link);

    try {
        // 1. Buscar produtos da venda
        if($stmt = mysqli_prepare($link, $sql_produtos)) {
            mysqli_stmt_bind_param($stmt, "i", $id_venda);
            if(!mysqli_stmt_execute($stmt)){
                throw new Exception("Erro ao buscar produtos da venda: ".mysqli_error($link));
            }
            $result = mysqli_stmt_get_result($stmt);
            $produtos = [];
            while($row = mysqli_fetch_assoc($result)){
                $produtos[] = ['id' => $row['id_produto'], 'qtd' => $row['qtd_vendida']];
            }
            mysqli_stmt_close($stmt);

            // 2. Atualizar estoque
            if($stmt = mysqli_prepare($link, $sql_update_estoque)) {
                foreach($produtos as $p){
                    mysqli_stmt_bind_param($stmt, "ii", $p['qtd'], $p['id']);
                    if(!mysqli_stmt_execute($stmt)){
                        throw new Exception("Erro ao atualizar estoque: ".mysqli_error($link));
                    }
                }
                mysqli_stmt_close($stmt);
            }

            // 3. Deletar itens da venda
            if($stmt = mysqli_prepare($link, $sql_delete_venda_produto)) {
                mysqli_stmt_bind_param($stmt, "i", $id_venda);
                if(!mysqli_stmt_execute($stmt)){
                    throw new Exception("Erro ao deletar venda_produto: ".mysqli_error($link));
                }
                mysqli_stmt_close($stmt);
            }

            // 4. Deletar a venda
            if($stmt = mysqli_prepare($link, $sql_delete_venda)) {
                mysqli_stmt_bind_param($stmt, "i", $id_venda);
                if(!mysqli_stmt_execute($stmt)){
                    throw new Exception("Erro ao deletar venda: ".mysqli_error($link));
                }
                mysqli_stmt_close($stmt);
            }

            mysqli_commit($link);
            echo "Venda e produtos deletados; estoque atualizado com sucesso!";
        } else {
            throw new Exception("Erro ao preparar consulta de produtos: ".mysqli_error($link));
        }
    } catch(Exception $e){
        mysqli_rollback($link);
        echo "Erro: " . $e->getMessage();
    }

    mysqli_close($link);
}
?>
