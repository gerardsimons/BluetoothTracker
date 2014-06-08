<?php
require_once(dirname(__FILE__) . '/settings.php');
require_once(THEME_LIB . '/forms/template.php');

//Get type of the shortcode
if(!isset($_GET['type']))
{
    _e('Type parameter must be provided', TEXTDOMAIN);
    return;
}

$type = trim($_GET['type']);

if(!array_key_exists($type, $pxScTemplate))
{
    printf(__('No shortcode is defined with type "%s"', TEXTDOMAIN), $type);
    return;
}

$sc = $pxScTemplate[$type];
$template = new Template(path_combine(dirname(__FILE__), 'templates'));

//Output shortcode template
?>
<script class="px-sc-template" type="text/html"><?php echo $sc['shortcode']; ?></script>
<script class="px-sc-preview" type="text/html"><?php echo MCE_URI; ?>/preview.php</script>
<script class="px-sc-flags" type="text/html"><?php echo array_value('flags', $sc); ?></script>
<?php

foreach($sc['fields'] as $key => $settings)
{
    ?>
    <div class="px-sc-section px-clearfix">
        <?php
        $fname = $settings['type'] . '-field';
        echo $template->GetTemplate($fname, array('key'=>$key, 'settings'=>$settings));
        ?>
    </div>
    <?php
}