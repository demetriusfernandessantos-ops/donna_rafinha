<?php
header('Content-Type: application/json; charset=utf-8');

require_once "config.php";

if (!isset($_POST["id"]) || empty($_POST["id"])) {
    echo json_encode(["status" => "error", "message" => "ID não informado."]);
    exit;
}

$id = (int) $_POST["id"];

$sql = "DELETE FROM roupas WHERE id = ?";
$stmt = mysqli_prepare($link, $sql);

if (!$stmt) {
    echo json_encode(["status" => "error", "message" => "Erro ao preparar statement."]);
    exit;
}

mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Roupa não encontrada."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => mysqli_error($link)]);
}

mysqli_stmt_close($stmt);
mysqli_close($link);
?>
