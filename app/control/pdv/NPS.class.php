<?php

use Adianti\Control\TAction;
use Adianti\Control\TWindow;
use Adianti\Widget\Base\TScript;
use Adianti\Widget\Form\TRadioGroup;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Wrapper\BootstrapFormBuilder;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Form\TCheckGroup;
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
class NPS extends TWindow
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
        parent::setTitle('Avaliação');
        parent::disableEscape();

        TScript::importFromFile('app/lib/include/circle/nps.js');

        if(parent::isMobile()){

            parent::setSize(0.9, null);



        }
        
        // create the form
        $this->form = new BootstrapFormBuilder('form_nps');
       // $this->form->generateAria();
       // $this->form->setFormTitle('Fechar Pedido');
        
        // create the form fields
        $type        = new TRadioGroup('type');
        $text        = new TText('text');
     
        $text->setSize('100%', 50);
        $text->placeholder = 'Digite aqui';
     

        

    
       
       
        
      
        $combo_items = array();
        $combo_items['1'] ='1';
        $combo_items['2'] ='2';
        $combo_items['3'] ='3';
        $combo_items['4'] ='4';
        $combo_items['5'] ='5';
        $combo_items['6'] ='6';
        $combo_items['7'] ='7';
        $combo_items['8'] ='8';
        $combo_items['9'] ='9';
        $combo_items['10'] ='10';
      
        $type->addItems($combo_items);
        $type->setLayout('horizontal');
        $type->setUseButton();
        $type->setSize('100%');
        
        // default value
       // $type->setValue('1');
        
        // fire change event
       self::onChangeType( ['type' => '12'] );
        
        // add the fields inside the form
       // $this->form->setColumnClasses(2, ['col-sm-4', 'col-sm-8']);
        $type->setChangeAction(new TAction(array($this, 'onChangeType')));
        $this->form->addContent([new TLabel('<i class="fas fa-question-circle"></i> Em uma escala de <b>zero a dez</b>, qual a probabilidade de você recomendar nosso produto a um amigo ou colega? *')]);
        
        $this->form->addFields(  [$type] );
$label1 = new TLabel('<i class="fas fa-question-circle"></i> O que você sente falta e o que foi decepcionante em sua experiência conosco?');
$label1->{'name'} = 'labeltext';
        $this->form->addContent([$label1]);
        $this->form->addFields(  [$text] );
      



  
       
      
        
       

       

         $this->form->addAction('Opinar e avaliar serviço', new TAction(array($this, 'avaliar')), 'fas: fa-comment-alt green');
        

       

         
       
       
   
        // wrap the page content using vertical box
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        //$vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->add($this->form);

     

        parent::add($vbox);
      
    }
    
    /**
     * Event executed when type is changed
     */
    public static function onChangeType($param)
    {
       
        echo '<pre>';


        print_r($param);

        echo '</pre>';


        if ($param['type'] < '10')
        {
            TQuickForm::showField('form_nps', 'text');
            TQuickForm::showField('form_nps', 'labeltext');
        
          //  TQuickForm::hideField('form_checkout', 'itens');
         
        }
        else
        {
            TQuickForm::hideField('form_nps', 'text');
            TQuickForm::hideField('form_nps', 'labeltext');
       
        }
    }

    public function avaliar($param){

        
        
    }

  

    
}
