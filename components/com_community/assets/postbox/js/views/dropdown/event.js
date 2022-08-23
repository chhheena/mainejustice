define('views/dropdown/event',[
    'sandbox',
    'views/dropdown/base',
    'utils/constants',
    'utils/language'
],

// definition
// ----------
function( $, BaseView, constants, language ) {

    return BaseView.extend({

        template: _.template('\
            <div class="joms-postbox-dropdown event-dropdown">\
                <div style="clear:both">\
                    <div class=event-time-column>\
                    <label class="joms-checkbox">\
                        <input type="checkbox" class="joms-checkbox private-event" name="permission" value="1">\
                        <span title="<%= language.event.private_tips %>">\
                        <%= language.event.private %>\
                        </span>\
                    </label>\
                    </div>\
                </div>\
                <div style="clear:both">\
                    <div class=event-time-column>\
                        <span>\
                            <%= language.event.category %>\
                            <span style="color:red">*</span>\
                        </span>\
                        <div class="joms-select--wrapper">\
                            <select class="joms-event-category joms-select" style="width:100%"></select>\
                        </div>\
                    </div>\
                    <div class=event-time-column>\
                        <span>\
                            <%= language.event.location %>\
                            <span style="color:red">*</span>\
                        </span>\
                        <input type=text class=joms-input name=location placeholder="<%= language.event.location_hint %>">\
                    </div>\
                </div>\
                <div style="clear:both">\
                    <div class=event-time-column>\
                        <span>\
                            <%= language.event.start %>\
                            <span style="color:red">*</span>\
                        </span>\
                        <input type=text class="joms-input joms-pickadate-startdate" placeholder="<%= language.event.start_date_hint %>" style="background-color:transparent;cursor:text">\
                        <input type=text class="joms-input joms-pickadate-starttime" placeholder="<%= language.event.start_time_hint %>" style="background-color:transparent;cursor:text">\
                        <span class="event-all-day joms-event-allday" style="margin-left:0;visibility:hidden">\
                            <span>All Day Event</span>\
                            <span style="position:relative">\
                                <i class=joms-icon-check-empty></i>\
                            </span>\
                        </span>\
                    </div>\
                    <div class=event-time-column>\
                        <span>\
                            <%= language.event.end %>\
                            <span style="color:red">*</span>\
                        </span>\
                        <input type=text class="joms-input joms-pickadate-enddate" placeholder="<%= language.event.end_date_hint %>" style="background-color:transparent;cursor:text">\
                        <input type=text class="joms-input joms-pickadate-endtime" placeholder="<%= language.event.end_time_hint %>" style="background-color:transparent;cursor:text">\
                    </div>\
                </div>\
                <nav class="joms-postbox-tab selected">\
                    <div class="joms-postbox-action event-action">\
                        <button class=joms-postbox-done><%= language.event.done_button %></button>\
                    </div>\
                </nav>\
            </div>\
        '),

        events: {
            'click .joms-postbox-done': 'onSave'
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

            this.$el.replaceWith( div );
            this.setElement( div );

            this.$category = this.$('.joms-event-category').empty();
            this.$location = this.$('[name=location]').val('');
            this.$startdate = this.$('.joms-pickadate-startdate').pickadate( $.extend({}, translations, { min: new Date(), format: 'd mmmm yyyy', klass: { frame: 'picker__frame startDate' } }) );
            this.$starttime = this.$('.joms-pickadate-starttime').pickatime({ interval: 15, format: timeFormatLabel, formatLabel: timeFormatLabel, klass: { frame: 'picker__frame startTime' } });
            this.$enddate = this.$('.joms-pickadate-enddate').pickadate( $.extend({}, translations, { format: 'd mmmm yyyy', klass: { frame: 'picker__frame endDate' } }) );
            this.$endtime = this.$('.joms-pickadate-endtime').pickatime({ interval: 15, format: timeFormatLabel, formatLabel: timeFormatLabel, klass: { frame: 'picker__frame endTime' } });
            this.$done = this.$('.joms-event-done');

            categories = constants.get('eventCategories') || [];
            if ( categories && categories.length ) {
                for ( i = 0; i < categories.length; i++ ) {
                    this.$category.append( '<option value="' + categories[i].id + '">' + categories[i].name + '</option>' );
                }
            }

            this.startdate = this.$startdate.pickadate('picker');
            this.starttime = this.$starttime.pickatime('picker');
            this.enddate = this.$enddate.pickadate('picker');
            this.endtime = this.$endtime.pickatime('picker');

            this.startdate.on({ set: $.bind( this.onSetStartDate, this ) });
            this.starttime.on({ set: $.bind( this.onSetStartTime, this ) });
            this.enddate.on({ set: $.bind( this.onSetEndDate, this ) });
            this.endtime.on({ set: $.bind( this.onSetEndTime, this ) });

            this.$private = this.$el.find('.private-event');

            return this;
        },

        // ---------------------------------------------------------------------

        value: function() {
            return this.data;
        },

        reset: function() {
            this.$category.val( this.$category.find('option').eq(0).attr('value') );
            this.$location.val('');
            this.$startdate.val('');
            this.$starttime.val('');
            this.$enddate.val('');
            this.$endtime.val('');
            this.$private.prop('checked', false);
        },

        // ---------------------------------------------------------------------

        onSetStartDate: function( o ) {
            var ts = o.select;
            this.enddate.set({ min: new Date(ts) }, { muted: true });
            this._checkTime();
        },

        onSetEndDate: function( o ) {
            var ts = o.select;
            this.startdate.set({ max: new Date(ts) }, { muted: true });
            this._checkTime();
        },

        onSetStartTime: function() {
            this._checkTime('start');
        },

        onSetEndTime: function() {
            this._checkTime('end');
        },

        onSave: function() {
            var category = this.$category.val(),
                location = this.$location.val(),
                startdate = this.startdate.get('select'),
                starttime = this.starttime.get('select'),
                enddate = this.enddate.get('select'),
                endtime = this.endtime.get('select'),
                error;

            // get category
            category = [ category, this.$category.find('[value=' + category + ']').text() ];

            // get start date and time
            startdate && (startdate = [ this.startdate.get('select', 'yyyy-mm-dd'), this.startdate.get('value') ]);
            starttime && (starttime = [ this.starttime.get('select', 'HH:i'), this.starttime.get('value') ]);

            // get end date and time
            enddate && (enddate = [ this.enddate.get('select', 'yyyy-mm-dd'), this.enddate.get('value') ]);
            endtime && (endtime = [ this.endtime.get('select', 'HH:i'), this.endtime.get('value') ]);

            // data
            this.data = {
                category  : category,
                location  : location,
                startdate : startdate,
                starttime : starttime,
                enddate   : enddate,
                endtime   : endtime,
                allday    : false,
                private   : this.$private.is(':checked') ? true : false
            };

            // check values
            if ( !this.data.category ) {
                error = language.get('event.category_not_selected');
            } else if ( !this.data.location ) {
                error = language.get('event.location_not_selected');
            } else if ( !this.data.startdate ) {
                error = language.get('event.start_date_not_selected');
            } else if ( !this.data.starttime ) {
                error = language.get('event.end_date_not_selected');
            } else if ( !this.data.enddate ) {
                error = language.get('event.start_time_not_selected');
            } else if ( !this.data.endtime ) {
                error = language.get('event.end_time_not_selected');
            }

            if ( error ) {
                window.alert( error );
                return;
            }

            this.trigger( 'select', this.data );
            this.hide();
        },

        // ---------------------------------------------------------------------
        // Helper functions.
        // ---------------------------------------------------------------------

        getTemplate: function() {
            var html = this.template({
                language: {
                    event: language.get('event') || {}
                }
            });

            return $( html ).hide();
        },

        _checkTime: function() {
            var startdate = this.startdate.get('select'),
                enddate = this.enddate.get('select'),
                starttime, endtime;

            if ( !startdate || !enddate )
                return;

            if ( enddate.year <= startdate.year && enddate.month <= startdate.month && enddate.date <= startdate.date ) {
                starttime = this.starttime.get('select');
                endtime = this.endtime.get('select');

                if ( !starttime ) {
                    this.endtime.set({ min: false }, { muted: true });
                } else {
                    this.endtime.set({ min: [ starttime.hour, starttime.mins ] }, { muted: true });
                    if ( endtime && endtime.time < starttime.time )
                        this.endtime.set({ select: [ starttime.hour, starttime.mins ] }, { muted: true });
                }

                if ( !endtime ) {
                    this.starttime.set({ max: false }, { muted: true });
                } else {
                    this.starttime.set({ max: [ endtime.hour, endtime.mins ] }, { muted: true });
                    if ( starttime && starttime.time > endtime.time )
                        this.starttime.set({ select: [ endtime.hour, endtime.mins ] }, { muted: true });
                }
            } else {
                this.starttime.set({ max: false }, { muted: true });
                this.endtime.set({ min: false }, { muted: true });
            }
        }

    });

});
