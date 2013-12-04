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

if (!defined('_PS_VERSION_'))
	exit;
ini_set('memory_limit', '-1');
	
class paypalc extends PaymentModule
{
	private $_html = '';
	private $_postErrors = array();

	public $paypalc;
	public $address;
	public $extra_mail_vars;

	public function __construct()
	{
		$this->name = 'paypalc';
		$this->tab = 'payments_gateways';
		$this->version = '2.3';
		$this->author = 'PrestaShop';

		$this->currencies = true;
		$this->currencies_mode = 'checkbox';

		parent::__construct();

		$this->displayName = $this->l('Paypal Authorisation/Capture');
		$this->description = $this->l('This module allows you to accept payments by paypal using Capture and Authorisation method.');
		$this->confirmUninstall = $this->l('Are you sure you want to delete these details?');

	}

	public function install()
	{
		if (!parent::install() || !$this->registerHook('payment') || !$this->registerHook('paymentReturn') || !$this->registerHook('displayAdminOrder'))
			return false;
			
Db::getInstance()->execute('CREATE TABLE  IF NOT EXISTS `'._DB_PREFIX_.'paypalc_order` ( `id_paypalc` int(11) NOT NULL AUTO_INCREMENT, `id_order` int(11) DEFAULT NULL, `id_customer` int(11) DEFAULT NULL, `transactionid` varchar(50) DEFAULT NULL, `amount` float DEFAULT NULL, `status` int(11) DEFAULT NULL, `totalcapture` int(11) DEFAULT NULL, `datecreated` datetime DEFAULT NULL, `dateupdated` datetime DEFAULT NULL, PRIMARY KEY (`id_paypalc`) ) ENGINE=InnoDB DEFAULT CHARSET=latin1');  

Db::getInstance()->execute('CREATE TABLE  IF NOT EXISTS `'._DB_PREFIX_.'paypalc_transaction` ( `id_paypalc` int(11) DEFAULT NULL, `datetime` datetime DEFAULT NULL, `amount` float DEFAULT NULL, `status` int(11) DEFAULT NULL ) ENGINE=InnoDB DEFAULT CHARSET=latin1');
		
		return true;
	}

	public function uninstall()
	{
		if (!Configuration::deleteByName('PAYPALC_API_USERNAME')
				|| !Configuration::deleteByName('PAYPALC_API_PASSWORD')
				|| !Configuration::deleteByName('PAYPALC_API_SIGNATURE')
				|| !Configuration::deleteByName('PAYPALC_API_MODE')
		        || parent::uninstall())
			return false;
			
			
			
		return true;
	}

	private function _postValidation()
	{
		if (Tools::isSubmit('btnSubmit'))
		{
			if (!Tools::getValue('api_username'))
				$this->_postErrors[] = $this->l('Enter API USERNAME.');
			elseif (!Tools::getValue('api_password'))
				$this->_postErrors[] = $this->l('Enter API Password');
			elseif (!Tools::getValue('api_sign'))
				$this->_postErrors[] = $this->l('Enter API Key.');	
			elseif (!Tools::getValue('api_mode'))
				$this->_postErrors[] = $this->l('Select the paypal mode.');
		}
	}

	private function _postProcess()
	{
		if (Tools::isSubmit('btnSubmit'))
		{
			Configuration::updateValue('PAYPALC_API_USERNAME', Tools::getValue('api_username'));
			Configuration::updateValue('PAYPALC_API_PASSWORD', Tools::getValue('api_password'));
			Configuration::updateValue('PAYPALC_API_SIGNATURE', Tools::getValue('api_sign'));
			Configuration::updateValue('PAYPALC_API_MODE', Tools::getValue('api_mode'));
			
		}
		$this->_html .= '<div class="conf confirm"> '.$this->l('Settings updated').'</div>';
	}

	private function _displayPaypalc()
	{
		$this->_html .= '<img src="../modules/paypalc/paypal.png" style="float:left; margin-right:15px;"><b>'.$this->l('This module allows you to accept payments by paypal usind authorisation do capture mode.').'</b><br /><br />
		'.$this->l('If the client chooses this payment method, the order status will change to "Waiting for payment."').'<br />
		'.$this->l('You will need to manually capture the amount depends on your status of the shipping/products etc..').'<br /><br /><br />';
	}

