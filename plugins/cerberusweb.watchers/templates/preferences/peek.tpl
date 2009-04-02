<form action="{devblocks_url}{/devblocks_url}" method="post">
<input type="hidden" name="c" value="preferences">
<input type="hidden" name="a" value="handleTabAction">
<input type="hidden" name="tab" value="core.pref.notifications">
<input type="hidden" name="action" value="saveWatcherPanel">
<input type="hidden" name="id" value="{$filter->id}">

<h2>Add Mail Notification</h2>

<div style="height:400;overflow:auto;">
<b>Notification Name:</b> (e.g. Emergency Support to SMS)<br>
<input type="text" name="name" value="{$filter->name|escape}" size="45" style="width:95%;"><br>
<br>

<h2>If these criteria match:</h2>

{* Date/Time *}
{assign var=expanded value=false}
{if isset($filter->criteria.dayofweek) || isset($filter->criteria.timeofday)}
	{assign var=expanded value=true}
{/if}
<label><input type="checkbox" {if $expanded}checked="checked"{/if} onclick="toggleDiv('divBlockDateTime',(this.checked?'block':'none'));if(!this.checked)checkAll('divBlockDateTime',false);"> <b>Current Date/Time</b></label><br>
<table width="500" style="margin-left:10px;display:{if $expanded}block{else}none{/if};" id="divBlockDateTime">
	<tr>
		<td valign="top">
			{assign var=crit_dayofweek value=$filter->criteria.dayofweek}
			<label><input type="checkbox" id="chkRuleDayOfWeek" name="rules[]" value="dayofweek" {if !is_null($crit_dayofweek)}checked="checked"{/if}> Day of Week:</label>
		</td>
		<td valign="top">
			<label><input type="checkbox" name="value_dayofweek[]" value="0" {if $crit_dayofweek.sun}checked="checked"{/if}> {'Sunday'|date_format:'%a'}</label>
			<label><input type="checkbox" name="value_dayofweek[]" value="1" {if $crit_dayofweek.mon}checked="checked"{/if}> {'Monday'|date_format:'%a'}</label>
			<label><input type="checkbox" name="value_dayofweek[]" value="2" {if $crit_dayofweek.tue}checked="checked"{/if}> {'Tuesday'|date_format:'%a'}</label>
			<label><input type="checkbox" name="value_dayofweek[]" value="3" {if $crit_dayofweek.wed}checked="checked"{/if}> {'Wednesday'|date_format:'%a'}</label>
			<label><input type="checkbox" name="value_dayofweek[]" value="4" {if $crit_dayofweek.thu}checked="checked"{/if}> {'Thursday'|date_format:'%a'}</label>
			<label><input type="checkbox" name="value_dayofweek[]" value="5" {if $crit_dayofweek.fri}checked="checked"{/if}> {'Friday'|date_format:'%a'}</label>
			<label><input type="checkbox" name="value_dayofweek[]" value="6" {if $crit_dayofweek.sat}checked="checked"{/if}> {'Saturday'|date_format:'%a'}</label>
		</td>
	</tr>
	<tr>
		<td valign="top">
			{assign var=crit_timeofday value=$filter->criteria.timeofday}
			<label><input type="checkbox" id="chkRuleTimeOfDay" name="rules[]" value="timeofday" {if !is_null($crit_timeofday)}checked="checked"{/if}> Time of Day:</label>
		</td>
		<td valign="top">
			<i>from</i> 
			<select name="timeofday_from">
				{section start=0 loop=24 name=hr}
				{section start=0 step=30 loop=60 name=min}
					{assign var=hr value=$smarty.section.hr.index}
					{assign var=min value=$smarty.section.min.index}
					{if 0==$hr}{assign var=hr value=12}{/if}
					{if $hr>12}{math assign=hr equation="x-12" x=$hr}{/if}
					{assign var=val value=$smarty.section.hr.index|cat:':'|cat:$smarty.section.min.index}
					<option value="{$val}" {if $crit_timeofday.from==$val}selected="selected"{/if}>{$hr|string_format:"%d"}:{$min|string_format:"%02d"} {if $smarty.section.hr.index<12}AM{else}PM{/if}</option>
				{/section}
				{/section}
			</select>
			 <i>to</i> 
			<select name="timeofday_to">
				{section start=0 loop=24 name=hr}
				{section start=0 step=30 loop=60 name=min}
					{assign var=hr value=$smarty.section.hr.index}
					{assign var=min value=$smarty.section.min.index}
					{if 0==$hr}{assign var=hr value=12}{/if}
					{if $hr>12}{math assign=hr equation="x-12" x=$hr}{/if}
					{assign var=val value=$smarty.section.hr.index|cat:':'|cat:$smarty.section.min.index}
					<option value="{$val}" {if $crit_timeofday.to==$val}selected="selected"{/if}>{$hr|string_format:"%d"}:{$min|string_format:"%02d"} {if $smarty.section.hr.index<12}AM{else}PM{/if}</option>
				{/section}
				{/section}
			</select>
		</td>
	</tr>
