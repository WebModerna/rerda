<?php
include(_PS_MODULE_DIR_.'deleteordersfree/deleteordersfree.php');

class deleteorderstab14 extends AdminTab
{
  	public function __construct(){
	    $this->deleteorders = new deleteordersfree();
	    return parent::__construct();
  	}

  	public function display(){
  		$this->deleteorders->displayAdvert();
			
		if (isset($_POST['idord'])){
			if (is_numeric($_POST['idord'])){
				$this->deleteorders->deleteorderbyid($_POST['idord']);
			}
		}		
		
		$this->deleteorders->token = $this->token;
		
		$this->deleteorders->displayinputid();
		$this->deleteorders->displayFooter();
  	}
}
?>