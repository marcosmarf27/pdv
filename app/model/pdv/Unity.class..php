<?php
/**
 * Product Active Record
 * @author  Pablo Dall'Oglio
 */
class Unity extends TRecord
{
    const TABLENAME = 'unity';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('name');
        
    
     
    }
}
