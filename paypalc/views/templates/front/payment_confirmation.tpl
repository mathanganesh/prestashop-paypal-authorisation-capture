<form action="{$link->getModuleLink('paypalc', 'validation', [], true)|escape:'html'}" method="post" >

<table>
{foreach from=$acknowledgement key=k item=v}
<tr><td>{$k}</td><td>{$v}</tr>
{/foreach}
 <tr>
 
 <input type="hidden" name="token" value="{$hidden.TOKEN}" />
 <input type="hidden" name="PayerID" value="{$hidden.PAYERID}" />
 <input type="hidden" name="currencyCodeType" value="{$hidden.CURRENCYCODE}" />
 <input type="hidden" name="paymentType" value="Authorization" />
 <input type="hidden" name="AMT" value="{$hidden.AMT}" />
 
                <td class="thinfield">
                     <input type="submit" value="Pay" name="btnSubmit"/>
                </td>
            </tr>
</table>
</form>