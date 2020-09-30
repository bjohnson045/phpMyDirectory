<div data-role="controlgroup" data-type="horizontal">
    <?php if($page['previous_url']) { ?>
        <a data-role="button" data-inline="true" data-mini="true" data-icon="arrow-l" href="<?php echo $page['previous_url']; ?>">Previous</a>
    <?php } ?>
    <?php if($page['next_url']) { ?>
        <a data-role="button" data-inline="true" data-mini="true" data-icon="arrow-r" href="<?php echo $page['next_url']; ?>">Next</a>
    <?php } ?>
</div>