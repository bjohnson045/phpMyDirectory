<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo $lang['block_social_links']; ?></h3>
    </div>
    <div class="panel-body">
        <?php if($config['facebook_page_id']) { ?><a title="<?php echo $lang['block_social_links_facebook']; ?>" href="http://facebook.com/<?php echo $config['facebook_page_id']; ?>"><span class="fa fa-2x fa-facebook-square"></span></a><?php } ?>
        <?php if($config['twitter_site_id']) { ?><a title="<?php echo $lang['block_social_links_twitter']; ?>" href="http://twitter.com/<?php echo $config['twitter_site_id']; ?>"><span class="fa fa-2x fa-twitter-square"></span></a><?php } ?>
        <?php if($config['linkedin_company_id']) { ?><a title="<?php echo $lang['block_social_links_linkedin_company']; ?>" href="http://linkedin.com/company/<?php echo $config['linkedin_company_id']; ?>"><span class="fa fa-2x fa-linkedin-square"></span></a><?php } ?>
        <?php if($config['google_page_id']) { ?><a title="<?php echo $lang['block_social_links_google']; ?>" href="http://plus.google.com/<?php echo $config['google_page_id']; ?>"><span class="fa fa-2x fa-google-plus-square"></span></a><?php } ?>
        <?php if($config['youtube_id']) { ?><a title="<?php echo $lang['block_social_links_youtube']; ?>" href="http://youtube.com/user/<?php echo $config['youtube_id']; ?>"><span class="fa fa-2x fa-youtube-square"></span></a><?php } ?>
        <?php if($config['pinterest_id']) { ?><a title="<?php echo $lang['block_social_links_pinterest']; ?>" href="http://pinterest.com/<?php echo $config['pinterest_id']; ?>"><span class="fa fa-2x fa-pinterest-square"></span></a><?php } ?>
    </div>
</div>