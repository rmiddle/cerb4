<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2007, WebGroup Media LLC
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
/*
 * IMPORTANT LICENSING NOTE from your friends on the Cerberus Helpdesk Team
 * 
 * Sure, it would be so easy to just cheat and edit this file to use the 
 * software without paying for it.  But we trust you anyway.  In fact, we're 
 * writing this software for you! 
 * 
 * Quality software backed by a dedicated team takes money to develop.  We 
 * don't want to be out of the office bagging groceries when you call up 
 * needing a helping hand.  We'd rather spend our free time coding your 
 * feature requests than mowing the neighbors' lawns for rent money. 
 * 
 * We've never believed in encoding our source code out of paranoia over not 
 * getting paid.  We want you to have the full source code and be able to 
 * make the tweaks your organization requires to get more done -- despite 
 * having less of everything than you might need (time, people, money, 
 * energy).  We shouldn't be your bottleneck.
 * 
 * We've been building our expertise with this project since January 2002.  We 
 * promise spending a couple bucks [Euro, Yuan, Rupees, Galactic Credits] to 
 * let us take over your shared e-mail headache is a worthwhile investment.  
 * It will give you a sense of control over your in-box that you probably 
 * haven't had since spammers found you in a game of "E-mail Address 
 * Battleship".  Miss. Miss. You sunk my in-box!
 * 
 * A legitimate license entitles you to support, access to the developer 
 * mailing list, the ability to participate in betas and the warm fuzzy 
 * feeling of feeding a couple obsessed developers who want to help you get 
 * more done than 'the other guy'.
 *
 * - Jeff Standen, Mike Fogg, Brenan Cavish, Darren Sugita, Dan Hildebrandt
 * 		and Joe Geck.
 *   WEBGROUP MEDIA LLC. - Developers of Cerberus Helpdesk
 */
class DAO_CommunityTool extends DevblocksORMHelper {
    const ID = 'id';
    const NAME = 'name';
    const CODE = 'code';
    const COMMUNITY_ID = 'community_id';
    const EXTENSION_ID = 'extension_id';
    
	public static function create($fields) {
	    $db = DevblocksPlatform::getDatabaseService();
		$id = $db->GenID('generic_seq');
		$code = self::_generateUniqueCode();
		
		$sql = sprintf("INSERT INTO community_tool (id,name,code,community_id,extension_id) ".
		    "VALUES (%d,'',%s,0,'')",
		    $id,
		    $db->qstr($code)
		);
		$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		self::update($id, $fields);
		
		return $id;
	}

	// [TODO] APIize?
	private static function _generateUniqueCode($length=8) {
	    $db = DevblocksPlatform::getDatabaseService();
	    
	    // [JAS]: [TODO] Inf loop check
	    do {
	        $code = substr(md5(mt_rand(0,1000) * microtime()),0,$length);
	        $exists = $db->GetOne(sprintf("SELECT id FROM community_tool WHERE code = %s",$db->qstr($code)));
	        
	    } while(!empty($exists));
	    
	    return $code;
	}
	
	public static function update($id, $fields) {
        self::_update($id, 'community_tool', $fields);
	}
	
