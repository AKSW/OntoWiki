/*
 * On document ready, prepare needed stuff
 */
$(document).ready(function(){
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
        redrawRDFauthor();
    });
    
    $(document).bind("RDFauthor.added", function(){
        redrawRDFauthor();
    });
    
    // prepare page on navigation done
    $(document).bind("navigation.done", function(){        
        // redraw page
        redrawNavigation();
        
        // remove loader
        $.pageLoading(true);
    });
});
