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
 
/********************************************************
GetExpressCheckoutDetails.php

This functionality is called after the buyer returns from
PayPal and has authorized the payment.

Displays the payer details returned by the
GetExpressCheckoutDetails response and calls
DoExpressCheckoutPayment.php to complete the payment
authorization.

Called by ReviewOrder.php.

Calls DoExpressCheckoutPayment.php and APIError.php.

********************************************************/
ini_set('session.bug_compat_42',0);
ini_set('session.bug_compat_warn',0);
 
class paypalcConfirmationModuleFrontController extends ModuleFrontController
{
	public $ssl = true;

	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		$this->display_column_left = false;
		parent::initContent();

		
		
		$token =urlencode( Tools::getValue('token'));
         
		$nvpstr="&TOKEN=".$token;

		@$nvpstr = $nvpHeader.$nvpstr;
		
		 $resArray=paypalc::hash_call("GetExpressCheckoutDetails",$nvpstr);
		   $_SESSION['reshash']=$resArray;
		   $ack = strtoupper($resArray["ACK"]);
		   
		   
		 

		  if($ack == 'SUCCESS' || $ack == 'SUCCESSWITHWARNING'){
		     
			 $showdata=array('EMAIL','PAYERID','PAYERSTATUS','FIRSTNAME','LASTNAME','AMT');
			 
			 foreach($resArray as $key=>$datas)
			 {
			    if(in_array($key,$showdata))
				{
				  $output[$key]=$datas;
				}
			 }
			 
			
			 $this->context->smarty->assign(array(
			'acknowledgement' => $output,
			'hidden'=>$resArray
             ));			
			 			 
			 
			 $this->setTemplate('payment_confirmation.tpl');
			 
			}
			 
			 
		
	}
}
