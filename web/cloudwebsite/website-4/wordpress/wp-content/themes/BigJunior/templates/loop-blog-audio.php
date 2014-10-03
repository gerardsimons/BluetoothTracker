<?php get_template_part( 'templates/loop', "blog-meta" ); ?>
<div class="post-content">
    <?php
    //Parse the content for the first occurrence of video url
    $audio = extract_audio_info(get_meta('audio-url'));

    if($audio != null)
    {
        //Extract video ID
        ?>
        <div class="post-media audio-frame">
        <?php
            echo soundcloud_get_embed($audio['url']);
        ?>
        </div>
    <?php
    }

    the_content(__('Keep Reading &rarr;', TEXTDOMAIN));
?>
</div>