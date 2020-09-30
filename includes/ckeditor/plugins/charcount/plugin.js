CKEDITOR.plugins.add( 'charcount', {
    init : function( editor ) {
        var defaultFormat = '%count% / %limit%';
        var limit = 0;
        var format = defaultFormat;

        var intervalId;
        var lastCount = 0;
        var limitReachedNotified = false;
        var limitRestoredNotified = false;

        if(editor.config.charcount_limit != undefined) {
            limit = editor.config.charcount_limit;
        }

        if(editor.config.charcount_format != undefined) {
            format = editor.config.charcount_format;
        }

        function counterId(editor) {
            return editor.name+'_counter';
        }

        function counterElement(editor) {
            return document.getElementById( counterId(editor) );
        }

        function updateCounter(editor) {
            // Remove tags, new lines, tabs, combine spaces, and consider HTML entities 1 character
            var count = editor.getData().replace(/<[^>]+>|\n|\t/ig, '').replace(/\s+/g, ' ').replace(/&\w+;/g ,'X').replace(/^\s*/g, '').replace(/\s*$/g, '').length;

            var html = format.replace('%count%', count).replace('%limit%', limit);
            counterElement(editor).innerHTML = html;

            if(count > limit){
                limitReached(editor);
            } else {
                withinLimit(editor);
            }
        }

        function limitReached(editor) {
            $('#'+counterId(editor)).css('color','red');
            $('#'+counterId(editor)).css('font-weight','bold');
        }

        function withinLimit(editor) {
            if($('#'+counterId(editor)).css('font-weight') == 'bold') {
                $('#'+counterId(editor)).css('color','inherit');
                $('#'+counterId(editor)).css('font-weight','normal');
            }
        }

        editor.on('instanceReady', function() {}, editor, null, 100);

        editor.on('dataReady', function(event) {
            var count = editor.getData().replace(/<[^>]+>|\n|\t/ig, '').replace(/\s+/g, ' ').replace(/&\w+;/g ,'X').replace(/^\s*/g, '').replace(/\s*$/g, '').length;
            if(count > limit){
                limitReached(editor);
            }
            updateCounter(event.editor);
        }, editor, null, 100);

        editor.on('key', function(event) {
            updateCounter(event.editor);
        }, editor, null, 100);
   }
});