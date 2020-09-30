<?php if(count($results)) { ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo $lang['block_documents_new']; ?></h3>
    </div>
    <ul class="list-group">
    <?php foreach($results as $result) { ?>
        <li class="list-group-item">
            <h5 class="list-group-item-heading"><a href="<?php echo $result['url']; ?>"><?php echo $this->escape($result['title']); ?></a></h5>
            <span class="tiny"><?php echo $result['date']; ?></span>
        </li>
    <?php } ?>
    </ul>
</div>
<?php } ?>