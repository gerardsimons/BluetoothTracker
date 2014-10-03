<?php
$layout = get_meta('layout');
$media  = get_meta('media');
$containerClass = '';
$mediaClass   = 'portfolio-media';
$contentClass = 'portfolio-content';


if($layout == 'half' && $media != 'none')
{
    $containerClass = 'row';
    $mediaClass    .= ' span7';
    $contentClass  .= ' span5';
}
?>

<!--Content Row-->
<div class="<?php echo "$layout $containerClass" ?>">

    <?php if($media != 'none'){ ?>

    <div class="<?php echo $mediaClass ?>">
        <?php
        if($media == 'video')
        {
            $vid   = get_meta('video-id');
            $vType = get_meta('video-type');
        ?>
        <div class="video-frame">
            <?php
            if($vType == 'youtube')
            {?>
                <iframe width="560" height="315" src="http://www.youtube.com/embed/<?php echo $vid; ?>" frameborder="0" allowfullscreen></iframe>
            <?php
            }
            else
            {?>
                <iframe src="http://player.vimeo.com/video/<?php echo $vid; ?>?color=ff4c2f" width="500" height="281" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>
            <?php
            }
            ?>
        </div>
        <?php
        }
        else//Image or slide show
        {
            $images = get_meta('image');
            if(count($images))
            {?>
            <div class="flexslider">
                <ul class="slides">
            <?php
                $imageSize = $layout == 'half' ? 'portfolio-single-split' : 'portfolio-single';
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
        }
        ?>
    </div>
    <?php }//end if($media != 'none') ?>
    <div class="<?php echo $contentClass ?>"><?php the_content(); ?></div>
</div>

<?php //Related portfolio items
$related = get_related_posts_by_taxonomy(get_the_ID(), 'skills', 4);

if($related->have_posts())
{
    ?>
    <div class="hr-title">
        <div></div>
        <div class="title"><h3><?php _e('Related Entries', TEXTDOMAIN) ?></h3></div>
        <div></div>
    </div>
    <div class="row portfolio-related">
        <?php while ($related->have_posts()) { $related->the_post(); ?>
            <div class="span3 item">
                <?php
                if ( function_exists('has_post_thumbnail') && has_post_thumbnail() )
                    the_post_thumbnail('portfolio-related4');
                ?>

                <div class="portfolio-related-info">
                    <div class="overlay-wrapper">
                        <div class="overlay">
                            <h3 class="overlay-title"><?php the_title(); ?></h3>
                            <?php $terms = implode_post_terms('skills');

                            if($terms != null && strlen($terms))
                            {
                            ?>
                            <hr/>
                            <div class="overlay-category"><?php echo $terms; ?></div>
                            <?php
                            }
                            ?>
                            <a href="<?php the_permalink(); ?>" class="overlay-link"></a>
                        </div>
                    </div>
                </div>
            </div>
        <?php
        }
        ?>
    </div>
<?php
}
wp_reset_query();