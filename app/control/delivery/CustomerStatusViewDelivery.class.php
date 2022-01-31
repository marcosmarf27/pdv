<?php

use Adianti\Widget\Base\TScript;
use Adianti\Widget\Template\THtmlRenderer;
/**
 * CustomerStatusView
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class CustomerStatusViewDelivery extends TPage
{
   
    
    /**
     * Class constructor
     * Creates the page
     */
    public function __construct()
    {
        parent::__construct();
        // creates form
     
        parent::add('');
    }
    
    /**
     * Show customer data and sales
     */

    
    public function onExportPDF($param)
    {
        try
        {
            // process HTML
            $html = $this->onCheckStatus($param);
            
            // string with HTML contents
            $contents = $html->getContents();
            
            $options = new \Dompdf\Options();
            $options->setChroot(getcwd());
            
            // converts the HTML template into PDF
            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($contents);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            
            $file = 'app/output/status.pdf';
            
            // write and open file
            file_put_contents($file, $dompdf->output());
            //parent::openFile('tmp/status.pdf');
            
            $window = TWindow::create(_t('Customer Status'), 0.8, 0.8);
            $object = new TElement('object');
            $object->data  = $file;
            $object->type  = 'application/pdf';
            $object->style = "width: 100%; height:calc(100% - 10px)";
            $window->add($object);
            $window->show();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
    
    public static function delete($param)
    {
        try
        {
            TTransaction::open('samples');
            $key = $param['key'];
            $object = new Contact($key);
            $customer_id = $object->customer_id;
            $object->delete();
            TTransaction::close();
            
            $action = new TAction(array('CustomerStatusView', 'onCheckStatus'));
            $action->setParameter('customer_id', $customer_id);
            new TMessage('info', 'Record deleted', $action);
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    public function onCheckStatus( $param )
    {
        try
        {
           
          
            
            // load the html template
            $html = new THtmlRenderer('app/resources/customer_status.html');
            $html->disableHtmlConversion();
            
            TTransaction::open('samples');
            if (isset($param['key']))
            {
                // load customer identified in the form
                $object = new PedidoDelivery($param['key']);
                if ($object)
                {
                    // create one array with the customer data
                   // $array_object = $object->toArray();
                    $array_object['id'] = $object->gloria_food_id;
                    $array_object['id_interno']    =  $object->id;
                    $array_object['cliente'] = $object->client_first_name . ' '  .$object->client_last_name ;
                    $array_object['telefone'] = $object->client_phone;
                    $array_object['pagamento'] = $object->used_payment_methods;
                    $array_object['endereco'] = $object->client_address;
                    $array_object['instrucoes'] = $object->instructions;
                    $array_object['data_pedido'] = $object->data_pedido . $object->hora;
                    $array_object['status'] = $object->status;
                    $array_object['email'] = $object->client_email;
                    $array_object['lat'] = $object->latitude;
                    $array_object['lon'] = $object->longitude;

                  
                    // replace variables from the main section with the object data
                    $html->enableSection('main',  $array_object);
                
                    
                    $replaces = array();
                    $sales = ItemDelivery::where('pedido_delivery_id' ,'=', $object->id)->load();
                    if ($sales)
                    {
                        $total = 0;
                        // iterate the customer sales
                      
                            // foreach sale item
                            foreach ($sales as $item)
                            
                            {

                                $jsonitem = json_decode($item->opcoes);
                                $opformatada = '';
                                foreach($jsonitem as $ob){
                
                                    $opformatada .= mb_strtolower('<b>' . $ob->group_name . ': </b>'. $ob->name . '<br>', 'UTF-8');
                
                                }
                                // define the multidimensional array with the sale items

                                $valor = $item->total_item_price > 0 ? $item->total_item_price: $item->item_discount ;
                                $cor = $item->item_discount > 0 ? 'green' : '';

                                
                                $replaces[] = array('descricao'                =>  $item->name == 'DELIVERY_FEE' ? 'Taxa de entrega' :  $item->quantity . 'X ' . $item->name . '<br>' . $opformatada,
                                                  
                                                   
                                                    'valor'          => number_format($valor, 2),

                                                    'cor' =>  $cor
                                                  
                                                  ); 
                                $total += $item->total_item_price;
                            
                        }
                        $totals['total'] = number_format($object->total_price, 2);
                        $totals['class'] = 'alert alert-danger';
                        
                        // replace sale items and totals
                        $html->enableSection('sale-details',  $replaces, TRUE);
                        $html->enableSection('sale-totals',   $totals);
                    }

                    
                 
                }
                else
                {
                    throw new Exception('Customer not found');
                }
            }
            
            TTransaction::close();
            parent::add($html);
            
            return $html;
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }

    public function onLoad($param){

    }

   
}
