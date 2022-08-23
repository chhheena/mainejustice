var Vue = require( 'joms' ).Vue,
    ChatbarSidebar = require( './chatbar-sidebar' ),
    ChatbarWindow = require( './chatbar-window' ),
    settings = window.joms_plg_jomsocialchatbar || {},
    configs = settings.configs || {},
    templates = settings.templates || {};

module.exports = Vue.component( 'chatbar', {
    template: templates.chatbar,
    components: {
        ChatbarSidebar: ChatbarSidebar,
        ChatbarWindow: ChatbarWindow
    },
    data: function() {
        var data = { 
            position: 'right',
            limitOpened: 0 
        };

        if ( configs.chat_bar_position ) {
            data.position = configs.chat_bar_position;
        }

        return data;
    },
    mounted: function() {
        this.setLimitOpened();
        window.addEventListener( 'resize', this.setLimitOpened );
    },
    computed: {
        openedChats: function() {
            var store = this.$store,
                getter = store.getters[ 'chats/opened' ],
                opened = getter.opened,
                needClose = getter.needClose;

            _.each( needClose, function( id ) {
                store.dispatch( 'chats/close', id );
            });

            return opened;
        }
    },

    methods: {
        setLimitOpened: function() {
            this.$store.dispatch( 'chats/setLimitOpened' );
        }
    } 
});
