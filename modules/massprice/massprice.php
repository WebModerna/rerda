<?php
/**
 * 2007-2014 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * No redistribute in other sites, or copy.
 *
 * @author    RSI <rsi_2004@hotmail.com>
 * @copyright 2007-2014 RSI
 * @license   http://catalogo-onlinersi.net
 */

class MassPrice extends Module
{
	private $_html = '';
	private $_postErrors = array();
	public function __construct()
	{
		$this->name       = 'massprice';
		$this->module_key = 'c22aa30c1a5d33d5e8c7d59a1f412e38';
		if (_PS_VERSION_ < '1.4.0.0')
			$this->tab = 'Tools';
		if (_PS_VERSION_ > '1.4.0.0' && _PS_VERSION_ < '1.5.0.0')
		{
			$this->tab           = 'administration';
			$this->author        = 'RSI';
			$this->need_instance = 1;
		}
		if (_PS_VERSION_ > '1.5.0.0')
		{
			$this->tab    = 'administration';
			$this->author = 'RSI';
		}
		if (_PS_VERSION_ > '1.6.0.0')
			$this->bootstrap = true;
		$this->version = '2.1.0';
		parent::__construct();
		$this->displayName = $this->l('Mass price update');
		$this->description = $this->l('Mass price update by category-www.catalogo-onlinersi.net');
		if (_PS_VERSION_ < '1.5')
			require(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');

	}

	public function install()
	{
		if (!Configuration::updateValue('MASSPRICE_NBR', 1) || !parent::install() || !$this->registerHook('header'))
			return false;
		if (!Configuration::updateValue('MASSPRICE_SKIP_CAT', 1))
			return false;
		if (!Configuration::updateValue('MASSPRICE_REQUIERED1', 0))
			return false;
		if (!Configuration::updateValue('MASSPRICE_MIN', ''))
			return false;
			if (!Configuration::updateValue('MASSPRICE_MAX', ''))
			return false;
		if (!Configuration::updateValue('MASSPRICE_SYMBOL', 0))
			return false;
		if (!Configuration::updateValue('MASSPRICE_COMB', 0))
			return false;
		if (!Configuration::updateValue('MASSPRICE_LAST', ''))
			return false;
		return true;
	}

	private function _displayInfo()
	{
		$this->context->smarty->assign('last', Configuration::get('MASSPRICE_LAST'));
		return $this->display(__FILE__, 'views/templates/hook/infos.tpl');
	}

	private function _displayAdds()
	{
		return $this->display(__FILE__, 'views/templates/hook/adds.tpl');
	}

	public function getConfigFieldsValues()
	{
			/*categories*/
	/*	$typec = Category::getCategories($this->context->language->id, true, false);
				$id_category = array();
		foreach ($typec as $type)
			if (!is_null(Tools::getValue('skipcat_'.(int)$type['id_category'])))
				$id_category['skipcat_'.(int)$type['id_category']] = true;

		$types2 = Category::getCategories($this->context->language->id, true, false);

		$id_category = array();
		foreach ($types2 as $type2)
			$id_category[] = $type2['id_category'];

		$id_category_post = array();
		foreach ($id_category as $id)
			if (Tools::getValue('skipcat_'.(int)$id))
				$id_category_post['skipcat_'.(int)$id] = true;

		$id_category_config = array();
		if ($confs2 = Configuration::get('MASSPRICE_SKIP_CAT'))
			$confs2 = explode(',', Configuration::get('MASSPRICE_SKIP_CAT'));
		else
			$confs2 = array();

		foreach ($confs2 as $conf2)
			$id_category_config['skipcat_'.(int)$conf2] = true;/*
		/*attributes*/
		/*categories*/
		
		/**/
		$fields_values = array(
			'nbr'        => Tools::getValue('nbr', Configuration::get('MASSPRICE_NBR')),
			'requiered1' => Tools::getValue('requiered1', Configuration::get('MASSPRICE_REQUIERED1')),
			'symbol'      => Tools::getValue('symbol', Configuration::get('MASSPRICE_SYMBOL')),
			'comb'     => Tools::getValue('comb', Configuration::get('MASSPRICE_COMB')),
				'min'      => Tools::getValue('min', Configuration::get('MASSPRICE_MIN')),
			'max'      => Tools::getValue('max', Configuration::get('MASSPRICE_MAX')),
			'last'          => Tools::getValue('last', Configuration::get('MASSPRICE_LAST')),
			'skipcat'      => Tools::getValue('skipcat', explode(',', Configuration::get('MASSPRICE_SKIP_CAT'))),
		);
		
		
		/*$fields_values = array_merge($fields_values, array_intersect($id_category_config, $id_category_config));*/
		
		return $fields_values;
	}
	
	public function postProcess()
	{
		$errors  = '';
		$_html   = '';
		$skipcat = '';
		$skipman = '';
		$skipatt = '';
		if (Tools::isSubmit('submitMassPrice'))
		{
			
			/*$types2      = Category::getCategories($this->context->language->id, true, false);
			$id_category = array();
			foreach ($types2 as $type2)
				if (Tools::getValue('skipcat_'.(int)$type2['id_category']))
					$id_category[] = $type2['id_category'];
					/*attributes*/
			

		
		/*	Configuration::updateValue('MASSPRICE_SKIP_CAT', implode(',', $id_category));*/

	if ($skipcat = Tools::getValue('skipcat'))
				Configuration::updateValue('MASSPRICE_SKIP_CAT', implode(',', $skipcat));
			elseif (Shop::getContext() == Shop::CONTEXT_SHOP || Shop::getContext() == Shop::CONTEXT_GROUP)
				Configuration::deleteFromContext('MASSPRICE_SKIP_CAT');
				
			if ($nbr = Tools::getValue('nbr'))
				Configuration::updateValue('MASSPRICE_NBR', $nbr);
			elseif (Shop::getContext() == Shop::CONTEXT_SHOP || Shop::getContext() == Shop::CONTEXT_GROUP)
				Configuration::deleteFromContext('MASSPRICE_NBR');

			if ($requiered1 = Tools::getValue('requiered1'))
				Configuration::updateValue('MASSPRICE_REQUIERED1', $requiered1);
			elseif (Shop::getContext() == Shop::CONTEXT_SHOP || Shop::getContext() == Shop::CONTEXT_GROUP)
				Configuration::deleteFromContext('MASSPRICE_REQUIERED1');

			if ($symbol = Tools::getValue('symbol'))
				Configuration::updateValue('MASSPRICE_SYMBOL', $symbol);
			elseif (Shop::getContext() == Shop::CONTEXT_SHOP || Shop::getContext() == Shop::CONTEXT_GROUP)
				Configuration::deleteFromContext('MASSPRICE_SYMBOL');
			if ($min = Tools::getValue('min'))
				Configuration::updateValue('MASSPRICE_MIN', $min);
			elseif (Shop::getContext() == Shop::CONTEXT_SHOP || Shop::getContext() == Shop::CONTEXT_GROUP)
				Configuration::deleteFromContext('MASSPRICE_MIN');
				
				if ($max = Tools::getValue('max'))
				Configuration::updateValue('MASSPRICE_MAX', $max);
			elseif (Shop::getContext() == Shop::CONTEXT_SHOP || Shop::getContext() == Shop::CONTEXT_GROUP)
				Configuration::deleteFromContext('MASSPRICE_MAX');

			if ($comb = Tools::getValue('comb'))
				Configuration::updateValue('MASSPRICE_COMB', $comb);
			elseif (Shop::getContext() == Shop::CONTEXT_SHOP || Shop::getContext() == Shop::CONTEXT_GROUP)
				Configuration::deleteFromContext('MASSPRICE_COMB');

				if ($last = Tools::getValue('last'))
				Configuration::updateValue('MASSPRICE_LAST', $last);
			elseif (Shop::getContext() == Shop::CONTEXT_SHOP || Shop::getContext() == Shop::CONTEXT_GROUP)
				Configuration::deleteFromContext('MASSPRICE_LAST');
			if ($symbol == 0)
			$sy = '-';
			if ($symbol == 1)
			$sy = '+';
			if ($symbol == 2)
			$sy = $this->l('New value');
			if ($requiered1 == 0)
			$re = $this->l('Amount');
			if ($requiered1 == 1)
			$re = '%';
			if ($comb == 0)
			$co = $this->l('Dont update combinations, only update base price');
			if ($comb == 1)
			$co = $this->l('Update base price and combination price');
			if ($comb == 2)
			$co = $this->l('Update combinations, dont update base price');
			$last = $this->l('Price increment:'.(float)$nbr.',Type of increment:'.$sy.' in '.$re.' - '.$co.' on categories ID:'.(string)Configuration::get('MASSPRICE_SKIP_CAT'));
		Configuration::updateValue('MASSPRICE_LAST', $last);
		$this->updateall();
		}
	
		return $this->_html;
	}
	public function renderForm()
	{
		/*manufacturers*/
	
		/*categories*/
			$root = Category::getRootCategory();
$selected_cat = explode(',', Configuration::get('MASSPRICE_SKIP_CAT'));
$tree = new HelperTreeCategories('categories-treeview');
$tree->setUseCheckBox(true)->setAttribute('is_category_filter', $root->id)->setRootCategory($root->id)->setSelectedCategories($selected_cat)->setUseSearch(true)->setInputName('skipcat');
$categoryTreeCol1 = $tree->render();

		$types2 = Category::getCategories($this->context->language->id, true, false);
		foreach ($types2 as $key => $type)
			$types2[$key]['label'] = $type['name'];
		/*attributes*/
		

		$options2             = array(
			array(
				'id_option' => '1',       // The value of the 'value' attribute of the <option> tag.
				'name'      => '+'   // The value of the text content of the  <option> tag.
			),
			array(
				'id_option' => '0',
				'name'      => '-'
			),
			array(
				'id_option' => '2',
				'name'      => $this->l('New value (dont use percentage or amount )')
			),
		);
		$options3             = array(
			array(
				'id_option' => '1',       // The value of the 'value' attribute of the <option> tag.
				'name'      => $this->l('Percentage')    // The value of the text content of the  <option> tag.
			),
			array(
				'id_option' => '0',
				'name'      => $this->l('Amount')
			),
		);
		$options4             = array(
		
			array(
				'id_option' => '0',
				'name'      => $this->l('Dont update combinations, only update base price')
			),
			

		);
		
		$fields_form          = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Configuration'),
					'icon'  => 'icon-cogs'
				),
				'input'  => array(
					array(
						'type'  => 'text',
						'label' => $this->l('Price value'),
						'name'  => 'nbr',
						'desc'  => $this->l('Percentage or amount to increase/decrease/new price'),
					),
					array(
						'type'    => 'select',
						'label'   => $this->l('Increase or decrease'),
						'name'    => 'symbol',
						'options' => array(
							'query' => $options2,
							'id'    => 'id_option',
							'name'  => 'name'
						)
					),
					array(
						'type'    => 'select',
						'label'   => $this->l('Type of update'),
						'name'    => 'requiered1',
						'options' => array(
							'query' => $options3,
							'id'    => 'id_option',
							'name'  => 'name'
						)
					),
					array(
						'type'  => 'text',
						'label' => $this->l('Min price range'),
						'name'  => 'min',
						'desc'  => $this->l('The minimun price range to update. The module updates base prices that are greater than this minimum (leave blank to skip)'),
					),
					array(
						'type'  => 'text',
						'label' => $this->l('Max price range'),
						'name'  => 'max',
						'desc'  => $this->l('The maximun price range to update. The module updates base prices that are less than this maximum (leave blank to skip)'),
					),
					array(
						'type'    => 'select',
						'label'   => $this->l('Update product combinations?'),
						'name'    => 'comb',
						'class'     => 'l',
						'options' => array(
							'query' => $options4,
							'id'    => 'id_option',
							'name'  => 'name'
							)
							),


							array(
'type'  => 'categories_select',
'label' => $this->l('Shop category to include'),
'desc'    => $this->l('Select the categories you want to change price for each product in selectec categories'),  
'name'  => 'skipcat',
'category_tree'  => $categoryTreeCol1 //This is the category_tree called in form.tpl
),
					
					
					
				),
				'submit' => array(
					'title' => $this->l('Update prices'),
				)
			),
		);
		$helper               = new HelperForm();
		$helper->show_toolbar = true;
		$helper->table                    = $this->table;
		$lang                             = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language    = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$this->fields_form                = array();
		$helper->identifier               = $this->identifier;
		$helper->submit_action            = 'submitMassPrice';
		$helper->currentIndex             = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token    = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = array(
			'fields_value' => $this->getConfigFieldsValues(),
			'languages'    => $this->context->controller->getLanguages(),
			'id_language'  => $this->context->language->id
		);
		return $helper->generateForm(array($fields_form));
	}
	public function displayForm()
	{
		$this->_html .= '
  <form action="'.$_SERVER['REQUEST_URI'].'" method="post" enctype="multipart/form-data">
            <fieldset><legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Settings').'</legend>
			<p><strong>'.$this->l('Last rule used: ').'</strong>'.Configuration::get('MASSPRICE_LAST').'</p>
                <label>'.$this->l('Price value').'</label>
                <div class="margin-form">
                    <input type="text" size="5" name="nbr" value="'.Tools::getValue('nbr', Configuration::get('MASSPRICE_NBR')).'" />
                    <p class="clear">'.$this->l('Percentage or amount to increase/decrease/new price').'</p>
                </div>
                <label>'.$this->l('Increase or decrease').'</label>
                <div class="margin-form">
                                    <select name="symbol" >
      <option value="1"'.((Configuration::get('MASSPRICE_SYMBOL') == '1') ? 'selected="selected"' : '').'>'.$this->l('+').'</option>
      <option value="0"'.((Configuration::get('MASSPRICE_SYMBOL') == '0') ? 'selected="selected"' : '').'>'.$this->l('-').'</option>
      <option value="2"'.((Configuration::get('MASSPRICE_SYMBOL') == '2') ? 'selected="selected"' : '').'>'.$this->l('New value (dont use percentage or amount    )').'</option>
    </select>
        </div>
            <label>'.$this->l('Type of update').'</label>
                <div class="margin-form">
                                    <select name="requiered1" >
      <option value="1"'.((Configuration::get('MASSPRICE_REQUIERED1') == '1') ? 'selected="selected"' : '').'>'.$this->l('Percentage').'</option>
      <option value="0"'.((Configuration::get('MASSPRICE_REQUIERED1') == '0') ? 'selected="selected"' : '').'>'.$this->l('Amount').'</option>
    </select>
        </div>
        <label>'.$this->l('Update product combinations?').'</label>
                <div class="margin-form">
                                    <select name="comb" >
      <option value="0"'.((Configuration::get('MASSPRICE_COMB') == '0') ? 'selected="selected"' : '').'>'.$this->l('Dont update combinations, only update base price').'</option>
   
    </select>
        </div>
		 <label>'.$this->l('Min price range').'</label>
                <div class="margin-form">
                    <input type="text" size="5" name="min" value="'.Tools::getValue('min', Configuration::get('MASSPRICE_MIN')).'" />
                    <p class="clear">'.$this->l('The minimun price range to update. The module updates base prices that are greater than this minimum (leave blank to skip)').'</p>
                </div>
				 <label>'.$this->l('Max price range').'</label>
                <div class="margin-form">
                    <input type="text" size="5" name="max" value="'.Tools::getValue('max', Configuration::get('MASSPRICE_MAX')).'" />
                    <p class="clear">'.$this->l('The maximun price range to update. The module updates base prices that are less than this maximum (leave blank to skip)').'</p>
                </div>
        ';
		$skipcat = Configuration::get('MASSPRICE_SKIP_CAT');

		if (!empty($skipcat))
			$skipcat_array = explode(',', $skipcat);
		else
			$skipcat_array = array();
		$this->_html .= '
                  <label>'.$this->l('Shop category to include').'</label>
                  <div class="margin-form">
                        <select name="skipcat[]" multiple="multiple" style="width:200px; height:300px">';
		$categories = Category::getCategories($this->context->language->id);
		ob_start();
		$this->recurseCategory($categories, $categories[0][1], 1, $skipcat_array);
		$this->_html .= ob_get_contents();
		ob_end_clean();
		$this->_html .= '
                        </select>
                        <p class="clear">'.$this->l('Select the categories you want to change price for each product in selectec categories').'</p>

                   </div>

               
	
						
        <center><input type="submit" name="submitMassPrice" value="'.$this->l('Update').'" class="button" /></center>

    <center><a href="../modules/massprice/moduleinstall.pdf">README</a></center><br/>
        <center><p><a href="../modules/massprice/termsandconditions.pdf">TERMS</a></p></center>
		 <center>  <p>Follow  us:</p></center>
     <center><p><a href="https://www.facebook.com/ShackerRSI" target="_blank"><img src="../modules/massprice/views/img/facebook.png" style="  width: 64px;margin: 5px;" /></a>
        <a href="https://twitter.com/prestashop_rsi" target="_blank"><img src="../modules/massprice/views/img/twitter.png" style="  width: 64px;margin: 5px;" /></a>
         <a href="https://www.pinterest.com/prestashoprsi/" target="_blank"><img src="../modules/massprice/views/img/pinterest.png" style="  width: 64px;margin: 5px;" /></a>
           <a href="https://plus.google.com/+shacker6/posts" target="_blank"><img src="../modules/massprice/views/img/googleplus.png" style="  width: 64px;margin: 5px;" /></a>
            <a href="https://www.linkedin.com/profile/view?id=92841578" target="_blank"><img src="../modules/massprice/views/img/linkedin.png" style="  width: 64px;margin: 5px;" /></a>
			<a href="https://www.youtube.com/channel/UCBFSNtJpjYj4zLX9nO_oZk" target="_blank"><img src="../modules/massprice/views/img/youtube.png" style="  width: 64px;margin: 5px;" /></a>
			</p></center>
			<br/>
		<p>Video:</p><br/>
		<iframe width="640" height="360" src="https://www.youtube.com/embed/JsPIvhmFSRI?rel=0&amp;showinfo=0" frameborder="0" allowfullscreen></iframe><br/>
		<p>Other products:</p><br/>
			<object type="text/html" data="http://catalogo-onlinersi.net/modules/productsanywhere/images.php?idproduct=&desc=yes&buy=yes&type=home_default&price=yes&style=false&color=10&color2=40&bg=ffffff&width=800&height=310&lc=000000&speed=5&qty=15&skip=29,14,42,44,45&sort=1" width="800" height="310" style="border:0px #066 solid;"></object>

          </fieldset>
          </form>
		  <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
			<fieldset><legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Contribute').'</legend>
				<p class="clear">'.$this->l('You can contribute with a donation if our free modules and themes are usefull for you. Clic on the link and support us!').'</p>
				<p class="clear">'.$this->l('For more modules & themes visit: www.catalogo-onlinersi.com.ar').'</p>
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="HMBZNQAHN9UMJ">
<input type="image" src="https://www.paypalobjects.com/WEBSCR-640-20110401-1/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/WEBSCR-640-20110401-1/en_US/i/scr/pixel.gif" width="1" height="1">
	</fieldset>
</form>
';

		return $this->_html;
	}

	public function updateall()
	{
		$errors       = '';
		$nbr          = Configuration::get('MASSPRICE_NBR');
		$min          = Configuration::get('MASSPRICE_MIN');
			$max          = Configuration::get('MASSPRICE_MAX');
		$requiered1   = Configuration::get('MASSPRICE_REQUIERED1');
		$symbol       = Configuration::get('MASSPRICE_SYMBOL');
		$comb         = Configuration::get('MASSPRICE_COMB');
		$skipcat      = Configuration::get('MASSPRICE_SKIP_CAT');
		$skipcategory = Configuration::get('MASSPRICE_SKIP_CAT');
	
	
		($GLOBALS['___mysqli_ston'] = mysqli_connect(_DB_SERVER_, _DB_USER_, _DB_PASSWD_)) || die(((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_error($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
		mysqli_query($GLOBALS['___mysqli_ston'], 'SET NAMES UTF8'); //this is needed for UTF 8 characters - multilanguage
		((bool)mysqli_query($GLOBALS['___mysqli_ston'], 'USE '.constant('_DB_NAME_'))) || die(((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_error($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
		$sorgudc = mysqli_query($GLOBALS['___mysqli_ston'], '
        SELECT p.*, pl.*, cp.* 
        FROM `'._DB_PREFIX_.'product` p
        LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON p.`id_product` = cp.`id_product`
		LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = '.$this->context->language->id.')
		
        WHERE cp.`id_category`  IN ('.(string)$skipcategory.') 
			'.(((string)$min != null) ? ' AND p.`price` >= ('.(string)$min.')' : '').'
		'.(((string)$max != null) ? ' AND p.`price` <= ('.(string)$max.')' : '').'
        GROUP BY p.`id_product`');
		$veridc   = mysqli_fetch_assoc($sorgudc);
		$rowcount = mysqli_num_rows($sorgudc);
		$sorgudc5 = mysqli_query($GLOBALS['___mysqli_ston'], '
        SELECT p.*, pl.*, cp.*
        FROM `'._DB_PREFIX_.'product'.((_PS_VERSION_ > '1.5.0.0') ? '_shop' : '').'` p
        LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON p.`id_product` = cp.`id_product`
		LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = '.$this->context->language->id.')
        '.((_PS_VERSION_ > '1.5.0.0') ? 'LEFT JOIN `'._DB_PREFIX_.'product` ps ON p.`id_product` = ps.`id_product`' : '').'
		
        WHERE cp.`id_category`  IN ('.(string)$skipcategory.') '.((_PS_VERSION_ > '1.5.0.0') ? 'AND p.`id_shop` = '.(int)$this->context->shop->id : '').'
			'.(((string)$min != null) ? ' AND p.`price` >= ('.(string)$min.')' : '').'
		'.(((string)$max != null) ? ' AND p.`price` <= ('.(string)$max.')' : '').'
        GROUP BY p.`id_product`');
		$veridc5    = mysqli_fetch_assoc($sorgudc5);
		$rowcount2	= mysqli_num_rows($sorgudc);
		$sorgu4     = 'SELECT * FROM `'._DB_PREFIX_.'product_attribute_combination` GROUP BY id_product_attribute';
		$resultado4 = mysqli_query($GLOBALS['___mysqli_ston'], $sorgu4);
		$veri4      = mysqli_fetch_array($resultado4);
		$varrr = '  SELECT p.*, pl.*, cp.*
        FROM `'._DB_PREFIX_.'product` p
        LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON p.`id_product` = cp.`id_product`
		LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = '.$this->context->language->id.')
	
        WHERE cp.`id_category`  IN ('.(string)$skipcategory.')
			'.(((string)$min != null) ? ' AND p.`price` >= ('.(string)$min.')' : '').'
		'.(((string)$max != null) ? ' AND p.`price` <= ('.(string)$max.')' : '').'
        GROUP BY p.`id_product`';
		
		$i = 0;
		do
		{
			$i++;
			$sorgudc2 = mysqli_query($GLOBALS['___mysqli_ston'], '
        SELECT *
        FROM `'._DB_PREFIX_.'product'.((_PS_VERSION_ > '1.5.0.0') ? '_shop' : '').'`
        WHERE `id_product` =  '.(int)$veridc['id_product'].' '.((_PS_VERSION_ > '1.5.0.0') ? 'AND `id_shop` = '.(int)$this->context->shop->id : '').'
        ');

			$veridc2  = mysqli_fetch_assoc($sorgudc2);
			//var_dump($a);
		
		

			/**/
			// Db::getInstance()->Execute("RESET QUERY CACHE;");

			if ($rowcount > 0 || $rowcount2 > 0)
			{
			if ($symbol == 1 && $requiered1 == 0)
			{
				$price = $veridc['price'] + $nbr;
				if (_PS_VERSION_ > '1.5.0.0')
				{
					if ($comb != '2')
					{
						Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product` SET `price` = '.(float)$price.' WHERE `'._DB_PREFIX_.'product`.`id_product` = '.(int)$veridc['id_product'].';');
						Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product_shop` SET `price` = '.(float)$price.' WHERE `'._DB_PREFIX_.'product_shop`.`id_product` = '.(int)$veridc['id_product'].' AND `'._DB_PREFIX_.'product_shop`.`id_shop` = '.(int)$this->context->shop->id.';');
						$this->_html .= $this->displayConfirmation('UPDATE Product <a href="../index.php?id_product='.(int)$veridc['id_product'].'&controller=product&id_lang='.(int)$this->context->language->id.'" target="_blank">'.(string)$veridc['name'].'</a>  to price '.(float)$price.'');

					}
					if ($comb == '1' || $comb == '2')
					{	
					
						do
						{
						
							$pricea = $veri3['price'] + $nbr;
							if ((int)$veri3['id_product_attribute'] != null)
							{
									


								
							}
							else
							$this->_html .= $this->displayConfirmation('No combination match (product dont have this combination or any combination)');
						
							
						} while ($veri3 = mysqli_fetch_assoc($resultado3));
					}

				}
				if (_PS_VERSION_ < '1.5.0.0')
				{
				if ($comb != '2')
{
					Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product` SET `price` = '.(float)$price.' WHERE `'._DB_PREFIX_.'product`.`id_product` = '.(int)$veridc['id_product'].' ;');
						$this->_html .= $this->displayConfirmation('UPDATE Product <a href="../product.php?id_product='.(int)$veridc['id_product'].'" target="_blank">'.(string)$veridc['name'].'</a>  to price '.(float)$price.'');
}
				if ($comb == '1' || $comb == '2')
				{
					do
					{

						if ((int)$veri3['id_product_attribute'] != null)
						{
							
						}
						else
							$this->_html .= $this->displayConfirmation('No combination match (product dont have this combination or any combination)');
					} while ($veri3f = mysqli_fetch_assoc($resultado3f));
				}
				}
			}

			if ($symbol == 0 && $requiered1 == 0)
			{
				$price = $veridc['price'] - $nbr;
				if (_PS_VERSION_ > '1.5.0.0')
				{
					if ($comb != '2')
					{
						Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product` SET `price` = '.(float)$price.' WHERE `'._DB_PREFIX_.'product`.`id_product` = '.(int)$veridc['id_product'].';');
						Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product_shop` SET `price` = '.(float)$price.' WHERE `'._DB_PREFIX_.'product_shop`.`id_product` = '.(int)$veridc['id_product'].' AND `'._DB_PREFIX_.'product_shop`.`id_shop` = '.(int)$this->context->shop->id.';');
												$this->_html .= $this->displayConfirmation('UPDATE Product <a href="../index.php?id_product='.(int)$veridc['id_product'].'&controller=product&id_lang='.(int)$this->context->language->id.'" target="_blank">'.(string)$veridc['name'].'</a>  to price '.(float)$price.'');
					}
					if ($comb == '1' || $comb == '2')
					{
						do
						{
							// echo $sorgu3.'<br/>'.(int)$veri3['id_product_attribute'].'-'.$veri3['id_product'].'<br/>';
							$pricea = $veri3['price'] - $nbr;
							if ((int)$veri3['id_product_attribute'] != null)
							{
						

							}
							else
							$this->_html .= $this->displayConfirmation('No combination match (product dont have this combination or any combination)');
						} while ($veri3 = mysqli_fetch_assoc($resultado3));
					}
				}
				if (_PS_VERSION_ < '1.5.0.0')
				{
				if ($comb != '2')
{
					Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product` SET `price` = '.(float)$price.' WHERE `'._DB_PREFIX_.'product`.`id_product` = '.(int)$veridc['id_product'].' ;');
					$this->_html .= $this->displayConfirmation('UPDATE Product <a href="../product.php?id_product='.(int)$veridc['id_product'].'" target="_blank">'.(string)$veridc['name'].'</a>  to price '.(float)$price.'');
}
				if ($comb == '1' || $comb == '2')
				{
					do
					{
						$pricea = $veri3f['price'] - $nbr;
						if ((int)$veri3['id_product_attribute'] != null)
{
						
}

						else
							$this->_html .= $this->displayConfirmation('No combination match (product dont have this combination or any combination)');
					} while ($veri3f = mysqli_fetch_assoc($resultado3f));
				}
				}
			}

			if ($symbol == 1 && $requiered1 == 1)
			{
				$per   = ($veridc['price'] * $nbr) / 100;
				$price = $veridc['price'] + $per;
				if (_PS_VERSION_ > '1.5.0.0')
				{
					if ($comb != '2')
					{
						Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product` SET `price` = '.(float)$price.' WHERE `'._DB_PREFIX_.'product`.`id_product` = '.(int)$veridc['id_product'].';');
						Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product_shop` SET `price` = '.(float)$price.' WHERE `'._DB_PREFIX_.'product_shop`.`id_product` = '.(int)$veridc['id_product'].' AND `'._DB_PREFIX_.'product_shop`.`id_shop` = '.(int)$this->context->shop->id.';');
											$this->_html .= $this->displayConfirmation('UPDATE Product <a href="../index.php?id_product='.(int)$veridc['id_product'].'&controller=product&id_lang='.(int)$this->context->language->id.'" target="_blank">'.(string)$veridc['name'].'</a>  to price '.(float)$price.'');


					}
					if ($comb == '1' || $comb == '2')
					{
						do
						{
							//echo $sorgu3.'<br/>'.(int)$veri3['id_product_attribute'].'-'.$veri3['id_product'].'<br/>';
							$pera   = ($veri3['price'] * $nbr) / 100;
							$pricea = $veri3['price'] + $pera;
							if ((int)$veri3['id_product_attribute'] != null)
							{
						

							}
							else
							$this->_html .= $this->displayConfirmation('No combination match (product dont have this combination or any combination)');
						} while ($veri3 = mysqli_fetch_assoc($resultado3));
					}
				}
				if (_PS_VERSION_ < '1.5.0.0')
				{
				if ($comb != '2')
{
					Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product` SET `price` = '.(float)$price.' WHERE `'._DB_PREFIX_.'product`.`id_product` = '.(int)$veridc['id_product'].' ;');
					$this->_html .= $this->displayConfirmation('UPDATE Product <a href="../product.php?id_product='.(int)$veridc['id_product'].'" target="_blank">'.(string)$veridc['name'].'</a>  to price '.(float)$price.'');
}

				if ($comb == '1' || $comb == '2')
				{
					do
					{
						$pera   = ($veri3f['price'] * $nbr) / 100;
						$pricea = $veri3f['price'] + $pera;
						if ((int)$veri3['id_product_attribute'] != null)
{
						}

						else
							$this->_html .= $this->displayConfirmation('No combination match (product dont have this combination or any combination)');
					} while ($veri3f = mysqli_fetch_assoc($resultado3f));
				}
				}
			}
			if ($symbol == 0 && $requiered1 == 1)
			{
				$per   = ($veridc['price'] * $nbr) / 100;
				$price = $veridc['price'] - $per;
				if (_PS_VERSION_ > '1.5.0.0')
				{
					if ($comb != '2')
					{
						Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product_shop` SET `price` = '.(float)$price.' WHERE `'._DB_PREFIX_.'product_shop`.`id_product` = '.(int)$veridc['id_product'].' AND `'._DB_PREFIX_.'product_shop`.`id_shop` = '.(int)$this->context->shop->id.';');
						Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product` SET `price` = '.(float)$price.' WHERE `'._DB_PREFIX_.'product`.`id_product` = '.(int)$veridc['id_product'].';');
											$this->_html .= $this->displayConfirmation('UPDATE Product <a href="../index.php?id_product='.(int)$veridc['id_product'].'&controller=product&id_lang='.(int)$this->context->language->id.'" target="_blank">'.(string)$veridc['name'].'</a>  to price '.(float)$price.'');


					}
					if ($comb == '1' || $comb == '2')
					{
						do
						{
							$pera   = ($veri3['price'] * $nbr) / 100;
							$pricea = $veri3['price'] - $pera;
							//echo $sorgu3.'<br/>'.(int)$veri3['id_product_attribute'].'-'.$veri3['id_product'].'<br/>';
							if ((int)$veri3['id_product_attribute'] != null)
							{

							}
							else
							$this->_html .= $this->displayConfirmation('No combination match (product dont have this combination or any combination)');
						} while ($veri3 = mysqli_fetch_assoc($resultado3));
					}
				}
				if (_PS_VERSION_ < '1.5.0.0')
				{
				if ($comb != '2')
{
					Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product` SET `price` = '.(float)$price.' WHERE `'._DB_PREFIX_.'product`.`id_product` = '.(int)$veridc['id_product'].' ;');
					$this->_html .= $this->displayConfirmation('UPDATE Product <a href="../product.php?id_product='.(int)$veridc['id_product'].'" target="_blank">'.(string)$veridc['name'].'</a>  to price '.(float)$price.'');
}

				if ($comb == '1' || $comb == '2')
				{
					do
					{
						$pera   = ($veri3f['price'] * $nbr) / 100;
						$pricea = $veri3f['price'] - $pera;
						if ((int)$veri3['id_product_attribute'] != null)
							{
						
							}
						else
							$this->_html .= $this->displayConfirmation('No combination match (product dont have this combination or any combination)');
					} while ($veri3f = mysqli_fetch_assoc($resultado3f));
				}
				}
			}
			if ($symbol == 2 && $requiered1 == 1)
			{
				$per   = $nbr;
				$price = $per;
				if (_PS_VERSION_ > '1.5.0.0')
				{
					if ($comb != '2')
					{
						Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product` SET `price` = '.(float)$price.' WHERE `'._DB_PREFIX_.'product`.`id_product` = '.(int)$veridc['id_product'].';');
						Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product_shop` SET `price` = '.(float)$price.' WHERE `'._DB_PREFIX_.'product_shop`.`id_product` = '.(int)$veridc['id_product'].' AND `'._DB_PREFIX_.'product_shop`.`id_shop` = '.(int)$this->context->shop->id.';');
												$this->_html .= $this->displayConfirmation('UPDATE Product <a href="../index.php?id_product='.(int)$veridc['id_product'].'&controller=product&id_lang='.(int)$this->context->language->id.'" target="_blank">'.(string)$veridc['name'].'</a>  to price '.(float)$price.'');

					}
					if ($comb == '1' || $comb == '2')
					{
						do
						{
							$pera   = $nbr;
							$pricea = $per;
							if ((int)$veri3['id_product_attribute'] != null)
							{
							
							}
							else
							$this->_html .= $this->displayConfirmation('No combination match (product dont have this combination or any combination)');

						} while ($veri3 = mysqli_fetch_assoc($resultado3));
					}
				}
				if (_PS_VERSION_ < '1.5.0.0')
				{
				if ($comb != '2')
{
					Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product` SET `price` = '.(float)$price.' WHERE `'._DB_PREFIX_.'product`.`id_product` = '.(int)$veridc['id_product'].' ;');
					$this->_html .= $this->displayConfirmation('UPDATE Product <a href="../product.php?id_product='.(int)$veridc['id_product'].'" target="_blank">'.(string)$veridc['name'].'</a>  to price '.(float)$price.'');
}

				if ($comb == '1' || $comb == '2')
				{
					do
					{
						$pera   = $nbr;
						$pricea = $per;
						if ((int)$veri3['id_product_attribute'] != null)
{
						
}

						else
							$this->_html .= $this->displayConfirmation('No combination match (product dont have this combination or any combination)');
					} while ($veri3f = mysqli_fetch_assoc($resultado3f));
				}
				}
			}
			if ($symbol == 2 && $requiered1 == 0)
			{
				$price = $nbr;
				if (_PS_VERSION_ > '1.5.0.0')
				{
					if ($comb != '2')
					{
						Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product` SET `price` = '.(float)$price.' WHERE `'._DB_PREFIX_.'product`.`id_product` = '.(int)$veridc['id_product'].';');
						Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product_shop` SET `price` = '.(float)$price.' WHERE `'._DB_PREFIX_.'product_shop`.`id_product` = '.(int)$veridc['id_product'].' AND `'._DB_PREFIX_.'product_shop`.`id_shop` = '.(int)$this->context->shop->id.';');
											$this->_html .= $this->displayConfirmation('UPDATE Product <a href="../index.php?id_product='.(int)$veridc['id_product'].'&controller=product&id_lang='.(int)$this->context->language->id.'" target="_blank">'.(string)$veridc['name'].'</a>  to price '.(float)$price.'');

					}
					if ($comb == '1' || $comb == '2')
					{
						do
						{
							//echo $sorgu3.'<br/>'.(int)$veri3['id_product_attribute'].'-'.$veri3['id_product'].'<br/>';
							$pricea = $nbr;
							if ((int)$veri3['id_product_attribute'] != null)
							{
								if ((int)$veri3['id_product_attribute'] != null)
								{
								

								}
								else
							$this->_html .= $this->displayConfirmation('No combination match (product dont have this combination or any combination)');
							}
						} while ($veri3 = mysqli_fetch_assoc($resultado3));
					}
				}
				if (_PS_VERSION_ < '1.5.0.0')
				{
				if ($comb != '2')
{
					Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product` SET `price` = '.(float)$price.' WHERE `'._DB_PREFIX_.'product`.`id_product` = '.(int)$veridc['id_product'].' ;');
					$this->_html .= $this->displayConfirmation('UPDATE Product <a href="../product.php?id_product='.(int)$veridc['id_product'].'" target="_blank">'.(string)$veridc['name'].'</a>  to price '.(float)$price.'');
}
				if ($comb == '1' || $comb == '2')
				{
					do
					{
						//echo $sorgu3f.'<br/>'.$veri3f['id_product_attribute'].'-'.$veri3f['id_product'].'<br/>';
						$pricea = $nbr;
						if ($veri3f['id_product_attribute'] != null)
{
						
}
						else
							$this->_html .= $this->displayConfirmation('No combination match (product dont have this combination or any combination)');
					} while ($veri3f = mysqli_fetch_assoc($resultado3f));
				}
				}
			}
			}//$this->_html .= $errors == '' ? $this->displayConfirmation('Products updated successfully') : @$errors;
			else
			$this->_html .= $this->displayConfirmation('No products match');
		} while ($veridc = mysqli_fetch_assoc($sorgudc));
	}

	public function getContent()
	{
		$errors = '';
		if (_PS_VERSION_ < '1.6.0.0')
		{
		if (Tools::isSubmit('submitMassPrice'))
		{
			$nbr = Tools::getValue('nbr');
			$requiered1 = Tools::getValue('requiered1');
			$symbol     = Tools::getValue('symbol');
			$comb       = Tools::getValue('comb');
			$min = Tools::getValue('min');
			$max = Tools::getValue('max');
			if ($symbol == 0)
			$sy = '-';
			if ($symbol == 1)
			$sy = '+';
			if ($symbol == 2)
			$sy = $this->l('New value');
			if ($requiered1 == 0)
			$re = $this->l('Amount');
			if ($requiered1 == 1)
			$re = '%';
			if ($comb == 0)
			$co = $this->l('Dont update combinations, only update base price');
			if ($comb == 1)
			$co = $this->l('Update base price and combination price');
			if ($comb == 2)
			$co = $this->l('Update combinations, dont update base price');
			$skipcat = Tools::getValue('skipcat');
		
			Configuration::updateValue('MASSPRICE_NBR', $nbr);
		
			Configuration::updateValue('MASSPRICE_SYMBOL', $symbol);
			Configuration::updateValue('MASSPRICE_COMB', $comb);
			Configuration::updateValue('MASSPRICE_REQUIERED1', $requiered1);
				Configuration::updateValue('MASSPRICE_MIN', $min);
					Configuration::updateValue('MASSPRICE_MAX', $max);
			if (!empty($skipcat))
				Configuration::updateValue('MASSPRICE_SKIP_CAT', implode(',', $skipcat));
			
			$skipcategory = Configuration::get('MASSPRICE_SKIP_CAT');
		
			$last = $this->l('Price increment:'.(float)$nbr.',Type of increment:'.$sy.' in '.$re.' - '.$co.' on categories ID:'.(string)$skipcategory);
			Configuration::updateValue('MASSPRICE_LAST', $last);
			$this->updateall();
		}

		return $this->displayForm();
		}
		else
		return $this->postProcess().$this->_displayInfo().$this->renderForm().$this->_displayAdds();;
	}
	


	public function getProductscath($idcat)
	{
		($GLOBALS['___mysqli_ston'] = mysqli_connect(_DB_SERVER_, _DB_USER_, _DB_PASSWD_)) || die(((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_error($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
		mysqli_query($GLOBALS['___mysqli_ston'], 'SET NAMES UTF8'); //this is needed for UTF 8 characters - multilanguage
		((bool)mysqli_query($GLOBALS['___mysqli_ston'], 'USE '.constant('_DB_NAME_'))) || die(((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_error($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
		$sql    = 'SELECT p.*
        FROM `'._DB_PREFIX_.'product` p
        WHERE p.`id_category_default` '.((_PS_VERSION_ > '1.5.0.0') ? 'AND id_shop_default = '.$this->context->shop->id : '').' IN ('.$idcat.') GROUP BY p.`id_product`';
		$result = Db::getInstance()->Execute($sql);
	}

	public function recurseCategory($categories, $current, $id_category = 1, $selectids_array)
	{
		if (str_repeat('&nbsp;', $current['infos']['level_depth'] * 5).preg_replace('/^[0-9]+\./', '', Tools::stripslashes($current['infos']['name'])) != 'Root')
		{
			if ($id_category != null && $current['infos']['name'] != null)
				echo '<option value="'.$id_category.'"'.(in_array($id_category, $selectids_array) ? ' selected="selected"' : '').'>'.str_repeat('&nbsp;', $current['infos']['level_depth'] * 5).preg_replace('/^[0-9]+\./', '', Tools::stripslashes($current['infos']['name'])).'</option>';
		}
		if (isset($categories[$id_category]))
			foreach ($categories[$id_category] as $key => $row)
				$this->recurseCategory($categories, $categories[$id_category][$key], $key, $selectids_array);

	}

}

?>