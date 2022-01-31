<?php
/**
 * Active Record for table City
 * @author  Pablo Dall'Oglio
 */
class ItemDelivery extends TRecord
{
    const TABLENAME = 'item_pedido_delivery';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}




 
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('name');
        parent::addAttribute('total_item_price');
        parent::addAttribute('quantity');
        parent::addAttribute('gloria_item_id');
        parent::addAttribute('pedido_delivery_id');
        parent::addAttribute('opcoes');
        parent::addAttribute('obs');
        parent::addAttribute('coupon');
        parent::addAttribute('item_discount');
       
    
     

    
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
