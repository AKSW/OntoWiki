$(document).ready(function() {
    // default value
    var defaultOffset = 0;
    var defaultLimit = 5;
    var defaultDisplayLimit = 5;
    // setup
    var setup = {
        offset: defaultOffset,
        limit: defaultLimit,
        displayLimit: defaultDisplayLimit,
    };

    // dom references
    var searchInput = $("#modellist-search-input");

    var updateModellistModule = function() {
        // update window
        $.get(urlBase + 'module/get/name/modellist/id/modellist', function(data) {
            $('#modellist').replaceWith(data);
            $('#modellist').addClass('has-contextmenus-block')
            .addClass('windowbuttonscount-right-1');

            $('#modellist').enhanceWindow();

            // reload data
            reloadList();
        });
    };

    var reloadList = function() {
        $('#modellist-search-input').addClass('is-processing');
        // do request
        $.post(urlBase + 'modellist/explore', {setup: setup}, function(data) {
            // unset search string if needed
            //if(setup.searchString) delete setup.searchString;
            // render data
            $('#modellist-container').html(data);
            $('#modellist-search-input').removeClass('is-processing');
        });
    };

    $(document).on('mouseup', '.modellist_hidden_button', function(e) {
        if ($(this).hasClass('show')) {
            sessionStore('showHiddenGraphs', true, {
                method: 'set',
                callback: function() {
                    updateModellistModule();
                }
            });
        } else {
            sessionStore('showHiddenGraphs', false, {
                method: 'unset',
                callback: function() {
                    updateModellistModule();
                }
            });
        }
    });

    // on 'click' doesn't work? WAT?
    $(document).on('mouseup', '.modellist_reset_button', function(e) {
        // set search string
        if(setup.searchString) delete setup.searchString;
        // reset to default values
        setup.limit = defaultLimit;
        setup.offset = defaultOffset;
        setup.displayLimit = defaultDisplayLimit;
        // trigger reload
        reloadList();
    });

    $(document).on('click', '#modellist-more', function () {
        setup.offset = parseInt(setup.displayLimit, 10);
        setup.displayLimit = parseInt(setup.offset, 10) + parseInt(setup.limit, 10);
        reloadList();
    });

    searchInput.on('keypress', function(event) {
        // do not create until user pressed enter
        if (event.which == 13) {
            if(event.currentTarget.value.length > 0) {
                // set search string
                setup.searchString = event.currentTarget.value;
                // reset to default values
                setup.limit = defaultLimit;
                setup.offset = defaultOffset;
                setup.displayLimit = defaultDisplayLimit;
                // clean input
                $(event.currentTarget).val('');
                // trigger reload
                reloadList();
            }
            return false;
        }
        return true;
    });

    // load existing state if present
    if(window.modellistStateSetup) {
        setup = modellistStateSetup;
    }

    // init
    reloadList();
});
