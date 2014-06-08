<?php
$name = $vars['key'];
$settings = $vars['settings'];
$placeholder = array_value('placeholder', $settings);//Optional value
?>

<div class="field csv-input">
    <div class="text-input clear-after">
        <input type="text" name="csv-value" class="csv-value" placeholder="<?php echo $placeholder; ?>" />
        <a href="#" class="btn-add"></a>
    </div>
    <div class="list"></div>
    <input type="hidden" name="<?php echo $name; ?>" value="<?php echo esc_attr( $this->GetValue($name) ); ?>" />
</div>