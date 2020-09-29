<p>
<?php if(isset($url)) { ?>
    <a href="<?php echo $url; ?>"><?php echo $this->escape($title); ?></a>
<?php } else { ?>
    <?php echo $this->escape($title); ?>
<?php } ?>
</p>
<p><?php echo $this->escape($address); ?></p>
<?php if(isset($directions_begin)) { ?>
    <?php echo $lang['public_listing_directions']; ?>:<br />
    <form action="http://maps.google.com/maps" method="get" target="_blank">
        <input type="text" name="saddr" id="saddr" style="width: 150px;"><br />
        <input class="button" value="<?php echo $this->escape($lang['public_submit']); ?>" type="submit">
        <input type="hidden" name="daddr" id="daddr" value="<?php echo $this->escape($directions_begin); ?>">
    </form>
<?php } ?>