<?php
/**
 * Product Form
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class ProductForm extends TPage
{
    protected $form;
    
    // trait with saveFile, saveFiles, ...
    use Adianti\Base\AdiantiFileSaveTrait;
    
    function __construct()
    {
        parent::__construct();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Product');
        $this->form->setFormTitle('Cadastros de produtos');
        $this->form->setClientValidation(true);
        
        // create the form fields
        $id          = new TEntry('id');
        $description = new TEntry('description');
        $nome = new TEntry('nome');
        $stock       = new TEntry('stock');
        $sale_price  = new TEntry('sale_price');
        $preco_promocao  = new TEntry('preco_promocao');
        $unity       = new TCombo('unity');
        $photo_path  = new TImageCropper('photo_path');
        $photo_path->setSize(500, 500);
        $photo_path->setCropSize(500, 500);
        $images      = new TMultiFile('images');
        $tela   = new TCheckButton('tela_principal');
        $tela->setIndexValue(1);
        $tela->setUseSwitch(true, 'blue');

        $categoria    = new TDBCombo('categoria', 'samples', 'Category', 'id', 'name');
        
        // allow just these extensions
        $photo_path->setAllowedExtensions( ['gif', 'png', 'jpg', 'jpeg'] );
        $images->setAllowedExtensions( ['gif', 'png', 'jpg', 'jpeg'] );
        
        // enable progress bar, preview
        $photo_path->enableFileHandling();
       // $photo_path->enablePopover();
        
        // enable progress bar, preview, and gallery mode
        $images->enableFileHandling();
        $images->enableImageGallery();
        $images->enablePopover('Preview', '<img style="max-width:300px" src="download.php?file={file_name}">');
        
        $id->setEditable( FALSE );
        $unity->addItems( ['PC' => 'Peças', 'GR' => 'Grãos', 'UN' => 'Unidades (Controlar estoque)'] );
        $stock->setNumericMask(2, ',', '.', TRUE); // TRUE: process mask when editing and saving
        $sale_price->setNumericMask(2, ',', '.', TRUE); // TRUE: process mask when editing and saving
        $this->form->appendPage('Produtos');
        // add the form fields
        $this->form->addFields( [new TLabel('ID', 'red')],          [$id], [new TLabel('Mais vendido?', 'red')],          [$tela] );
        $this->form->addFields( [new TLabel('Nome')],  [$nome], [new TLabel('Categoria', 'red')], [$categoria] );
        $this->form->addFields( [new TLabel('Description', 'red')], [$description] );
        $this->form->addFields( [new TLabel('Stock', 'red')],       [$stock],
                                [new TLabel('Sale Price', 'red')],  [$sale_price], 
                                [new TLabel('Preço Promocional', 'red')],  [$preco_promocao] );
        $this->form->addFields( [new TLabel('Unity', 'red')],       [$unity] );
        $this->form->addFields( [new TLabel('Photo Path')],  [$photo_path] );

        $this->form->appendPage('Galeria');
       // $this->form->setTabAction(new TAction([$this, 'onSave']));
        $this->form->addFields( [new TLabel('Images')],  [$images] );
        
       // $id->setSize('50%');
        
        $description->addValidation('Description', new TRequiredValidator);
        $stock->addValidation('Stock', new TRequiredValidator);
        $sale_price->addValidation('Sale Price', new TRequiredValidator);
        $unity->addValidation('Unity', new TRequiredValidator);
        
        // add the actions
        $this->form->addAction( 'Save', new TAction([$this, 'onSave']), 'fa:save green');
        $this->form->addActionLink( 'Clear', new TAction([$this, 'onEdit']), 'fa:eraser red');
        $this->form->addActionLink( 'List', new TAction(['ProductList', 'onReload']), 'fa:table blue');

        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', 'ProductList'));
        $vbox->add($this->form);

        parent::add($vbox);
    }
    
    /**
     * Overloaded method onSave()
     * Executed whenever the user clicks at the save button
     */
    public function onSave()
    {
        try
        {
            TTransaction::open('samples');
            
            // form validations
          //  $this->form->validate();
            
            // get form data
            $data   = $this->form->getData();
            
            // store product
            $object = new Product;
            $object->fromArray( (array) $data);
            $object->store();
            
            // copy file to target folder
            $this->saveFile($object, $data, 'photo_path', 'files/images_capas');

                  
            $this->saveFiles($object, $data, 'images', 'files/images', 'ProductImage', 'image', 'product_id');
            
            // send id back to the form
            $data->id = $object->id;
            $dados_file = json_decode(urldecode($data->photo_path));

            if($dados_file){

                $data->photo_path = $dados_file->fileName;

            } 

       
           


         
            $this->form->setData($data);
            
            TTransaction::close();
            TToast::show('success', 'Salvando dados...', 'top right', 'far:check-circle' );

           // AdiantiCoreApplication::loadPage('ProductForm', 'onEdit', [ 'register_state' => 'false', 'key' => $data->id]);

        }
        catch (Exception $e)
        {
            $this->form->setData($this->form->getData());
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    public function onEdit($param)
    {
        try
        {
            if (isset($param['key']))
            {
                TTransaction::open('samples');
                $object = new Product( $param['key'] );
                $object->images = ProductImage::where('product_id', '=', $param['key'])->getIndexedArray('id', 'image');
                $this->form->setData($object);
                TTransaction::close();
                return $object;
            }
            else
            {
                $this->form->clear();
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    public function onSaveGaleria(){



    }
}
