define('views/postbox/poll-time', [
    'sandbox',
    'views/base',
    'utils/constants',
    'utils/language'
],

// definition
// ----------
function( $, BaseView, constants, language ) {

    return BaseView.extend({

        template: _.template(
            '<div class="joms-postbox__poll-time--inner">\
                <span>\
                    <%= language.poll.expired_date %> <span style="color:red">*</span>\
                </span>\
                <input type=text class="joms-input joms-pickadate-enddate" placeholder=" <%= language.poll.epxired_date_hint %>" style="background-color:transparent;cursor:text">\
                <span>\
                    <%= language.poll.expired_time %> <span style="color:red">*</span>\
                </span>\
                <input type=text class="joms-input joms-pickadate-endtime" placeholder=" <%= language.poll.expired_time_hint %>" style="background-color:transparent;cursor:text">\
            </div>'
        ),

        events: {
        },

        render: function() {
            var div = this.getTemplate(),
                ampm = +constants.get('conf.eventshowampm'),
                firstDay = +constants.get('conf.firstday'),
                timeFormatLabel = ampm ? 'h:i A' : 'H:i',
                translations = {},
                categories,
                i;

            // Translations.
            translations.monthsFull = [
                language.get('datepicker.january'),
                language.get('datepicker.february'),
                language.get('datepicker.march'),
                language.get('datepicker.april'),
                language.get('datepicker.may'),
                language.get('datepicker.june'),
                language.get('datepicker.july'),
                language.get('datepicker.august'),
                language.get('datepicker.september'),
                language.get('datepicker.october'),
                language.get('datepicker.november'),
                language.get('datepicker.december')
            ];

            translations.monthsShort = [];
            for ( i = 0; i < translations.monthsFull.length; i++ )
                translations.monthsShort[i] = translations.monthsFull[i].substr( 0, 3 );

            translations.weekdaysFull = [
                language.get('datepicker.sunday'),
                language.get('datepicker.monday'),
                language.get('datepicker.tuesday'),
                language.get('datepicker.wednesday'),
                language.get('datepicker.thursday'),
                language.get('datepicker.friday'),
                language.get('datepicker.saturday')
            ];

            translations.weekdaysShort = [];
            for ( i = 0; i < translations.weekdaysFull.length; i++ )
                translations.weekdaysShort[i] = translations.weekdaysFull[i].substr( 0, 3 );

            translations.today = language.get('datepicker.today');
            translations['clear'] = language.get('datepicker.clear');

            translations.firstDay = firstDay;
            translations.selectYears = 200;
            translations.selectMonths = true;

            this.$el.html( div );

            this.$enddate = this.$('.joms-pickadate-enddate').pickadate( $.extend({}, translations, { format: 'd mmmm yyyy', klass: { frame: 'picker__frame endDate' }, min: true }) );
            this.$endtime = this.$('.joms-pickadate-endtime').pickatime({ interval: 15, format: timeFormatLabel, formatLabel: timeFormatLabel, klass: { frame: 'picker__frame endTime' } });

            this.enddate = this.$enddate.pickadate('picker');
            this.endtime = this.$endtime.pickatime('picker');

            this.enddate.on({ set: $.bind( this.onSetEndDate, this ) });
            this.endtime.on({ set: $.bind( this.onSetEndTime, this ) });

            return this;
        },

        // ---------------------------------------------------------------------

        value: function() {
            if (!this.$enddate.val() && this.data) {
                this.data.enddate = null;    
            }

            if (!this.$endtime.val() && this.data) {
                this.data.endtime = null; 
            }

            return this.data;
        },

        reset: function() {
            this.data = null;
            this.$enddate.val('');
            this.$endtime.val('');
        },

        // ---------------------------------------------------------------------

        onSetEndDate: function( o ) {
            // if (o.hasOwnProperty('clear')) {
            //     return;
            // }

            this.onSave();
        },

        onSetEndTime: function() {
            this.onSave();
        },

        onSave: function() {
            var enddate = this.enddate.get('select'),
                endtime = this.endtime.get('select'),
                error;

            // get end date and time
            enddate && (enddate = [ this.enddate.get('select', 'yyyy-mm-dd'), this.enddate.get('value') ]);
            endtime && (endtime = [ this.endtime.get('select', 'HH:i'), this.endtime.get('value') ]);

            // data
            this.data = {
                enddate   : enddate,
                endtime   : endtime
            };

            // check values
            if ( this.$enddate.val() ) {
                this.$enddate.css('border-color', '');
            } else {
                this.$enddate.css('border-color', '#ec0000');
            }

            if ( this.$endtime.val() ) {
                this.$endtime.css('border-color', '');
            } else {
                this.$endtime.css('border-color', '#ec0000');
            }

            this.trigger( 'select', this.data );
        },

        // ---------------------------------------------------------------------
        // Helper functions.
        // ---------------------------------------------------------------------

        getTemplate: function() {
            var html = this.template({
                language: {
                    event: language.get('event') || {},
                    poll: language.get('poll') || {}
                }
            });

            return $( html );
        }
    })
});
