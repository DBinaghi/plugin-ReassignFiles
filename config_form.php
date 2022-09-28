<div class="field">
	<h2><?php echo __('General behaviour'); ?></h2>
	
	<div class="two columns alpha">
		<?php echo get_view()->formLabel('reassign_files_local_reassign', __('Reassign in Item Editor')); ?>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation">
			<?php
			echo __('Check this if you want to have a reassign functionality on the "Files" tab inside the admin item editor. '.
					'If you frequently move files from one item to another, this could be helpful. However, if reassigning files '.
					'is a rather uncommon procedure, especially in huge Omeka databases, it is advisable to keep this '.
					'setting turned off and conduct it through the global reassign screen located in the left menu bar.');
			?>
		</p>
		<?php echo get_view()->formCheckbox('reassign_files_local_reassign', null, array('checked' => $localReassign)); ?>
	</div>

	<div class="two columns alpha">
		<?php echo get_view()->formLabel('reassign_files_show_file_details', __('Show Files details')); ?>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation">
			<?php
			echo __('Check this if you want to show extra details in the Files select box, like Item\'s title.');
			?>
		</p>
		<?php echo get_view()->formCheckbox('reassign_files_show_file_details', null, array('checked' => $showFileDetails)); ?>
	</div>

	<div class="two columns alpha">
		<?php echo get_view()->formLabel('reassign_files_delete_orphaned_items', __('Auto-Delete Orphaned Items')); ?>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation">
			<?php
			echo __('Check this if you want to automatically delete Items that become "orphaned" after reassigning their files to other Items'.
					'(often the case of files that were bulk-added through the "Dropbox" plugin). '.
					'This will affect only those Items that afterwards:'.
					'<ul>'.
					'<li>do not have an Item Type assigned,</li>'.
					'<li>do not have any associated Files left,</li>'.
					'<li>are neither subject nor object in a relationship (in case the "Item Relations" plugin is installed),</li>'.
					'<li>and contain at the most a title, but no other metadata whatsoever (i.e. entered text).</li>'.
					'</ul>');
			?>
		</p>
		<?php echo get_view()->formCheckbox('reassign_files_delete_orphaned_items', null, array('checked' => $deleteOrphanedItems)); ?>
	</div>
</div>

<h2><?php echo __('One-time actions'); ?></h2>
<div class="field">
	<div class="two columns alpha">
		<?php echo get_view()->formLabel('reassign_files_delete_orphaned_items_now', __('Orphaned Items Check')); ?>
	</div>
	<div class="inputs five columns omega">
		<p class="explanation">
			<?php
			echo __('Check this to initiate the search for orphaned Items and their deletion <b>now</b> and exactly <b>once</b>. '.
					'Please note: this action will be carried out as soon as you click on "Save Changes".');
			?>
		</p>
		<?php echo get_view()->formCheckbox('reassign_files_delete_orphaned_items_now', null, array('checked' => false)); ?>
	</div>
</div>
