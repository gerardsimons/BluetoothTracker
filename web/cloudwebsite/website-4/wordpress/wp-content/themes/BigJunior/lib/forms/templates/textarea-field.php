<?php
$name     = $vars['key'];
$settings = $vars['settings'];
$class    = array_value('class', $settings);//Optional value
$placeholder = array_value('placeholder', $settings);//Optional value
?>

<div class="field textarea-input <?php echo $class; ?>">
    <textarea name="<?php echo $name; ?>" cols="30" rows="10" placeholder="<?php echo $placeholder; ?>" ><?php echo esc_textarea($this->GetValue($name)); ?></textarea>
</div>