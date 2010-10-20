/*
 * On document ready, prepare needed stuff
 */
$(document).ready(function(){
    // quickfix for rdfa
    //$.ui = true;
    
    // load rdfauthor
    /*var rdf_script = document.createElement( 'script' );
    rdf_script.type = 'text/javascript';
    rdf_script.src = RDFAUTHOR_BASE+"src/rdfauthor.js";
    $('body').append( rdf_script );*/
    
    // prepare nav page on show
    $("#nav").bind("beforepageshow", function(){
        redrawNavigation();
    });
    
    // prepare instance list on show
    $("#instance-list").bind("beforepageshow", function(){
        redrawInstances();
    });
    
    // prepare properties list on show
    $("#properties-list").bind("beforepageshow", function(){
        redrawProperties();
    });
    
    // prepare search list on show
    $("#searchres-list").bind("beforepageshow", function(){
        var page = $("#searchres-list");
        page.page("destroy");
        page.page();
    });
    
    // prepare rdfa list on show
    $("#rdfa-list").bind("beforepageshow", function(){
        var page = $("#rdfa-list");
        page.page("destroy");
        page.page();
    });
    
    // prepare page on navigation done
    $(document).bind("navigation.done", function(){        
        // redraw page
        redrawNavigation();
        
        // remove loader
        $.pageLoading(true);
    });
});