	private function _displayForm()
	{
	 $sandbox='';
	  $live='';
	if(Configuration::get('PAYPALC_API_MODE')=='LIVE')
	{
	  $sandbox='';
	  $live='checked';
	}
	else if(Configuration::get('PAYPALC_API_MODE')=='SANDBOX')
	{
	$sandbox='checked';
	  $live='';
	}
	
		$this->_html .=
		'<form action="'.Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']).'" method="post">
			<fieldset>
			<legend><img src="../img/admin/contact.gif" />'.$this->l('Contact details').'</legend>
				<table border="0" width="500" cellpadding="0" cellspacing="0" id="form">
					<tr><td colspan="2">'.$this->l('Please specify the following details obtained from the paypal.').'.<br /><br /></td></tr>
					
					<tr><td width="130" style="height: 35px;">'.$this->l('API USERNAME').'</td><td><input type="text" name="api_username" value="'.Configuration::get('PAYPALC_API_USERNAME').'" style="width: 300px;" /></td></tr>
					
					<tr><td width="130" style="height: 35px;">'.$this->l('API PASSWORD').'</td><td><input type="text" name="api_password" value="'.Configuration::get('PAYPALC_API_PASSWORD').'" style="width: 300px;" /></td></tr>
					
					<tr><td width="130" style="height: 35px;">'.$this->l('API SIGNATURE').'</td><td><input type="text" name="api_sign" value="'.Configuration::get('PAYPALC_API_SIGNATURE').'" style="width: 300px;" /></td></tr>
					
					<tr><td width="130" style="height: 35px;">'.$this->l('Sandbox or live mode').'</td><td><input type="radio" name="api_mode" value="SANDBOX" '.$sandbox.'/> Sandbox
					<input type="radio" name="api_mode" value="LIVE" '.$live.'/> Live 
					</td></tr>
					
					<tr><td colspan="2" align="center"><br /><input class="button" name="btnSubmit" value="'.$this->l('Update settings').'" type="submit" /></td></tr>
				</table>
			</fieldset>
		</form>';
	}

	public function getContent()
	{
		$this->_html = '<h2>'.$this->displayName.'</h2>';

		if (Tools::isSubmit('btnSubmit'))
		{
			$this->_postValidation();
			if (!count($this->_postErrors))
				$this->_postProcess();
			else
				foreach ($this->_postErrors as $err)
					$this->_html .= '<div class="alert error">'.$err.'</div>';
		}
		else
			$this->_html .= '<br />';

		$this->_displayPaypalc();
		$this->_displayForm();

		return $this->_html;
	}

	public function hookPayment($params)
	{
		if (!$this->active)
			return;
		if (!$this->checkCurrency($params['cart']))
			return;

		$this->smarty->assign(array(
			'this_path' => $this->_path,
			'this_path_paypalc' => $this->_path,
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
		));
		
		if(Configuration::get('PAYPALC_API_USERNAME')=="" || Configuration::get('PAYPALC_API_SIGNATURE')=="" || Configuration::get('PAYPALC_API_MODE')=="" || Configuration::get('PAYPALC_API_PASSWORD')=="")
		{
		
		}
		else
		{	
		
		     $cart = $this->context->cart;
	
		if (!$this->checkCurrency($params['cart']))
			Tools::redirect('index.php?controller=order');

			
			
			$address = new Address($cart->id_address_invoice);		
			$country = (string)Country::getIsoById($address->id_country);
		    $state = (string)State::getNameById($address->id_state);

			$cartproduct = new Cart($this->context->cookie->id_cart); 
			$products=$cartproduct->getProducts();
			
			$amount = $this->context->cart->getOrderTotal(true);

			$taxes = $amount - $this->context->cart->getOrderTotal(false);

			
		
		$this->context->smarty->assign(array(
			'nbProducts' => $cart->nbProducts(),
			'cust_currency' => $cart->id_currency,
			'currencies' => $this->getCurrency((int)$cart->id_currency),
			'total' => $cart->getOrderTotal(true, Cart::BOTH),
			'isoCode' => $this->context->language->iso_code,			
			'PERSONNAME'=>$address->lastname.' '.$address->firstname,
			'SHIPTOSTREET'=>$address->address1,
			'SHIPTOCITY'=>$address->city,
			'SHIPTOSTATE'=>$state,
			'SHIPTOCOUNTRYCODE'=>$country,
			'SHIPTOZIP'=>$address->postcode,
			'PRODUCTS'=>$products,
			'token'=>Tools::passwdGen(36),
			'SHIPPINGAMT'=>(float)$this->context->cart->getTotalShippingCost()
		)); 
		
		
		
		
		return $this->display(__FILE__, 'payment.tpl');
		}
		
	}

