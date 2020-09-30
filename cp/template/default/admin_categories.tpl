<script type="text/javascript">
$(document).ready(function() {
    $("#categories_search").keyup(function () {
        var categories_search = $("#categories_search").val().length;
        if(categories_search > 2 || (categories_search > 0 && !isNaN($("#categories_search").val()))) {
            $.ajax({ data: ({ action: "admin_categories_search", value: $("#categories_search").val()}), success:
                function(data){
                    var myPopover = $('#categories_search').data('bs.popover');
                    myPopover.options.html = true;
                    myPopover.options.content = data;
                    $('#categories_search').popover('show');
                }
            });
        }
    });
    // If we focus back in the search form, trigger key up so the results show again
    $('#categories_search').focus(function() {
        $('#categories_search').trigger('keyup');
    });
    // Hide the popover if we click away from the search
    $('#categories_search').blur(function() {
        $('#categories_search').popover('hide');
    });
    $('#categories_search').popover({placement: 'bottom', trigger: 'manual', template: '<div class="popover"><div class="arrow"></div><div class="popover-inner"><div class="popover-content"><p></p></div></div></div>' });

    function expanders() {
        $(".collapsed,.expanded").unbind();
        $(".collapsed,.expanded").click(function(event) {
            event.preventDefault();
            if($(this).hasClass("collapsed")) {
                var that = this;
                $.ajax({ data: ({ action: "admin_categories_expand", value: $(this).attr('id')}), success:
                    function(data){
                        $(that).parent().parent().after(data);
                        $("tbody > tr").removeClass("odd").filter(":nth-child(even)").addClass("odd");
                        expanders();
                    }
                });
                $(this).removeClass("collapsed");
                $(this).addClass("expanded");
            } else {
                close_nodes($(this).attr('id'));
                $("tbody > tr").removeClass("odd").filter(":nth-child(even)").addClass("odd");
                $(this).removeClass("expanded");
                $(this).addClass("collapsed");
            }
        });
    }

    function close_nodes(parent_id) {
        $("."+parent_id+"_child div").each(function(){
            close_nodes($(this).attr('id'));
        });
        $("."+parent_id+"_child").remove();
    }
    expanders();

    var sortOnComplete = function(data) {
        $("#status_percent").html(data.percent+"%");
        if(data.percent == 100) {
            setTimeout(function() {
                hideLoadingMessage();
                window.location.replace("<?php echo BASE_URL_ADMIN; ?>/admin_categories.php");
            },1000);
        } else {
            sortStart(data.start+data.num,data.num);
        }
    };

    var sortStart = function(start,num) {
        if(start == 0) {
            showLoadingMessage('Sorting Categories');
            $("#status_percent").html("0%");
        }
        $.ajax({ data: ({ action: "admin_categories_sort", start: start, num: num }), success: sortOnComplete, dataType: "json"});
    };

    $("#category_sort").click(function(e) {
        e.preventDefault();
        if(confirm(<?php echo $this->escape_js($lang['admin_categories_sort_confirm']); ?>)) {
            sortStart(0,50);
        }
    });

    <?php if(value($_GET,'action') == 'sort') { ?>
        $("#category_sort").trigger('click');
    <?php } ?>

    var exportOnComplete = function(data) {
        $("#status_percent").html(data.percent+"%");
        if(data.percent == 100) {
            setTimeout(function() {
                hideLoadingMessage();
                window.location.replace("<?php echo BASE_URL_ADMIN; ?>/admin_categories.php?action=download");
            },1000);
        } else {
            exportStart(data.start+data.num,data.num);
        }
    };

    var exportStart = function(start,num) {
        if(start == 0) {
            showLoadingMessage('<?php echo $lang['admin_categories_export']; ?>');
            $("#status_percent").html("0%");
        }
        $.ajax({ data: ({ action: "admin_categories_export", start: start, num: num }), success: exportOnComplete, dataType: "json"});
    };

    $("#category_export").click(function(e) {
        e.preventDefault();
        exportStart(0,50);
    });

    <?php if(value($_GET,'action') == 'export') { ?>
        exportStart(0,50);
    <?php } ?>
});
</script>
<h1><?php echo $title; ?></h1>
<div class="form-inline">
    <label><?php echo $lang['admin_categories_quick_search']; ?>: </label><input id="categories_search" class="form-control input-md" type="text" name="categories_search">
</div>
<?php echo $content; ?>