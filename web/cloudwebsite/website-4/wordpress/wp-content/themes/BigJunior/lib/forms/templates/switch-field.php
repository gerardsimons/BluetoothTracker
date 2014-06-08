<?php
$name     = $vars['key'];
$settings = $vars['settings'];
$class    = array_value('class', $settings);//Optional value
$state0   = $settings['state0'];
$state1   = $settings['state1'];
$default  = array_value('default', $settings);//Optional value
$val      = $this->GetValue($name);
$val      = strlen($val) ? $val : $default;
?>
<div class="field clear-after <?php echo $class; ?>">
    <div class="label"></div>
    <input name="<?php echo $name; ?>" type="range" class="switch" value="<?php echo esc_attr( $val ); ?>" min="0" max="1" step="1"  data-state0="<?php echo $state0; ?>" data-state1="<?php echo $state1; ?>" />
</div>