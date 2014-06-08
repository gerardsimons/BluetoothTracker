<?php get_template_part( 'templates/loop', "blog-meta" ); ?>
<div class="post-content">
    <?php
    //Parse the content for the first occurrence of video url
    $video = extract_video_info(get_meta('video-url'));

    if($video != null)
    {
        $w = 500; $h = 280;
        get_video_meta($video);

        if(array_key_exists('width', $video))
        {
            $w = $video['width'];
            $h = $video['height'];
        }

        //Extract video ID
        ?>
        <div class="post-media video-frame">
        <?php
            if($video['type'] == 'youtube')
                $src = "http://www.youtube.com/embed/" . $video['id'];
            else
                $src = "http://player.vimeo.com/video/" . $video['id'] . "?color=ff4c2f";
        ?>
        <iframe src="<?php echo $src; ?>" width="<?php echo $w; ?>" height="<?php echo $h; ?>" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>
        </div>
    <?php
    }

    the_content(__('Keep Reading &rarr;', TEXTDOMAIN));
?>
</div>