	public function hookPaymentReturn($params)
	{
		if (!$this->active)
			return;

		$state = $params['objOrder']->getCurrentState();
		
		 if ($state == Configuration::get('PS_OS_PREPARATION'))
		{
			$this->smarty->assign(array(
				'total_to_pay' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
				'status' => 'ok',
				'id_order' => $params['objOrder']->id
			));
			if (isset($params['objOrder']->reference) && !empty($params['objOrder']->reference))
				$this->smarty->assign('reference', $params['objOrder']->reference);
		}
		else
			$this->smarty->assign('status', 'failed');
		
		return $this->display(__FILE__, 'payment_return.tpl');
	}

	public function checkCurrency($cart)
	{
		$currency_order = new Currency((int)($cart->id_currency));
		$currencies_module = $this->getCurrency((int)$cart->id_currency);

		if (is_array($currencies_module))
			foreach ($currencies_module as $currency_module)
				if ($currency_order->id == $currency_module['id_currency'])
					return true;
		return false;
	}
	
	
 public static function initalizevariables()
{


global $API_Endpoint,$version,$API_UserName,$API_Password,$API_Signature,$nvp_Header, $subject, $AUTH_token,$AUTH_signature,$AUTH_timestamp;

		define('API_USERNAME', Configuration::get('PAYPALC_API_USERNAME'));

		define('API_PASSWORD', Configuration::get('PAYPALC_API_PASSWORD'));
		define('API_SIGNATURE', Configuration::get('PAYPALC_API_SIGNATURE'));

		if(Configuration::get('PAYPALC_API_MODE')=='LIVE')
		{
		define('API_ENDPOINT', 'https://api-3t.paypal.com/nvp');
		}
		else if(Configuration::get('PAYPALC_API_MODE')=='SANDBOX')
		{
		define('API_ENDPOINT', 'https://api-3t.sandbox.paypal.com/nvp');

		}

		define('SUBJECT','');
		define('USE_PROXY',FALSE);
		define('PROXY_HOST', '127.0.0.1');
		define('PROXY_PORT', '808');

		if(Configuration::get('PAYPALC_API_MODE')=='LIVE')
		{
		define('PAYPAL_URL', 'https://www.paypal.com/webscr&cmd=_express-checkout&token=');
		}
		else if(Configuration::get('PAYPALC_API_MODE')=='SANDBOX')
		{
		define('PAYPAL_URL', 'https://www.sandbox.paypal.com/webscr&cmd=_express-checkout&token=');


		}   

		define('VERSION', '65.1');

		define('ACK_SUCCESS', 'SUCCESS');
		define('ACK_SUCCESS_WITH_WARNING', 'SUCCESSWITHWARNING');

	
	
	
if(defined('API_USERNAME'))
$API_UserName=API_USERNAME;

if(defined('API_PASSWORD'))
$API_Password=API_PASSWORD;

if(defined('API_SIGNATURE'))
$API_Signature=API_SIGNATURE;

if(defined('API_ENDPOINT'))
$API_Endpoint =API_ENDPOINT;

$version=VERSION;

if(defined('SUBJECT'))
$subject = SUBJECT;
// below three are needed if used permissioning
if(defined('AUTH_TOKEN'))
$AUTH_token= AUTH_TOKEN;

if(defined('AUTH_SIGNATURE'))
$AUTH_signature=AUTH_SIGNATURE;

if(defined('AUTH_TIMESTAMP'))
$AUTH_timestamp=AUTH_TIMESTAMP;
 
 
	}
	
	
 public static function nvpHeader()
{
 
 paypalc::initalizevariables();
 
 global $API_Endpoint,$version,$API_UserName,$API_Password,$API_Signature,$nvp_Header, $subject, $AUTH_token,$AUTH_signature,$AUTH_timestamp;


$nvpHeaderStr = "";

if(defined('AUTH_MODE')) {
	//$AuthMode = "3TOKEN"; //Merchant's API 3-TOKEN Credential is required to make API Call.
	//$AuthMode = "FIRSTPARTY"; //Only merchant Email is required to make EC Calls.
	//$AuthMode = "THIRDPARTY";Partner's API Credential and Merchant Email as Subject are required.
	$AuthMode = "AUTH_MODE"; 
} 
else {
	
	if((!empty($API_UserName)) && (!empty($API_Password)) && (!empty($API_Signature)) && (!empty($subject))) {
		$AuthMode = "THIRDPARTY";
	}
	
	else if((!empty($API_UserName)) && (!empty($API_Password)) && (!empty($API_Signature))) {
		$AuthMode = "3TOKEN";
	}
	
	elseif (!empty($AUTH_token) && !empty($AUTH_signature) && !empty($AUTH_timestamp)) {
		$AuthMode = "PERMISSION";
	}
    elseif(!empty($subject)) {
		$AuthMode = "FIRSTPARTY";
	}
	
}
switch($AuthMode) {
	
	case "3TOKEN" : 
			$nvpHeaderStr = "&PWD=".urlencode($API_Password)."&USER=".urlencode($API_UserName)."&SIGNATURE=".urlencode($API_Signature);
			break;
	case "FIRSTPARTY" :
			$nvpHeaderStr = "&SUBJECT=".urlencode($subject);
			break;
	case "THIRDPARTY" :
			$nvpHeaderStr = "&PWD=".urlencode($API_Password)."&USER=".urlencode($API_UserName)."&SIGNATURE=".urlencode($API_Signature)."&SUBJECT=".urlencode($subject);
			break;		
	case "PERMISSION" :
		    $nvpHeaderStr = $this->formAutorization($AUTH_token,$AUTH_signature,$AUTH_timestamp);
		    break;
}
	return $nvpHeaderStr;
}

/**
  * hash_call: Function to perform the API call to PayPal using API signature
  * @methodName is name of API  method.
  * @nvpStr is nvp string.
  * returns an associtive array containing the response from the server.
*/


public static function hash_call($methodName,$nvpStr)
{

  
	//declaring of global variables
	global $API_Endpoint,$version,$API_UserName,$API_Password,$API_Signature,$nvp_Header, $subject, $AUTH_token,$AUTH_signature,$AUTH_timestamp;
	// form header string
	$nvpheader=paypalc::nvpHeader();
	//setting the curl parameters.
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$API_Endpoint);
	curl_setopt($ch, CURLOPT_VERBOSE, 1);
 
