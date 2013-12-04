<br/>
<fieldset>
<legend><img src="../img/admin/tab-customers.gif">Combined Products Info</legend>
<table class="api" cellspacing=3 cellpadding=3>

        <tr>
            <th>
                <b>ID</b></th>
            <th>
                <b>Time</b></th>
            <th>
                <b>Status</b></th>
            <th>
                <b>Payer Name</b></th>
            <th>
                <b>Gross Amount</b></th>
        </tr>
				{foreach from=$previouspayments item=value}
		<tr>
            <td>		
                <b>{$value.TRANS}</b></td>
            <td>
               {$value.TIMES}</td>
            <td>
               {$value.NAME}</td>
            <td>
                {$value.AMT}</td>
            <td>
                {$value.STATUS}</td>
        </tr>
				{/foreach}
				</table>
				
				
				<hr  styel="border:2px dashed #000000"/>
				
{if $amountallowed>0}
				<form action="{$action}" method="post" >
<table class="api">
        <tr>
            <td class="thinfield">
                Authorization ID:</td>
            <td>
                <input type="text" name="authorization_id" value="{$authorizationid}" readonly=readonly>
                </td>
                <td><b>(Required)</b></td>
        </tr>
        <tr>
            <td class="thinfield">
                Complete Code Type:</td>
            <td>
                <select name="CompleteCodeType">				
                <option value="NotComplete">NotComplete</option>
                <option value="Complete">Complete</option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="thinfield">
                Amount:</td>
            <td>
                <input type="text" name="amount" value="{$amountallowed}" size="5" maxlength="7" />
                <input type="hidden" name="currency" value="USD">
                </td>
                <td><b>(Required)</b></td>
        </tr>
         <tr>
            <td class="thinfield">
                Note:</td>
            <td>
               <textarea name="note" cols="10" rows="4"></textarea>            
                </td>
            <td></td>
        </tr>	
        <tr>
            <td class="thinfield">
            </td>
            <td>
                <input type="Submit" value="Capture Payment" name="capturepayment" />
            </td>
        </tr>
    </table>
	</form>
	{else}
	Transaction is completed for this payment.
	{/if}
</fieldset>