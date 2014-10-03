<?php
$name     = $vars['key'];
$settings = $vars['settings'];
$class    = array_value('class', $settings);//Optional value
$placeholder = array_value('placeholder', $settings);//Optional value
?>

<div class="field text-input <?php echo $class; ?>">
    <input type="password" name="<?php echo $name; ?>" value="<?php echo esc_attr( $this->GetValue($name) ); ?>" placeholder="<?php echo $placeholder; ?>" />
</div>