	//turning off the server and peer verification(TrustManager Concept).
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_POST, 1);
	
	//in case of permission APIs send headers as HTTPheders
	if(!empty($AUTH_token) && !empty($AUTH_signature) && !empty($AUTH_timestamp))
	 {
		$headers_array[] = "X-PP-AUTHORIZATION: ".$nvpheader;
  
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers_array);
    curl_setopt($ch, CURLOPT_HEADER, false);
	}
	else 
	{
		$nvpStr=$nvpheader.$nvpStr;
	}
    //if USE_PROXY constant set to TRUE in Constants.php, then only proxy will be enabled.
   //Set proxy name to PROXY_HOST and port number to PROXY_PORT in constants.php 
	if(USE_PROXY)
	curl_setopt ($ch, CURLOPT_PROXY, PROXY_HOST.":".PROXY_PORT); 

	//check if version is included in $nvpStr else include the version.
	if(strlen(str_replace('VERSION=', '', strtoupper($nvpStr))) == strlen($nvpStr)) {
		$nvpStr = "&VERSION=" . urlencode($version) . $nvpStr;	
	}
	
	$nvpreq="METHOD=".urlencode($methodName).$nvpStr;
	
	//setting the nvpreq as POST FIELD to curl
	curl_setopt($ch,CURLOPT_POSTFIELDS,$nvpreq);

	//getting response from server
	$response = curl_exec($ch);

	//convrting NVPResponse to an Associative Array
	$nvpResArray=paypalc::deformatNVP($response);
	$nvpReqArray=paypalc::deformatNVP($nvpreq);
	$_SESSION['nvpReqArray']=$nvpReqArray;

	if (curl_errno($ch)) {
		// moving to display page to display curl errors
		  $_SESSION['curl_error_no']=curl_errno($ch) ;
		  $_SESSION['curl_error_msg']=curl_error($ch);
		  echo $_SESSION['curl_error_msg'];
		  
		 // $location = "APIError.php";
		 // header("Location: $location");
	 } else {
		 //closing the curl
			curl_close($ch);
	  }
return $nvpResArray;
}

/** This function will take NVPString and convert it to an Associative Array and it will decode the response.
  * It is usefull to search for a particular key and displaying arrays.
  * @nvpstr is NVPString.
  * @nvpArray is Associative Array.
  */

