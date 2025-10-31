<?php
require_once "config.php";

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "
        SELECT r.nome, vp.qtd_produto, r.valor_compra, r.valor_venda
        FROM venda_produto vp
        JOIN roupas r ON vp.id_produto = r.id
        WHERE vp.id_venda = $id
    ";

    $result = mysqli_query($link, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        echo '<table class="table table-sm mb-0">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Nome</th>';
        echo '<th>Quantidade</th>';
        echo '<th>Valor Compra</th>';
        echo '<th>Valor Venda</th>';
        echo '</tr>';
        echo '</thead><tbody>';
        while ($row = mysqli_fetch_assoc($result)) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['nome']) . '</td>';
            echo '<td>' . $row['qtd_produto'] . '</td>';
            echo '<td>R$ ' . number_format($row['valor_compra'], 2, ',', '.') . '</td>';
            echo '<td>R$ ' . number_format($row['valor_venda'], 2, ',', '.') . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<div class="text-center text-muted p-2">Nenhum item encontrado.</div>';
    }

    mysqli_close($link);
}
?>
