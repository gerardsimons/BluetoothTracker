<?php
$name = $vars['key'];
$settings = $vars['settings'];
$default  = array_value('default', $settings);
$selected = $this->GetValue($name);
$selected = $selected == '' ? $default : $selected;
$options  = $settings['options'];
$class        = array_value('class', $settings);
$selectAttrs  = array_value('attributes', $settings);
$optionsAttrs = array_value('option-attributes', $settings, array());
?>
<div class="field clear-after <?php echo $class; ?>">
    <div class="select">
        <div></div>
        <select name="<?php echo $name; ?>" <?php echo $selectAttrs; ?>>
            <?php
            foreach($options as $value => $text)
            {
                $selectedAttr = $value == $selected ? 'selected="selected"' : '';
                $attrs = array_key_exists($value, $optionsAttrs) ? $optionsAttrs[$value] : '';
                ?>
                <option value="<?php echo esc_attr($value); ?>" <?php echo "$selectedAttr $attrs"; ?>><?php  echo $text; ?></option>
            <?php
            }
            ?>
        </select>
    </div>
</div>