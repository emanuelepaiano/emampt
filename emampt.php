<?php 
/*
 *   This file is part of Multiple Products Task Prestashop module.
     (C) 2014 Emanuele Paiano -nixw0rm@gmail.com
     
     read the file LICENSE.txt for module permissions 
     
     Tested with 
     - Prestashop 1.5.6.2
 */


if (!defined('_PS_VERSION_'))
exit;
class EmaMpt extends Module
{
	
	public function __construct()
	{
		$this->name = 'emampt';
		
		$this->tab = 'pricing_promotion';
		
		$this->version = '1.0';
		
		$this->author = 'Emanuele Paiano';
		
		$this->need_instance = 0;
		
		$this->ps_versions_compliancy = array('min' => '1.5.6.0', 'max' => '1.6.1.11');
		
		parent::__construct();
		
		$this->displayName = $this->l('Multiple Products Task');
		
		$this->description = $this->l('A few clicks to edit products\' attributes');
		
		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
		
		if (!Configuration::get('QUANTVAL'))
		$this->warning = $this->l('Invalid percent Value');
		
		if (!Configuration::get('PERCENTVAL'))
		$this->warning = $this->l('Invalid percent Value');
	}
	
	public function install()
	{
		if (Shop::isFeatureActive())
			Shop::setContext(Shop::CONTEXT_ALL);			
		
		Configuration::updateValue('QUANTVAL', '0');
					
		if (!parent::install() || 
		!$this->registerHook('displayBackOfficeHeader') || 
		!$this->registerHook('displayAdminHomeQuickLinks'))
		return false;
		
		return true;
				
	}
	
	public function uninstall()
	{
		if (!parent::uninstall()) 
			Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'quantupdater`');
		
