<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2009, WebGroup Media LLC
|   unless specifically noted otherwise.
|
| This source code is released under the Cerberus Public License.
| The latest version of this license can be found here:
| http://www.cerberusweb.com/license.php
|
| By using this software, you acknowledge having read this license
| and agree to be bound thereby.
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

$db = DevblocksPlatform::getDatabaseService();
$datadict = NewDataDictionary($db,'mysql'); /* @var $datadict ADODB2_mysql */ // ,'mysql' 

$tables = $datadict->MetaTables();
$tables = array_flip($tables);

// ===========================================================================
// Expand timetracking_entry max size from 255 to longtext

if(isset($tables['timetracking_entry'])) {
	$columns = $datadict->MetaColumns('timetracking_entry');
	
	if(isset($columns['notes'])) {
		$sql = sprintf("ALTER TABLE timetracking_entry CHANGE COLUMN notes notes longtext DEFAULT '' NOT NULL");
		$db->Execute($sql);
	}
}

return TRUE;