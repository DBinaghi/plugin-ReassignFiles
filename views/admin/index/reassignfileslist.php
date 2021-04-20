<p><?php echo __('Please select one or more files to be reassigned to the above selected Item (tip: you can use the input field to filter the list).'); ?></p>
<div class="drawer-contents" style="background: none">
	<?php
		$view = get_view();
		echo $view->formText('reassignFilesFilter', null);
		$itemId = metadata('item', 'id');
		echo $view->formSelect('reassignFilesFiles[]', null , array('multiple' => true, 'size' => 15, 'style' => 'width: 600px;'), $fileNames);
	?>
	<input type="hidden" name="itemId" value="<?php echo $itemId; ?>">
</div>
