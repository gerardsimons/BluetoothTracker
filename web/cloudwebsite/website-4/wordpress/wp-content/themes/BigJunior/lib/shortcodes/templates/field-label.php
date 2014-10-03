<?php
$settings = $vars['settings'];
$desc     = array_value('desc', $settings);//Optional value
$title    = array_value('title', $settings);//Optional value
$class    = array_value('label-class', $settings);//Optional value
?>
<div class="px-input-label <?php echo $class; ?>">
    <strong><?php echo $title; ?></strong>
    <?php if(strlen($desc)){ ?>
    <span><?php echo $desc; ?></span>
    <?php } ?>
</div>