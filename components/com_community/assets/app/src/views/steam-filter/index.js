const $ = jQuery;
const $wrapper = $('.joms-activity-filter');
const $btn = $wrapper.find('.joms-activity-filter-action');
const $list = $wrapper.find('.joms-activity-filter-dropdown');
const $options = $wrapper.find('.joms-activity-filter__options');

function init() {
    $btn.on( 'click', toggle );
    $list.on( 'click', 'li', select );
    $list.on( 'change', 'select', filterChange );
    $list.on( 'keyup', 'input[type=text]', filterKeyup );
    $list.on( 'click', 'button.joms-button--primary', filterSearch );

    $options.find('li').not('.noselect').addClass('joms-js-filterbar-item');
    $( document ).on( 'click', '.joms-js-filterbar-item', makeDefault );
    $( document ).on('click', onDocumentClick );
}

function toggle() {
    $list.is(':visible') ? collapse() : expand();
}

function expand() {
    $list.show();
}

function collapse() {
    $list.hide();
}

function select( e ) {
    let li = $( e.currentTarget ),
        url = li.data('url') || '/',
        filter = li.data('filter');

    if ( filter === '__filter__' ) {
        return;
    }

    toggle();

    window.location = url;
}

function filterChange( e ) {
    let value = e.target.value,
        $input = $list.find('input[type=text]'),
        $button = $list.find('.joms-button--primary');

    if ( value === 'hashtag' || value === 'keyword' ) {
        $input.attr( 'placeholder', $input.data('label-' + value) );
        $button.html( $button.data('label-' + value) );
    }
}

function filterKeyup( e ) {
    let input = $( e.currentTarget ),
        value = input.val(),
        newValue = value;

    newValue = newValue.replace( /#/g, '' );
    if ( newValue.length !== value.length ) {
        input.val( newValue );
    }
}

function filterSearch( e ) {
    let btn, li, filter, input, value, url;

    e.preventDefault();
    e.stopPropagation();

    btn = $( e.currentTarget );
    li = btn.closest('li');
    filter = li.find('select').val();
    input = li.find('input');
    value = input.val().replace(/^\s+|\s+$/g, '');

    if ( !value ) {
        return;
    }

    if ( filter === 'hashtag' ) {
        value = value.split(' ');
        value = value[0];
    }

    url = li.data('url'),
    url = url.replace( '__filter__', filter );
    url = url.replace( '__value__', value );
    window.location = url;
}

function makeDefault( e ) {
    let btn, value, loading;

    e.preventDefault();
    e.stopPropagation();

    btn = $( e.currentTarget );
    value = btn.find('a').data('value');
    loading = $options.find('.noselect > img');

    if ( loading.css('visibility') !== 'hidden' )
        return;

    loading.css( 'visibility', 'visible' );

    joms.ajax({
        func: 'system,ajaxDefaultUserStream',
        data: [ value ],
        callback: json => {
            if ( json.error ) {
                joms.popup.info( 'Error', json.error );
            } else if ( json.success ) {
                joms.popup.info( '', json.message );
                $options.find('.joms-dropdown').hide();
                btn.addClass('active').siblings('li').removeClass('active');
                loading.css( 'visibility', 'hidden' );
            }
        }
    });
}

function onDocumentClick( event ) {
    const $elem = $(event.target);

    if ( $elem.closest('.joms-activity-filter').length ) {
        return;
    }

    collapse();
}

export default function() {
    init();
}