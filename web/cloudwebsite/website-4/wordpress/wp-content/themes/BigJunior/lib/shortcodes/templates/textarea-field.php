<?php
$name     = $vars['key'];
$settings = $vars['settings'];
$class    = array_value('class', $settings);//Optional value
$placeholder = array_value('placeholder', $settings);//Optional value
$flags    = array_value('flags', $settings);//Optional value
?>
<div class="px-input">
    <div class="px-input-textarea <?php echo $class; ?>">
        <textarea name="<?php echo $name; ?>" cols="30" rows="10" placeholder="<?php echo $placeholder; ?>" data-flags="<?php echo $flags; ?>" ></textarea>
    </div>
</div>
<?php echo $this->GetTemplate('field-label', $vars); ?>