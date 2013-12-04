{*
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
*}

<p class="payment_module">



<form action="modules/paypalc/process/ReviewOrder.php" method="POST">

{foreach from=$PRODUCTS key=k item=v}

<input type="hidden" name="L_NAME[]" value="{$v.name}" />
<input type="hidden" name="L_AMT[]" size="5" value="{$v.price}" />
<input type="hidden" name="L_QTY[]" value="{$v.cart_quantity}" />
<input type="hidden" name="totalamount" value="{$total}" />

{/foreach}

<input type="hidden" size="30" maxlength="32" name="SHIPPINGAMT" value="{$SHIPPINGAMT}" />



<input type=hidden name=paymentType value='Authorization' >
<input name="currencyCodeType" value="USD" type="hidden"/> 
<input type="hidden" size="30" maxlength="32" name="PERSONNAME" value="{$PERSONNAME}" />
<input type="hidden" size="30" maxlength="32" name="SHIPTOSTREET" value="{$SHIPTOSTREET}" />
<input type="hidden" size="30" maxlength="32" name="SHIPTOCITY" value="{$SHIPTOCITY}" />
<input type="hidden" size="30" maxlength="32" name="SHIPTOSTATE" value="{$SHIPTOSTATE}" />
<input type="hidden" size="30" maxlength="32" name="SHIPTOCOUNTRYCODE" value="{$SHIPTOCOUNTRYCODE}" />
<input type="hidden" size="30" maxlength="32" name="SHIPTOZIP" value="{$SHIPTOZIP}" />
<input type="hidden" size="30" maxlength="32" name="return_url" value="{$link->getModuleLink('paypalc', 'confirmation', [], true)|escape:'html'}" />
<input type="hidden" size="30" maxlength="32" name="cancel_url" value="{$link->getModuleLink('paypalc', 'confirmation', [], true)|escape:'html'}" />
<input type="image" name="submit" src="{$this_path_paypalc}paypal.png" width="159" height="59"/>{l s='Pay by paypal' mod='paypalc'}
</form>

</p>
