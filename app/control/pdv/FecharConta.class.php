<?php

use Adianti\Control\TAction;
use Adianti\Control\TWindow;
use Adianti\Database\TTransaction;
use Adianti\Registry\TSession;
use Adianti\Widget\Form\TRadioGroup;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Wrapper\BootstrapFormBuilder;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\THidden;
use Adianti\Widget\Form\TText;
use Adianti\Wrapper\BootstrapDatagridWrapper;

/**
 * FormShowHideRowsView
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class FecharConta extends TWindow
{
    private $form;
    private $detail_list;
    
    /**
     * Class constructor
     * Creates the page
     */
    function __construct()
    {
        parent::__construct();
        parent::setSize(0.5, null);
        parent::removePadding();
        parent::setTitle('Fechar Pedido');
        parent::disableEscape();

        if(parent::isMobile())
        {

            parent::setSize(0.9, null);

        }

        if(!TSession::getValue('cart_items')){

            TWindow::closeWindowByName('CheckoutPDV');

            new TMessage('warning', 'Não há produtos no carrinho!');

        }
        
        // create the form
        $this->form = new BootstrapFormBuilder('form_checkout');
     
        
        // create the form fields
        $type        = new TRadioGroup('type');
        $payment       = new TRadioGroup('payment');
        $totalcompra      = new THidden('totalcompra');
        $idvenda      = new THidden('id');
        $mesa = new TEntry('mesa');
        $mesa->placeholder = 'Informe a mesa/comanda';

        if(parent::isMobile())
        {

            $obs = new TText('obs');
            $obs->placeholder= 'Faça as observações aqui EX. Não colocar cebola, bem passado...';

           

        }else{

            $obs = new THtmlEditor('obs');
            $obs->setSize( '100%', 100);
            $obs->setOption('placeholder', 'Faça as observações aqui EX. Não colocar cebola, bem passado...');

        }
 
        $payment->setLayout('horizontal');
        $payment->setUseButton();
        $payment->setBreakItems(2);
     

        $combo_pay = array();
        $combo_pay['1'] ='<i class="fas fa-money-bill-wave"></i> Dinheiro';
        $combo_pay['2'] ='<i class="fas fa-credit-card"></i> Cartão Crédito';
        $combo_pay['3'] ='<i class="far fa-credit-card"></i> cartão Débito';
        $combo_pay['4'] ='<i class="fas fa-file-invoice-dollar"></i> Pix/Transferência';
        
        $payment->addItems($combo_pay);

        foreach ($payment->getLabels() as $key => $label)
        {
            $label->setSize(130);
           
        }
        

    
       
       
        
        $type->setChangeAction(new TAction(array($this, 'onChangeType')));
        $combo_items = array();
        $combo_items['1'] ='Avulso';
        $combo_items['2'] ='Mesa/Comanda';
      
        $type->addItems($combo_items);
        $type->setLayout('horizontal');
        $type->setUseButton();
        $type->setSize('100%');
        
        // default value
        $type->setValue('1');
        
        // fire change event
        self::onChangeType( ['type' => '1'] );
        
        // add the fields inside the form
        $this->form->addFields( [$type] );
        $this->form->addFields( [$mesa] );
        $this->form->addFields( [$totalcompra] );
        $this->form->addFields( [$idvenda] );
    
      



        //detalhes do item comprados

        $this->detail_list = new BootstrapDatagridWrapper( new TDataGrid );
        $this->detail_list->style = 'width:100%';
        $this->detail_list->disableDefaultClick();
        
        $product       = new TDataGridColumn('description',  'Desc', 'left');
        $price         = new TDataGridColumn('sale_price',  'valor',    'right');
        $amount        = new TDataGridColumn('amount',  'qtd',    'center');
       
     
        
        $this->detail_list->addColumn( $product );
        $this->detail_list->addColumn( $amount );
        $this->detail_list->addColumn( $price );
      
       
    
        
        $format_value = function($value) 
        {
            if (is_numeric($value)) {
                return 'R$ '.number_format($value, 2, ',', '.');
            }
            return $value;
        };
        
        $price->setTransformer($format_value);
        
        // define totals
        $price->setTotalFunction( function($values) 
        {
            return array_sum((array) $values);
        });
        
        $this->detail_list->createModel();
        $cart_items = TSession::getValue('cart_items');

        if($cart_items)
        {

            
            TTransaction::open('samples');
            $total= 0;
            $this->detail_list->clear();
            foreach ($cart_items as $id => $amount)
            {
                $product = new Product($id);
                
                $item = new StdClass;
                $item->description = $product->description;
                $item->amount      = $amount;
                $item->sale_price  = $amount * $product->sale_price;
                $total +=   $item->sale_price;
                $this->detail_list->addItem( $item );
              
                
            }

            $totalcompra->setValue($total);
            
            TTransaction::close();


        }
       
      
        
        $panel = new TPanelGroup('', '#f5f5f5');
        $panel->add($this->detail_list);
        $panel->{'name'} = 'itens';
        $panel->getBody()->style = 'overflow-x:auto';
        
        $this->form->addContent([$panel]);
        $this->form->addFields( [$obs] );

        $this->form->addFields( [$payment] );
      

        $this->form->addAction('Registrar', new TAction(array($this, 'registrar')), 'fas: fa-cash-register green');

        $dropdown = new TDropDown('Opções', 'fa:th blue');
        $dropdown->addPostAction( 'Imprimir', new TAction(array($this, 'imprimir') ), $this->form->getName(), 'far:file-pdf');
        $this->form->addFooterWidget($dropdown);
        

         
       
       
   
        // wrap the page content using vertical box
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        $vbox->add($this->form);
       

        parent::add($vbox);
    }
    
    /**
     * Event executed when type is changed
     */
    public static function onChangeType($param)
    {
        if ($param['type'] == '1')
        {
            TQuickForm::showField('form_checkout', 'itens');
            TQuickForm::showField('form_checkout', 'payment');
            TQuickForm::hideField('form_checkout', 'mesa');
        
        }
        else
        {
           
            TQuickForm::hideField('form_checkout', 'payment');
            TQuickForm::showField('form_checkout', 'mesa');
            TQuickForm::showField('form_checkout', 'itens');
       
        }
        
         
    }

    public function registrar($param)
    {
    
        try {
            //code...

            TTransaction::open('samples');

            $dados = $this->form->getData();

            if ($dados->type == 1)
            {
                $venda = new Sale;
                $venda->date = date('d-m-Y');
                $venda->total = $dados->totalcompra;
                $venda->obs = $dados->obs;
                $venda->customer_id = '';
                $venda->status = '1';
                $venda->pagamento = $dados->payment;
                $venda->system_user_id = TSession::getValue('userid');
                $venda->mes = date('m');
                $venda->ano = date('Y');
                $venda->mesa = '';
                $venda->datepag = date('d-m-Y');

                $venda->store();


                $cart_items = TSession::getValue('cart_items');

                if($cart_items)
                {
        
                    
                 
                  
                   
                    foreach ($cart_items as $id => $amount)
                    {
                        $product = new Product($id);
                        
                        $itemVenda = new SaleItem;
                        $itemVenda->sale_price = $amount * $product->sale_price;
                        $itemVenda->amount = $amount;
                        $itemVenda->discount = '';
                     
                        $itemVenda->product_id =  $product->id;
                        $itemVenda->sale_id =  $venda->id;
                        $itemVenda->mesa = '';
                        $itemVenda->store();
                      
                        
                    }
        
                  
                    
                  
        
        
                }

                $dados->id = $venda->id;
                $this->form->setData($dados);

                new TMessage('info', 'Registro salvo com sucesso!');

              

            }else{

                $venda = new Sale;
                $venda->date = date('Y-m-d');
                $venda->total = $dados->totalcompra;
                $venda->obs = $dados->obs;
                $venda->customer_id =  $dados->mesa;
                $venda->status = '1';
                $venda->pagamento = $dados->payment;
                $venda->system_user_id = TSession::getValue('userid');
                $venda->mes = date('m');
                $venda->ano = date('Y');
                $venda->mesa = $dados->mesa;
                $venda->datepag = date('d-m-Y');

                $venda->store();


                $cart_items = TSession::getValue('cart_items');

                if($cart_items)
                {
        
                    
                 
                  
                   
                    foreach ($cart_items as $id => $amount)
                    {
                        $product = new Product($id);
                        
                        $itemVenda = new SaleItem;
                        $itemVenda->sale_price = $amount * $product->sale_price;
                        $itemVenda->amount = $amount;
                        $itemVenda->discount = '';
                     
                        $itemVenda->product_id =  $product->id;
                        $itemVenda->sale_id =  $venda->id;
                        $itemVenda->mesa =  $dados->mesa;
                        $itemVenda->store();
                      
                        
                    }
        
                  
                    
                  
        
        
                }

                $dados->id = $venda->id;
                $this->form->setData($dados);
                self::onChangeType( ['type' => '2'] );

                new TMessage('info', 'Registro salvo com sucesso!');

              
            }

        

           
           
           
           
          

            TTransaction::close();


        } catch (Exception $e) {
            //throw $th;

            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
        
        
    }

    public function imprimir($param)
    {

        try {
            //code...
            TTransaction::open('samples');

            echo '<pre>';

            print_r($param);

            echo '</pre>';


            if(empty($param['id']))
            {

                throw new Exception("O pedido deve ser registrado antes de imprimir! ");
                
            }

            $venda =  Sale::find($param['id']);

            $html = new THtmlRenderer('app/resources/modelo_avulso.html');
            $html->disableHtmlConversion();
    
            $replace = array();
            $replace['tipo']    =  $param['type'] == '1' ? 'Avulso' : 'Mesa/Comanda' ;
            $replace['data']    =  $venda->date;
            $replace['usuario']    = TSession::getValue('login') ;
            $replace['pedido']    = $venda->id ;
           
            $replace['empresa']    =  'Pizza Adianti';
            $replace['mesa']    =  $venda->customer_id ? $venda->customer_id : 'Sem registro' ;
            $replace['obs']    = $venda->obs;
            $replace['total']    =  $venda->total;
          
            $html->enableSection('main', $replace);

            $replace2 = array();

            $itens = $venda->getSaleItems();


            foreach($itens as $item){

                $replace2[] = array('desc' => $item->product->description,
                                   'qtd'=> $item->amount, 
                                   'valor'=> $item->sale_price);

            }

            $html->enableSection('itens', $replace2, TRUE);


            $msg = $html->getContents();
            //$msg2 = urlencode($msg);

            //  $html = clone $this->datagrid;
            $contents = file_get_contents('app/resources/styles-print.html') . $html->getContents();
            // $impressao = urlencode($contents);
            // converts the HTML template into PDF
           
            
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->getOptions()->setChroot("app/images"); 
            $dompdf->loadHtml($contents);
            $customPaper = array(0,0,280,560);
            $dompdf->setPaper($customPaper, 'portrait');
            $dompdf->render();
            
            $file = 'app/output/datagrid-export.pdf';
            
            // write and open file
            file_put_contents($file, $dompdf->output());
            
            $window = TWindow::create('Impressão', 0.8, 0.8);
            $object = new TElement('object');
            $object->data  = $file;
            $object->type  = 'application/pdf';
            $object->style = "width: 100%; height:calc(100% - 10px)";
            $window->add($object);
            $window->show();

            TTransaction::close();

            TSession::delValue('cart_items');
            $itens = 0;
            TScript::create("$( '#carrinho' ).html( '{$itens}' );");
            TScript::create("Template.closeRightPanel()");

            TWindow::closeWindowByName('CheckoutPDV');

            


        



        } catch (Exception $e) {
            //throw $th;

            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
        
        


        
             
              
      
     
        

    
   


      

   
   

       
    }

    
}
