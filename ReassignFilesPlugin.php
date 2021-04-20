<?php

/**
* ConditionalElements plugin.
*
* @package Omeka\Plugins\ReassignFiles
*/
class ReassignFilesPlugin extends Omeka_Plugin_AbstractPlugin
{
	// Define Hooks
	protected $_hooks = array(
		'initialize',
		'install',
		'uninstall',
		'define_acl',
		'admin_items_form_files',
		'after_save_item',
		'config_form',
		'config'
	);

	//Define Filters
	protected $_filters = array('admin_navigation_main');

	//Define Options
	protected $_options = array(
		'reassign_files_local_reassign' => 0,
		'reassign_files_show_file_details' => 0,
		'reassign_files_delete_orphaned_items' => 1
	);
	
	public function hookInitialize()
	{
		add_translation_source(dirname(__FILE__) . '/languages');
	}

	/**
	* Install the plugin.
	*/
	public function hookInstall() {

		SELF::_installOptions();
	}

	/**
	* Uninstall the plugin.
	*/
	public function hookUninstall() {

		SELF::_uninstallOptions();
	}

	/*
	* Define ACL entry for reassignfiles controller.
	*/
	public function hookDefineAcl($args)
	{
		$acl = $args['acl'];

		$indexResource = new Zend_Acl_Resource('ReassignFiles_Index');
		$acl->add($indexResource);

		$acl->allow(array('super', 'admin'), 'ReassignFiles_Index', 'index');

	}

	/**
	* Display the reassignfiles list on the	item form.
	* This simply adds a heading to the output
	*/
	public function hookAdminItemsFormFiles()
	{
		$localReassign = (int)(boolean) get_option('reassign_files_local_reassign');
		if ($localReassign) {
			echo '<h3>' . __('Add Files from Other Items') . '</h3>';
			$itemId = metadata('item', 'id');
			$fileNames = SELF::reassignFiles_getFileNames($itemId); // from helpers/ReassignFilesFunctions.php
			echo common('reassignfileslist', array( "fileNames" => $fileNames ), 'index');
		}
	}

	public function hookAfterSaveItem($args)
	{
		if (!$args['post']) return;

		$record = $args['record'];
		$post = $args['post'];

		// reassign the selected files from other items to the current item
		if (isset($post['reassignFilesFiles']) and (isset($post['itemId']))) {
			$itemID = ( $post['itemId'] ? $post['itemId'] : $args["record"]["id"] );
			$errMsg = SELF::reassignFiles_reassignFiles($itemID, $post['reassignFilesFiles']);
			# if ($errMsg) { $this->_helper->flashMessenger( $errMsg, 'error' ); }
			// ... turns out, we don't actually have a $this->_helper object here :-(
		}
	}

	/**
	* Display the plugin configuration form.
	*/
	public static function hookConfigForm() {
		$localReassign = (int)(boolean) get_option('reassign_files_local_reassign');
		$showFileDetails = (int)(boolean) get_option('reassign_files_show_file_details');
		$deleteOrphanedItems = (int)(boolean) get_option('reassign_files_delete_orphaned_items');
		require dirname(__FILE__) . '/config_form.php';
	}

	/**
	* Handle the plugin configuration form.
	*/
	public static function hookConfig() {
		$localReassign = (int)(boolean) $_POST['reassign_files_local_reassign'];
		set_option('reassign_files_local_reassign', $localReassign);
		$showFileDetails = (int)(boolean) $_POST['reassign_files_show_file_details'];
		set_option('reassign_files_show_file_details', $showFileDetails);
		$deleteOrphanedItems = (int)(boolean) $_POST['reassign_files_delete_orphaned_items'];
		set_option('reassign_files_delete_orphaned_items', $deleteOrphanedItems);
		$deleteOrphanedItemsNow = (int)(boolean) $_POST['reassign_files_delete_orphaned_items_now'];
		if ($deleteOrphanedItemsNow) SELF::reassignFiles_deleteOrphans();
	}

	/**
	* reassignfiles admin navigation filter
	*/
	public function filterAdminNavigationMain($nav)
	{
		if (is_allowed('ReassignFiles_Index', 'index')) {
			$nav[] = array('label' => __('Reassign Files'), 'uri' => url('reassign-files'));
		}
		return $nav;
	}

	private function _batchDeleteOrphanedItems() {
		$db = get_db();
		#check file? itemrelation installed? title? description?
		$sql= "select id from `$db->Items` ";
		$items = $db->fetchAll($sql);
		#echo "<pre>"; print_r($items); die("</pre>");
	}

