<?php
/**
 * Active Record for table City
 * @author  Pablo Dall'Oglio
 */
class PedidoDelivery extends TRecord
{
    const TABLENAME = 'pedido_delivery';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}

 
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('gloria_food_id');
        parent::addAttribute('client_first_name');
        parent::addAttribute('client_last_name');
        parent::addAttribute('msg');
        parent::addAttribute('client_email');
        parent::addAttribute('client_phone');
        parent::addAttribute('status');
        parent::addAttribute('accepted_at');
        parent::addAttribute('used_payment_methods');
        parent::addAttribute('latitude');
        parent::addAttribute('longitude');
        parent::addAttribute('instructions');
        parent::addAttribute('client_address');
        parent::addAttribute('total_price');
        parent::addAttribute('mes');
        parent::addAttribute('ano');
        parent::addAttribute('data_pedido');
        parent::addAttribute('hora');
     

    
    }
    
    /**
     * Returns the state
     */
 /*    public function get_state()
    {
        return State::find($this->state_id);
    } */
    
    /**
     * Method getCustomers
     */
  /*   public function getCustomers()
    {
        $criteria = new TCriteria;
        $criteria->add(new TFilter('city_id', '=', $this->id));
        return Customer::getObjects( $criteria );
    } */
}
