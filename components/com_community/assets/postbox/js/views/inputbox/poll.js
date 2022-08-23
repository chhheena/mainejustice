define('views/inputbox/poll',[
    'views/inputbox/videourl',
    'sandbox',
    'utils/language'
],

// definition
// ----------
function( VideoUrlView, $, language ) {

    return VideoUrlView.extend({

        getTemplate: function() {
            var hint = language.get('poll.title_hint') || 'Poll title',
                html = this.template({ placeholder: hint });

            return $( html );
        }
    })
});
