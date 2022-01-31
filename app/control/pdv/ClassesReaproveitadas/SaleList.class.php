<?php

use Adianti\Database\TTransaction;
use Adianti\Registry\TSession;
use Adianti\Widget\Form\THidden;

/**
 * SaleList
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SaleList extends TPage
{
    protected $form;     // registration form
    protected $datagrid; // listing
    protected $pageNavigation;
    
    use Adianti\Base\AdiantiStandardListTrait;
    
    /**
     * Page constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->setDatabase('samples');          // defines the database
        $this->setActiveRecord('Sale');         // defines the active record
        $this->setDefaultOrder('id', 'desc');    // defines the default order
        $this->addFilterField('mesa', '=', 'mesa'); // filterField, operator, formField
       // $this->addFilterField('customer_id', '=', 'customer_id'); // filterField, operator, formField
        
        $this->addFilterField('date', '>=', 'date_from', function($value) {
            return TDate::convertToMask($value, 'dd/mm/yyyy', 'yyyy-mm-dd');
        }); // filterField, operator, formField, transformFunction
        
        $this->addFilterField('date', '<=', 'date_to', function($value) {
            return TDate::convertToMask($value, 'dd/mm/yyyy', 'yyyy-mm-dd');
        }); // filterField, operator, formField, transformFunction
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Sale');
        $this->form->setFormTitle('Registro de Pedidos (mesa/comanda)');
        
        // create the form fields
        $id        = new TEntry('mesa');
        $date_from = new TDate('date_from');
        $date_to   = new TDate('date_to');
        
      /*   $customer_id = new TDBUniqueSearch('customer_id', 'samples', 'Customer', 'id', 'name');
        $customer_id->setMinLength(1);
        $customer_id->setMask('{name} ({id})');
         */
        // add the fields
        $this->form->addFields( [new TLabel('Mesa/Comanda')],          [$id]); 
        $this->form->addFields( [new TLabel('Date (from)')], [$date_from],
                                [new TLabel('Date (to)')],   [$date_to] );
       // $this->form->addFields( [new TLabel('Customer')],    [$customer_id] );
        
        $id->setSize('50%');
        $date_from->setSize('100%');
        $date_to->setSize('100%');
        $date_from->setMask( 'dd/mm/yyyy' );
        $date_to->setMask( 'dd/mm/yyyy' );
        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('SaleList_filter_data') );
        
        // add the search form actions
        $this->form->addAction('Buscar', new TAction([$this, 'onSearch']), 'fa:search');
        $this->form->addActionLink('Novo',  new TAction(['SaleForm', 'onEdit']), 'fa:plus green');
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = '100%';
        
        // creates the datagrid columns
       // $column_id       = new TDataGridColumn('id', 'Id', 'center', '10%');
        $column_date     = new TDataGridColumn('date', 'Date', 'center', '30%');
        $column_customer = new TDataGridColumn('mesa', 'Mesa/Comanda', 'left', '25%');
        $column_total    = new TDataGridColumn('total', 'Total', 'right', '25%');
        $column_status    = new TDataGridColumn('status', 'Situação', 'right', '25%');
        
        // define format function
        $format_value = function($value) {
            if (is_numeric($value)) {
                return 'R$ '.number_format($value, 2, ',', '.');
            }
            return $value;
        };
        
        $column_total->setTransformer( $format_value );
        
        // add the columns to the DataGrid
      //  $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_date);
        $this->datagrid->addColumn($column_customer);
        $this->datagrid->addColumn($column_total);
        $this->datagrid->addColumn($column_status);
        
        // creates the datagrid column actions
      //  $column_id->setAction(new TAction([$this, 'onReload']),   ['order' => 'id']);
        $column_date->setAction(new TAction([$this, 'onReload']), ['order' => 'date']);
        
        // define the transformer method over date
        $column_date->setTransformer( function($value, $object, $row) {
            $date = new DateTime($value);
            return $date->format('d/m/Y');
        });

        $action_view   = new TDataGridAction(['SaleSidePanelView', 'onView'],   ['key' => '{id}', 'register_state' => 'false'] );
        $action_edit   = new TDataGridAction(['SaleForm', 'onEdit'],   ['key' => '{id}'] );
        $action_delete = new TDataGridAction([$this, 'onDelete'],   ['key' => '{id}'] );
        $action_fechar = new TDataGridAction([$this, 'fecharMesa'],   ['key' => '{id}'] );
       /*  $action_view->setUseButton(TRUE);
        $action_delete->setUseButton(TRUE);
        $action_edit->setUseButton(TRUE); */
        $action_delete->setDisplayCondition( array($this, 'displayColumnDelete') );
        $action_edit->setDisplayCondition( array($this, 'displayColumnEdit') );
        $action_fechar->setDisplayCondition( array($this, 'displayColumnEdit') );
      
        
        $this->datagrid->addAction($action_view, 'Visualização rápida', 'fa:search green fa-fw');
        $this->datagrid->addAction($action_edit, 'Editar Pedido',   'far:edit blue fa-fw');
        $this->datagrid->addAction($action_delete, 'Cancelar', 'far:trash-alt red fa-fw');
        $this->datagrid->addAction($action_fechar, 'Fechar Mesa', 'fas:cash-register r fa-fw');

        $column_customer->setTransformer( function($value, $object, $row) {


            if($value){
                $div = new TElement('span');
                $div->class="label label-success";
                $div->style="text-shadow:none; font-size:12px";
                $div->add($value);
                return $div;

            }else{

            }
            $div = new TElement('span');
            $div->class="label label-info";
            $div->style="text-shadow:none; font-size:12px";
            $div->add('Pedido Avulso');
            return $div;
        });

        $column_status->setTransformer(function($value) {
            
               
                
            switch($value)
            {
    
                case '1':
                   
                   
                    $div = new TElement('span');
                    $div->class="label label-warning";
                    $div->style="text-shadow:none; font-size:12px";
                    $div->add('Aberto');
                    return " $div";
                    break;
                case '2':
                   
                  
                    $div = new TElement('span');
                    $div->class="label label-success";
                    $div->style="text-shadow:none; font-size:12px";
                    $div->add('Pago');
                    return " $div";
                    break;
              
              
                
    
    
            }
         
         });
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // create the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));

        $dropdown = new TDropDown(_t('Export'), 'fa:list');
        $dropdown->setPullSide('right');
        $dropdown->setButtonClass('btn btn-default waves-effect dropdown-toggle');
        $dropdown->addAction( _t('Save as CSV'), new TAction([$this, 'onExportCSV'], ['register_state' => 'false', 'static'=>'1']), 'fa:table fa-fw blue' );
        $dropdown->addAction( _t('Save as PDF'), new TAction([$this, 'onExportPDF'], ['register_state' => 'false', 'static'=>'1']), 'far:file-pdf fa-fw red' );
        $dropdown->addAction( _t('Save as XML'), new TAction([$this, 'onExportXML'], ['register_state' => 'false', 'static'=>'1']), 'fa:code fa-fw green' );
       
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($panel = TPanelGroup::pack('', $this->datagrid, $this->pageNavigation));
        $panel->getBody()->style = 'overflow-x:auto';
        $panel->addHeaderWidget( $dropdown );
        parent::add($container);
    }

    public function displayColumnDelete( $object )
    {
        if ($object->status == '1' )
        {
            return TRUE;
        }
        return FALSE;
    }

    public function displayColumnEdit( $object )
    {
        if ($object->status == '1' )
        {
            return TRUE;
        }
        return FALSE;
    }

    public static function fecharMesa( $param )
    {
        // input fields
        $pedido   = new THidden('id');
        $pedido->setValue($param['key']);
       
        $payment       = new TRadioGroup('payment');
        $combo_pay = array();
        $combo_pay['1'] ='<i class="fas fa-money-bill-wave"></i> Dinheiro';
        $combo_pay['2'] ='<i class="fas fa-credit-card"></i> Cartão Crédito';
        $combo_pay['3'] ='<i class="far fa-credit-card"></i> cartão Débito';
        $combo_pay['4'] ='<i class="fas fa-file-invoice-dollar"></i> Pix/Transferência';

        $payment->setLayout('horizontal');
        $payment->setUseButton();
        $payment->setBreakItems(2);
        
        $payment->addItems($combo_pay);

        foreach ($payment->getLabels() as $key => $label)
        {
            $label->setSize(130);
           
        }
     
        
        $form = new BootstrapFormBuilder('input_form');
        $form->addFields( [new TLabel('Forma de pagamento')],     [$payment] );
        $form->addFields(      [$pedido] );
        
        
        // form action
        $form->addAction('Fechar Pedido', new TAction(array(__CLASS__, 'onConfirm')), 'fa:save green');
        
        // show input dialot
        new TInputDialog('Fechando Mesa', $form);
    }

    public static function onConfirm( $param )
    {

        if (isset($param['id']) AND $param['payment']) // validate required field
        {

            TTransaction::open('samples');
            $venda = Sale::find($param['id']);
            $venda->status = '2';
            $venda->pagamento = $param['payment'];
            $venda->datepag = date('Y-m-d');
            $venda->system_user_id = TSession::getValue('userid');
            $venda->store();
            new TMessage('info', 'Pedido fechado com sucesso!', new TAction(array('SaleList', 'onReload')));
            TTransaction::close();
           
        }
       
    }
}
