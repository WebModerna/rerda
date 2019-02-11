<?php
  class CsvWrite {
 
 private $id_lang;
 private $feeddir;     
 private $rates;
 private $mode;
 private $imgdir;
 
 public function __construct( $feeddir, $id_lang, $rates=null, $imgdir=null) {
     $this->feeddir=$feeddir;
     
     $this->id_lang=$id_lang;
     if(!is_null($rates)) {
     if(is_array($rates)) {
         foreach($rates as $r) {
           $this->rates[$r['rate']]= $r['id_tax'];
         }
     }
     else {
        $this->rates=(int) $rates;    
     }
     }
     $this->imgdir=$imgdir;
    // $this->stamp=date('ymdHi');
 }
 
public function createProducts($products,$from, $description=0) {
    $this->mode='product';
    $path=$this->_getPath($from);
     $fp=fopen($path, "w+");
    if(!$fp) {
    die ("failed to open ".$path);  
    }
 
 
 $keys=$this->_getKeys();
if($description)    
    $this->_collumnDescription($fp);
    
    foreach ($products AS $product)
    { 
    $row=array();
  
    $categories=array();
    foreach($product['categories'] as $category) {
         $categories[] =$category['id_category'];
    }

     $product['categories']=implode(',', $categories);
     $images = Image::getImages(intval($this->id_lang), $product['id_product']); 
     $imageurls=array();
     foreach($images as $image) {
          $imageurls[]=($this->imgdir.'/'.$image['id_product']."-".$image['id_image']).".jpg";
     } 
     if(is_array($imageurls)) {
     $product['imageurls']=implode(',', $imageurls);
     }  
     
     $product['tax_rate']=is_array($this->rates)?$this->rates[$product['tax_rate']]:$this->rates;
    
    $this->_writeData( $product, $fp);
  
    }

fclose($fp);   
         }
      
 
      
/*      
 public function createCategories($categories, $description) {  
 
$this->mode='category';
$path =$this->_getPath();

$fp=fopen($path, "w+");
if(!$fp) {
    die ("failed to open ".$path);  
}
if($description)    
    $this->_collumnDescription($fp);  
    $row=array();
   
   foreach($categories as $category) { 
       $this->_writeData( $category, $fp); 
   }  
   
   fclose($fp);  
  }
 */ 

  
      
 public function createItems($items, $mode, $description) {  
 
$this->mode=$mode;
$path =$this->_getPath();

$fp=fopen($path, "w+");
if(!$fp) {
    die ("failed to open ".$path);  
}
if($description)    
    $this->_collumnDescription($fp);  
    $row=array();
   
   foreach($items as $item) { 
       $this->_writeData( $item, $fp); 
   }  
   
   fclose($fp);  
  }
  
  
  private function   _writeData($array,  $fp) {
      
       $keys=$this->_getKeys();    

      
     $row=array();
       for($i=0; $i<count($keys); $i++) {
        
       if(isset($array[$keys[$i]]) ) {
            $row[$keys[$i]]=$array[$keys[$i]];
           
       }
       else {
          $row[$keys[$i]]=""; 
       }
    }  
    
       foreach($row as $item) {
       if(is_numeric($item)) {
          $s.='"'.$item.'";'; 
       }
       elseif(empty($item)) {
          $s.='"";'; 
       }
       else {
          $item=str_replace(array("\r\n", "\r", "\n", "\t"), ' ',  $item); 
          $item=str_replace('"', '""',   $item);  
          $s.='"'.$item.'";'; 
       }
    
    }
    
    $s=substr($s,0,strlen($s)-1). chr(10);
   
     
         fputs($fp,  $s);    
  
  }
  
  
  private function _getKeys() {
      switch($this->mode) {
         case 'category':
           return   array('id_category', 'active', 'name', 'id_parent',  'is_root_category', 'description', 'meta_title', 'meta_keywords', 'meta_description', 'link_rewrite');         
           case 'manufacturer':
           return   array('id_manufacturer', 'active', 'name', 'description',  'short_description', 'meta_title', 'meta_keywords', 'meta_description');  
             case 'supplier':
           return   array('id_supplier', 'active', 'name', 'description',  'meta_description', 'meta_title', 'meta_keywords');    
            case 'customer':
           return   array('id_customer', 'active', 'id_gender',  'email',  'passwd',  'birthday','lastname', 'firstname','newsletter','optin');    
         case 'address': // maji v ni bordel
           return   array('id_address', 'alias', 'active', 'email',  'id_manufacturer', 'id_supplier', 
           'company',  'lastname',  'firstname', 'address1', 'address2',  'postcode',  'city',  
           'id_country',    'id_state', 'other', 'phone', 'phone_mobile', 'vat_number');        
        case 'product':
           return   array('id_product', 'active','name', 'categories',  'price',  'tax_rate',  'wholesale_price',   'on_sale', 'reduction_amount', 'reduction_percent','reduction_from','reduction_to',   'reference',  'supplier_reference',    'id_supplier',   'id_manufacturer',    'ean13', 'upc', 'ecotax',  'weight', 'quantity','description_short',   'description',  'tags',  'meta_title',    'meta_keywords', 'meta_description',      'link_rewrite', 'instocktext','backordertext','imageurls','features');
 
      }
      
  }
  
  
  private function _collumnDescription($fp) {
     $keys=$this->_getKeys();
     $arr=array();
     while(list($key,$val)=each($keys)) {
        $arr[$val]=$val;   
     }
     $this->_writeData($arr, $fp); 
  }
  
  private function _getPath($from=null) {
       $path =$this->mode.'.csv';
      
       if($from && is_numeric($from))
           $path =sprintf('%03d',$from).'_'.$path;

       $path =$this->feeddir."/".$path;     
           
            if(file_exists($path))
            unlink($path);
            return $path;
  }
  }
?>
