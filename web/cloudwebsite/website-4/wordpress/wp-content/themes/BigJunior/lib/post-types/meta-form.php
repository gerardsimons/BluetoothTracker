<?php wp_nonce_field( 'theme-post-meta-form', THEME_NAME_SEO . '_post_nonce' ); ?>

<div class="px-container post-meta">
    <div class="px-main">
        <?php
            $this->SetWorkingDirectory(path_combine(THEME_LIB, 'forms/templates'));
            echo $this->GetTemplate('section', $vars);
        ?>
    </div>
</div>
