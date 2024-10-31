<?php
    $json = file_get_contents('json_teste.json');

    $arrayApi = json_decode($json, true);
    $valorTotalParcela = [];//check
    $valorTotalDivida = [];//load
    $valorDesconto = [];
    $opcoesParcelamento = [];
    $quantidadeTitulo = [];
    $produto = '';
    $produtoNegociacao = '';
    $parcela = '';
    $valorItem = 0;
    $count = 0;
    $valorTotalParcela['total'] = 0;
    $totalParcela = 0;

    foreach($arrayApi['dividas_calculadas']['produtos']['produto'] as $produtos) {
        $valorTotalParcela[$produtos['pro_nom']] = [];
        $produto = $produtos['pro_nom'];
        foreach($produtos['formasNegociacao']['forma_negociacao'] as $formaNegociacao) {
            $valorTotalParcela[$produto][$formaNegociacao['for_nom']] = [];
            $produtoNegociacao = $formaNegociacao['for_nom'];
            foreach($formaNegociacao['parcelas']['parcela'] as $parcelas) {
                foreach($parcelas['lancamentos']['item'] as $itemParcela) {
                    if($itemParcela['descricao'] == "PRINCIPAL")
                    $valorItem = number_format((int)$itemParcela['valor'], 2) - ((number_format((int)$itemParcela['valor'], 2) * number_format((int)$itemParcela['maximo_desconto'], 2))/100);
                }
                $valorTotalParcela[$produto][$produtoNegociacao]['divida'] += number_format($valorItem, 2);
            }
            $totalParcela += $valorTotalParcela[$produto][$produtoNegociacao]['divida'];
            
        }
    }
    $valorTotalDivida = $valorTotalParcela;
    print_r("$totalParcela \n");
    print_r($valorTotalDivida);
?>