	/**
	 * Enter description here...
	 *
	 * @param integer $id
	 * @return Model_CommunityTool
	 */
	public static function get($id) {
		$items = self::getList(array($id));
		
		if(isset($items[$id]))
		    return $items[$id];
		    
		return NULL;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param string $code
	 * @return Model_CommunityTool
	 */
	public static function getByCode($code) {
		if(empty($code)) return NULL;
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = sprintf("SELECT id FROM community_tool WHERE code = %s",
			$db->qstr($code)
		);
		$id = $db->GetOne($sql);
		
		if(!empty($id)) {
			return self::get($id);
		}
		
		return NULL;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param array $ids
	 * @return Model_CommunityTool[]
	 */
	public static function getList($ids=array()) {
	    if(!is_array($ids)) $ids = array($ids);
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = "SELECT id,name,code,community_id,extension_id ".
		    "FROM community_tool ".
		    (!empty($ids) ? sprintf("WHERE id IN (%s) ", implode(',', $ids)) : " ").
		    "ORDER BY community_id"
		;
		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */

		return self::_createObjectsFromResultSet($rs);
	}
	
	static function getWhere($where=null) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = "SELECT id,name,code,community_id,extension_id ".
			"FROM community_tool ".
			(!empty($where)?sprintf("WHERE %s ",$where):" ").
			"ORDER BY community_id "
			;
		$rs = $db->Execute($sql);
		
		return self::_createObjectsFromResultSet($rs);
	}
	
	static private function _createObjectsFromResultSet($rs) {
		$objects = array();
		
		if(is_a($rs,'ADORecordSet'))
		while(!$rs->EOF) {
		    $object = new Model_CommunityTool();
		    $object->id = intval($rs->fields['id']);
		    $object->name = $rs->fields['name'];
		    $object->code = $rs->fields['code'];
		    $object->community_id = intval($rs->fields['community_id']);
		    $object->extension_id = $rs->fields['extension_id'];
		    $objects[$object->id] = $object;
		    $rs->MoveNext();
		}
		
		return $objects;
	}
	
	public static function delete($ids) {
	    if(!is_array($ids)) $ids = array($ids);
	    $db = DevblocksPlatform::getDatabaseService();
	    
		foreach($ids as $id) {
			@$tool = DAO_CommunityTool::get($id);
			if(empty($tool)) continue;

		    /**
		     * [TODO] [JAS] Deleting a community tool needs to run a hook first so the 
		     * tool has a chance to clean up its own DB tables abstractly.
		     * 
		     * e.g. Knowledgebase instances which store data outside the tool property table
		     * 
		     * After this is done, a future DB patch for those plugins should clean up any 
		     * orphaned data.
		     */
			
		    $sql = sprintf("DELETE QUICK FROM community_tool WHERE id = %d", $id);
		    $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
			
		    $sql = sprintf("DELETE QUICK FROM community_tool_property WHERE tool_code = '%s'", $tool->code);
		    $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		}
	}

    /**
     * Enter description here...
     *
     * @param DevblocksSearchCriteria[] $params
     * @param integer $limit
     * @param integer $page
     * @param string $sortBy
     * @param boolean $sortAsc
     * @param boolean $withCounts
     * @return array
     */
    static function search($params, $limit=10, $page=0, $sortBy=null, $sortAsc=null, $withCounts=true) {
		$db = DevblocksPlatform::getDatabaseService();
		$fields = SearchFields_CommunityTool::getFields();
		
		// Sanitize
		if(!isset($fields[$sortBy]))
			$sortBy=null;

        list($tables,$wheres) = parent::_parseSearchParams($params, array(), $fields,$sortBy);
		$start = ($page * $limit); // [JAS]: 1-based [TODO] clean up + document
		
		$sql = sprintf("SELECT ".
			"ct.id as %s, ".
			"ct.name as %s, ".
			"ct.code as %s, ".
			"ct.community_id as %s, ".
			"ct.extension_id as %s ".
			"FROM community_tool ct ",
//			"INNER JOIN team tm ON (tm.id = t.team_id) ".
			    SearchFields_CommunityTool::ID,
			    SearchFields_CommunityTool::NAME,
			    SearchFields_CommunityTool::CODE,
			    SearchFields_CommunityTool::COMMUNITY_ID,
			    SearchFields_CommunityTool::EXTENSION_ID
			).
			
			// [JAS]: Dynamic table joins
//			(isset($tables['ra']) ? "INNER JOIN requester r ON (r.ticket_id=t.id)" : " ").
			
			(!empty($wheres) ? sprintf("WHERE %s ",implode(' AND ',$wheres)) : "").
			(!empty($sortBy) ? sprintf("ORDER BY %s %s",$sortBy,($sortAsc || is_null($sortAsc))?"ASC":"DESC") : "")
		;
		$rs = $db->SelectLimit($sql,$limit,$start) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		$results = array();
		
		if(is_a($rs,'ADORecordSet'))
		while(!$rs->EOF) {
			$result = array();
			foreach($rs->fields as $f => $v) {
				$result[$f] = $v;
			}
			$ticket_id = intval($rs->fields[SearchFields_CommunityTool::ID]);
			$results[$ticket_id] = $result;
			$rs->MoveNext();
		}

		// [JAS]: Count all
		$total = -1;
		if($withCounts) {
		    $rs = $db->Execute($sql);
		    $total = $rs->RecordCount();
		}
		
		return array($results,$total);
    }
};

class SearchFields_CommunityTool implements IDevblocksSearchFields {
	// Table
	const ID = 'ct_id';
	const NAME = 'ct_name';
	const CODE = 'ct_code';
	const COMMUNITY_ID = 'ct_community_id';
	const EXTENSION_ID = 'ct_extension_id';
	