public static function deformatNVP($nvpstr)
{

	$intial=0;
 	$nvpArray = array();


	while(strlen($nvpstr)){
		//postion of Key
		$keypos= strpos($nvpstr,'=');
		//position of value
		$valuepos = strpos($nvpstr,'&') ? strpos($nvpstr,'&'): strlen($nvpstr);

		/*getting the Key and Value values and storing in a Associative Array*/
		$keyval=substr($nvpstr,$intial,$keypos);
		$valval=substr($nvpstr,$keypos+1,$valuepos-$keypos-1);
		//decoding the respose
		$nvpArray[urldecode($keyval)] =urldecode( $valval);
		$nvpstr=substr($nvpstr,$valuepos+1,strlen($nvpstr));
     }
	return $nvpArray;
}
		public static function formAutorization($auth_token,$auth_signature,$auth_timestamp)
		{
			$authString="token=".$auth_token.",signature=".$auth_signature.",timestamp=".$auth_timestamp ;
			return $authString;
		}

  public function hookdisplayAdminOrder($params)
	{
	
		$id_order = (int)Tools::getValue('id_order');		
		
		$getTokens=DB::getInstance()->getRow("select * from "._DB_PREFIX_."paypalc_order where id_order=".$id_order);		
	if(!empty($getTokens))
	{
	     $hashcall=new paypalc();
		$getalldetails=json_decode($getTokens['fullinfo']);	

 $this->context->smarty->assign(array(
 'action'=>Tools::safeOutput($_SERVER['REQUEST_URI'])
 ));
		
	    if (Tools::isSubmit('capturepayment'))
		{

				$authorizationID=urlencode($_REQUEST['authorization_id']);
				$completeCodeType=urlencode($_REQUEST['CompleteCodeType']);
				$amount=urlencode($_REQUEST['amount']);
				$currency=urlencode($_REQUEST['currency']);
				$note=urlencode($_REQUEST['note']);

				$nvpStr="&AUTHORIZATIONID=$authorizationID&AMT=$amount&COMPLETETYPE=$completeCodeType&CURRENCYCODE=$currency&NOTE=$note";
                 
				
				$resArray=$hashcall->hash_call("DOCapture",$nvpStr);
				
				
				$reqArray=$_SESSION['nvpReqArray'];
				$ack = strtoupper($resArray["ACK"]);
				if($ack == 'SUCCESS' || $ack == 'SUCCESSWITHWARNING'){
	               $this->context->smarty->assign(array(
                    'capturestatus'=>'Success',
					'captureamount'=>$amount,
					));	  
				 return $this->display(__FILE__, 'authorisationcomplete.tpl');
				}
				else
				{
				   $this->context->smarty->assign(array(
                    'capturestatus'=>'Failed',
					'captureamount'=>$amount,
					));	
					return $this->display(__FILE__, 'authorisationerror.tpl');
				}
				
		}
		else
		{
	    
	    
				$nvpStr="&STARTDATE=".$getalldetails->TIMESTAMP;
				$nvpStr.="&ENDDATE=".$getalldetails->TIMESTAMP;    
				$nvpStr=$nvpStr."&TRANSACTIONID=".$getalldetails->TRANSACTIONID;	
				$resArray=$hashcall->hash_call("TransactionSearch",$nvpStr);
				$reqArray=$_SESSION['nvpReqArray'];
				$ack = strtoupper($resArray["ACK"]);
 
	    if($ack == 'SUCCESS' || $ack == 'SUCCESSWITHWARNING'){
					
			$count=0;
			while (isset($resArray["L_TRANSACTIONID".$count])) 
		      	$count++;
				
		$previous=array();$restamount=0;$ID=0;$creditedamount=0;
		while ($count>0) {
			  $previous[$ID]['TRANS']   = $resArray["L_TRANSACTIONID".$ID];
			  $previous[$ID]['TIMES']= $resArray["L_TIMESTAMP".$ID];
			  $previous[$ID]['NAME'] = $resArray["L_NAME".$ID]; 
			  $previous[$ID]['AMT'] = $resArray["L_AMT".$ID]; 
			  $previous[$ID]['STATUS']  = $resArray["L_STATUS".$ID]; 
			
					if($resArray["L_TRANSACTIONID".$ID]!=$getalldetails->TRANSACTIONID && $previous[$ID]['STATUS']=="Completed")
					{
					   $creditedamount=$creditedamount+$resArray["L_AMT".$ID];
					}
					  $count--; $ID++;	
			}		
			
	$restamount=$getalldetails->AMT-$creditedamount;
		
             $this->context->smarty->assign(array(
                    'previouspayments'=>$previous,
                    'authorizationid'=>$getalldetails->TRANSACTIONID,
           			'amountallowed'=>$restamount
					));				
			return $this->display(__FILE__, 'orderdetails.tpl');
			} 
		   else
		   {		   
		     return $this->display(__FILE__, 'orderdetailsfailed.tpl');
		   }
		}
		}
	}

}
