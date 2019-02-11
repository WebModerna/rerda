<?php
 /*
* 2011 PrestaHost.cz 
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
*  @author PrestaHost.cz  <info@prestahost.cz>
*  @copyright  2007-2011 PrestaHost.cz
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

  class CsvExport extends Module
{
    
   private $config;
   private $id_lang;
  
    public function __construct()
    {
      
        
        $this->name = 'csvexport';
        $this->version = '0.2';
        $this->author = 'PrestaHost.cz';
        $this->need_instance = 0;
        if (version_compare(_PS_VERSION_, 1.4) >= 0)
            $this->tab = 'administration';
        else
            $this->tab = 'Products';
        parent::__construct();
        $this->displayName = $this->l('Export in CSV');
        $this->description = $this->l('Export your content in CSV format suitable for import to other Prestashop website.');
        
        $config = Configuration::getMultiple(array('CSV_STEP', 'CSV_MAX', 'CSV_RATE', 'CSV_FEEDDIR', 'CSV_IMGDIR'));
        $this->config=$config;
    }

  public  function install()
    {
        if (!parent::install())
            return false;
         Configuration::updateValue('CSV_STEP', 1000); 
         Configuration::updateValue('CSV_MAX',  10000);
         Configuration::updateValue('CSV_RATE',  1);
         Configuration::updateValue('CSV_FEEDDIR',  '../download');
         Configuration::updateValue('CSV_IMGDIR',  '../upload/p');
         
        return true;
   }
   
 public  function uninstall()
    {
        if (!Configuration::deleteByName('CSV_STEP') OR !Configuration::deleteByName('CSV_MAX') 
            OR !Configuration::deleteByName('CSV_RATE') 
            OR !Configuration::deleteByName('CSV_FEEDDIR')
            OR !Configuration::deleteByName('CSV_IMGDIR')
            OR !parent::uninstall())
            return false;
        return true;
    }
   
 
function getContent()
    {
        $this->_html = '<h2>'.$this->displayName.'</h2>';

        if (!empty($_POST))
        {
            //$this->_postValidation();
            if (!sizeof($this->_postErrors))
                $this->_postProcess();
            else
                foreach ($this->_postErrors AS $err)
                    $this->_html .= '<div class="alert error">'. $err .'</div>';
        }
        else
            $this->_html .= '<br />';

        $this->_displayCvsExport();
        $this->_displayForm();

        return $this->_html;
    } 
 
 
   
  private function _displayForm()
    {   
        $this->_html .=
        '<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
            <fieldset>
            <legend>'.$this->l('Settings').'</legend>';
            
        $this->_html .=$this->l('Products per csv file (1000 max)').'<input type="text" name="CSV_STEP" value="'.$this->config['CSV_STEP'].'"><br />';   
        $this->_html .=$this->l('Max total products (1000 max)').'<input type="text" name="CSV_MAX" value="'.$this->config['CSV_MAX'].'"><br />';   
        $this->_html .=$this->l('Default tax rate').'<input type="text" name="CSV_RATE" value="'.$this->config['CSV_RATE'].'">
        '.$this->l('Tax id used in the target shop').'<br />';  
        $this->_html .=$this->l('Where to save csv files').'<input type="text" name="CSV_FEEDDIR" value="'.$this->config['CSV_FEEDDIR'].'"><br />'; 
          $this->_html .=$this->l('Product images dir').'<input type="text" name="CSV_IMGDIR" value="'.$this->config['CSV_IMGDIR'].'">
        '.$this->l('Copy the images to this folder of the target shop, should differ from standard image directory ').'<br />';   
        
              
        $this->_html .=$this->l('Settings').'<input type="submit" name="btnSettings" value="'.$this->l('Save settings').'"><br />';     
        $this->_html .= '</fieldset>';   
         
        
       
         $this->_html .= '<br /><fieldset>';  
         $this->_html .= '<legend>'.$this->l('Create csv').'</legend>'; 
         $this->_html .='<input type="checkBox" name="chckDescription"';
         if(Tools::getValue('chckDescription')) {
              $this->_html .=' checked="checked"';
         }
         
         $this->_html .='>' .$this->l('Include column description').'<br />';
         
          $this->_html .='
        <input class="button" type="submit" name="btnCategories" value="'.$this->l('Create categories').'"> 
         <br /><br />'; 
         
          $this->_html .='
         <input type="submit"  class="button"  name="btnManufacturers" value="'.$this->l('Create manufacturers').'"> 
         <br /><br />'; 
         
         $this->_html .='
         <input type="submit"  class="button"  name="btnSuppliers" value="'.$this->l('Create suppliers').'"> 
         <br /><br />';
         
           $this->_html .='
         <input type="submit"  class="button"  name="btnCustomers" value="'.$this->l('Create customers').'"> 
         <br /><br />';
         
            $this->_html .='
         <input type="submit"  class="button"  name="btnAddresses" value="'.$this->l('Create adresses').'"> 
         <br /><br />';
         
         $this->_html .='<input type="submit"  class="button"   name="btnProducts" value="'.$this->l('Create products').'"><br /><br />'; 
         
        $this->_html .='</fieldset></form>';
        $this->_html .='<br /><br /><br /><fieldset><legend>'.$this->l('CSV files in ').$this->config['CSV_FEEDDIR'].'</legend>'.$this->_listFiles().'<fieldset>';
    } 
   
   private function _listFiles() {
       $retval='';
    if(is_dir($this->config['CSV_FEEDDIR'])) {
        if ($dh = opendir($this->config['CSV_FEEDDIR'])) {
            while (($file = readdir($dh)) !== false) { 
            if($file !='.' && $file !='..'  && substr($file, strlen($file)-3) =='csv') {
                $kb=number_format(filesize($this->config['CSV_FEEDDIR'].'/'.$file) / 1024,2);
                $retval.= $file.' <small><i>'.date('d.m.Y H:i', filemtime($this->config['CSV_FEEDDIR'].'/'.$file)).' '.$kb.' kB</i></small><br />';
            }
            }
            closedir($dh);
        }
    }
    return $retval;
   }
   
   private function _displayCvsExport()
    {
        $this->_html .= '<img src="../modules/cvsexport/cvsexport.jpg" style="float:left; margin-right:15px;"><b>'.$this->displayName.'</b><br /><br />'.$this->description.'<br />
       česká podpora Prestashopu: <b><a href="http://www.prestahost.cz">PrestaHost.cz </a></b>  <br /> 
       <br /><br />';
    }
   
   
   
   private function _postProcess()
    {
        if (Tools::isSubmit('btnSettings') )
        {
        $feeddir= Tools::getValue('CSV_FEEDDIR');
      
        $step=Tools::getValue('CSV_STEP');
        if($step > 1000) 
           $step=1000;
           
        $max=Tools::getValue('CSV_MAX');
        if($max > 10000) 
           $max=10000;
           
        $rate=Tools::getValue('CSV_RATE');
      
        if(is_dir($feeddir) &&
         Configuration::updateValue('CSV_STEP', $step) &&
         Configuration::updateValue('CSV_MAX',  $max) &&
         Configuration::updateValue('CSV_RATE',  $rate) &&
         Configuration::updateValue('CSV_FEEDDIR', $feeddir) &&
         Configuration::updateValue('CSV_IMGDIR', Tools::getValue('CSV_IMGDIR'))
        ) {
         $this->_html .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('Setting saved').'" />'.$this->l('Setting saved').'</div>'; 
         $this->config['CSV_FEEDDIR']=$feeddir;
         $this->config['CSV_RATE']=$rate;
         $this->config['CSV_MAX']=$max;
         $this->config['CSV_STEP']=$step;
          $this->config['CSV_IMGDIR']=Tools::getValue('CSV_IMGDIR'); 
        }
        else  {
         $this->_html .= '<div class="conf confirm"><img src="../img/admin/forbidden.gif" alt="'.$this->l('Setting not saved').'" />'.$this->l('Setting not saved').'</div>';  
        }
        }
        elseif(Tools::isSubmit('btnCategories') ) {
             $this->csvCategories();
        }
          elseif(Tools::isSubmit('btnManufacturers') ) {
             $this->csvManufacturers();
        }
          elseif(Tools::isSubmit('btnSuppliers') ) {
             $this->csvSuppliers();
        }
          elseif(Tools::isSubmit('btnCustomers') ) {
             $this->csvCustomers();
        }
          elseif(Tools::isSubmit('btnAddresses') ) {
             $this->csvAdresses();
        }
         elseif(Tools::isSubmit('btnProducts') ) {
              $this->csvProducts();
        }    
    }   
    
    
    private function   csvCategories() {
            $this->csvInit();
            require_once("CsvCategory.php");
              
            $categories=Category::getCategories($this->id_lang, false);
            $Cat=new CsvCategory();
            $categories=$Cat->lineariseCategories($categories);
            $Write=new CsvWrite( $this->config['CSV_FEEDDIR'], $this->id_lang, $this->config['rate']);
            $description=Tools::getValue('chckDescription')?1:0;
          //  $Write->createCategories($categories, $description);  
             $Write->createItems($categories, 'category', $description);   
    }
    
        private function   csvManufacturers() {
             $this->csvInit();
            require_once("CsvManufacturer.php");
             $Fetch=new CsvManufacturer();
            $items= $Fetch->getItems($this->id_lang);
            $Write=new CsvWrite( $this->config['CSV_FEEDDIR'], $this->id_lang, $this->config['rate']);
            $description=Tools::getValue('chckDescription')?1:0;
            $Write->createItems($items, 'manufacturer', $description);   
    }
    
         private function   csvSuppliers() {
             $this->csvInit();
            require_once("CsvSupplier.php");
             $Fetch=new CsvSupplier();
            $items= $Fetch->getItems($this->id_lang);
            $Write=new CsvWrite( $this->config['CSV_FEEDDIR'], $this->id_lang, $this->config['rate']);
            $description=Tools::getValue('chckDescription')?1:0;
            $Write->createItems($items, 'supplier', $description);   
    }
    
         private function   csvCustomers() {
             $this->csvInit();
            require_once("CsvCustomer.php");
             $Fetch=new CsvCustomer();
            $items= $Fetch->getItems($this->id_lang);
            $Write=new CsvWrite( $this->config['CSV_FEEDDIR'], $this->id_lang, $this->config['rate']);
            $description=Tools::getValue('chckDescription')?1:0;
            $Write->createItems($items, 'customer', $description);   
    }
    
        private function   csvAdresses() {
             $this->csvInit();
            require_once("CsvAddress.php");
             $Fetch=new CsvAddress();
            $items= $Fetch->getItems($this->id_lang);
            $Write=new CsvWrite( $this->config['CSV_FEEDDIR'], $this->id_lang, $this->config['rate']);
            $description=Tools::getValue('chckDescription')?1:0;
            $Write->createItems($items, 'address', $description);   
    }
    
    
     private function   csvProducts() {
        $this->csvInit();
        require_once("CsvPrices.php");
        
        $Write=new CsvWrite( $this->config['CSV_FEEDDIR'], $this->id_lang, $this->config['CSV_RATE'], $this->config['CSV_IMGDIR']);
        $CsvPrices=new CsvPrices();
        $from=0;
        $max =$this->config['max'] < 10000?$this->config['CSV_MAX'] : 10000;
        $step =$this->config['step'] < 1000?$this->config['CSV_STEP'] : 1000;
        $limit =$max>$step?$step:$max; 
        
        
        for($j=0; $j< $max; $j+=$step) {
                $from++;
              $products =Product::getProducts($this->id_lang,     $j,      $limit,  'id_product', 'asc',      false,                    false); 
               
                
           //        $products =$this->getProducts($this->id_lang,     $j,      $limit,  'id_product', 'asc',      false,                    true); 
            //     echo "$j  ... $limit <br />";
               
                if(empty($products))
                break;

                for($i=0;$i<count($products); $i++) {

                   
                      $prices=$CsvPrices->getPriceReduction($products[$i]['id_product']);
                     
                        if(is_array($prices)) {
                            while(list($key,$val)=each($prices)) {
                                if(is_string($key)) {
                                    if($key=='reduction_from' || $key=='reduction_to')
                                    $products[$i][$key]=substr($val,0,10); 
                                    else
                                    $products[$i][$key]=$val;
                                }
                            } 

                        }

                        $products[$i]['categories']= Db::getInstance()->ExecuteS('
                        SELECT  '._DB_PREFIX_.'category_lang.`id_category`
                        FROM 

                        `'._DB_PREFIX_.'category_product` LEFT JOIN `'._DB_PREFIX_.'category_lang`
                        ON `'._DB_PREFIX_.'category_product`.id_category =  `'._DB_PREFIX_.'category_lang`.id_category
                        WHERE `id_product` = '.$products[$i]['id_product'].' AND id_lang ='.$this->id_lang) ;
                 
                 if(strlen( $products[$i]['description_short']) > 800) {
                      $products[$i]['description_short']=strip_tags($products[$i]['description_short']);     
                      if(function_exists('mb_substr'))
                          $products[$i]['description_short']=mb_substr($products[$i]['description_short'], 0,800, 'UTF-8');
                      else
                         $products[$i]['description_short']=substr($products[$i]['description_short'], 0,800);
                 
                 }    
                        
                }
                $description=Tools::getValue('chckDescription')?1:0;   
                $Write->createProducts($products,$from, $description); 

        }
        
    }
    
    private function csvInit() {
        $this->id_lang=Configuration::get(PS_LANG_DEFAULT)?Configuration::get(PS_LANG_DEFAULT):6; 
        require_once("CsvWrite.php"); 
    }
    
    private  function getProducts($id_lang, $start, $limit, $orderBy, $orderWay, $id_category = false)
    {
        
        Db::getInstance()->Execute('SET NAMES \'utf8\''); 
        if (!Validate::isOrderBy($orderBy) OR !Validate::isOrderWay($orderWay))
            die (Tools::displayError());
        if ($orderBy == 'id_product' OR    $orderBy == 'price' OR    $orderBy == 'date_add')
            $orderByPrefix = 'p';
        elseif ($orderBy == 'name')
            $orderByPrefix = 'pl';
        elseif ($orderBy == 'position')
            $orderByPrefix = 'c';
         $sql='SELECT p.*, pl.* , t.`rate` AS tax_rate, m.`name` AS manufacturer_name, s.`name` AS supplier_name
        FROM `'._DB_PREFIX_.'product` p
        LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product`)
        LEFT JOIN `'._DB_PREFIX_.'tax` t ON (t.`id_tax` = p.`id_tax`)
        LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON (m.`id_manufacturer` = p.`id_manufacturer`)
        LEFT JOIN `'._DB_PREFIX_.'supplier` s ON (s.`id_supplier` = p.`id_supplier`)'.
        ($id_category ? 'LEFT JOIN `'._DB_PREFIX_.'category_product` c ON (c.`id_product` = p.`id_product`)' : '').'
        WHERE pl.`id_lang` = '.intval($id_lang).
        ($id_category ? ' AND c.`id_category` = '.$id_category : '').'
        ORDER BY '.(isset($orderByPrefix) ? $orderByPrefix.'.' : '').'`'.pSQL($orderBy).'` '.pSQL($orderWay).
        ($limit > 0 ? ' LIMIT '.intval($start).','.intval($limit) : '');   
        $rq = Db::getInstance()->ExecuteS($sql);
        if($orderBy == 'price')
        {
            Tools::orderbyPrice($rq,$orderWay);
        }
     //  echo $sql.'<br />';
        return ($rq);
    }
}



  

 






