<?php if(sizeof($categories) > 0) { ?>
    <ul data-role="listview">
    <?php foreach($categories as $key=>$value) { ?>
        <li data-role="list-divider" role="heading">
            <?php echo $this->escape($value['title']); ?>
        </li>
        <?php if(sizeof($questions) > 0) { ?>
            <?php foreach($questions as $key2=>$value2) { ?>
                <?php if($value2['category_id'] == $key) { ?>
                <li><a style="white-space:normal" href="<?php echo BASE_URL; ?>/faq.php?id=<?php echo $key2; ?>"><?php echo $value2['question']; ?></a></li>
                <!--
                <div>
                    <a id="faq_question<?php echo $key2; ?>" class="faq_question" ></a>
                    <div style="display: none;" class="faq_answer"><?php echo $value2['answer']; ?></div>
                </div>
                -->
                <?php } ?>
            <?php } ?>
        <?php } ?>
    <?php } ?>
    </ul>
<?php } ?>
<?php if($question) { ?>
    <h3><?php echo $question['question']; ?></h3>
    <p><?php echo $question['answer']; ?></p>
<?php } ?>

