<?php

class Rsisql extends Module
{
    private $_html = '';
    private $_postErrors = array();

    public function __construct()
    {
        $this->name = 'rsisql';
        if (_PS_VERSION_ > "1.4.0.0" && _PS_VERSION_ < "1.5.0.0") {
            $this->tab = 'administration';
            $this->author = 'RSI';
            $this->need_instance = 1;
        } elseif (_PS_VERSION_ > "1.5.0.0") {
            $this->tab = 'administration';
            $this->author = 'RSI';
        } else {
            $this->tab = 'Tools';
        }
        $this->version = '2.0.0';
        if (_PS_VERSION_ > '1.6.0.0') {
            $this->bootstrap = true;
        }
        parent::__construct();

        $this->displayName = $this->l('RSI SQL BUDDY');
        $this->description = $this->l('Manage your databases  - www.catalogo-onlinersi.net');
    }

    public function install()
    {
        if (!Configuration::updateValue(
                'SQ_NBR',
                8
            ) OR !parent::install() OR !$this->registerHook('Home')
        ) {
            return false;
        }


        $nb = 10;

        return true;
    }

    public function getContent()
    {

        global $cookie, $currentIndex;
        global $varsq;
        if (_PS_VERSION_ < "1.5.0.0") {
            $varsq = "fdfgsd";
            $output = '
		<iframe src="../modules/rsisql/index.php" width="100%" height="900"></iframe>
			<center><a href="../modules/rsisql/moduleinstall.pdf">README</a></center><br/>
						<center><a href="../modules/rsisql/termsandconditions.pdf">TERMS</a></center><br/>
		';
            $output .= '<h2>'.$this->displayName.'</h2>';
            if (Tools::isSubmit('submitRsisql')) {

                @chmod(
                    "../modules/rsisql/index.php",
                    0700
                );
                $xmla = fopen(
                    "../modules/rsisql/servers.php",
                    "w"
                );
                fwrite(
                    $xmla,
                    ' <?php
    $this->servers = array(
                  \'rsisql\'   => array(\'text\' => \'Prestashop\', \'host\' => \''._DB_SERVER_.'\', \'port\' => \'3306\',
                                       \'user\' => \''._DB_USER_.'\', \'pass\' => \'\',
                                       \'init\' => "SET NAMES \'utf8\'")
          
				 );
?>
	
	'
                );


                if (isset($errors) AND sizeof($errors)) {
                    $output .= $this->displayError(
                        implode(
                            '<br />',
                            $errors
                        )
                    );
                } else {
                    $output .= $this->displayConfirmation($this->l('Settings updated'));
                }
            }


            return $output.$this->displayForm();
        } else {
            return $this->_displayInfo().$this->_displayAdds();


        }
    }

    private function _displayInfo()
    {
        return $this->display(
            __FILE__,
            'views/templates/hook/infos.tpl'
        );
    }

    private function _displayAdds()
    {
        $this->context->smarty->assign(
            array(
                'psversion' => _PS_VERSION_
            )
        );
        return $this->display(
            __FILE__,
            'views/templates/hook/adds.tpl'
        );
    }

    public function displayForm()
    {
        $output = '';
        $output .= "<hr/>";
        $output .= "Donate: Paypal rsi_2004@hotmail.com";
        $output .= "<hr/>";


        $nb = 10;
        /*
        // Getting data
            if ($contents = @file_get_contents($url))
            {
                if ($url) @$src = new XML_Feed_Parser($contents);
                $output .= '';
                for ($i = 0; isset($src) AND $i < ($nb ? $nb : 5); ++$i)
                {
                    @$item = $src->getEntryByOffset($i);
                    $output .= '<li><a href="'.(@$item->link).'">'.Tools::htmlentitiesUTF8(@$item->title).'</a></li>';
                }
            }*/
        return $output;
    }


}
