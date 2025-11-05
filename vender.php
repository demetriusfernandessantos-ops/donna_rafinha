<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="stylesheet" href="icons/bootstrap-icons.css">
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/jquery.min.js"></script>
    <script src="js/jquery.maskMoney.min.js"></script>
    <title>Donna Rafinha</title>
    <style>
        body {
            background-color: #ddd;
        }
        #corpo {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        #busca {
            padding: 20px;
            height: 700px;
            width: 800px;
            background-color: #fff;
            margin-right: 30px;
            overflow-y: scroll;
        }
        #vender {
            width: 400px;
            height: 700px;
            background-color: #ffffff;
        }
        #botoes {
            margin: 40px;
            position: absolute;
        }
        .list-group li:hover {
            background-color: #dddddd;
        }
        #vender {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-direction: column;
        }
        .btn-vender {
            height: 40px;
        }
        .select-wrap {
            border: 1px solid #777;
            border-radius: 4px;
            margin-bottom: 10px;
            padding: 0 5px 5px;
            width:200px;
            background-color:#ebebeb;
        }
        .select-wrap label {
            font-size:10px;
            text-transform: uppercase;
            color: #777;
            padding: 2px 8px 0;
        }
        select {
            background-color: #ebebeb;
            border:0px;
        }
        #produtos {
            width: 400px;
            height: 600px;
            overflow-y: scroll;
        }
            .produto-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 6px 10px;
    border-radius: 4px;
    background-color: #f8f9fa;
    overflow: hidden;
}

.produto-nome {
    flex: 1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-right: 10px;
    font-weight: 500;
}

.produto-valor {
    min-width: 80px;
    text-align: right;
    font-weight: bold;
    color: #2c3e50;
}

.produto-remove {
    margin-left: 10px;
    font-size: 12px;
    padding: 2px 6px;
}
.imagem{
    width: 150px;
    height: 200px;
}
    </style>
</head>
<body>
<div id="botoes">
    <a href="index.html" class="btn btn-secondary mr-2">
        <i class="bi bi-arrow-left"></i>
        Voltar
    </a> 
    <a href="vendas_listagem.php" class="btn btn-primary ml-2" style="margin-left:10px">
        <i class="bi bi-arrow-left"></i>
        Gerenciar vendas
    </a>
</div>

<div id="corpo">
    <div id="busca">
        <div class="form-group d-flex align-items-center">
            <label for="buscar">Buscar:</label>
            <input type="text" autocomplete="off" class="form-control" id="buscar" name="buscar">
        </div>
        <ul id="resultados" class="list-group mt-4">
        </ul>
    </div>

    <div id="vender">
        <div id="produtos">
            <ul class="list-group mt-4"></ul>
        </div>
        <div>
            <div class="d-flex">
                <div class="form-group d-flex align-items-center d-flex flex-column" style="margin-right: 20px; width:200px;">
                    <input type="text" autocomplete="off" placeholder="0,00" class="form-control" id="valor_venda" name="valor_venda">
                    <select class="form-select" id="forma_pag" aria-label="Default select example">
                        <option selected>Forma de pagamento</option>
                        <option>Dinheiro</option>
                        <option>Pix</option>
                        <option>Fiado</option>
                        <option>Cartão</option>
                    </select>
                    <input type="text" id="valor_troco" class="form-control mt-2" placeholder="Valor do troco"
                            style="display:none" autocomplete="off">
                </div>

                <button class="btn btn-primary btn-vender" onclick="fazerVenda()">
                    <i class="bi bi-currency-dollar"></i>
                    Vender
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    var delayTimer;
    var produtos = []; // cada produto = {id, nome, valor}

    function doSearch(text) {
        clearTimeout(delayTimer);
        delayTimer = setTimeout(function() {
            if (text) {
                $.post("buscar.php", { busca: text }).done(function(result) {
                    $('#resultados').html(result);
                })
            }
        }, 100);
    }

    $(document).ready(function() {
        $.post("buscar.php", { busca: '' }).done(function(result) {
            $('#resultados').html(result);
        });
    });

    $("#buscar").keyup(function(e) {
        doSearch(e.target.value);
    });

    function parseValor(valor) {
        if (!valor) return 0;
        valor = valor.toString().replace(/[^\d,.-]/g, '').replace(',', '.');
        var n = parseFloat(valor);
        return isNaN(n) ? 0 : n;
    }

    function adicionar(nome, id, valor) {
        var valorNum = parseValor(valor);
        produtos.push({ id, nome, valor: valorNum });

        $('#produtos ul').prepend(`
            <li class="list-group-item produto-item">
                <span class="produto-nome" title="${nome}">${nome}</span>
                <span class="produto-valor">R$ ${valorNum.toFixed(2).replace('.', ',')}</span>
                <button class="btn btn-danger btn-sm produto-remove" onclick="remover(this.parentNode, ${id})">X</button>
            </li>
        `);

        atualizarTotal();
    }

    function remover(elemento, id) {
        produtos = produtos.filter(p => p.id != id);
        $(elemento).remove();
        atualizarTotal();
    }

    function atualizarTotal() {
        var total = produtos.reduce((soma, p) => soma + (parseFloat(p.valor) || 0), 0);
        $('#valor_venda').val('R$ ' + total.toFixed(2).replace('.', ','));
    }

    function fazerVenda() {
        if (produtos.length === 0) {
            alert('É necessário adicionar produtos para concluir !!!');
            return;
        }

        var valor_venda = $('#valor_venda').val();
        var forma_pag = $('#forma_pag').val();
        var valor_troco = $('#valor_troco').val();
        

        if (!valor_venda) {
            $('#valor_venda').addClass("is-invalid");
            return;
        } else {
            $('#valor_venda').removeClass("is-invalid");
        }

        if (forma_pag === 'Forma de pagamento') {
            $('#forma_pag').addClass("is-invalid");
            return;
        } else {
            $('#forma_pag').removeClass("is-invalid");
        }

        // Exibe o modal
        var modal = new bootstrap.Modal(document.getElementById('modalVenda'), {
            backdrop: 'static',
            keyboard: false
        });
        modal.show();

        // Botão imprimir
        $('#btnImprimir').off('click').on('click', function() {
            imprimirVenda(valor_venda, forma_pag, valor_troco);
        });

        // Botão finalizar
        $('#btnFinalizar').off('click').on('click', function() {
            finalizarVenda(valor_venda, forma_pag);
        });
    }

    function finalizarVenda(valor_venda, forma_pag) {
        var idProdutos = produtos.map(p => p.id);
        $.post("fazer_venda.php", { valor_venda, forma_pag, idProdutos }).done(function(result) {
            window.location.href = "vendas_listagem.php";
        });
    }

    $(function(){
        $('#valor_venda, #valor_troco').maskMoney({
            prefix:'R$ ',
            allowNegative: true,
            thousands:'.',
            decimal:',',
            affixesStay: true
        });
    });

