{if !empty($categories)}
<form action="{devblocks_url}{/devblocks_url}" method="post">
<input type="hidden" name="c" value="tickets">
<input type="hidden" name="a" value="saveTeamFilters">
<input type="hidden" name="team_id" value="{$dashboard_team_id}">
<table cellpadding="0" cellspacing="0" border="0" class="tableOrange" width="220" class="tableBg">
	<tr>
		<td class="tableThOrange" nowrap="nowrap"> <img src="{devblocks_url}images/bookmark.gif{/devblocks_url}"> {$translate->_('dashboard.team_filters')|capitalize}</td>
	</tr>
	<tr>
		<td class="tableCellBg" width="100%" style="padding:2px;">
			<table cellpadding="0" cellspacing="0" border="0" width="100%">
				{foreach from=$categories item=category key=category_id}
					{if $category_counts.0}
						{math assign=percent equation="(x/y)*50" x=$category_counts.$category_id y=$category_counts.0 format="%0.0f"}
					{/if}
				<tr>
					<td class="tableCellBg" width="100%"><label><input type="checkbox" name="categories[]" value="{$category->id}" {if isset($team_filters.categories.$category_id)}checked{/if}> {$category->name} ({$category_counts.$category_id})</label></td>
					<td class="tableCellBgIndent" width="0%" nowrap="nowrap" style="width:51px;"><img src="{devblocks_url}images/cerb_graph.gif{/devblocks_url}" width="{$percent}" height="15"><img src="{devblocks_url}images/cer_graph_cap.gif{/devblocks_url}" height="15" width="1"></td>
				</tr>
				{foreachelse}
				<tr>
					<td colspan="2" class="tableCellBg">{$translate->_('dashboard.no_categories')}</td>
				</tr>
				{/foreach}
			</table>
			
			<label><input type="checkbox" name="hide_categorized" value="1" {if $team_filters.hide_categorized}checked{/if}> <i>Hide Categorized</i></label><br>
			<label><input type="checkbox" name="hide_assigned" value="1" {if $team_filters.hide_assigned}checked{/if}> <i>Hide with Active Tasks</i></label><br>
			
			<div align="right">
				<input type="submit" value="{$translate->_('common.filter')|capitalize}">
			</div>
		</td>
	</tr>
</table>
</form>
{/if}