<?php
$name     = $vars['key'];
$settings = $vars['settings'];
$selected = $this->GetValue($name);
$options  = $settings['options'];
$class    = array_value('class', $settings);
?>
<div class="field imageSelect <?php echo $class; ?>">
    <?php
    foreach($options as $key => $value)
    {
        $selectedClass = $value == $selected ? 'selected' : '';
        ?>
        <a href="#" class="<?php echo $key . ' ' . $selectedClass; ?>"><?php echo $value; ?></a>
    <?php
    }
    ?>
    <input name="<?php echo $name; ?>" type="text" value="<?php echo $selected; ?>" />
</div>