<div itemscope itemtype="http://schema.org/JobPosting">
    <h1><span itemprop="title"><?php echo $this->escape($title); ?></span></h1>
    <div class="row">
        <?php if($listing_title) { ?>
            <div class="col-lg-8 col-md-6 col-sm-6 col-xs-7">
                <p><?php echo $lang['public_jobs_from']; ?> <a href="<?php echo $listing_url; ?>"><?php echo $this->escape($listing_title); ?></a></p>
            </div>
        <?php } ?>
    </div>
    <div class="row">
        <div class="col-md-12">
            <?php echo $share; ?>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6 col-md-6">
            <h2><?php echo $lang['public_jobs_overview']; ?></h2>
            <p><?php echo $lang['public_jobs_date']; ?>: <span itemprop="datePosted"><?php echo $date; ?></span></p>
            <?php if($date_update) { ?>
                <p><?php echo $lang['public_jobs_date_update']; ?>: <?php echo $date_update; ?></p>
            <?php } ?>
            <p><?php echo $lang['public_jobs_type']; ?>: <?php echo $lang['public_jobs_type_'.$this->escape($type)]; ?></p>
            <?php echo $custom_fields; ?>
        </div>
        <div class="col-lg-6 col-md-6">
            <h2><?php echo $lang['public_jobs_contact_information']; ?></h2>
            <?php if($contact_name) { ?>
                <p><span class="fa fa-user"></span> <?php echo $lang['public_jobs_contact_name']; ?>: <?php echo $this->escape($contact_name); ?></span></p>
            <?php } ?>
            <?php if($website) { ?>
                <p><span class="fa fa-globe"></span> <?php echo $lang['public_jobs_website']; ?>: <span itemprop="sameAs"><a target="_blank" rel="nofollow" href="<?php echo $this->escape($website); ?>"><?php echo $this->escape($website); ?></a></span></p>
            <?php } ?>
            <?php if($phone) { ?>
                <p><span class="fa fa-phone"></span> <?php echo $lang['public_jobs_phone']; ?>: <?php echo $this->escape($phone); ?></p>
            <?php } ?>
            <?php if($email) { ?>
                <p><span class="fa fa-envelope-o"></span> <?php echo $lang['public_jobs_email']; ?>: <a rel="nofollow" href="mailto:<?php echo $this->escape($email); ?>"><?php echo $this->escape($email); ?></a></p>
            <?php } ?>
        </div>
    </div>
    <?php if($description) { ?>
    <div class="row">
        <?php if($description) { ?>
            <div class="col-md-12">
                <h2><?php echo $lang['public_jobs_description']; ?></h2>
                <span itemprop="description"><?php echo $this->escape_html($description); ?></span>
            </div>
        <?php } ?>
    </div>
    <?php } ?>
    <?php if($requirements) { ?>
    <div class="row">
        <div class="col-md-12">
            <h2><?php echo $lang['public_jobs_requirements']; ?></h2>
            <span itemprop="experienceRequirements"><?php echo $this->escape_html($requirements); ?></span>
        </div>
    </div>
    <?php } ?>
    <?php if($benefits) { ?>
    <div class="row">
        <div class="col-md-12">
            <h2><?php echo $lang['public_jobs_benefits']; ?></h2>
            <span itemprop="jobBenefits"><?php echo $this->escape_html($benefits); ?></span>
        </div>
    </div>
    <?php } ?>
    <?php if($compensation) { ?>
    <div class="row">
        <div class="col-md-12">
            <h2><?php echo $lang['public_jobs_compensation']; ?></h2>
            <span itemprop="experienceRequirements"><?php echo $this->escape_html($compensation); ?></span>
        </div>
    </div>
    <?php } ?>
    <?php if($other_jobs) { ?>
    <div class="row">
        <div class="col-md-12">
        <h2><?php echo $lang['public_jobs_other']; ?> <?php echo $this->escape($listing_title); ?></h2>
        <?php foreach($other_jobs AS $job) { ?>
            <p><a href="<?php echo $job['url']; ?>"><?php echo $this->escape($job['title']); ?></a></p>
        <?php } ?>
        </div>
    </div>
    <?php } ?>
</div>