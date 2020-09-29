<script type="text/javascript">
$(document).ready(function() {
    $("#calendar_small").fullCalendar({
        isRTL: <?php echo $PMDR->getLanguage('textdirection') == 'rtl' ? 'true' : 'false'; ?>,
        header: {
            left:   'title',
            right:  'today prev,next'
        },
        events: {
            url: "./ajax.php",
            data: {
                action: "events_calendar"
            }
        },
        loading: function(isLoading) {
            if(isLoading) {}
        },
        buttonText: {
            today: 'today',
            month: 'month',
            week:  'week',
            day:   'day'
        },
        views: {
            month: {
                displayEventEnd: false,
                timeFormat: false
            }
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
<div id="calendar_small"></div>