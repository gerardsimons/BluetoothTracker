<div <?php post_class(); ?>>
    <?php get_template_part( 'templates/single', "post-meta" ); ?>
    <div class="post-content">
        <div class="post-media">
            <?php

            $images = get_meta('gallery');
            if(count($images))
            {?>
                <div class="flexslider">
                    <ul class="slides">
                        <?php
                        $imageSize = 'post-single';
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
            }?>
        </div>
        <?php
        the_content();
        wp_link_pages();
        ?>
    </div>
</div>
<?php comments_template('', true); ?>