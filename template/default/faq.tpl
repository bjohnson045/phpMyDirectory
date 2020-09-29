<script type="text/javascript">
$(document).ready(function(){
    $("#faq_search").autocomplete({
        minLength: 2,
        source: function(request,response) {
            $.ajax({
                dataType: "json",
                data: {
                    action: "faq_search",
                    keywords: request.term
                },
                success: function(data) {
                    response(data);
                }
            })
        },
        select: function(event,ui) {
            $('html,body').animate({scrollTop: $("#faq_question"+ui.item.id).offset().top-100},'slow');
            $("#faq_question"+ui.item.id).trigger('click');
        },
        close: function() {
            $(this).val('');
        }
    });
    $(".faq_question").click(function(event) {
        event.preventDefault();
        $(".faq_answer").slideUp();
        $(".faq_question").removeClass("faq_question_open");
        if($(this).parent().children(".faq_answer").is(":hidden")) {
            $(this).addClass("faq_question_open");
            $(this).parent().children(".faq_answer").slideDown();
        }
    });
});
</script>
<?php if(sizeof($categories) > 0) { ?>
    <div class="row">
        <div class="col-lg-3">
            <input id="faq_search" type="text" class="form-control" name="faq_search" placeholder="<?php echo $lang['public_faq_search'];?>">
        </div>
    </div>
    <?php foreach($categories as $key=>$value) { ?>
        <h4><?php echo $this->escape($value['title']); ?></h4>
        <?php if(sizeof($questions) > 0) { ?>
            <?php foreach($questions as $key2=>$value2) { ?>
                <?php if($value2['category_id'] == $key) { ?>
                <div>
                    <a id="faq_question<?php echo $key2; ?>" class="faq_question" href="<?php echo BASE_URL; ?>/faq.php?id=<?php echo $key2; ?>"><?php echo $value2['question']; ?></a>
                    <div style="display: none;" class="faq_answer"><?php echo $value2['answer']; ?></div>
                </div>
                <?php } ?>
            <?php } ?>
        <?php } ?>
    <?php } ?>
<?php } ?>
<?php if($question) { ?>
    <strong><?php echo $question['question']; ?></strong>
    <p><?php echo $question['answer']; ?></p>
<?php } ?>