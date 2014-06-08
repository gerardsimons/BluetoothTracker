<?php
$name        = $vars['key'];
$settings    = $vars['settings'];
$class       = array_value('class', $settings);//Optional value
$label       = array_value('label', $settings);//Optional value
$placeholder = array_value('placeholder', $settings);//Optional value
?>

<div class="field color-field clear-after <?php echo $class; ?>">
    <div class="label"><?php echo $label; ?></div>
    <div class="color-field-wrap clear-after">
        <input name="<?php echo $name; ?>" type="text" value="<?php echo esc_attr( $this->GetValue($name) ); ?>" class="colorinput" placeholder="<?php echo $placeholder; ?>" />
    </div>
</div>