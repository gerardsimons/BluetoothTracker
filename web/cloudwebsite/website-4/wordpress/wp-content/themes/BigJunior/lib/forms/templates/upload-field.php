<?php
$name        = $vars['key'];
$settings    = $vars['settings'];
$value       = $vars['val'];
$class       = array_value('class', $settings);
$title       = array_value('title', $settings, __('Upload Image', TEXTDOMAIN));
$referer     = array_value('referer', $settings);
$placeholder = array_value('placeholder', $settings);
?>
<div class="field upload-field clear-after <?php echo $class; ?>" data-title="<?php echo $title; ?>" data-referer="<?php echo $referer; ?>" >
    <input type="text" name="<?php echo $name; ?>" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php echo $placeholder; ?>" />
    <a href="#" class="upload-button"><?php _e('Browse', TEXTDOMAIN); ?></a>
</div>