<?php
// Inclui o arquivo de configuração
require_once "config.php";

$nome = trim($_POST["busca"] ?? '');

if ($nome === '') {
    // Busca padrão: últimas roupas cadastradas (ordem decrescente por ID)
    $stmt = $link->prepare("SELECT * FROM roupas ORDER BY id DESC LIMIT 10");
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Busca por nome
    $stmt = $link->prepare("SELECT * FROM roupas WHERE nome LIKE CONCAT(?, '%')");
    $stmt->bind_param("s", $nome);
    $stmt->execute();
    $result = $stmt->get_result();
}

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id = (int)$row["id"];
        $nomeRoupa = htmlspecialchars($row["nome"]);
        $valorVenda = (float)$row["valor_venda"];
        $valorFormatado = number_format($valorVenda, 2, ',', '.');

        echo '<li class="list-group-item m-2 d-flex align-items-center justify-content-between">';

        // Conteúdo à esquerda (imagem + nome)
        echo '<div class="d-flex align-items-center">';

        if (!empty($row['imagem_base64'])) {
            echo '<img src="data:image/'. $row['extensao'] .';base64,' . $row['imagem_base64'] . '" 
                      class="img-thumb me-2" 
                      alt="Imagem da roupa" 
                      style="width: 100px; height: 150px; object-fit: cover; border-radius: 5px;">';
        } else {
            echo '<i class="bi bi-image me-2" style="font-size: 2rem; color: #ccc;"></i>';
        }

        echo '<span>' . $nomeRoupa . ' - R$ ' . $valorFormatado . '</span>';
        echo '</div>';

        // Botão à direita
        echo '<button class="btn btn-primary"
                    onclick="adicionar(\'' . $nomeRoupa . '\', ' . $id . ', \'' . $valorFormatado . '\')">
                    Adicionar
              </button>';

        echo '</li>';
    }
} else {
    echo "<div class='alert alert-danger' role='alert'>
            Nenhum resultado encontrado
          </div>";
}

$stmt->close();
$link->close();
?>
