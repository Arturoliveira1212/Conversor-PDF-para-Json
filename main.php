<?php

require 'vendor/autoload.php';

use Smalot\PdfParser\Parser;

try{
    $parser = new Parser();
    $pdf = $parser->parseFile('Sicoob_Demonstracao.pdf');
    $text = $pdf->getText();
} catch( Exception $e ){
    die( 'Houve um erro ao abrir o arquivo PDF.' );
}

$dadosTabelas = explode( 'Sacado	Nosso Número Seu Número', $text );
array_shift( $dadosTabelas );

$dados = formatarDadosPdf( $dadosTabelas );
criarJson( $dados );

function formatarDadosPdf( array $dadosTabelas ){
    $resposta = [];

    foreach( $dadosTabelas as $dadosTabela ){
        $tipoData = 'Dt. Liquid.';
        if( strpos( $dadosTabela, 'Dt. Baixa' ) !== false ){
            $tipoData = 'Dt. Baixa';
        }

        $tipoValor = 'Vlr. Cobrado';
        if( strpos( $dadosTabela, 'Vlr. Baixa' ) !== false ){
            $tipoValor = 'Vlr. Baixa';
        }

        $linhas = explode( "\n", $dadosTabela );
        foreach( $linhas as $linha ){
            $pattern = '/^\d+-\d+\s.*$/';
            if( preg_match( $pattern, $linha ) ){
                $textoSemEspacosExtras = preg_replace('/\s+/', ' ', trim($linha));
                $data = explode( ' ', $textoSemEspacosExtras );
                $registro = formarRegistro( $data, $tipoData, $tipoValor );
                if( ! empty( $registro ) ){
                    $resposta[] = $registro;
                }
            }
        }
    }

    return $resposta;
}

function formarRegistro( array $dados, $tipoData, $tipoValor ){
    $nome = obterNomeSacado( $dados );
    $datas = obterDatas( $dados );
    $numeros = formatarNumeros( $dados );
    limpaEspacoesVazios( $dados );

    $registro = [];
    if( $nome != null && $datas != null && $numeros != null ){
        $registro = array_merge( $dados, $numeros, [ $nome ], $datas );

        return [
            'Sacado' => $registro[7] ?? '',
            'Nosso numero' => $registro[0] ?? '',
            'Seu numero' => $registro[1] ?? '',
            'NN Corresp' => '',
            'Dt. Previsao Credito' => $registro[10] ?? '',
            'Dt. Vencimento' => $registro[8] ?? '',
            'Valor' => $registro[2] ?? '',
            'Vlm. Mora' => $registro[6] ?? '',
            'Vlr. Desc.' => $registro[3] ?? '',
            'Vlr. Outros Acresc.' => $registro[4] ?? '',
            $tipoData => $registro[9] ?? '',
            $tipoValor => $registro[5] ?? ''
        ];
    }

    return [];
}

function obterNomeSacado( array &$dados ){
    $nomeSacado = "";

    foreach( $dados as &$valor ){
        if (preg_match('/[A-Za-z]+/', $valor, $matches)) {
            $nomeSacado .= $matches[0] . " ";
            $valor = str_replace($matches[0], '', $valor);
        }
    }

    $nomeSacado = trim($nomeSacado);
    return $nomeSacado;
}

function obterDatas( array &$dados ){
    $datas = [];

    foreach ($dados as &$valor) {
        preg_match_all('/(\d{1,2}[,\.]?\d{0,3})?(\d{2}\/\d{2}\/\d{4})/', $valor, $matches);

        if (!empty($matches[2])) {
            foreach ($matches[2] as $data) {
                $datas[] = $data;
                $valor = str_replace($data, '', $valor);
            }
        }
    }

    return array_reverse( $datas );
}

function formatarNumeros( array &$dados ){
    foreach ($dados as $indice => &$valor) {
        $quantidadeVirgulas = substr_count($valor, ',');
        $data = explode( ',', $valor );
        if( $quantidadeVirgulas === 2 ){
            $numero1 = $data[0] . ',' . substr($data[1], 0, 2);
            $numero2 = substr($data[1], 2, 1) . ',' . $data[2];
            $valor = '';
            return [ $numero1, $numero2 ];
        } else if( $quantidadeVirgulas == 3 ){
            $numero1 = $data[0] . ',' . substr($data[1], 0, 2);
            $numero2 = substr($data[1], 2, 1) . ',' . $data[2];
            $numero3 = substr($data[1], 2, 3) . ',' . $data[3];
            $valor = '';
            return [ $numero1, $numero2, $numero3 ];
        }
    }

    return [];
}

function limpaEspacoesVazios( array &$dados ){
    foreach( $dados as $indice => $valor ){
        if( $valor == "" || $valor == '-' ){
            unset( $dados[$indice] );
        }
    }

    $dados = array_values( $dados );
}

function criarJson( array $dados ){
    $jsonData = json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    file_put_contents('dados.json', $jsonData);
}