</table>

{* Ticket *}
{assign var=expanded value=false}
{if isset($filter->criteria.mask) || isset($filter->criteria.groups)}
	{assign var=expanded value=true}
{/if}
<label><input type="checkbox" {if $expanded}checked="checked"{/if} onclick="toggleDiv('divBlockTicket',(this.checked?'block':'none'));if(!this.checked)checkAll('divBlockTicket',false);"> <b>Ticket</b></label><br>
<table width="500" style="margin-left:10px;display:{if $expanded}block{else}none{/if};" id="divBlockTicket">
	<tr>
		<td valign="top">
			{assign var=crit_mask value=$filter->criteria.mask}
			<label><input type="checkbox" id="chkRuleMask" name="rules[]" value="mask" {if !is_null($crit_mask)}checked="checked"{/if}> Mask:</label>
		</td>
		<td valign="top">
			<input type="text" name="value_mask" size="45" value="{$crit_mask.value|escape}" onchange="document.getElementById('chkRuleMask').checked=((0==this.value.length)?false:true);" style="width:95%;">
		</td>
	</tr>
	<tr>
		<td valign="top" colspan="2">
			{assign var=crit_groups value=$filter->criteria.groups}
			<label><input type="checkbox" id="chkRuleGroupId" name="rules[]" value="groups" onclick="toggleDiv('divRuleGroups',(this.checked?'block':'none'));" {if !is_null($crit_groups)}checked="checked"{/if}> Group/Bucket: (any of the following)</label><br>
			
			<div id="divRuleGroups" style="margin-left:20px;display:{if !is_null($crit_groups)}block{else}none{/if};">
			{foreach from=$groups key=group_id item=group}
			{if isset($memberships.$group_id)}
			<label><input type="checkbox" name="value_groups[]" value="{$group_id}" onclick="toggleDiv('divRuleGroup{$group_id}',(this.checked?'block':'none'));" {if isset($crit_groups.groups.$group_id)}checked="checked"{/if}> {$group->name}</label><br>
			<div id="divRuleGroup{$group_id}" style="display:{if isset($crit_groups.groups.$group_id)}block{else}none{/if};margin-left:15px;margin-bottom:5px;">
				<label><input type="checkbox" name="value_group{$group_id}_all" value="1" {if empty($crit_groups.groups.$group_id)}checked="checked"{/if} onclick="toggleDiv('divRuleGroupBuckets{$group_id}',(this.checked?'none':'block'));"> <i>{$translate->_('common.all')|capitalize}</i></label><br>
				<div id="divRuleGroupBuckets{$group_id}" style="display:{if empty($crit_groups.groups.$group_id)}none{else}block{/if};margin-left:15px;margin-bottom:5px;">
					<label><input type="checkbox" name="value_group{$group_id}_buckets[]" value="0" {if is_array($crit_groups.groups.$group_id) && in_array(0,$crit_groups.groups.$group_id)}checked="checked"{/if}> {$translate->_('common.inbox')|capitalize}</label><br>
					{foreach from=$group_buckets.$group_id item=bucket}
					<label><input type="checkbox" name="value_group{$group_id}_buckets[]" value="{$bucket->id}"  {if is_array($crit_groups.groups.$group_id) && in_array($bucket->id,$crit_groups.groups.$group_id)}checked="checked"{/if}> {$bucket->name}</label><br>
					{/foreach}
				</div>
			</div>
			{/if}
			{/foreach}
			</div>
		</td>
	</tr>
