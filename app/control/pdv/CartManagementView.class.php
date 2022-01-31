<?php

use Adianti\Registry\TSession;
use Adianti\Widget\Base\TScript;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TSpinner;

class CartManagementView extends TPage
{
    private $datagrid;
    
    public function __construct()
    {
        parent::__construct();

        parent::setTargetContainer('adianti_right_panel');


       /*  if (!parent::isMobile()){
           
          } */
        
       // parent::setTargetContainer("adianti_right_panel");

        $this->form = new BootstrapFormBuilder('form_search_Product');
        
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = '100%';
        $this->datagrid->disableDefaultClick();

        $column_amount = new TDataGridColumn('amount',  'Qtd',  'right',   '40%');
        $column_price = new TDataGridColumn('sale_price', 'Preço', 'right',   '25%');

        $column_id = new TDataGridColumn('id',  'ID',  'center', '5%');
        
        // add the columns
        $this->datagrid->addColumn( $column_id );
        $this->datagrid->addColumn( new TDataGridColumn('description',  'Descrição',  'left',   '30%') );
        $this->datagrid->addColumn( $column_amount );
        $this->datagrid->addColumn(  $column_price);
        
        $action1 = new TDataGridAction([$this, 'onDelete'],   ['id'=>'{id}' ] );
       // $action1->setUseButton(TRUE);
        $this->datagrid->addAction($action1, 'Excluir', 'far:trash-alt red');

        $column_id->setVisibility(false);
        $column_amount->setTransformer( function($value, $object, $row) {
            $widget = new TSpinner('amount' . '_' . $object->id);
            
           // $widget->setNumericMask(2,',','.', true);
            $widget->setValue( $object->amount );
            $widget->setRange(0,100,1);
            
            $widget->setSize('100%');
            $widget->setFormName('form_search_Product');
           // $this->form->addField($widget);
             $action = new TAction( [$this, 'onSaveInline'],
                                   ['column' => 'amount'] );
            
            $widget->setExitAction( $action ); 
            return $widget;
        });

        $format_value = function($value) {
            if (is_numeric($value)) {
                return 'R$ '.number_format($value, 2, ',', '.');
            }
            return $value;
        };

        $column_price->setTransformer( $format_value );
        

        $column_price->enableTotal('sum', 'R$', 2, ',', '.');
        
        // creates the datagrid model
        $this->datagrid->createModel();

       /*  $button = TButton::create('action1', [$this, 'onClose'], 'Cancelar', 'fa:window-close');
        $button->setFormName('form_search_Product'); */
       
        
      //  <i class="fas fa-chevron-circle-left"></i>
        
        $back = new TActionLink('Continuar comprando...', new TAction(array($this, 'onClose')), 'black', null, null, 'fa:chevron-circle-left green');
        $back->addStyleClass('btn btn-default btn-sm');
   
        $panel = new TPanelGroup;
        $panel->add($this->datagrid);
        $panel->addFooter($back);
       // $panel->addFooter($button);
        $this->form->add($panel);
        
        parent::add($panel);
        
    }
    
    /**
     * Delete an item from cart items
     */
    public function onDelete( $param )
    {
        $cart_items = TSession::getValue('cart_items');
        unset($cart_items[ $param['key'] ]);
        TSession::setValue('cart_items', $cart_items);

        $itens = count(TSession::getValue('cart_items'));

        /*      echo '<pre>';
     
             print_r($param);
             print_r($cart_items);
     
             echo '</pre>'; */
             TScript::create("$( '#carrinho' ).html( '{$itens}' );");
        
        $this->onReload();
    }
    
    /**
     * Reload the cart list
     */
    public function onReload()
    {
        $cart_items = TSession::getValue('cart_items');
        
        try
        {
            TTransaction::open('samples');
            $this->datagrid->clear();

            if($cart_items){

                foreach ($cart_items as $id => $amount)
                {
                    $product = new Product($id);
                    
                    $item = new StdClass;
                    $item->id          = $product->id;
                    $item->description = $product->description;
                    $item->amount      = $amount;
                    $item->sale_price  = $amount * $product->sale_price;
                    
                    $this->datagrid->addItem( $item );
                }
                
            }
          
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
    
    /**
     * shows the page
     */
    function show()
    {
        $this->onReload();
        parent::show();
    }
    
    /**
     * Close side panel
     */
    public static function onClose($param)
    {
        TScript::create("Template.closeRightPanel()");
        //AdiantiCoreApplication::loadPage('ListMaisVendidos', 'onReload', [ 'register_state' => 'false']);
    }

    public static function onSaveInline( $param )
    {
       
        $name   = $param['_field_name'];
        $value  = $param['_field_value'];
        $column = $param['column'];
        
        $parts  = explode('_', $name);
        $id     = end($parts);
        
        $cart_items = TSession::getValue('cart_items');
        
        if (isset($cart_items[ $id ]))
        {
            $cart_items[ $id ] = $value;
        }
        else
        {
            $cart_items[ $id ] = 1;
        }
        
        ksort($cart_items);
        
        TSession::setValue('cart_items', $cart_items);  

        $itens = count($cart_items);

   /*      echo '<pre>';

        print_r($param);
        print_r($cart_items);

        echo '</pre>'; */
        TScript::create("$( '#carrinho' ).html( '{$itens}' );");
        
        AdiantiCoreApplication::loadPage('CartManagementView', 'onReload', ['register_state' => 'false']);
    }

    public static function cancelar(){

        new TMessage('info', 'Informações reiniciadas, já pode fazer um novo pedido!');
        TSession::delValue('cart_items');
        $itens = 0;
        TScript::create("$( '#carrinho' ).html( '{$itens}' );");
        AdiantiCoreApplication::loadPage('ListMaisVendidos', 'onReload', ['adianti_open_tab' => '1', 'adianti_tab_name' => 'Mais Vendidos']);
    }
}
