define('views/stream/layout',[
    'sandbox',
    'views/base',
    'views/stream/filterbar'
],

// definition
// ----------
function(
    $,
    BaseView,
    FilterbarView
) {

    return BaseView.extend({

        initialize: function() {
            this.filterbar = new FilterbarView();
        },

        render: function() {
            this.filterbar.render();
        }

    });

});
