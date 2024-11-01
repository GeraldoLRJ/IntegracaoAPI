function processarDadosPagamento(jsonApi) {
    const resultados = {
        valor_total_parcela_desconto_aplicado: 0,
        valor_total_divida: {},
        valor_desconto: {},
        opcoes_parcelamento: {},
        quantidade_titulo: {}
    };

    jsonApi.dividas_calculadas.produtos.produto.forEach(produto => {
        const nomeProduto = produto.pro_nom;
        let totalComDesconto = 0;
        let quantidadeTitulos = 0;
        const parcelamentoOpcoes = [];
        let valoresParcela = {};

        produto.formasNegociacao.forma_negociacao.forEach(formaNegociacao => {
            let parcelaValores = 0;
            let totalDivida = 0;
            const parcelas = formaNegociacao.parcelas.parcela;
            quantidadeTitulos += parcelas.length;
            let maxNumParcela = Number(formaNegociacao.regras_acordo.regra_acordo.aco_maxnumpar);
            let minNumParcela = Number(formaNegociacao.regras_acordo.regra_acordo.aco_minnumpar);

            const numeroParcelas = 
                minNumParcela === 1 &&
                maxNumParcela === 1
                    ? "A VISTA"
                    : minNumParcela+'X A '+maxNumParcela+'X';

            parcelas.forEach(parcela => {
                parcela.lancamentos.item.forEach(item => {
                    if (item.descricao === "PRINCIPAL") {
                        const valor = parseFloat(item.valor.replace(',', '.'));
                        const desconto = parseFloat(item.maximo_desconto);
                        const valorDescontado = (valor - (valor * desconto / 100)).toFixed(2);

                        totalDivida += parseFloat(valor);
                        totalComDesconto += parseFloat(valorDescontado);
                        parcelaValores += parseFloat(valorDescontado);
                    }
                });
            });

            parcelamentoOpcoes.push({
                nome: formaNegociacao.for_nom,
                quantidade_parcelas: numeroParcelas,
                valor_parcela: 'R$ '+totalDivida.toFixed(2)
            });
            
            while (minNumParcela <= maxNumParcela) {
                const parcNom = minNumParcela + 'X';
                valoresParcela[parcNom] = 'R$' + (totalDivida * minNumParcela).toFixed(2);
                minNumParcela++;
            }

            resultados.valor_total_parcela_desconto_aplicado += parcelaValores;
        });

        resultados.valor_total_divida[nomeProduto] = valoresParcela;
        resultados.valor_desconto[nomeProduto] = 'R$ '+totalComDesconto.toFixed(2);
        resultados.opcoes_parcelamento[nomeProduto] = parcelamentoOpcoes;
        resultados.quantidade_titulo[nomeProduto] = quantidadeTitulos+' títulos';
    });

    resultados.valor_total_parcela_desconto_aplicado = 'R$ '+resultados.valor_total_parcela_desconto_aplicado.toFixed(2);

    return resultados;
}
const fs = require('fs');
const jsonApi = JSON.parse(fs.readFileSync('json_teste.json', 'utf8'));
const resultado = processarDadosPagamento(jsonApi);
console.log(JSON.stringify(resultado, null, 2));