<?php
$title = isset($title) ? $title : get_the_title();
?>
<div id="page-title">
    <div class="container clearfix">
        <h1 class="title"><?php echo $title; ?></h1>
        <?php get_template_part( 'templates/breadcrumb' ); ?>
    </div>
</div>