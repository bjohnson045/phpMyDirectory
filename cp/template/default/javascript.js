$(document).ready(function(){
    /* Handle admin quick search ajax query */
    $("#quicksearch").keyup(function () {
        var quicksearchlength = $("#quicksearch").val().length;
        if(quicksearchlength > 2 || (quicksearchlength > 0 && !isNaN($("#quicksearch").val()))) {
            $.ajax({ data: ({ action: "admin_quick_search", value: $("#quicksearch").val()}), success:
                function(data){
                    var myPopover = $('#quicksearch').data('bs.popover');
                    myPopover.options.html = true;
                    myPopover.options.content = data;
                    $('#quicksearch').popover('show');
                }
            });
        }
    });
    // If we focus back in the search form, trigger key up so the results show again
    $('#quicksearch').focus(function() {
        $('#quicksearch').trigger('keyup');
    });
    // Hide the popover if we click away from the search
    $('#quicksearch').blur(function() {
        setTimeout(function() {$('#quicksearch').popover('hide')},300);
    });
    $('#quicksearch').popover({title: 'Search Results', placement: 'bottom', trigger: 'manual'});

    $('.dropdown-toggle').dropdown();

    $('input.error, select.error, textarea.error').closest('.form-group').addClass('has-error');

    $('*[data-action]').on('mouseover',function() {
        var e = $(this);
        e.off('mouseover');
        $.ajax({
            data: e.data(),
            success: function(d) {
                e.qtip({
                    show: {
                        delay: 0,
                        ready: true
                    },
                    hide: {
                        fixed: true,
                        delay: 100
                    },
                    style: {
                        classes: 'qtip-bootstrap qtip-shadow',
                        tip: { corner: 'leftTop', size: { x: 20, y: 8 } }
                    },
                    position: { at: 'right center', my: 'left top', adjust: { x: 5 } },
                    content: d
                });
            }
        });
    });
});

function tooltip(element, text, title) {
    if(title) {
        var content_options = { title: { text: title }, text: text }
    } else {
        var content_options = { text: text }
    }
    $('#'+element).qtip({
        show: { delay: 0 },
        hide: {
            fixed: true,
            delay: 100
        },
        style: {
            classes: 'qtip-bootstrap qtip-shadow',
            tip: { corner: 'leftTop', size: { x: 20, y: 8 } }
        },
        position: { at: 'right center', my: 'left top', adjust: { x: 5 } },
        content: content_options
    });
}


function updateOrdering(table,form) {
    $.ajax({data: ({ action: "update_ordering", table: table, data: $("#"+form).serialize()}), success: function() { location.reload(true) }});
}

function showLoadingMessage(message) {
    if(message && $("#loading p").length == 0) {
        $("#loading").append('<p>'+message+'.. <span id="status_percent"></span></p>');
    }
    $("#loading").slideDown();
}

function hideLoadingMessage() {
    $("#loading").slideUp();
    $("#loading p").remove();
}