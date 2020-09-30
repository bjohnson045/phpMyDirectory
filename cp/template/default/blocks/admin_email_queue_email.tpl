<h4>From:</h4>
<p><?php echo $from_name; ?> - <?php echo $from_email; ?></p>

<h4>Recipients:</h4>
<?php foreach($recipients AS $recipient) { ?>
    <?php echo $recipient; ?><br />
<?php } ?>

<h4>Subject:</h4>
<p><?php echo $subject; ?></p>

<?php if(count($attachments)) { ?>
    <h4>Attachments:</h4>
    <?php foreach($attachments AS $attachment) { ?>
        <?php echo $attachment['file_name']; ?><br />
    <?php } ?>
<?php } ?>
<?php foreach($message_parts AS $part) { ?>
    <?php if($part['type'] == 'text/html') { ?>
        <h4>Message (HTML):</h4>
    <?php } else { ?>
        <h4>Message (Plain Text):</h4>
    <?php } ?>
    <?php echo $part['message']; ?>
<?php } ?>

