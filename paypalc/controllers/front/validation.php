<?php
/*
* 2007-2013 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

/**
 * @since 1.5.0
 */
class paypalcValidationModuleFrontController extends ModuleFrontController
{
	public function postProcess()
	{
		$cart = $this->context->cart;

		if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active)
			Tools::redirect('index.php?controller=order&step=1');

		// Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
		
		$authorized = false;
		
	
		foreach (Module::getPaymentModules() as $module)
			if ($module['name'] == 'paypalc')
			{
				$authorized = true;
				break;
			}

		if (!$authorized)
			die($this->module->l('This payment method is not available.', 'validation'));

		$customer = new Customer($cart->id_customer);

		if (!Validate::isLoadedObject($customer))
			Tools::redirect('index.php?controller=order&step=1');

				$currency = $this->context->currency;
				$total = (float)$cart->getOrderTotal(true, Cart::BOTH);

				$token=Tools::getValue('token');
				$PayerID=Tools::getValue('PayerID');
				$currencyCodeType=Tools::getValue('currencyCodeType');
				$paymentType=Tools::getValue('paymentType');
	  
	 
	    if (Tools::isSubmit('btnSubmit'))
		{
		
				$token =urlencode($token);
				$paymentAmount =urlencode(Tools::getValue('AMT'));
				$paymentType = urlencode($paymentType);
				$currCodeType = urlencode($currencyCodeType);
				$payerID = urlencode(Tools::getValue('PayerID'));
				$serverName = urlencode($_SERVER['SERVER_NAME']);
						   
						
				$nvpstr='&TOKEN='.$token.'&PAYERID='.$payerID.'&PAYMENTACTION='.$paymentType.'&AMT='.$paymentAmount.'&CURRENCYCODE='.$currCodeType.'&IPADDRESS='.$serverName ;
				   
				$resArrayConfirm=paypalc::hash_call("DoExpressCheckoutPayment",$nvpstr);

			    $ack = strtoupper($resArrayConfirm["ACK"]);

	

			if($ack != 'SUCCESS' && $ack != 'SUCCESSWITHWARNING'){
				
				 foreach($resArrayConfirm as $key=>$values)
				 {
				  echo '<tr><td>'.$key.'</td><td>'.$values.'</td></tr>';
				 }
				 
				 die("Error in paypal transaction. Please try again");
					  
			}   
			else
			{
			  $transactionid=$resArrayConfirm['TRANSACTIONID'];
			  $fullinfo=json_encode($resArrayConfirm);
			}
			
        }			
		
	  		
		$query="select id_paypalc from "._DB_PREFIX_."paypalc_order where token='".$token."' and payerid='".$PayerID."'";
	  
	  $verifyprevTransaction=DB::getInstance()->getRow($query);
	
	if(empty($verifyprevTransaction))
	{
	
		$this->module->validateOrder((int)$cart->id, Configuration::get('PS_OS_PREPARATION'), $total, $this->module->displayName, NULL, NULL, (int)$currency->id, false, $customer->secure_key);
		
	  $id_order=$this->module->currentOrder;
	 
	  
	  $sql="insert into "._DB_PREFIX_."paypalc_order(id_order,token,currencytype,payerid,transactionid,fullinfo) values('".$id_order."','".$token."','".$currencyCodeType."','".$PayerID."','".$transactionid."','".$fullinfo."')";
	  
	  DB::getInstance()->execute($sql);
		
		
	Tools::redirect('index.php?controller=order-confirmation&id_cart='.(int)$cart->id.'&id_module='.(int)$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key);

	}
	else
	{
	  die("Attempting the same transactionid twice.");
	}
	}
}
