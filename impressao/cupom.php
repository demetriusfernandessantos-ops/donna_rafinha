<?php
$dados = [
    'dataAtual' => $_POST['dataAtual'] ?? '',
    'forma_pag' => $_POST['forma_pag'] ?? '',
    'valor_venda' => $_POST['valor_venda'] ?? '',
    'produtos' => json_decode($_POST['produtos'] ?? '[]', true)
];

$html = '<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Cupom Fiscal - Donna Rafinha</title>
<style>
    html, body {
        width: 183px;
        height: auto;
        font-family: monospace;
        font-weight: bold;
        font-size: 10px;
        margin: 0 auto;
        padding: 0;
        background: #fff;
    }
    body {
        display: flex;
        flex-direction: column;
        height: auto !important;
    }
    .cabecalho {
        text-align: center;
        margin-bottom: 5px;
    }
    .cabecalho img {
        display: block;
        margin: 0 auto 5px;
    }
    .data {
        text-align: left;
        margin-bottom: 4px;
    }
    h2 {
        font-size: 18px;
        margin: 0;
    }
    .forma {
        margin: 5px 0 10px 0;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 5px;
    }
    td {
        padding: 2px 0;
    }
    td.nome {
        max-width: 200px;
        word-wrap: break-word;
        white-space: normal;
        text-align: left;
    }
    td.valor {
        width: 60px;
        text-align: right;
        white-space: nowrap;
    }
    .linha {
        border-bottom: 2px dashed #000;
        margin: 5px 0;
    }
    .total {
        border-top: 1px dashed #000;
        margin-top: 4px;
        padding-top: 2px;
        text-align: right;
    }
</style>
</head>
<body>
    <div class="cabecalho">
        <img src="../imagens/logo_preta.png" width=100 height=100 alt="Logo">
        <h2>Donna Rafinha</h2>
    </div>
    <div class="linha"></div>
    <div>Data: ' . htmlspecialchars($dados['dataAtual']) . '</div>
    <div>Forma de pagamento: ' . htmlspecialchars($dados['forma_pag']) . '</div>
    <div>Troco:</div>
    <div class="linha"></div>

    <table>
        <tbody>';

foreach ($dados['produtos'] as $p) {
    $nome = substr($p['nome'], 0, 22);
    $valor = number_format($p['valor'], 2, ',', '.');
    $html .= '<tr>
                <td class="nome">' . htmlspecialchars($nome) . '</td>
                <td class="valor">' . $valor . '</td>
              </tr>';
}

$html .= '
        </tbody>
    </table>
    <div class="linha"></div>
    <div class="total">TOTAL: ' . htmlspecialchars($dados['valor_venda']) . '</div>
    <div class="linha"></div>
    <div class="cabecalho">Obrigado pela preferência!</div>
</body>
</html>';

file_put_contents(__DIR__ . '/cupom.html', $html);
echo 'ok';
