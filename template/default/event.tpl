<?php if(LOGGED_IN AND $rsvp) { ?>
<script type="text/javascript">
$(document).ready(function() {
    $("#rsvp").click(function(e) {
        e.preventDefault();
        rsvp = $(this);
        $.ajax({
            data: ({
                action: 'event_rsvp',
                id: <?php echo $id; ?>,
                rsvp: rsvp.data("rsvp")
            }),
            success: function() {
                if(rsvp.data("rsvp") == 0) {
                    rsvp.text("<?php echo $lang['public_events_rsvp_cancel']; ?>");
                    rsvp.data("rsvp",1);
                } else {
                    rsvp.text("<?php echo $lang['public_events_rsvp']; ?>");
                    rsvp.data("rsvp",0);
                }
            }
        });
    });
    <?php if($_GET['rsvp'] == 'true') { ?>
        $('#rsvp').trigger('click');
    <?php } ?>
});
</script>
<?php } ?>
<div itemscope itemtype="http://schema.org/Event">
    <h1><span itemprop="name"><?php echo $this->escape($title); ?></span></h1>
    <div class="row">
        <?php if($listing_title) { ?>
            <div class="col-lg-8 col-md-6 col-sm-6 col-xs-7">
                <p><?php echo $lang['public_events_from']; ?> <a href="<?php echo $listing_url; ?>"><?php echo $this->escape($listing_title); ?></a></p>
            </div>
        <?php } ?>
        <div class="col-lg-4 col-md-6 col-sm-6 col-xs-5">
        <?php if($rsvp AND !$expired) { ?>
            <a href="<?php echo $rsvp_url; ?>" id="rsvp" class="pull-right btn btn-default btn-sm" data-rsvp="<?php echo $rsvped; ?>"><?php if($rsvped) { ?><?php echo $lang['public_events_rsvp_cancel']; ?><?php } else { ?><?php echo $lang['public_events_rsvp']; ?><?php } ?></a>
        <?php } ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <?php echo $share; ?>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6 col-md-6">
            <h2><?php echo $lang['public_events_overview']; ?></h2>
            <p><?php echo $lang['public_events_date']; ?>: <?php echo $date; ?></p>
            <?php if($date_update) { ?>
                <p><?php echo $lang['public_events_date_update']; ?>: <?php echo $date_update; ?></p>
            <?php } ?>
            <?php if(!$expired) { ?>
                <p><?php echo $lang['public_events_date_start']; ?>: <?php echo $date_start; ?></p>
                <p><?php echo $lang['public_events_date_end']; ?>: <?php echo $date_end; ?></p>
            <?php } else { ?>
                <p class="text-danger"><?php echo $lang['public_events_no_upcoming']; ?></p>
            <?php } ?>
            <?php echo $custom_fields; ?>
            <?php if(!$expired) { ?>
                <a class="btn btn-default btn-sm" href="http://www.google.com/calendar/event?action=TEMPLATE&text=<?php echo $this->escape($title); ?>&dates=<?php echo $date_start_google; ?>/<?php echo $date_end_google; ?>&details=<?php echo $this->escape_html($description); ?>&location=<?php echo $location; ?>" target="_blank" rel="nofollow"><?php echo $lang['public_events_add_google']; ?></a>
                <a class="btn btn-default btn-sm" href="<?php echo URL; ?>?action=ical"><?php echo $lang['public_events_add_ical']; ?></a>
            <?php } ?>
        </div>
        <div class="col-lg-6 col-md-6">
            <h2><?php echo $lang['public_events_contact_information']; ?></h2>
            <?php if($contact_name) { ?>
                <p><span class="fa fa-user"></span> <?php echo $lang['public_events_contact_name']; ?>: <?php echo $this->escape($contact_name); ?></span></p>
            <?php } ?>
            <?php if($website) { ?>
                <p><span class="fa fa-globe"></span> <?php echo $lang['public_events_website']; ?>: <span itemprop="sameAs"><a target="_blank" href="<?php echo $this->escape($website); ?>"><?php echo $this->escape($website); ?></a></span></p>
            <?php } ?>
            <?php if($phone) { ?>
                <p><span class="fa fa-phone"></span> <?php echo $lang['public_events_phone']; ?>: <?php echo $this->escape($phone); ?></p>
            <?php } ?>
            <?php if($email) { ?>
                <p><span class="fa fa-envelope-o"></span> <?php echo $lang['public_events_email']; ?>: <a href="mailto:<?php echo $this->escape($email); ?>"><?php echo $this->escape($email); ?></a></p>
            <?php } ?>
        </div>
    </div>
    <?php if($description OR $image_url) { ?>
    <div class="row">
        <?php if($description) { ?>
            <div class="<?php if($image_url) { ?>col-md-6<?php } else { ?>col-md-12<?php } ?>">
                <h2><?php echo $lang['public_events_description']; ?></h2>
                <span itemprop="description"><?php echo $this->escape_html($description); ?></span>
            </div>
        <?php } ?>
        <?php if($image_url) { ?>
            <div class="<?php if($description) { ?>col-md-6<?php } else { ?>col-md-12<?php } ?>">
                <h2><?php echo $lang['public_events_image']; ?></h2>
                <img class="img-thumbnail img-responsive" itemprop="logo" src="<?php echo $image_url; ?>" alt="<?php echo $this->escape($title); ?>" title="<?php echo $this->escape($title); ?>" />
            </div>
        <?php } ?>
    </div>
    <?php } ?>
    <?php if($admission) { ?>
    <div class="row">
        <div class="col-md-12">
            <h2><?php echo $lang['public_events_admission']; ?></h2>
            <span itemprop="description"><?php echo $this->escape_html($admission); ?></span>
        </div>
    </div>
    <?php } ?>
    <div class="row">
        <div class="col-md-12">
            <h2><?php echo $lang['public_events_location']; ?></h2>
            <p><?php echo $lang['public_events_location']; ?>: <?php echo $location; ?></p>
            <?php if(!empty($venue)) { ?>
                <p><?php echo $lang['public_events_venue']; ?>: <?php echo $venue; ?></p>
            <?php } ?>
            <?php if($map) { ?>
                <?php echo $map; ?>
            <?php } ?>
        </div>
    </div>
    <?php if(count($dates) > 1) { ?>
    <div class="row">
        <div class="col-md-12">
        <h2><?php echo $lang['public_events_upcoming_dates']; ?></h2>
        <?php foreach($dates AS $date) { ?>
            <p><?php echo $date['date_start']; ?> - <?php echo $date['date_end']; ?></p>
        <?php } ?>
        </div>
    </div>
    <?php } ?>
    <?php if($other_events) { ?>
    <div class="row">
        <div class="col-md-12">
        <h2><?php echo $lang['public_events_other']; ?> <?php echo $this->escape($listing_title); ?></h2>
        <?php foreach($other_events AS $event) { ?>
            <p><a href="<?php echo $event['url']; ?>"><?php echo $this->escape($event['title']); ?></a></p>
        <?php } ?>
        </div>
    </div>
    <?php } ?>
    <?php if(count($past_dates) > 1) { ?>
    <div class="row">
        <div class="col-md-12">
        <h2><?php echo $lang['public_events_past_dates']; ?></h2>
        <?php foreach($past_dates AS $date) { ?>
            <p><?php echo $date['date_start']; ?> - <?php echo $date['date_end']; ?></p>
        <?php } ?>
        </div>
    </div>
    <?php } ?>
</div>