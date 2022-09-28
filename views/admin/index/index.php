<?php
	queue_js_file('items');
	queue_js_file('tabs');
	queue_css_file('reassignfiles');
	echo head(array('title' => __('Reassign Files to Item'), 'bodyclass' => 'reassignfiles'));
?>

<?php echo flash(); ?>

<form method="post" action="<?php echo url('reassign-files/index/save'); ?>">
	<fieldset class="reassignfiles-fieldset">
		<legend><?php echo __("Step 1: Select Item"); ?></legend>
		<div class="field">
			<div class="inputs nine columns alpha">
				<p class="explanation">
					<?php 
						echo __("Please select an existing Item to reassign files to (currently displaying %d latest modified Items, <a href='$extendedUrl'>click here to display %d more</a>).", $numLatest, $numExtension); 
					?>
				</p>
				<?php echo $this->formSelect('reassignFilesItem', false, array('multiple' => false, 'style' => 'width: 700px;'), $itemSelect); ?>
			</div>
		</div>
	</fieldset>
	
	<fieldset class="reassignfiles-fieldset">
		<legend><?php echo __("Step 2: Select File(s) to Reassign"); ?></legend>
		<div class="field">
			<div class="inputs nine columns alpha">
				<p class="explanation"><?php echo __("Please select one or more files to be reassigned to the above selected Item (tip: you can use the input field here below to filter the list)."); ?></p>
				<?php echo $this->formText('reassignFilesFilter', null); ?>
				<?php echo $this->formSelect('reassignFilesFiles[]', null, array('multiple' => true, 'size' => 15, 'style' => 'width: 700px;'), $files); ?>
			</div>
		</div>
	</fieldset>
	
	<fieldset>
		<div class="field">
			<button type="submit" name="reassign-button" class="add big green button"><?php echo __("Reassign File(s)"); ?></button>
		</div>
	</fieldset>
</form>

<?php echo foot(); ?>
