<?php
$name     = $vars['key'];
$settings = $vars['settings'];
$class    = array_value('class', $settings);//Optional value
$placeholder = array_value('placeholder', $settings);//Optional value
$flags    = array_value('flags', $settings);//Optional value
?>

<div class="px-input">
    <div class="px-input-text <?php echo $class; ?>">
        <input type="text" name="<?php echo $name; ?>" placeholder="<?php echo $placeholder; ?>" data-flags="<?php echo $flags; ?>" />
    </div>
</div>
<?php echo $this->GetTemplate('field-label', $vars); ?>