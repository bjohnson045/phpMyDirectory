<h3><?php echo $title; ?></h3>
<h5><?php echo $lang['public_blog_by']; ?> <?php echo $user; ?> <?php echo $lang['public_blog_on']; ?> <?php echo $date_publish; ?> <?php echo $lang['public_blog_in']; ?> <?php echo $categories; ?></h5>
<?php if($image_url) { ?>
    <img id="blog_post_img" src="<?php echo $image_url; ?>" alt="<?php echo $title; ?>" />
<?php } ?>
<p><?php echo $this->escape_html($content); ?></p>

    <!--
    <?php if($next_url OR $previous_url) { ?>
        <div data-role="footer" data-theme="a">
            <fieldset class="ui-grid-a">
                <?php if($previous_url) { ?>
                    <div class="ui-block-a"><a style="margin: 5px;" href="<?php echo $previous_url; ?>" data-role="button" data-icon="arrow-l"><?php echo $previous_title; ?></a></div>
                <?php } ?>
                <?php if($next_url) { ?>
                    <?php if(!$previous_url) { ?>
                    <div class="ui-block-a"> </div>
                    <?php } ?>
                    <div class="ui-block-b"><a style="margin: 5px; float: right" href="<?php echo $next_url; ?>" data-role="button" data-icon="arrow-r" data-iconpos="right"><?php echo $next_title; ?></a></div>
                <?php } ?>
            </fieldset>
        </div>
    <?php } ?>
    -->