$('#forma_pag').on('change', function() {
    if ($(this).val() === 'Dinheiro') {
        $('#valor_troco').show();
    } else {
        $('#valor_troco').hide().val('');
    }
});
</script>
<!-- Modal de confirmação -->
<div class="modal fade" id="modalVenda" tabindex="-1" aria-labelledby="modalVendaLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalVendaLabel">Venda concluída</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body text-center">
        <p>O que deseja fazer agora?</p>
      </div>
      <div class="modal-footer d-flex justify-content-around">
        <button type="button" class="btn btn-success" id="btnFinalizar">
          <i class="bi bi-check-circle"></i> Finalizar
        </button>
        <button type="button" class="btn btn-primary" id="btnImprimir">
          <i class="bi bi-printer"></i> Imprimir
        </button>
      </div>
    </div>
  </div>
</div>
<script src="./js/qz-tray.js"></script>
<script>

    // Função que conecta o QZ Tray
    function conectarQZ() {
        if (!qz.websocket.isActive()) {
            return qz.websocket.connect().catch(err => {
                alert("Erro ao conectar com QZ Tray: " + err);
            });
        }
        return Promise.resolve();
    }

    async function imprimirVenda(valor_venda, forma_pag, valor_troco) {
        const dataAtual = new Date().toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'short' });
        const conteudo = `
         Donna Rafinha
--------------------------------
Data: ${dataAtual}
Forma de pagamento: ${forma_pag}
--------------------------------
${produtos.map(p => `${p.nome.substring(0, 22).padEnd(23, ' ')} R$${p.valor.toString()}`).join('\n')}
--------------------------------
TOTAL: ${valor_venda}
--------------------------------
Obrigado pela preferência!

`;


    const dadosVenda = {
        dataAtual,
        forma_pag,
        valor_venda,
        valor_troco,
        produtos
    };
    localStorage.setItem("vendaAtual", JSON.stringify(dadosVenda));

        try {


        await $.post('impressao/cupom.php', {
            dataAtual,
            forma_pag,
            valor_venda,
            valor_troco,
            produtos: JSON.stringify(produtos)
        });


            await conectarQZ();

            // Localiza a impressora instalada no PC
            const printer = await qz.printers.find("IMPRESSORA MONICA"); // altere para o nome da sua impressora térmica
            
            const config = qz.configs.create(printer, {
                colorType: 'blackwhite',
                copies: 1,
                density: 2,
                duplex: false,
                altPrinting: false,
                encoding: 'Cp850',
                units: 'mm',
                margins: { top: 0, right: 0, bottom: 0, left: 0 },
                paperWidth: 58, // largura do papel
            });

            

        await qz.print(config, [
            { type: 'pixel', format: 'html', flavor: 'file', data: 'impressao/cupom.html' },
            //{ type: 'raw', format: 'plain', data: conteudo + "\n\n\n\n\x1D\x56\x01" } // corte automático
        ]);


        } catch (err) {
            console.log("Erro na impressão: " + err);
        }
    }
</script>
</body>
</html>

