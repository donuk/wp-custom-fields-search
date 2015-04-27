<form class='wpcfs-search-form'>
<h1>Form</h1>
<ul>
<?php
	foreach($inputs as $input){
?>
	<li class='<?=$input->getCSSClasses()?>'>
		<label for='<?php echo $input->getHtmlId()?>'><?php echo $input->getLabel();?></label>
		<?php echo $input->render();?>
	</li>
<?php
	}

?>
</ul>
<input type='submit' value='Search'/>
<input type='hidden' name='wpcfs-search-source' value='<?php echo $form_id?>'/>
</form>
