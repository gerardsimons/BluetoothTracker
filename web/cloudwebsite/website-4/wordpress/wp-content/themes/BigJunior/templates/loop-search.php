<div class="results">

    <?php
    $i=1;
    $page = get_query_var('paged') ? get_query_var('paged') : 1;
    if($page > 1) $i = (($page - 1) * get_query_var('posts_per_page')) + 1;

    while(have_posts()){ the_post();
    ?>
        <div class="search-item clearfix">
            <div class="count"><?php echo str_pad($i, 2, "0", STR_PAD_LEFT); ?></div>
            <div class="content">
                <h3 class="title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                <span class="date"><?php the_time(get_option('date_format')); ?></span>
            </div>
        </div>
    <?php
        $i++;
    }
    ?>

</div>