	/**
	 * @return DevblocksSearchField[]
	 */
	static function getFields() {
		$columns = array(
			SearchFields_CommunityTool::ID => new DevblocksSearchField(SearchFields_CommunityTool::ID, 'ct', 'id'),
			SearchFields_CommunityTool::NAME => new DevblocksSearchField(SearchFields_CommunityTool::NAME, 'ct', 'name'),
			SearchFields_CommunityTool::CODE => new DevblocksSearchField(SearchFields_CommunityTool::CODE, 'ct', 'code'),
			SearchFields_CommunityTool::COMMUNITY_ID => new DevblocksSearchField(SearchFields_CommunityTool::COMMUNITY_ID, 'ct', 'community_id'),
			SearchFields_CommunityTool::EXTENSION_ID => new DevblocksSearchField(SearchFields_CommunityTool::EXTENSION_ID, 'ct', 'extension_id'),
		);
		
		// Sort by label (translation-conscious)
		uasort($columns, create_function('$a, $b', "return strcasecmp(\$a->db_label,\$b->db_label);\n"));

		return $columns;		
	}
};	

class DAO_CommunityToolProperty {
	const TOOL_CODE = 'tool_code';
	const PROPERTY_KEY = 'property_key';
	const PROPERTY_VALUE = 'property_value';
	
	const _CACHE_PREFIX = 'um_comtoolprops_';
	
	static function getAllByTool($tool_code) {
		$cache = DevblocksPlatform::getCacheService();

		if(null == ($props = $cache->load(self::_CACHE_PREFIX.$tool_code))) {
			$props = array();
			
			$db = DevblocksPlatform::getDatabaseService();
			
			$sql = sprintf("SELECT property_key, property_value ".
				"FROM community_tool_property ".
				"WHERE tool_code = %s ",
				$db->qstr($tool_code)
			);
			$rs = $db->Execute($sql);
			
			$props = array();
			
			while(!$rs->EOF) {
				$k = $rs->fields['property_key'];
				$v = $rs->fields['property_value'];
				$props[$k] = $v;
				$rs->MoveNext();
			}
			
			$cache->save($props, self::_CACHE_PREFIX.$tool_code);
		}		
		
		return $props;
	}
	
	static function get($tool_code, $key, $default=null) {
		$props = self::getAllByTool($tool_code);
		@$val = $props[$key];
		
		return (is_null($val) || (!is_numeric($val) && empty($val))) ? $default : $val;
	}
	
	static function set($tool_code, $key, $value) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$db->Replace(
			'community_tool_property',
			array(
				self::TOOL_CODE => $db->qstr($tool_code),
				self::PROPERTY_KEY => $db->qstr($key),
				self::PROPERTY_VALUE => $db->qstr($value),
			),
			array(self::TOOL_CODE, self::PROPERTY_KEY),
			false
		);
		
		// Invalidate cache
		$cache = DevblocksPlatform::getCacheService();
		$cache->remove(self::_CACHE_PREFIX.$tool_code);
	}
};

class DAO_CommunitySession {
	const SESSION_ID = 'session_id';
	const CREATED = 'created';
	const UPDATED = 'updated';
	const PROPERTIES = 'properties';
	
	static public function save(Model_CommunitySession $session) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = sprintf("UPDATE community_session SET updated = %d WHERE session_id = %s",
			time(),
			$db->qstr($session->session_id)
		);
		$db->Execute($sql);
		
		$db->UpdateBlob(
			'community_session', 
			self::PROPERTIES, 
			serialize($session->getProperties()), 
			"session_id=".$db->qstr($session->session_id)
		);
	}
	
	/**
	 * @param string $session_id
	 * @return Model_CommunitySession
	 */
	static public function get($session_id) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = sprintf("SELECT session_id, created, updated, properties ".
			"FROM community_session ".
			"WHERE session_id = %s",
			$db->qstr($session_id)
		);
		$row = $db->GetRow($sql);
		
		if(empty($row)) {
			$session = self::create($session_id);
		} else {
			$session = new Model_CommunitySession();
			$session->session_id = $row['session_id'];
			$session->created = $row['created'];
			$session->updated = $row['updated'];
			
			if(!empty($row['properties']))
				@$session->setProperties(unserialize($row['properties']));
		}
		
		return $session;
	}
	
	/**
	 * @param string $session_id
	 * @return Model_CommunitySession
	 */
	static private function create($session_id) {
		$db = DevblocksPlatform::getDatabaseService();

		$session = new Model_CommunitySession();
		$session->session_id = $session_id;
		$session->created = time();
		$session->updated = time();
		
		$sql = sprintf("INSERT INTO community_session (session_id, created, updated, properties) ".
			"VALUES (%s, %d, %d, '')",
			$db->qstr($session->session_id),
			$session->created,
			$session->updated
		);
		$db->Execute($sql);
		
		self::gc(); // garbage collection
		
		return $session;
	}
	
	static private function gc() {
		$db = DevblocksPlatform::getDatabaseService();
		$sql = sprintf("DELETE QUICK FROM community_session WHERE updated < %d",
			(time()-(60*60)) // 1 hr
		);
		$db->Execute($sql);
	}
};

?>