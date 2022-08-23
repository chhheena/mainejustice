var Vue = require( 'joms' ).Vue,
    localStore = require( 'joms' ).storage,
    settings = window.joms_plg_jomsocialchatbar || {},
    configs = settings.configs || {},
    templates = settings.templates || {};

module.exports = Vue.component( 'chatbar-sidebar', {
    template: templates.chatbar_sidebar,
    data: function() {
        var data, state;

        data = {
            expanded: true,
            fetching: false,
            fetchDone: false
        };

        if ( +configs.remember_last_state ) {
            state = localStore.get( 'chatbar' ) || {};
            _.extend( data, state.sidebar || {} );
        }

        return data;
    },
    computed: {
        chats: function() {
            return this.$store.getters['chats/sidebar'];
        }
    },
    methods: {
        open: function( id ) {
            this.$store.dispatch( 'chats/open', id );
        },
        toggle: function() {
            var config = localStore.get( 'chatbar' ) || {},
                configSidebar = config.sidebar || {};

            this.expanded = ! this.expanded;
            _.extend( configSidebar, { expanded: this.expanded });
            _.extend( config, { sidebar: configSidebar });

            localStore.set( 'chatbar', config );
        },
        fetch: function(ids) {
            if ( ! this.fetching && ! this.fetchDone ) {
                this.fetching = true;
                this.fetchDebounced(ids);
            }
        },
        fetchDebounced: function(ids) {
            var that = this;
            this.$store.dispatch( 'chats/fetchMore', { list : ids } ).fail(function() {
                that.fetchDone = true;
            }).always(function() {
                that.fetching = false;
            });
        },
        handleScroll: function( e ) {
            var el = e.currentTarget,
                scrollTop = el.scrollTop,
                delta = e.deltaY,
                height;
            if ( scrollTop === 0 && delta < 0 ) {
                e.preventDefault();
                e.stopPropagation();
            } else {
                var items = el.querySelectorAll('.joms-chat__item');
                var ids = [];
                for (var i = 0; i < items.length; i++) {
                    ids.push(+items[i].getAttribute('data-id'));
                }
                height = el.scrollHeight - el.clientHeight;
                if ( Math.abs( scrollTop - height ) <= 1 && delta > 0 ) {
                    e.preventDefault();
                    e.stopPropagation();
                    if ( !this.fetchDone ) {
                        this.fetch(ids);
                    }
                }
            }
        },
        setActiveWindow: function( chat ) {
            this.$store.dispatch( 'chats/setActiveWindow', +chat.id );
            this.$store.dispatch( 'chats/open', +chat.id );
            if ( +chat.seen === 0 ) {
                this.$store.dispatch( 'chats/seen', +chat.id );
            }
        }
    }
});
