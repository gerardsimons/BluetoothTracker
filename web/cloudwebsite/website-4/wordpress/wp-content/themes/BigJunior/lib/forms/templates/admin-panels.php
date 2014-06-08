<div class="px-main">
    <?php
    $panels = $this->template['panels'];

    foreach($panels as $panelKey => $panel)
    {
    ?>
        <div id="<?php echo $panelKey; ?>" class="panel">
            <div class="content-head">
                <div class="px-content-wrap">
                    <a href="#" class="save-button" >
                        <?php echo $this->GetImage('save_icon.png', 'Save', 'save-icon'); ?>
                        <?php echo $this->GetImage('loading24.gif', 'Loading', 'loading-icon'); ?>
                        <?php _e('Save', TEXTDOMAIN); ?>
                    </a>
                    <h3><?php echo $panel['title']; ?></h3>

                    <div class="support">
                        <a href="<?php echo $this->template['document-url']; ?>"><?php _e('Documentation', TEXTDOMAIN); ?></a><span class="separator"></span><a href="<?php echo $this->template['support-url']; ?>"><?php _e('Support', TEXTDOMAIN); ?></a>
                    </div>
                </div>
            </div>
            <?php echo $this->GetTemplate('section', $panel['sections']); ?>
        </div>
    <?php
    }
    ?>
</div>