		parent::uninstall();
	}
	
	
	/* Load tasks list to run */
	
	private function loadTaskList()
	{
		 $this->taskList['quantity']=strval(Tools::getValue('EnabledQuantitytask_0'));
			 
		 $this->taskList['price']=strval(Tools::getValue('EnabledPricetask_0'));
		 
		 $this->taskList['change_supplier']=strval(Tools::getValue('EnabledChangeSuppliertask_0'));
		 
		 $this->taskList['change_manufacturer']=strval(Tools::getValue('EnabledChangeManufacturertask_0'));
		 
		 $this->taskList['add_supplier']=strval(Tools::getValue('EnabledAddSuppliertask_0'));
		 
		 
		 return $this->taskList;
		 
	}
	
	
	/* Running tasks list */
	
	private function runTaskList()
	{
		 if ($this->taskList['add_supplier']=='on') $this->RuntaskAddSupplier();
		 else Configuration::updateValue('EnabledAddSuppliertask_0', $this->taskList['add_supplier']);
	
		 if ($this->taskList['quantity']=='on') $this->RuntaskQuantity();
		 else Configuration::updateValue('EnabledQuantitytask_0', $this->taskList['quantity']);
			 
		 if ($this->taskList['price']=='on')  $this->RuntaskPrice();
		 else Configuration::updateValue('EnabledPricetask_0', $this->taskList['price']);
		 
		 if ($this->taskList['change_supplier']=='on')  $this->RuntaskChangeSupplier();
		 else Configuration::updateValue('EnabledChangeSuppliertask_0', $this->taskList['change_supplier']);
		 
		 if ($this->taskList['change_manufacturer']=='on')  $this->RuntaskChangeManufacturer();
		 else Configuration::updateValue('EnabledChangeManufacturertask_0', $this->taskList['change_manufacturer']);
		 
	}
	
	
	
	public function getContent()
	{
		$output = null; 
		
		if (Tools::isSubmit('submit' . $this->name))
		{
			/* Loading tasks list */
			 
			
			 $this->loadTaskList();
			 
			 
			 /* Running tasks */
			 
			$this->runTaskList();
			 
			 
			 if (!$this->successTask) 
			 
			 	$output .= $this->displayError($this->l('Error while running task(s)'));
			 
			 else 
			 
			 	$output .= $this->displayConfirmation($this->l('Task(s) terminated successfully'));	 	 
		}
		
		return $output.$this->displayForm();
	}
	
	
	
	/* Return true if first loading page else return false */
	
	private function isFirstTime()
	{
		/* If is submit action then this is results loading page */
		
		return !Tools::isSubmit('submit' . $this->name);
	}
	
	
	
	/* Run task checking Inputs */
	
	
	private function RuntaskQuantity()
	{
		$value = strval(Tools::getValue('QUANTVAL'));
			
		$category_val = strval(Tools::getValue('IDCAT_QUANTITY'));
		
		$mode_val = strval(Tools::getValue('MODE_QUANTITY'));
		
		$subcategory_checkbox=strval(Tools::getValue('SUBCATEGORY_QUANTITY_0'));
		
		if (!$value || empty($value) || !Validate::isInt($value))
		
			$output .= $this->displayError( $this->l('Invalid options for task quantity') );
		
		else
		{
			Configuration::updateValue('QUANTVAL', $value);
			
			Configuration::updateValue('IDCAT_QUANTITY', $category_val);
			
			Configuration::updateValue('MODE_QUANTITY', $mode_val);
			
			Configuration::updateValue('EnabledQuantitytask_0', $this->taskList['quantity']);
			
			Configuration::updateValue('SUBCATEGORY_QUANTITY_0', $subcategory_checkbox);
			
			$this->successTask=false;
			
			$this->executetaskChangeQuantity($mode_val, $category_val, $value, $subcategory_checkbox);
			
		}
	}
	
	
	/* Run task checking Inputs */
	
	private function RuntaskChangeSupplier()
	{
		$currentSupplier = strval(Tools::getValue('IDCurrentSUPPLIER_CHANGESUPPLIER'));
			
		$newSupplier = strval(Tools::getValue('IDNEWSUPPLIER_CHANGESUPPLIER'));
		
		$category = strval(Tools::getValue('IDCAT_CHANGESUPPLIER'));
		
		$subcategory_checkbox=strval(Tools::getValue('SUBCATEGORY_CHANGESUPPLIER_0'));
				
		if ($newSupplier==0 || $currentSupplier==0)
		
			$output .= $this->displayError( $this->l('Invalid options for task Change Supplier') );
		
		else
		{
			Configuration::updateValue('IDCurrentSUPPLIER_CHANGESUPPLIER', $currentSupplier);
			
			Configuration::updateValue('IDNEWSUPPLIER_CHANGESUPPLIER', $newSupplier);
			
			Configuration::updateValue('IDCAT_CHANGESUPPLIER', $category);
			
			Configuration::updateValue('EnabledChangeSuppliertask_0', $this->taskList['change_supplier']);
			
			Configuration::updateValue('SUBCATEGORY_CHANGESUPPLIER_0', $subcategory_checkbox);
			
			$this->successTask=false;
			
			$this->executetaskChangeSupplier($currentSupplier, $newSupplier, $category, $subcategory_checkbox);
			
		}
	}
	
	private function RuntaskChangeManufacturer()
	{
		$currentManufacturer = strval(Tools::getValue('IDMANUF_CHANGEMANUFACTURER'));
			
		$newManufacturer = strval(Tools::getValue('IDNEWMAN_CHMANUF'));
		
		$category = strval(Tools::getValue('IDCAT_CHANGEMANUFACTURER'));
		
		$subcategory_checkbox=strval(Tools::getValue('SUBCATEGORY_CHANGEMANUFACTURER_0'));
				
		if ($newManufacturer==0 || $currentManufacturer==0)
		{
			$output .= $this->displayError( $this->l('Invalid options for task Change Manufacturer') );		
		}else{
			Configuration::updateValue('IDMANUF_CHANGEMANUFACTURER', $currentManufacturer);
			
			Configuration::updateValue('IDNEWMAN_CHMANUF', $newManufacturer);
			
			Configuration::updateValue('IDCAT_CHANGEMANUFACTURER', $category);
			
			Configuration::updateValue('EnabledChangeManufacturertask_0', $this->taskList['change_manufacturer']);
			
			Configuration::updateValue('SUBCATEGORY_CHANGEMANUFACTURER_0', $subcategory_checkbox);
			
			$this->successTask=false;
			
			$this->executetaskChangeManufacturer($currentManufacturer, $newManufacturer, $category, $subcategory_checkbox);
			
		}
	}
	
	private function RuntaskAddSupplier()
	{
		$currentSupplier = strval(Tools::getValue('IDSUPPL_ADDSUPPL'));
			
		$newSupplier = strval(Tools::getValue('IDNEWSUPPL_ADDSUPPL'));
		
		$category = strval(Tools::getValue('IDCAT_ADDSUPPLIER'));
		
		$subcategory_checkbox=strval(Tools::getValue('SUBCATEGORY_ADDSUPPLIER_0'));
		
			
		if ($newSupplier==0 || $currentSupplier==0)
		{
			$output .= $this->displayError( $this->l('Invalid options for task Add Supplier') );		
		}else{
			Configuration::updateValue('IDSUPPL_ADDSUPPL', $currentSupplier);
			
			Configuration::updateValue('IDNEWSUPPL_ADDSUPPL', $newSupplier);
			
			Configuration::updateValue('IDCAT_ADDSUPPLIER', $category);
			
			Configuration::updateValue('EnabledAddSuppliertask_0', $this->taskList['add_supplier']);
			
			Configuration::updateValue('SUBCATEGORY_ADDSUPPLIER_0', $subcategory_checkbox);
			
			$this->successTask=false;
			
			$this->executetaskAddSupplier($currentSupplier, $newSupplier, $category, $subcategory_checkbox);
			
		}
	}
	
	private function RuntaskPrice()
	{
		$value = strval(Tools::getValue('PERCENTVAL'));
				
		$category_val = strval(Tools::getValue('IDCAT_PRICES'));
		
		$mode_val = strval(Tools::getValue('MODE_PRICES'));
		
		$subcategory_checkbox=strval(Tools::getValue('SUBCATEGORY_PRICE_0'));
		
		
		if (!$value || empty($value) || !Validate::isFloat($value))
		
			$output .= $this->displayError( $this->l('Invalid options for task price') );
		
		else
		{
			
			Configuration::updateValue('PERCENTVAL', $value);
			
			Configuration::updateValue('IDCAT_PRICES', $category_val);
			
			Configuration::updateValue('MODE_PRICES', $mode_val);
			
			Configuration::updateValue('EnabledPricetask_0', $this->taskList['price']);
			
			Configuration::updateValue('SUBCATEGORY_PRICE_0', $subcategory_checkbox);
			
			$this->successTask=false;
			
			$this->executetaskPrice($mode_val, $category_val, $value, $subcategory_checkbox);
			
			$output .= $this->displayConfirmation();
		}
	}
	
	
	/* END Run task */
	
	
	
	
	
	/* Execute task generating SQL Queries */
	
	
		
	private function executetaskChangeQuantity($mode_val, $category_val, $value, $subcategory_checkbox)
	{
	  if ($mode_val==0 && $category_val==0)	$var_sw=0;
	  	
	  if ($mode_val==0 && $category_val!=0)	$var_sw=1;
	  
	  if ($mode_val==1 && $category_val==0)	$var_sw=2;
	  
	  if ($mode_val==1 && $category_val!=0) $var_sw=3;
	  
	  if ($mode_val==2 && $category_val==0)	$var_sw=4;
	  
	  if ($mode_val==2 && $category_val!=0) $var_sw=5;
	  
	  if ($mode_val==3 && $category_val==0)	$var_sw=6;
	  
	  if ($mode_val==3 && $category_val!=0) $var_sw=7;
	  
	  
	  if ($subcategory_checkbox)
	  		$list=implode(',', $this->getSubCategoryList($category_val)); 
	  else 
	  		$list=$category_val;
	  
	  
	  switch($var_sw)
	  {
	  	case 0:			
			Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'stock_available` SET quantity = quantity+'. $value);
			$this->successTask=true;
	  		break;
	  	
	  	case 1:
							
			Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'stock_available` SET quantity = quantity+'. $value . ' WHERE id_product IN (select id_product from ps_product where id_category_default IN (' . $list . '))');
			$this->successTask=true;
	  		break;
	  	
	  	case 2:
	  		Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'stock_available` SET quantity = '. $value);
	  		Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product_shop` SET quantity = '. $value);
	  		$this->successTask=true;			
	  		break;
	  	
	  	case 3:
							
			Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product_shop` SET quantity='. $value . ' WHERE id_category_default IN (' . $list. ')');
		
			Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'stock_available` SET quantity = '. $value . ' WHERE id_product IN (select id_product from ps_product where id_category_default IN (' . $list. '))');
			$this->successTask=true;
	  		break;
	  	case 4:
	  		Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'stock_available` SET quantity=(quantity / '. $value .')');
	  		Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product_shop` SET quantity=(quantity /'. $value . ')');
	  		$this->successTask=true;			
	  		break;
	  	
	  	case 5:
							
			Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product_shop` SET quantity=(quantity /'. $value . ') WHERE id_category_default IN (' . $list. ')');
		
			Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'stock_available` SET quantity=(quantity /'. $value . ') WHERE id_product IN (select id_product from ps_product where id_category_default=' . $list . ')');
			$this->successTask=true;
	  		break;
	  	case 6:
	  		Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'stock_available` SET quantity=(quantity*'. $value . ')');
	  		Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product_shop` SET quantity=(quantity*'. $value . ')');
	  		$this->successTask=true;			
	  		break;
	  	
	  	case 7:
							
			Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product_shop` SET quantity=(quantity*'. $value . ') WHERE id_category_default IN (' . $list. ')');
		
			Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'stock_available` SET quantity=(quantity*'. $value . ') WHERE id_product IN (select id_product from ps_product where id_category_default IN (' . $list. ')');
			$this->successTask=true;
	  		break;
	  }
	  
	  
	}
	
	
	
	private function executetaskChangeSupplier($currentSupplier, $newSupplier, $category, $subcategory_checkbox)
	{
	  if ($category==0) $var_sw=0;
	  	
	  if ($category!=0) $var_sw=1;
	  
	  if ($subcategory_checkbox)
	  		$list=implode(',', $this->getSubCategoryList($category)); 
	  else 
	  		$list=$category;
	  
	  $res=Db::getInstance()->executeS('select count(id_product) as num from '._DB_PREFIX_.'product where id_category_default IN (' . $list . ') AND id_supplier=' . $currentSupplier);
	  
	  $results=Db::getInstance()->executeS('select id_product from '._DB_PREFIX_.'product where id_category_default IN (' . $list . ') AND id_supplier=' . $currentSupplier);
	  
	  for($i=0;$i<$res[0]['num'];$i++)
	  {
	  	if ($i!=($res[0]['num']-1))
	  	{
	  		$product_list.=$results[$i]['id_product'] . ',';
	  	}else{
	  		$product_list.=$results[$i]['id_product'];
	  	}
	  }
	  
	  
	  
	  if ($currentSupplier!=0 && $newSupplier!=0)
	  switch($var_sw)
	  {
	  	case 0:			
			Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product_supplier` SET id_supplier = '. $newSupplier . ' WHERE id_supplier=' . $currentSupplier);
			Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product` SET id_supplier = '. $newSupplier . ' WHERE id_supplier=' . $currentSupplier);
			$this->successTask=true;
	  		break;
	  	
	  	case 1:
							
			Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product_supplier` SET id_supplier = '. $newSupplier . ' WHERE id_product IN ('.$product_list.') AND id_supplier=' . $currentSupplier);
			Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product` SET id_supplier = '. $newSupplier . ' WHERE id_product IN ('.$product_list .') AND id_supplier=' . $currentSupplier);
			$this->successTask=true;
	  		break;
	  }else
	  {
	  	$output .= $this->displayError( $this->l('Select old supplier and new supplier') );
	  }
	  
	  
	}
	
	
	private function executetaskAddSupplier($currentSupplier, $newSupplier, $category, $subcategory_checkbox)
	{
	  if ($category==0) $var_sw=0;
	  	
	  if ($category!=0) $var_sw=1;
	  
	  if ($subcategory_checkbox)
	  		$list=implode(',', $this->getSubCategoryList($category)); 
	  else 
	  		$list=$category;
	  
	  $res=Db::getInstance()->executeS('select count(id_product) as num from '._DB_PREFIX_.'product where id_category_default IN (' . $list . ') AND id_supplier=' . $currentSupplier);
	  
	  $results=Db::getInstance()->executeS('select * from '._DB_PREFIX_.'product where id_category_default IN (' . $list . ') AND id_supplier=' . $currentSupplier);
	  
	  for($i=0;$i<$res[0]['num'];$i++)
	  {
	  	array_push($product_array,$results[$i]['id_product']);
	  }
	  
	  
	  if ($currentSupplier!=0 && $newSupplier!=0)
	  switch($var_sw)
	  {
	  	case 0:
			for($i=0;$i<$res[0]['num'];$i++)
				Db::getInstance()->execute('insert into '._DB_PREFIX_.'product_supplier(id_product, id_supplier,id_currency) VALUES('.$results[$i]['id_product'].', '.$newSupplier.',1)');
			$this->successTask=true;
	  		break;
	  	
	  	case 1:
			for($i=0;$i<$res[0]['num'];$i++)
				if(IN_ARRAY($results[$i]['id_category_default'], $product_list)){
					Db::getInstance()->execute('insert into '._DB_PREFIX_.'product_supplier(id_product, id_supplier,id_currency) VALUES('.$results[$i]['id_product'].', '.$newSupplier.',1)');
				}			
			
			$this->successTask=true;
	  		break;
	  }else
	  {
	  	$output .= $this->displayError( $this->l('Select old supplier and new supplier') );
	  }
	  
	  
	}
	
	
	
	
	
	private function executetaskChangeManufacturer($currentManufacturer, $newManufacturer, $category, $subcategory_checkbox)
	{
	  if ($category==0) $var_sw=0;
	  	
	  if ($category!=0) $var_sw=1;
	  
	  if ($subcategory_checkbox)
	  		$list=implode(',', $this->getSubCategoryList($category)); 
	  else 
	  		$list=$category;
	  
	  $res=Db::getInstance()->executeS('select count(id_product) as num from '._DB_PREFIX_.'product where id_category_default IN (' . $list . ') AND id_manufacturer=' . $currentManufacturer);
	  
	  $results=Db::getInstance()->executeS('select id_product from '._DB_PREFIX_.'product where id_category_default IN (' . $list . ') AND id_manufacturer=' . $currentManufacturer);
	  
	  for($i=0;$i<$res[0]['num'];$i++)
	  {
	  	if ($i!=($res[0]['num']-1))
	  	{
	  		$product_list.=$results[$i]['id_product'] . ',';
	  	}else{
	  		$product_list.=$results[$i]['id_product'];
	  	}
	  }
	  
	  
	  if ($currentManufacturer!=0 && $newManufacturer!=0)
	  switch($var_sw)
	  {
	  	case 0:			
			Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product` SET id_manufacturer = '. $newManufacturer . ' WHERE id_manufacturer=' . $currentManufacturer);
			$this->successTask=true;
	  		break;
	  	
	  	case 1:
							
			Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product` SET id_manufacturer = '. $newManufacturer . ' WHERE id_product IN ('. $product_list .') AND id_manufacturer=' . $currentManufacturer);
			$this->successTask=true;
	  		break;
	  }else
	  {
	  	$output .= $this->displayError( $this->l('Select old manufacturer and new manufacturer') );
	  }
	  
	  
	}
	
	
	private function executetaskPrice($mode_val, $category_val, $value, $subcategory_checkbox)
	{
		  if ($mode_val==0 && $category_val==0)	$var_sw=0;
		  	
		  if ($mode_val==0 && $category_val!=0)	$var_sw=1;
		  
		  if ($mode_val==1 && $category_val==0)	$var_sw=2;
		  
		  if ($mode_val==1 && $category_val!=0) $var_sw=3;
		  
		  if ($mode_val==2 && $category_val==0)	$var_sw=4;
		  
		  if ($mode_val==2 && $category_val!=0) $var_sw=5;
		  
		  if ($mode_val==3 && $category_val==0)	$var_sw=6;
		  
		  if ($mode_val==3 && $category_val!=0) $var_sw=7;
		  
		  if ($mode_val==4 && $category_val==0)	$var_sw=8;
		  
		  if ($mode_val==4 && $category_val!=0) $var_sw=9;
		  
		  if ($mode_val==5 && $category_val==0)	$var_sw=10;
		  
		  if ($mode_val==5 && $category_val!=0) $var_sw=11;
		  
		  if ($mode_val==6 && $category_val==0) $var_sw=12;
		  
		  if ($mode_val==6 && $category_val!=0) $var_sw=13;
		  
		  if ($mode_val==7 && $category_val==0) $var_sw=14;
		  
		  if ($mode_val==7 && $category_val!=0) $var_sw=15;
		  
		  
		 if ($subcategory_checkbox)
	  		$list=implode(',', $this->getSubCategoryList($category_val)); 
	 	 else 
	  		$list=$category_val;
		  
		 
		  switch($var_sw)
		  {
		  	case 0:			
				Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product_shop` SET price = price+(price*'. $value . ')/100');
				Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product` SET price = price+(price*'. $value . ')/100');
				$this->successTask=true;
		  		break;
		  	
		  	case 1:				
				Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product_shop` SET price = price+(price*'. $value . ')/100 WHERE id_category_default IN (' . $list. ')');
			
				Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product` SET price = price+(price*'. $value . ')/100 WHERE id_category_default IN (' . $list. ')');
				$this->successTask=true;
		  		break;
		  	
		  	case 2:
		  		Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product_shop` SET price=price+'. $value);
				Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product` SET price=price+'. $value);			
				$this->successTask=true;			
		  		break;
		  	
		  	case 3:
								
				Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product_shop` SET price=price+'. $value . ' WHERE id_category_default IN (' . $list. ')');
			
				Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product` SET price=price+'. $value . ' WHERE id_category_default IN (' . $list. ')');
				$this->successTask=true;
		  		break;
		  		
		  	case 4:		
				Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product_shop` SET price = price/((price*'. $value . ')/100)');
				Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product` SET price = price/((price*'. $value . ')/100)');
				$this->successTask=true;
		  		break;
		  	
		  	case 5:
								
				Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product_shop` SET price = price/((price*'. $value . ')/100) WHERE id_category_default IN (' . $list. ')');
			
				Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product` SET price = price/((price*'. $value . ')/100) WHERE id_category_default IN (' . $list. ')');
				$this->successTask=true;
		  		break;
		  	case 6:		
				Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product_shop` SET price = price/'. $value);
				Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product` SET price = price/'. $value);
				$this->successTask=true;
		  		break;
		  	
		  	case 7:
								
				Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product_shop` SET price = price/'. $value . ' WHERE id_category_default IN (' . $list. ')');
			
				Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product` SET price = price/'. $value . ' WHERE id_category_default IN (' . $list. ')');
				$this->successTask=true;
		  		break;
		  	case 8:		
				Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product_shop` SET price = price*((price*'. $value . ')/100)');
				Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product` SET price = price*((price*'. $value . ')/100)');
				$this->successTask=true;
		  		break;
		  	
		  	case 9:
								
				Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product_shop` SET price = price*((price*'. $value . ')/100) WHERE id_category_default IN (' . $list. ')');
			
				Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product` SET price = price*((price*'. $value . ')/100) WHERE id_category_default IN (' . $list. ')');
				$this->successTask=true;
		  		break;
		  	
		  	case 10:		
				Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product_shop` SET price = price*'. $value);
				Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product` SET price = price*'. $value);
				$this->successTask=true;
		  		break;
		  	
		  	case 11:
								
				Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product_shop` SET price = price*'. $value . ' WHERE id_category_default IN (' . $list. ')');
			
				Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product` SET price = price*'. $value . ' WHERE id_category_default  IN (' . $list. ')');
				$this->successTask=true;
		  		break;
		  	
		  	case 12:		
				Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product_shop` SET price = 0');
				Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product` SET price = 0');
				$this->successTask=true;
		  		break;
		  	
		  	case 13:			
				Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product_shop` SET price = 0 WHERE id_category_default IN (' . $list. ')');
			
				Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product` SET price = 0 WHERE id_category_default IN (' . $list. ')');
				$this->successTask=true;
		  		break;
		  		
		  	case 14:		
				Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product_shop` SET price = ' . $value);
				Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product` SET price = ' . $value);
				$this->successTask=true;
		  		break;
		  	
		  	case 15:			
				Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product_shop` SET price = ' . $value . ' WHERE id_category_default IN (' . $list. ')');
			
				Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'product` SET price = ' . $value . ' WHERE id_category_default IN (' . $list. ')');
				$this->successTask=true;
		  		break;
		  	
		  }
		
		}
	
		
	
	/* END Execute task */
	
	
	
	/* "Tools" Methods */	
	
	
	/* This method returns an array with recursive subcategories of $category.*/
		
		private function getSubCategoryList($category=0)
		{
		 
		 $pivot=array();
		 
		 $sqlnumRows='SELECT count(*) as count from `'._DB_PREFIX_.'category`';
		 
		 $numRowsCategoryArr = Db::getInstance()->ExecuteS($sqlnumRows);		 
		 
		 $numRowsCategory=$numRowsCategoryArr[0]['count'];
		 
		 $sqlPsCategory='SELECT * from `'._DB_PREFIX_.'category`';
		 
		 $psCategory = Db::getInstance()->ExecuteS($sqlPsCategory);
		 
		 /* Here start algoritm to build pivot[] array */
		 
		 $pivot[0]=$category;
		 
		 for($i=0;$i<$numRowsCategory && $i<count($pivot); $i++)
		 {
		 	$parent=$pivot[$i];
		 	for($j=0;$j<$numRowsCategory; $j++)
		 	{
		 		if($psCategory[$j]['id_parent']==$parent)
		 		{
		 			array_push($pivot, $psCategory[$j]['id_category']);
		 		}
		 	}	
		 }
		 
		 return $pivot;
		 
		 
		}
	
	
	private function getCategorylist($options, $IdCategoryToIgnore=0)
	{
		$sql='SELECT id_category, name from `'._DB_PREFIX_.'category_lang`';
	
		if ($results = Db::getInstance()->ExecuteS($sql))
			foreach ($results as $row)
				if ($row['id_category']!=$IdCategoryToIgnore)	array_push($options, array('value' => $row['id_category'], 'name' => $row['name'])); 
		return $options;	
	}
	
	
	
	private function getParentCategory($IdCategory)
	{
		$sql='SELECT id_category, id_parent from `'._DB_PREFIX_.'category` WHERE id_category=' . $IdCategory;
	
		if ($results = Db::getInstance()->ExecuteS($sql))
			$parent['id_category']=$results[0]['id_parent']; 
		
		$sql='SELECT id_category, name from `'._DB_PREFIX_.'category_lang` WHERE id_category=' . $parent['id_category'];
		
		if ($results = Db::getInstance()->ExecuteS($sql))
			$parent['name']=$results[0]['name'];
	
		return $parent;	
	}
	
	
	/* End "Tools" Methods */
	
	
	
	public function displayForm()
	{
	
		/*	options task checkbox */
	     $options_runtaskmanufacturer = array(
			      array(
				'id_option' => 0, 
				'name' => $this->l('Run this task')
			      )
			);
	     
	     $options_runtaskchangemanufacturer = array(
			      array(
				'id_option' => 0, 
				'name' => $this->l('Run this task')
			      )
			);
	     
	     $options_runtaskchangesupplier = array(
			      array(
				'id_option' => 0, 
				'name' => $this->l('Run this task')
			      )
			);
		
	     $options_runtaskquantity = array(
			      array(
				'id_option' => 0, 
				'name' => $this->l('Run this task')
			      )
			);
	     
	     $options_runtaskprice = array(
			      array(
				'id_option' => 0, 
				'name' => $this->l('Run this task')
			      )
			);
			
			
			
		
	      /* options subcategory checkbox */
				
	     $options_subcategory_checkbox_manufacturer = array(
			      array(
				'id_option' => 0, 
				'name' => $this->l('update products in subcategories  ')
			      )
			);  
		
	    $options_subcategory_checkbox_supplier = array(
			      array(
				'id_option' => 0, 
				'name' => $this->l('update products in subcategories  ')
			      )
			);  
	
	    $options_subcategory_checkbox = array(
		      array(
			'id_option' => 0, 
			'name' => $this->l('update products in subcategories  ')
		      )
		);
		
	    $options_subcategory_checkbox_quantity = array(
		      array(
			'id_option' => 0, 
			'name' => $this->l('update products in subcategories  ')
		      )
		);
			
	    $options_subcategory_checkbox_price = array(
			      array(
				'id_option' => 0, 
				'name' => $this->l('update products in subcategories  ')
			      )
			);
	
	    
	    
	
	
	
	
		/* OPTIONS <select> tag */
	
		$options = array(
		      array(
			'value' => 0, 
			'name' => $this->l('No category specified')
		      )
		);
	
		$options=$this->getCategorylist($options, 1); 
		
		
		/* Category options for change supplier and manufacturer tasks */
		
		
		$options_suppliermanufacturer_changetask = array(
		      array(
			'value' => 0, 
			'name' => $this->l('All categories')
		      )
		);
		
		
		$options_suppliermanufacturer_changetask=$this->getCategorylist($options_suppliermanufacturer_changetask, 1);
		
		
		/* Options supplier tasks */
		
		$options_supplier = array(
		      array(
			'value' => 0, 
			'name' => $this->l('No supplier specified')
		      )
		);
		
		
		$options_manufacturer = array(
		      array(
			'value' => 0, 
			'name' => $this->l('No manufacturer specified')
		      )
		);		
		
		/* for change manufacturer and supplier tasks */
		
		$options_supplier_changetask = array(
		      array(
			'value' => 0, 
			'name' => $this->l('Select Supplier')
		      )
		);
		
		$options_manufacturer_changetask = array(
		      array(
			'value' => 0, 
			'name' => $this->l('Select Manufacturer')
		      )
		);		
			
			
			
			
			
		/* OPTIONS MODE task */
		
		$options_mode_quantity = array(
		      array(
			'value' => 0, 
			'name' => $this->l('Add value to quantity')
		      ),
		      array(
			'value' => 1, 
			'name' => $this->l('Set quantity to value')
		      ),
		       array(
			'value' => 2, 
			'name' => $this->l('Quantity divided by value (as constant)')
		      ),
		      array(
			'value' => 3, 
			'name' => $this->l('Multiply quantity by value (as constant)')
		      ),
		);
		
		$options_mode_price = array(
		      array(
			'value' => 0, 
			'name' => $this->l('Price plus value (as base price percentual)')
		      ),
		      array(
			'value' => 1, 
			'name' => $this->l('Price plus value (as constant)')
		      ),
		      array(
			'value' => 2, 
			'name' => $this->l('Price divided by value (as base price percentual)')
		      ),
		       array(
			'value' => 3, 
			'name' => $this->l('Price divided by value (as constant)')
		      ),
		      array(
			'value' => 4, 
			'name' => $this->l('Multiply price by value (as base price percentual)')
		      ),
		      array(
			'value' => 5, 
			'name' => $this->l('Multiply price by value (as constant)')
		      ),
		      array(
			'value' => 6, 
			'name' => $this->l('Ignore the value and set prices to zero')
		      ),
		      array(
			'value' => 7, 
			'name' => $this->l('Set prices to value')
		      )
		      
		      
		);
		
		
		
		
		
		
		$sql='SELECT id_supplier, name from `'._DB_PREFIX_.'supplier`';
	
		if ($results = Db::getInstance()->ExecuteS($sql))
			foreach ($results as $row)
				array_push($options_supplier, array('value' => $row['id_supplier'], 'name' => $row['name'])); 
			
			
		/*	manufacturer 	*/
		
	
		$sql='SELECT id_manufacturer, name from `'._DB_PREFIX_.'manufacturer`';
	
		if ($results = Db::getInstance()->ExecuteS($sql))
			foreach ($results as $row)
				array_push($options_manufacturer, array('value' => $row['id_manufacturer'], 'name' => $row['name'])); 
		
		$sql='SELECT id_supplier, name from `'._DB_PREFIX_.'supplier`';
	
		if ($results = Db::getInstance()->ExecuteS($sql))
			foreach ($results as $row)
				array_push($options_supplier_changetask, array('value' => $row['id_supplier'], 'name' => $row['name'])); 
			
			
		/*	manufacturer 	*/
		
	
		$sql='SELECT id_manufacturer, name from `'._DB_PREFIX_.'manufacturer`';
	
		if ($results = Db::getInstance()->ExecuteS($sql))
			foreach ($results as $row)
				array_push($options_manufacturer_changetask, array('value' => $row['id_manufacturer'], 'name' => $row['name'])); 
		
			
				
	
	    // Get default Language
	    $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
	     
	    // Quantity products form array
	    
	    $fields_form[0]['form'] = array(
		'legend' => array(
		    'title' => $this->l('Edit quantity available products '),
		),
		
		'input' => array(
		    array(
			'type' => 'text',
			'label' => $this->l('Value'),
			'name' => 'QUANTVAL',
			'size' => 5,
			'hint' => $this->l('Update value. For decrease you can use negative value')
		    ),
		    array(
			'type' => 'select',
			'label' => $this->l(' Default category'),
			'name' => 'IDCAT_QUANTITY',
			'required' => false,
			'hint' => $this->l('Edit products quantity into a specific category.'),
		    'options' => array(
		    'query' => $options,                        
	            'id' => 'value',
	            'name' => 'name'                               
		   )),
		   
		    /* only for demo not work in demo edition */
		    array(
			'type' => 'checkbox',
			'name' => 'SUBCATEGORY_QUANTITY',
			'hint' => $this->l('If enable update subcategories products'),
			'values'  => array(
				'query' => $options_subcategory_checkbox_quantity,                           
				'id'    => 'id_option',                        
				'name'  => 'name'
				),
			    ),
		   array(
			'type' => 'select',
			'label' => $this->l('Default supplier'),
			'name' => 'IDSUPPLIER_QUANTITY',
			'required' => false,
			'hint' => $this->l('Edit products quantity from a specific supplier.'),
		    'options' => array(
		    'query' => $options_supplier,                        
	            'id' => 'value',
	            'name' => 'name'                               
		   )),
		   array(
			'type' => 'select',
			'label' => $this->l('Manufacturer'),
			'name' => 'IDMANUFACTURER_QUANTITY',
			'required' => false,
			'hint' => $this->l('Edit products quantities from a specific manufacturer.'),
		    'options' => array(
		    'query' => $options_manufacturer,                        
	            'id' => 'value',
	            'name' => 'name'                               
		   )),
		   array(
			'type' => 'select',
			'label' => $this->l('Task'),
			'name' => 'MODE_QUANTITY',
			'required' => false,
			'hint' => $this->l('Add value to quantity or set to constant value?'),
		    'options' => array(
		    'query' => $options_mode_quantity,                        
	            'id' => 'value',
	            'name' => 'name'                               
		   )),
		    array(
			'type' => 'checkbox',
			'name' => 'EnabledQuantitytask',
			'hint' => $this->l('If enable execute task selected in this form'),
			'values'  => array(
				'query' =>  $options_runtaskquantity,                           
				'id'    => 'id_option',                        
				'name'  => 'name'
				),
			),
		 ),
	    );
	    
	     // Price Updater form
	     
	     	 
	     
		    $fields_form[1]['form'] = array(
			'legend' => array(
			    'title' => $this->l('Edit products base price in stock'),
			),
			'input' => array(
			    array(
				'type' => 'text',
				'label' => $this->l('Value'),
				'name' => 'PERCENTVAL',
				'size' => 5,
				'hint' => $this->l('Increment value. To decrease use negative value')
			    ),
			    array(
				'type' => 'select',
				'label' => $this->l(' Default category'),
				'name' => 'IDCAT_PRICES',
				'required' => false,
				'hint' => $this->l('Edit products price into a specific category.'),
			    'options' => array(
        		    'query' => $options,                        
  		            'id' => 'value',
  		            'name' => 'name'                               
   			   )),
   			   
   			   /* only for demo not work in demo edition */
   			   array(
				'type' => 'checkbox',
				'name' => 'SUBCATEGORY_PRICE',
				'hint' => $this->l('If enable update subcategories products'),
				'values'  => array(
					'query' => $options_subcategory_checkbox_price,                           
					'id'    => 'id_option',                        
					'name'  => 'name'
					),
			    ),
   			   array(
				'type' => 'select',
				'label' => $this->l('Default supplier'),
				'name' => 'IDSUPPLIER_PRICES',
				'required' => false,
				'hint' => $this->l('Edit products price from a specific supplier.'),
			    'options' => array(
        		    'query' => $options_supplier,                        
  		            'id' => 'value',
  		            'name' => 'name'                               
   			   )),
   			   array(
				'type' => 'select',
				'label' => $this->l('Manufacturer'),
				'name' => 'IDMANUFACTURER_PRICES',
				'required' => false,
				'hint' => $this->l('Edit products price from a specific manufacturer.'),
			    'options' => array(
        		    'query' => $options_manufacturer,                        
  		            'id' => 'value',
  		            'name' => 'name'                               
   			   )),
   			   
   			   array(
				'type' => 'select',
				'label' => $this->l('Task'),
				'name' => 'MODE_PRICES',
				'required' => false,
				'hint' => $this->l('Increment by percentual or constant value?'),
			    'options' => array(
        		    'query' => $options_mode_price,                        
  		            'id' => 'value',
  		            'name' => 'name'                               
   			   )),
   			   array(
				'type' => 'checkbox',
				'name' => 'EnabledPricetask',
				'hint' => $this->l('If enable execute task selected in this form'),
				'values'  => array(
				'query' =>  $options_runtaskprice,                           
				'id'    => 'id_option',                        
				'name'  => 'name'
				),
			  ),	
   			 ),			
			'submit' => array(
			    'title' => $this->l('Run task(s)'),
			    'class' => 'button',
			    'name' => 'submit' . $this->name
			)
		    );
	    
	    
	    
	   
	    
	    /* Change Supplier */
	    
	    
	     $fields_form[2]['form'] = array(
			'legend' => array(
			    'title' => $this->l('Change default supplier'),
			),
			'input' => array(
   			   array(
				'type' => 'select',
				'label' => $this->l('Current Supplier'),
				'name' => 'IDCurrentSUPPLIER_CHANGESUPPLIER',
				'required' => false,
				'hint' => $this->l('Select new manufacturer from list.'),
			    'options' => array(
        		    'query' => $options_supplier_changetask,                        
  		            'id' => 'value',
  		            'name' => 'name'                               
   			   )),
   			   array(
				'type' => 'select',
				'label' => $this->l('New Supplier'),
				'name' => 'IDNEWSUPPLIER_CHANGESUPPLIER',
				'required' => false,
				'hint' => $this->l('Select new supplier from list.'),
			    'options' => array(
        		    'query' => $options_supplier_changetask,                        
  		            'id' => 'value',
  		            'name' => 'name'                               
   			   )),
   			    array(
				'type' => 'select',
				'label' => $this->l(' Default category'),
				'name' => 'IDCAT_CHANGESUPPLIER',
				'required' => false,
				'hint' => $this->l('Edit products supplier into a specific category.'),
			    'options' => array(
        		    'query' => $options_suppliermanufacturer_changetask,                        
  		            'id' => 'value',
  		            'name' => 'name'                               
   			   )),
   			   
   			   /* only for demo not work in demo edition */
   			   array(
				'type' => 'checkbox',
				'name' => 'SUBCATEGORY_CHANGESUPPLIER',
				'hint' => $this->l('If enable update subcategories products'),
				'values'  => array(
					'query' => $options_subcategory_checkbox_supplier,                           
					'id'    => 'id_option',                        
					'name'  => 'name'
					),
			    ),
   			   array(
				'type' => 'checkbox',
				'name' => 'EnabledChangeSuppliertask',
				'hint' => $this->l('If enable execute task selected in this form'),
				'values'  => array(
				'query' =>  $options_runtaskchangesupplier,                           
				'id'    => 'id_option',                        
				'name'  => 'name'
				),
			  )	
   			 )			
		    );
	    
	    
	    /* Add Supplier to products */
	    
	    
	     $fields_form[3]['form'] = array(
			'legend' => array(
			    'title' => $this->l('Add supplier to products set'),
			),
			'input' => array(
   			   array(
				'type' => 'select',
				'label' => $this->l('Current Supplier'),
				'name' => 'IDSUPPL_ADDSUPPL',
				'required' => false,
				'hint' => $this->l('Select new supplier from list.'),
			    'options' => array(
        		    'query' => $options_supplier_changetask,                        
  		            'id' => 'value',
  		            'name' => 'name'                               
   			   )),
   			   array(
				'type' => 'select',
				'label' => $this->l('Supplier to be added'),
				'name' => 'IDNEWSUPPL_ADDSUPPL',
				'required' => false,
				'hint' => $this->l('Select supplier from list.'),
			    'options' => array(
        		    'query' => $options_supplier_changetask,                        
  		            'id' => 'value',
  		            'name' => 'name'                               
   			   )),
   			    array(
				'type' => 'select',
				'label' => $this->l(' Default category'),
				'name' => 'IDCAT_ADDSUPPLIER',
				'required' => false,
				'hint' => $this->l('Add products supplier into a specific category.'),
			    'options' => array(
        		    'query' => $options_suppliermanufacturer_changetask,                        
  		            'id' => 'value',
  		            'name' => 'name'                               
   			   )),
   			   
   			   array(
				'type' => 'checkbox',
				'name' => 'SUBCATEGORY_ADDSUPPLIER',
				'hint' => $this->l('If enable update subcategories products'),
				'values'  => array(
					'query' => $options_subcategory_checkbox_supplier,                           
					'id'    => 'id_option',                        
					'name'  => 'name'
					),
			    ),
   			   array(
				'type' => 'checkbox',
				'name' => 'EnabledAddSuppliertask',
				'hint' => $this->l('If enable execute task selected in this form'),
				'values'  => array(
				'query' =>  $options_runtaskchangesupplier,                           
				'id'    => 'id_option',                        
				'name'  => 'name'
				),
			  )	
   			 )			
		    );
	    
	    
	    /* Change Manufacturer */
	    
	    
	     $fields_form[4]['form'] = array(
			'legend' => array(
			    'title' => $this->l('Change Manufacturer '),
			),
			'input' => array(
   			   array(
				'type' => 'select',
				'label' => $this->l('Current Manufacturer'),
				'name' => 'IDMANUF_CHANGEMANUFACTURER',
				'required' => false,
				'hint' => $this->l('Select new manufacturer from list.'),
			    'options' => array(
        		    'query' => $options_manufacturer_changetask,                        
  		            'id' => 'value',
  		            'name' => 'name'                               
   			   )),
   			   
   			   array(
				'type' => 'select',
				'label' => $this->l('New Manufacturer'),
				'name' => 'IDNEWMAN_CHMANUF',
				'required' => false,
				'hint' => $this->l('Select new manufacturer from list.'),
			    'options' => array(
        		    'query' => $options_manufacturer_changetask,                        
  		            'id' => 'value',
  		            'name' => 'name'                               
   			   )),
   			    array(
				'type' => 'select',
				'label' => $this->l(' Default category'),
				'name' => 'IDCAT_CHANGEMANUFACTURER',
				'required' => false,
				'hint' => $this->l('Edit products manufacturer into a specific category.'),
			    'options' => array(
        		    'query' => $options_suppliermanufacturer_changetask,                        
  		            'id' => 'value',
  		            'name' => 'name'                               
   			   )),
   			   
   			   array(
				'type' => 'checkbox',
				'name' => 'SUBCATEGORY_CHANGEMANUFACTURER',
				'hint' => $this->l('If enable update subcategories products'),
				'values'  => array(
					'query' => $options_subcategory_checkbox_manufacturer,                           
					'id'    => 'id_option',                        
					'name'  => 'name'
					),
			    ),
   			   array(
				'type' => 'checkbox',
				'name' => 'EnabledChangeManufacturertask',
				'hint' => $this->l('If enable execute task selected in this form'),
				'values'  => array(
				'query' =>  $options_runtaskmanufacturer,                           
				'id'    => 'id_option',                        
				'name'  => 'name'
				),
			  )	
   			 )			
		    );
	    
	    
	    /* Category manage form */
	    
	     $categoryParentList=array();
	    
	    $sql='SELECT id_category, name from `'._DB_PREFIX_.'category_lang`';
		$categoryParentList=array();
	
		if ($results = Db::getInstance()->ExecuteS($sql))
			foreach ($results as $row)
				/* remove Root category and Homepage for prestashop security */
				if ($row['id_category']!=1 && $row['id_category']!=2)
				{
					$parent=$this->getParentCategory($row['id_category'], 1);
					$options_category = array(
					      array(
						'value' => $parent['id_category'], 
						'name' => $parent['name']
					      )
					);
					
					foreach ($options as $current) 
						if ($current['value']!=0 && $current['value']!=$parent['id_category'] ) 
							array_push($options_category, $current);
					
		   			   array_push($categoryParentList, 
						array('type' => 'select', 
						'label' => $row['name'], 
						'name' => 'IDCAT_CATEGORY_' . $row['id_category'], 
						'required' => false,
						'hint' => $this->l('Set parent category.'),
						'options' => array(
						'query' => $options_category,
						'id' => 'value',
						'name' => 'name'))
			  		    );
	  		    	}
	  		    	
   		$catSelect=array();
   		
   		foreach($categoryParentList as $current)
   		{
   			array_push($catSelect, $current);
   		}
   			   
	    
	    $fields_form[5]['form'] = array(
			'legend' => array(
			    'title' => $this->l('Change parent category '),
			),
			'input' =>$catSelect
		);
		  
		  
		  
		  /* end category form*/
	    
	    
	    
	    
	    $helper = new HelperForm();
	     
	    // Module, token and currentIndex
	    $helper->module = $this;
	    
	    $helper->name_controller = $this->name;
	    
	    $helper->token = Tools::getAdminTokenLite('AdminModules');
	    
	    $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
	     
	    // Language
	    
	    $helper->default_form_language = $default_lang;
	    
	    $helper->allow_employee_form_lang = $default_lang;
	     
	    // Title and toolbar
	    
	    $helper->title = $this->displayName;
	    
	    $helper->show_toolbar = true;        // false -> remove toolbar
	    
	    $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
	    
	    $helper->submit_task = 'submit'.$this->name;
	    
	    $helper->toolbar_btn = array(
		'save' =>
		array(
		    'desc' => $this->l('Apply changes'),
		    'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
		    '&token='.Tools::getAdminTokenLite('AdminModules'),
		    'name'=>'test'
		)/*,
		
		'back' => array(
		    'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
		    'desc' => $this->l('Modules List')
		)*/
	    );
	     
	    // Load current value
	    
	    $helper->fields_value['IDSUPPLIER_QUANTITY'] = Configuration::get('IDSUPPLIER_QUANTITY');
	    
	    $helper->fields_value['SUBCATEGORY_QUANTITY_0'] = Configuration::get('SUBCATEGORY_QUANTITY_0');
	    
	    $helper->fields_value['SUBCATEGORY_PRICE_0'] = Configuration::get('SUBCATEGORY_PRICE_0');
	    
	    $helper->fields_value['IDMANUFACTURER_QUANTITY'] = Configuration::get('IDMANUFACTURER_QUANTITY');
	    
	    $helper->fields_value['IDSUPPLIER_PRICES'] = Configuration::get('IDSUPPLIER_PRICES');
	    
	    $helper->fields_value['IDMANUFACTURER_PRICES'] = Configuration::get('IDMANUFACTURER_PRICES');
	    
	    $helper->fields_value['QUANTVAL'] = Configuration::get('QUANTVAL');
	    
	    $helper->fields_value['IDCAT_QUANTITY'] = Configuration::get('IDCAT_QUANTITY');
	    
	    $helper->fields_value['MODE_QUANTITY'] = Configuration::get('MODE_QUANTITY');
	    
	    $helper->fields_value['PERCENTVAL'] = Configuration::get('PERCENTVAL');
	    
	    $helper->fields_value['IDCAT_PRICES'] = Configuration::get('IDCAT_PRICES');
	    
	    $helper->fields_value['MODE_PRICES'] = Configuration::get('MODE_PRICES');
	    
	    $helper->fields_value['EnabledQuantitytask_0'] = Configuration::get('EnabledQuantitytask_0');
	    
	    $helper->fields_value['EnabledPricetask_0'] = Configuration::get('EnabledPricetask_0');
	     
	    return $helper->generateForm($fields_form);
	}
	
	
	// BACK OFFICE HOOKS

	/**
 	 * admin <head> Hook
	 */
	public function hookDisplayBackOfficeHeader()
	{
		// CSS
		//$this->context->controller->addCSS($this->_path.'views/css/elusive-icons/elusive-webfont.css');
		// JS
		// $this->context->controller->addJS($this->_path.'views/js/js_file_name.js');	
	}

	/**
	 * Hook for back office dashboard
	 */
	public function hookDisplayAdminHomeQuickLinks()
	{	
		$this->context->smarty->assign('emampt', $this->name);
		$this->context->smarty->assign('current_module', $this->_path);
	    return $this->display(__FILE__, 'views/templates/hooks/quick_links.tpl');    
	}
	
}


?>
