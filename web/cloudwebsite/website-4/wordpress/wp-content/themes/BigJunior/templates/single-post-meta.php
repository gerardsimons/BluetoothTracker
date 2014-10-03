<div class="post-meta">
    <h1 class="post-title"><?php the_title(); ?></h1>
    <hr class="hr-small hr-margin-small" />
        <span class="post-info">
            <span><?php if(comments_open()) comments_popup_link( 'No Comments', '1 Comment', '%', 'comments-link', ''); ?></span>
            <span class="post-info-separator">|</span>
            <span class="post-date"><?php the_date(); ?></span>
            <span class="post-info-separator">|</span>
            <span class="post-categories"><?php _e('in ', TEXTDOMAIN); the_category(', '); ?></span>
            <span class="post-info-separator">|</span>
            <span class="post-author"><?php _e('by ', TEXTDOMAIN); the_author_posts_link(); ?></span>
        </span>
</div>