	/**
	* Returns all fileNames as data source for the multi-select box
	*/
	public static function reassignFiles_getFileNames($filterItemID = 0) {
		$filterItemId = intval($filterItemID);
		$filterItemInfix = ( $filterItemId > 0 ? "AND f.item_id <> $filterItemID" : "" );
		$showFileDetails = (int)(boolean) get_option('reassign_files_show_file_details');

		$fileNames = array();
		$db = get_db();

		$select = "
			SELECT f.original_filename AS original_filename, f.item_id AS itemId, f.id AS fileId
			FROM {$db->File} f
			WHERE 1 $filterItemInfix
			ORDER BY original_filename
		";

		$files = $db->fetchAll($select);

		$itemIds = array();
		foreach ($files as $file) {
			$itemIds[$file['itemId']] = $file['itemId'];
		}

		$itemNames = array();
		foreach ($itemIds as $itemId) {
			$item = get_record_by_id('Item', $itemId);
			if (isset($item)) $itemNames[$itemId] = metadata($item, array('Dublin Core', 'Title'));
		}

		foreach ($files as $file) {
			$fileNames[$file['fileId']] = $file['original_filename'] . ' [' .
				($showFileDetails ? 'item #' . $file['itemId'] . ', ' . $itemNames[$file['itemId']] . ' | ' : '') .
				'file #' . $file['fileId'] . ']';
		}
		return $fileNames;
	}

	/**
	* Do the actual work: Reassign the $files (specified by their file IDs towards one target item ID
	*/
	public static function reassignFiles_reassignFiles($itemID, $files) {
		$errMsg = false;
		$itemID = intval($itemID); // typecast / filter item ID for strange characters

		if ($itemID) {
			$db = get_db();
			// make sure that the target item actually exists in the database
			$targetExists = $db->fetchOne("SELECT count(*) FROM `$db->Items` where id=$itemID");

			if ($targetExists) {
				$fileIDs = array();
				foreach ($files as $file) {
					$fileID = intval($file); // typecast / filter file IDs for strange characters
					if ($fileID) {$fileIDs[] = $fileID;}
				}

				if ($fileIDs) { // at least one?
					$fileIDs = implode(",", $fileIDs);
					$deleteOrphanedItems = (int)(boolean) get_option('reassign_files_delete_orphaned_items');

					// 1st: If applicable, figure out which items might be orphaned after the reassign
					$potentialOrphans = array();
					if ($deleteOrphanedItems) {
						$sql = "SELECT item_id from `$db->File` where id IN ($fileIDs)";
						$potentialOrphans = $db->fetchAll($sql);
					}

					// 2nd: Actually reassign the files
					$sql = "UPDATE `$db->File` set item_id = $itemID where id IN ($fileIDs)";
					$db->query($sql); // let's do this

					// 3rd: If applicable, take care of orphans, i.e. delete them
					if ($deleteOrphanedItems) SELF::reassignFiles_deleteOrphans($potentialOrphans);

					# $errMsg = $sql;
				}
				else {$errMsg = __('Please select one or more files to reassign to the selected item.');}
			}
			else {$errMsg = __('Please select an existing item to reassign files to.');}
		}
		else {$errMsg = __('Please select an item to reassign files to.');}

		return $errMsg;
	}

	/**
	* If applicable, check if items who just had files assigned to other items are now "empty" and, if so, delete them
	*/
	static function reassignFiles_deleteOrphans($potentialOrphans = false) {
		$db = get_db();

		$idInfix = "WHERE true"; // Sanity: Check _all_ items

		if (is_array($potentialOrphans)) { // Received an array of item IDs?
			$justIds = array();
			foreach ($potentialOrphans as $potentialOrphan) $justIds[] = $potentialOrphan["item_id"];
			if ($justIds) {
				$justIdString = implode(",", $justIds);
				$idInfix = "WHERE it.id in ($justIdString)"; // limiting the items to be searched
			}
			else {
				$idInfix="WHERE false"; // No IDs? Then don't search at all
			} 
		}

		// Huge left join query:
		// - Find those items (in case they are still existent)
		// - without any element texts
		// - without any other files assigned to them
		// - (if applicable) without item relations participation

		// Is ItemRelations installed? In that case: Create infixes to check items' relations as well
		$irJoin = $irWhere = "";
		if (SELF::reassignFiles_withItemRelations()) {
			$irJoin = "LEFT JOIN `$db->ItemRelationsRelations` ir
				ON it.id = ir.subject_item_id OR it.id = ir.object_item_id";
			$irWhere = "AND ir.subject_item_id IS NULL
				AND ir.object_item_id IS NULL";
		}

		$sql = "SELECT it.id
			FROM `$db->Items` it
			LEFT JOIN {$db->ElementText} et
			ON it.id = et.record_id AND et.element_id<>50
			LEFT JOIN `$db->File` f
			ON it.id = f.item_id
			$irJoin
			$idInfix
			AND et.record_id IS NULL
			AND f.item_id IS NULL
			$irWhere
			AND it.item_type_id IS NULL";
		$orphans = $db->fetchAll($sql);

		if ($orphans) {
			foreach ($orphans as $orphan) {
				$orphanObject = $db->getTable('Item')->find($orphan["id"]);
				$orphanObject->delete();
			}
		}
	}

	/**
	* Checks if ItemRelations is installed, so it could be taken into account during orphaned items search
	*/
	static function reassignFiles_withItemRelations() {
		$db = get_db();
		$sql = "SELECT 1 FROM `$db->ItemRelationsRelations` LIMIT 1";

		$withItemRelations = false;
		try {$withItemRelations = ($db->fetchOne($sql) === 1);}
		catch (Exception $e) { $withItemRelations=false; }

		return $withItemRelations;
	}
}
