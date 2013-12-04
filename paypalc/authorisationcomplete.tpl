<br/>
<fieldset>
<legend><img src="../img/admin/tab-customers.gif">Paypal Transaction Details</legend>
 <table class="api" cellspacing=3 cellpadding=3>

        <tr>
            <th>
			Status
			</th>
			<th>
			Amount Captured
			</th>
		</tr>
		
		 <tr>
            <td>
			{$capturestatus}
			</td>
			<td>
			{$captureamount}
			</td>
		</tr>
  </table>
  <br>
  
  <a href="{$action}">Refresh</a>
</fieldset>