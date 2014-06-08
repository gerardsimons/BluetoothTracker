<?php
/* @var Template $this */
$cnt = count($vars);
$i=1;

foreach($vars as $sKey => $section)
{
    ?>
    <div class="section section-<?php echo $sKey ?>">
        <div class="section-head">
            <div class="section-tooltip"><?php echo $section['tooltip'];  ?></div>
            <div class="label"><?php echo $section['title']; ?></div>
        </div>
        <?php
        //Render fields
        $fields = $section['fields'];

        foreach($fields as $key => $settings)
        {
            $isArray = array_value('array', array_value('meta', $settings, array()), false);
            $val     = $this->GetValue($key);
            $fieldRepeat = 1;

            //Convert the key so it become array type
            if($isArray)
            {
                $key .= '[]';

                if(is_array($val))
                    $fieldRepeat = max(count($val), $fieldRepeat);
            }

            for($m=0; $m<$fieldRepeat; $m++)
            {
                $value = is_array($val) ? array_value($m, $val) : $val;

                echo $this->GetField($key, $settings, array('val' => $value));
            }
        }
        ?>
    </div>
    <?php if($i < $cnt){ ?>
    <hr />
<?php
}
    $i++;
}