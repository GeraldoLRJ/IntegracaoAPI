<?php
function processarDadosPagamento($jsonApi) {
    $resultados = [
        'valor_total_parcela_desconto_aplicado' => 0,
        'valor_total_divida' => [],
        'valor_desconto' => [],
        'opcoes_parcelamento' => [],
        'quantidade_titulo' => []
    ];

    foreach ($jsonApi['dividas_calculadas']['produtos']['produto'] as $produto) {
        $nomeProduto = $produto['pro_nom'];
        $totalComDesconto = 0;
        $quantidadeTitulos = 0;
        $parcelamentoOpcoes = [];
        $valoresParcela = [];

        foreach ($produto['formasNegociacao']['forma_negociacao'] as $formaNegociacao) {
            $parcelaValores = 0;
            $totalDivida = 0;
            $parcelas = $formaNegociacao['parcelas']['parcela'];
            $quantidadeTitulos += count($parcelas);
            $maxNumParcela = $formaNegociacao['regras_acordo']['regra_acordo']['aco_maxnumpar'];
            $minNumParcela = $formaNegociacao['regras_acordo']['regra_acordo']['aco_minnumpar'];
            $numeroParcelas = 
                $minNumParcela == 1 && 
                $maxNumParcela == 1
                ? "A VISTA"
                : sprintf("%sX A %sX", $minNumParcela, $maxNumParcela);

            foreach ($parcelas as $parcela) {
                foreach ($parcela['lancamentos']['item'] as $item) {
                    if($item['descricao'] == "PRINCIPAL"){
                        $valor = number_format(floatval(str_replace(',', '.', $item['valor'])), 2);
                        $desconto = number_format(floatval($item['maximo_desconto']), 2);
                        $valorDescontado = number_format($valor - ($valor * $desconto / 100), 2);
                        
                        $totalDivida += $valor;
                        $totalComDesconto += $valorDescontado;
                        $parcelaValores += $valorDescontado;
                    }
                }
            }

            $parcelamentoOpcoes[] = [
                'nome' => $formaNegociacao['for_nom'],
                'quantidade_parcelas' => "$numeroParcelas",
                'valor_parcelas' => "R$ $totalDivida"
            ];

            while($minNumParcela <= $maxNumParcela){
                $valoresParcela[sprintf("$minNumParcela"."X")] = sprintf("R$ %s", $totalDivida*$minNumParcela);
                
                $minNumParcela++;
            }
            
            $resultados['valor_total_parcela_desconto_aplicado'] += $parcelaValores;
        }

        $resultados['valor_total_divida'][$nomeProduto] = $valoresParcela;
        $resultados['valor_desconto'][$nomeProduto] = "R$ $totalComDesconto";
        $resultados['opcoes_parcelamento'][$nomeProduto] = $parcelamentoOpcoes;
        $resultados['quantidade_titulo'][$nomeProduto] = "$quantidadeTitulos títulos";
    }

    $resultados['valor_total_parcela_desconto_aplicado'] = "R$ {$resultados['valor_total_parcela_desconto_aplicado']}";

    return $resultados;
}

$jsonApi = json_decode(file_get_contents('json_teste.json'), true);
$resultado = processarDadosPagamento($jsonApi);
print_r($resultado);
?>