</table>

{* Get Ticket Fields *}
{include file="file:$core_tpl/groups/manage/filters/peek_get_custom_fields.tpl" fields=$ticket_fields filter=$filter divName="divGetTicketFields" label="Ticket custom fields"}

{* Message *}
{assign var=expanded value=false}
{if isset($filter->criteria.is_outgoing) || isset($filter->criteria.subject) || isset($filter->criteria.from) || isset($filter->criteria.body)}
	{assign var=expanded value=true}
{/if}
<label><input type="checkbox" {if $expanded}checked="checked"{/if} onclick="toggleDiv('divBlockMessage',(this.checked?'block':'none'));if(!this.checked)checkAll('divBlockMessage',false);"> <b>Message</b></label><br>
<table width="500" style="margin-left:10px;display:{if $expanded}block{else}none{/if};" id="divBlockMessage">
	<tr>
		<td valign="top">
			{assign var=crit_is_outgoing value=$filter->criteria.is_outgoing}
			<label><input type="checkbox" id="chkRuleIsOutgoing" name="rules[]" value="is_outgoing" {if !is_null($crit_is_outgoing)}checked="checked"{/if}> Event:</label>
		</td>
		<td valign="top">
			<label><input type="radio" name="value_is_outgoing" value="1" {if $crit_is_outgoing.value}checked="checked"{/if} onclick="document.getElementById('chkRuleIsOutgoing').checked=true;"> Outgoing</label>
			<label><input type="radio" name="value_is_outgoing" value="0" {if !$crit_is_outgoing.value}checked="checked"{/if} onclick="document.getElementById('chkRuleIsOutgoing').checked=true;"> Incoming</label>
		</td>
	</tr>
	{*
	<tr>
		<td valign="top">
			{assign var=crit_tocc value=$filter->criteria.tocc}
			<label><input type="checkbox" id="chkRuleTo" name="rules[]" value="tocc" {if !is_null($crit_tocc)}checked="checked"{/if}> To/Cc:</label>
		</td>
		<td valign="top">
			<input type="text" name="value_tocc" size="45" value="{$crit_tocc.value|escape}" value="{$tocc_list}" onchange="document.getElementById('chkRuleTo').checked=((0==this.value.length)?false:true);" style="width:95%;"><br>
			<i>Comma-delimited address patterns; only one e-mail must match.</i><br>
			Example: support@example.com, support@*, *@example.com<br>
		</td>
	</tr>
	*}
	<tr>
		<td valign="top">
			{assign var=crit_from value=$filter->criteria.from}
			<label><input type="checkbox" id="chkRuleFrom" name="rules[]" value="from" {if !is_null($crit_from)}checked="checked"{/if}> From:</label>
		</td>
		<td valign="top">
			<input type="text" name="value_from" size="45" value="{$crit_from.value|escape}" onchange="document.getElementById('chkRuleFrom').checked=((0==this.value.length)?false:true);" style="width:95%;">
		</td>
	</tr>
	<tr>
		<td valign="top">
			{assign var=crit_subject value=$filter->criteria.subject}
			<label><input type="checkbox" id="chkfiltersubject" name="rules[]" value="subject" {if !is_null($crit_subject)}checked="checked"{/if}> Subject:</label>
		</td>
		<td valign="top">
			<input type="text" name="value_subject" size="45" value="{$crit_subject.value|escape}" onchange="document.getElementById('chkfiltersubject').checked=((0==this.value.length)?false:true);" style="width:95%;">
		</td>
	</tr>
	<tr>
		<td valign="top">
			{assign var=crit_body value=$filter->criteria.body}
			<label><input type="checkbox" id="chkRuleBody" name="rules[]" value="body" {if !is_null($crit_body)}checked="checked"{/if}> Body Content:</label>
		</td>
		<td valign="top">
			<input type="text" name="value_body" size="45" value="{$crit_body.value|escape}" onchange="document.getElementById('chkRuleBody').checked=((0==this.value.length)?false:true);" style="width:95%;"><br>
			<i>Enter as a <a href="http://us2.php.net/manual/en/regexp.reference.php" target="_blank">regular expression</a>; scans content line-by-line.</i><br>
			Example: /(how do|where can)/i<br>
		</td>
	</tr>
