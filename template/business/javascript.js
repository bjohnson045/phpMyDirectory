$(document).ready(function() {
    // Fix jQuery UI button conflict with bootstrap
    var btn = $.fn.button.noConflict();
    $.fn.btn = btn;

    $('input.error, select.error, textarea.error').closest('.form-group').addClass('has-error');

    /* Autocomplete search */
    $("#keyword").keyup(function () {
        var keyword_length = $("#keyword").val().length;
        if(keyword_length > 2 || (keyword_length > 0 && !isNaN($("#keyword").val()))) {
            $.ajax({ data: ({ action: "keyword_search", value: $("#keyword").val()}), success:
                function(data){
                    var myPopover = $('#keyword').data('bs.popover');
                    myPopover.options.html = true;
                    myPopover.options.content = data;
                    $('#keyword').popover('show');
                }
            });
        }
    });
    // If we focus back in the search form, trigger key up so the results show again
    $('#keyword').focus(function() {
        $('#keyword').trigger('keyup');
    });
    // Hide the popover if we click away from the search
    $('#keyword').blur(function() {
        setTimeout(function() {$('#keyword').popover('hide')},300);
    });
    $('#keyword').popover({title: 'Search Results', placement: 'bottom', trigger: 'manual'});
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
