<script type="text/javascript">
$(document).ready(function() {
    $("#calendar").fullCalendar({
        isRTL: <?php echo $PMDR->getLanguage('textdirection') == 'rtl' ? 'true' : 'false'; ?>,
        header: {
            left:   'title',
            center: 'month,basicWeek',
            right:  'today prev,next'
        },
        events: {
            url: "./admin_ajax.php",
            data: {
                action: "admin_calendar"
            }
        },
        loading: function(isLoading) {
            if(isLoading) {
                $("#month").val($("#calendar").fullCalendar('getDate').month());
                $("#year").val($("#calendar").fullCalendar('getDate').year());
            }
        },
        buttonText: {
            today: 'today',
            month: 'month',
            week:  'week',
            day:   'day'
        }
    });

    $("#month").change(function() {
        $("#calendar").fullCalendar('gotoDate',$.fullCalendar.moment([$("#year").val(),$(this).val()]));
    });

    $("#year").change(function() {
        $("#calendar").fullCalendar('gotoDate',$.fullCalendar.moment([$(this).val(),$("#month").val()]));
    });

    $("#date_select").prependTo(".fc-right");
    $("#date_select").show();
});
</script>
<h1><?php echo $lang['admin_calendar']; ?></h1>
<div id="date_select" class="form-inline" style="float: left; display: none">
    <select id="month" name="month" class="form-control" style="width: auto">
    <?php foreach($months AS $key=>$month) { ?>
        <option value="<?php echo $key; ?>"><?php echo $month; ?></option>
    <?php } ?>
    </select>
    <select id="year" name="year" class="form-control" style="width: auto">
    <?php foreach($years AS $year) { ?>
        <option><?php echo $year; ?></option>
    <?php } ?>
    </select>
</div>
<div id="calendar"></div>