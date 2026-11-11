<?php

namespace App\Services;

use App\Models\Categoria;

class EntidadeReferenciaService
{
     /**
     * Recebe o valor e a fatura e Retoma a referencia 
     */
     // Algoritmo que gera referencia com 11 digitos
     public static function GerarReferencia($factura, $valor1)
     {
         // Multiplicar o valor por 100 para duas casas decimais (centavos)
         $valor = $valor1 * 100;
         // Entidade
         $entidade = 90028;
         // Fatura
         $fatura = $factura;
 
         $entidadeFaturaValor = $entidade.''.$fatura.''.$valor;
 
         $my_string = $entidadeFaturaValor;
 
         // Declaracao de Array Vazio
         $my_array = [];
 
         $si = 0;
         $pi = 0;
         $pn = 0;
         $cd = 0;
 
         // Deve-se percorrer todos os dígitos de “EntidadeReferenciaMesMontante” para calcular o “Si” e o “Pi”:
         for ($i = 0; $i < strlen($my_string); ++$i) {
             if ($my_string[$i] != ' ') {
                 if ($i == 0) {
                     $si = $my_string[$i] + 0;
                     $pi = ($si * 10) % 97;
                 } else {
                     $si = $my_string[$i] + $pi;
                     $pi = ($si * 10) % 97;
                 }
             }
         }
 
         // Calcular “Pn”
         $pn = ($pi * 10) % 97;
 
         // Calcular check digit
         $cd = 98 - $pn;
 
         if (strlen($cd)) {
             $cd = str_pad($cd, 2, '0', STR_PAD_LEFT);
         }
 
         $referencia = $fatura.''.$cd;
         // Fim de Check Digit
 
         return $referencia;
     }


}
