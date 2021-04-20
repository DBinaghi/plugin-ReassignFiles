<?php
queue_js_file('items');
queue_js_file('tabs');
queue_css_file('reassignfiles');
echo head(array('title' => __('Reassign Files to Item'), 'bodyclass' => 'reassignfiles'));
?>
<?php echo flash(); ?>
<div class="drawer-contents" style="background: none">
	<form method="post" action="<?php echo url('reassign-files/index/save'); ?>">
		<fieldset style="border: 1px solid black; padding:15px; margin:10px;">
			<h2><?php echo __("Step 1: Select Item"); ?></h2>
			<div class="field">
				<div class="inputs seven columns alpha">
					<p class="explanation"><?php echo __("Please select an existing item to reassign files to."); ?></p>
					<p class="explanation">
						<?php
							echo sprintf(__("<em>Please note:</em> Currently displaying %d latest modified items."), $numLatest)
								. " <a href='$extendedUrl'>"
								. "[" . sprintf(__("Click here to display %d more."), $numExtension) . "]"
								. "</a>";
						?>
					</p>
					<?php echo $this->formSelect('reassignFilesItem', false, array('multiple' => false, 'style' => 'width: 700px;'), $itemSelect); ?>
				</div>
			</div>
		</fieldset>
		
		<fieldset style="border: 1px solid black; padding:15px; margin:10px;">
			<h2><?php echo __("Step 2: Select File(s) to Reassign"); ?></h2>
			<div class="field">
				<div class="inputs seven columns alpha">
					<p class="explanation"><?php echo __("Please select one or more files to be reassigned to the above selected item (tip: you can use the input field here below to filter the list)."); ?></p>
					<?php echo $this->formText('reassignFilesFilter', null);	?>
					<?php echo $this->formSelect('reassignFilesFiles[]', null, array('multiple' => true, 'size' => 15, 'style' => 'width: 700px;'), $files); ?>
				</div>
			</div>
		</fieldset>
		
		<fieldset>
			<div class="field">
				<button type="submit" name="reassign-button" class="add big green button"><?php echo __("Reassign File(s)"); ?></button>
			</div>
		</fieldset>
	</div>
</form>
<?php echo foot();
