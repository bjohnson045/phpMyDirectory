<script type="text/javascript">
$(document).ready(function() {
    $("#locations_search").keyup(function () {
        var locations_search = $("#locations_search").val().length;
        if(locations_search > 2 || (locations_search > 0 && !isNaN($("#locations_search").val()))) {
            $.ajax({ data: ({ action: "admin_locations_search", value: $("#locations_search").val()}), success:
                function(data){
                    var myPopover = $('#locations_search').data('bs.popover');
                    myPopover.options.html = true;
                    myPopover.options.content = data;
                    $('#locations_search').popover('show');
                }
            });
        }
    });
    // If we focus back in the search form, trigger key up so the results show again
    $('#locations_search').focus(function() {
        $('#locations_search').trigger('keyup');
    });
    // Hide the popover if we click away from the search
    $('#locations_search').blur(function() {
        $('#locations_search').popover('hide');
    });
    $('#locations_search').popover({placement: 'bottom', trigger: 'manual', template: '<div class="popover"><div class="arrow"></div><div class="popover-inner"><div class="popover-content"><p></p></div></div></div>'});

    function expanders() {
        $(".collapsed,.expanded").unbind();
        $(".collapsed,.expanded").click(function(event) {
            event.preventDefault();
            if($(this).hasClass("collapsed")) {
                var that = this;
                $.ajax({ data: ({ action: "admin_locations_expand", value: $(this).attr('id')}), success:
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
                window.location.replace("<?php echo BASE_URL_ADMIN; ?>/admin_locations.php");
            },1000);
        } else {
            sortStart(data.start+data.num,data.num);
        }
    };

    var sortStart = function(start,num) {
        if(start == 0) {
            showLoadingMessage('Sorting Locations');
            $("#status_percent").html("0%");
        }
        $.ajax({ data: ({ action: "admin_locations_sort", start: start, num: num }), success: sortOnComplete, dataType: "json"});
    };

    $("#location_sort").click(function(e) {
        e.preventDefault();
        if(confirm(<?php echo $this->escape_js($lang['admin_locations_sort_confirm']); ?>)) {
            sortStart(0,50);
        }
    });

    <?php if(value($_GET,'action') == 'sort') { ?>
        $("#location_sort").trigger('click');
    <?php } ?>

    var exportOnComplete = function(data) {
        $("#status_percent").html(data.percent+"%");
        if(data.percent == 100) {
            setTimeout(function() {
                hideLoadingMessage();
                window.location.replace("<?php echo BASE_URL_ADMIN; ?>/admin_locations.php?action=download");
            },1000);
        } else {
            exportStart(data.start+data.num,data.num);
        }
    };

    var exportStart = function(start,num) {
        if(start == 0) {
            showLoadingMessage('<?php echo $lang['admin_locations_export']; ?>');
            $("#status_percent").html("0%");
        }
        $.ajax({ data: ({ action: "admin_locations_export", start: start, num: num }), success: exportOnComplete, dataType: "json"});
    };

    $("#location_export").click(function(e) {
        e.preventDefault();
        exportStart(0,50);
    });

    <?php if(value($_GET,'action') == 'export') { ?>
        exportStart(0,50);
    <?php } ?>
});
</script>
<h1><?php echo $title; ?></h1>
<form class="form-inline">
    <label><?php echo $lang['admin_locations_quick_search']; ?>:</label> <input id="locations_search" class="form-control input-md" type="text" name="locations_search"></form>
<?php echo $content; ?>