</table>

{* Message Headers *}
{assign var=expanded value=false}
{section name=headers start=0 loop=5}
	{assign var=headerx value='header'|cat:$smarty.section.headers.iteration}
	{if isset($filter->criteria.$headerx)}
		{assign var=expanded value=true}
	{/if}
{/section}
<label><input type="checkbox" {if $expanded}checked="checked"{/if} onclick="toggleDiv('divBlockHeaders',(this.checked?'block':'none'));if(!this.checked)checkAll('divBlockHeaders',false);"> <b>Message headers</b></label><br>
<table width="500" style="margin-left:10px;display:{if $expanded}block{else}none{/if};" id="divBlockHeaders">
	{section name=headers start=0 loop=5}
	{assign var=headerx value='header'|cat:$smarty.section.headers.iteration}
	{assign var=crit_headerx value=$filter->criteria.$headerx}
	<tr>
		<td valign="top">
			<input type="checkbox" id="chkHeader{$smarty.section.headers.iteration}" name="rules[]" {if !is_null($crit_headerx)}checked="checked"{/if} value="header{$smarty.section.headers.iteration}">
			<input type="text" name="{$headerx}" value="{$crit_headerx.header|escape}" size="16" onchange="document.getElementById('chkHeader{$smarty.section.headers.iteration}').checked=((0==this.value.length)?false:true);">:
		</td>
		<td valign="top">
			<input type="text" name="value_{$headerx}" value="{$crit_headerx.value|escape}" size="45" style="width:95%;">
		</td>
	</tr>
	{/section}
</table>

{* Get Address Fields *}
{include file="file:$core_tpl/groups/manage/filters/peek_get_custom_fields.tpl" fields=$address_fields filter=$filter divName="divGetAddyFields" label="Sender address"}

{* Get Org Fields *}
{include file="file:$core_tpl/groups/manage/filters/peek_get_custom_fields.tpl" fields=$org_fields filter=$filter divName="divGetOrgFields" label="Sender organization"}

<br>
<h2>Then perform these actions:</h2>
<table width="500">
	<tr>
		{assign var=act_email value=$filter->actions.email}
		<td valign="top" colspan="2">
			<input type="hidden" name="do[]" value="email">
			<b>Forward to:</b><br>
			{foreach from=$addresses item=address}
			<label><input type="checkbox" name="do_email[]" value="{$address->address|escape}" {if is_array($act_email.to) && in_array($address->address,$act_email.to)}checked="checked"{/if}> {$address->address}</label><br>
			{/foreach} 
		</td>
	</tr>
</table>

</div>
<br>

<button type="submit"><img src="{devblocks_url}c=resource&p=cerberusweb.core&f=images/check.gif{/devblocks_url}" align="top"> {$translate->_('common.save_changes')}</button>
</form>
<br>