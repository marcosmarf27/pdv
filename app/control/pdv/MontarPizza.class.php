<?php

use Adianti\Database\TTransaction;
use Adianti\Registry\TSession;

/**
 * ProductCatalogView
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class MontarPizza extends TPage
{
    private $form, $cards, $pageNavigation;
    
    use Adianti\Base\AdiantiStandardCollectionTrait;
    
    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->setDatabase('samples');
        $this->setActiveRecord('Product');
        $this->addFilterField('description');
      
        $this->setLimit(15);
        $criteria = new TCriteria();
        $criteria->add(new TFilter('categoria','=', 5));
        $this->setCriteria($criteria);


        TSession::delValue($this->activeRecord.'_filter_'.'description');
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_montar_pizza');
     
        
        $description = new TEntry('description');
        TTransaction::open('samples');

        $products = Product::getIndexedArray('{id}', '{description}');
        $description->setCompletion( array_values( $products ));
        $description->setSize('100%');
        $description->placeholder = 'Pesquise aqui...';

        TTransaction::close();
        
        $button = TButton::create('action1', [$this, 'onSearch'], 'Buscar', 'fa:search blue');
        $this->form->addFields(  [$description]  );
        $this->form->addFields(  [$button]  );
     
        $description->setValue( TSession::getValue( 'Product_description' ) );
        

        // keep the form filled with the search data
        
        // creates a DataGrid
        $this->cards = new TCardView;
        $this->cards->setUseButton();
	
		
		    $this->setCollectionObject($this->cards);
		
		    $this->cards->setItemTemplate(
                                    '<div class="card" style="width: 18rem;">
                                    <img class="card-img-top" src="{photo_path}">
                                    <div class="card-body">
                                    <h5 class="card-title">{description}</h5>
                                    <hr>
                                    <p class="card-text"> R$ {sale_price}</p>
                                    <a generator="adianti" href="index.php?class=ListMaisVendidos&method=onSelect&id={id}&register_state=false" class="btn btn-primary">+ adicionar</a>
                                    </div>
                                    </div>'
                                    );

                                  
        
	
		
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        
        // creates the page structure using a table
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
       // $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->add($this->form); // add a row to the form
        $vbox->add(TPanelGroup::pack('', $this->cards, $this->pageNavigation)); // add a row for page navigation
        
        // add the table inside the page
        parent::add($vbox);
    }
    
    /**
     * Select product
     */
    public static function onSelect( $param )
    {
        $cart_items = TSession::getValue('cart_items');
        
        if (isset($cart_items[ $param['id'] ]))
        {
            $cart_items[ $param['id'] ] ++;
        }
        else
        {
            $cart_items[ $param['id'] ] = 1;
        }
        
        ksort($cart_items);
        
        TSession::setValue('cart_items', $cart_items);

        $itens = count($cart_items);

     
        TScript::create("$( '#carrinho' ).html( '{$itens}' );");
        AdiantiCoreApplication::loadPage('CartManagementView', 'onReload', [ 'register_state' => 'false']);
    }

  /*   public function onSearchMenu($param)
    {
      
      if(isset($param['codcategoria']))
      {
        TSession::delValue($this->activeRecord.'_filter_'.'description');
        $filter = new TFilter('categoria', '=', $param['codcategoria']);

        TSession::setValue($this->activeRecord.'_filter_'.'categoria', $filter);

        $this->onReload( ['offset'=>0, 'first_page'=>1] );


      }else{

        TSession::setValue($this->activeRecord.'_filter_'.'categoria', NULL);

        $this->onSearch($param = null);
       


      }
    } */


}
