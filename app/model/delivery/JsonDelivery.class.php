<?php
/**
 * Active Record for table City
 * @author  Pablo Dall'Oglio
 */
class JsonDelivery extends TRecord
{
    const TABLENAME = 'json_delivery';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}




 
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('quantidade');
        parent::addAttribute('json_pedido');
        parent::addAttribute('data_json');
        parent::addAttribute('salvo');
     
       
    
     

    
    }
    
 
}
