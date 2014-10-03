<?php
$name = $vars['key'];
$settings = $vars['settings'];
$options  = $settings['options'];
$selected     = array_value('default', $settings);//Optional value
$optionsFlags = array_value('option-flags', $settings, array());
$flags        = array_value('flags', $settings);//Optional value
?>

<div class="px-input">
    <div class="px-input-select">
        <div></div>
        <select name="<?php echo $name; ?>" data-flags="<?php echo $flags; ?>">
            <?php
            foreach($options as $value => $text)
            {
                $selectedAttr = $value == $selected ? 'selected="selected"' : '';
                $flags = array_value($value, $optionsFlags);
                ?>
                <option value="<?php echo esc_attr($value); ?>" data-flags="<?php echo $flags; ?>" <?php echo $selectedAttr; ?> ><?php echo $text; ?></option>
            <?php
            }
            ?>
        </select>
    </div>
</div>
<?php echo $this->GetTemplate('field-label', $vars); ?>