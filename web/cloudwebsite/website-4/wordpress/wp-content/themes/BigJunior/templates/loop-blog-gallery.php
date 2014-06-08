<?php get_template_part( 'templates/loop', "blog-meta" ); ?>
<div class="post-content">
    <div class="post-media">
    <?php

    $images = get_meta('gallery');
    if(is_array($images) && count($images))
    {?>
        <div class="flexslider">
            <ul class="slides">
                <?php
                $imageSize = 'post-thumbnail';
                foreach($images as $img)
                {
                    //For getting image size use
                    //http://php.net/manual/en/function.getimagesize.php
                    $imgId = get_image_id($img);
                    if($imgId == -1)//Fallback
                        $imgTag = "<img src=\"$img\" />";
                    else
                        $imgTag = wp_get_attachment_image($imgId, $imageSize);
                    ?>
                    <li><?php echo $imgTag; ?></li>
                <?php
                }?>
            </ul>
        </div>
    <?php
    }
    ?>
    </div>
    <?php
    the_content(__('Keep Reading &rarr;', TEXTDOMAIN));
    ?>
</div>