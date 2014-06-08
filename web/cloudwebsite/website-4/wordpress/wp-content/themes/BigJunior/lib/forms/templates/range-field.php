<?php
$name     = $vars['key'];
$settings = $vars['settings'];
$class    = array_value('class', $settings);//Optional value
$label    = array_value('label', $settings);//Optional value
$min      = array_value('min', $settings, 1);//Optional value
$max      = array_value('max', $settings, 100);//Optional value
$step     = array_value('step', $settings, 1);//Optional value
?>

<div class="field clear-after <?php echo $class; ?>">
    <div class="label"><?php echo $label; ?></div>
    <input name="<?php echo $name; ?>" type="range" min="<?php echo $min; ?>" max="<?php echo $max; ?>" step="<?php echo $step; ?>"  value="<?php echo esc_attr( $this->GetValue($name) ); ?>" />
</div>