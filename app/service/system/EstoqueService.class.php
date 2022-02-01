<?php


class EstoqueService {

    public static function checaEstoque($itenskeys){

        $itenszerados = array();

        foreach ($itenskeys as $id => $amount)
        {
            $product =  Product::find($id);

            if($product->stock == 0 and $product->unity != 'UN'){

                continue;

            }else{

                if($product->stock >= $amount){

                    $product->stock =  $product->stock - $amount;
                    $product->store();
                }else{


                   $itenszerados[$id] = $product->description;

                  // print_r($itenszerados);

                }
            }



            


            
            
          
            
        }

        if(count($itenszerados) > 0){

           // new TMessage('info', 'O pedido não pode ser feito porque os seguintes :' . implode(',', $itenszerados) . 'estão com estoque menor do que solicitado!');

           return $itenszerados;

        }else{

            return False;
        }

    }
}