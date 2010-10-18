/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

var selected_model = '';

// TODO: add to PHP and remove here
var RDFAUTHOR_MOBILE = true;

//$("#navigation").bind('pageAnimationEnd', function(e, info){
//   getBase(e);
//});

var redrawNavigation = function(){
    var nav = $("#nav");
    nav.page("destroy");
    nav.page();
};

$(document).ready(function(){
    // load rdfauthor
    var rdf_script = document.createElement( 'script' );
    rdf_script.type = 'text/javascript';
    rdf_script.src = RDFAUTHOR_BASE+"src/rdfauthor.js";
    $('body').append( rdf_script );
    
    // prepare nav page on show
    $("#nav").bind("beforepageshow", function(){
        redrawNavigation();
    });
    
    // prepare instance list on show
    $("#instance-list").bind("beforepageshow", function(){
        var page = $("#instance-list");
        page.page("destroy");
        page.page();
    });
    
    // prepare properties list on show
    $("#properties-list").bind("beforepageshow", function(){
        var page = $("#properties-list");
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

function getBase(element){
    $.pageLoading();
    
    if( selected_model != $(element).attr('about') ){
        // select base
        selected_model = $(element).attr('about');
        // set rdfa vars
        RDFAUTHOR_DEFAULT_GRAPH = selected_model;
        RDFAUTHOR_DEFAULT_SUBJECT = selected_model;
        // get
        var url = urlBase + 'model/select/?m=' + $(element).attr('about');
        var title = $(element).text();

        $.get(url, function(data){
            $('#nav-title').text(title);
            navigationEvent('reset');

            $(document).bind("navigation.done", function(e, status){
                //$("#loader").remove();
                $(element).attr("class","");
                $(document).unbind(e);
                
                location.hash = "nav";
                //$.changePage($(".ui-page-active"), $("#nav"), "slide");
            });
        })
    }else{
        location.hash = "nav";
        //$.changePage($(".ui-page-active"), $("#nav"), "slide");
    }
    
    
}

function onNavigationEntryClick(entry){
    // show load progress
    $.pageLoading();

    if( $(entry).parents("li").hasClass("arrow") ){
        // navigate
        navigationEvent('navigateDeeper', $(entry).parents("li").attr('about'));
    }else{
        url = $(entry).attr('about');
        // set rdfa
        RDFAUTHOR_DEFAULT_SUBJECT = url;
        // get
        title = $(entry).text();
        $.get(url, function(data){
            $("#instance-title").text(title);
            $('#instance-content').html(data);
            
            // remove loader
            $.pageLoading(true);

            // switch page
            location.hash = "instance-list";
        })
    }
}

function pageList(entry, animate){
    url = $(entry).attr('about');
    $.get(url, function(data){
        $('#instance-content').html(data);

        if(animate)
            jQT.goTo("#instance-list", "slide");
    })
}

function onInstanceClick(entry, animate){
    if( typeof(animate) == 'undefined' ) animate = true;
    
    // loading 
    $.pageLoading();

    url = $(entry).attr('about');
    title = $(entry).text();
    $.get(url, function(data){
        $("#properties-title").text(title);
        $('#properties-content').html(data);
        
        // remove animation
        $.pageLoading(true);

        if(animate) location.hash = "properties-list";
    })
}

function toggleMenu(element){
    if( $("#menu-form").length == 0 ){
        var menu_string = '\
            <div id="menu-form">\
                <ul class="individual">\
                    <li><input id="search-input" type="text" placeholder="Search"></li>\
                    <li><a href="#" onclick="doSearch( $(\'#search-input\').val() ); return false;">Go</a></li>\
                </ul>';

        if( $("div.current").attr('id') == "properties-list" ){
            menu_string += '<ul class="rounded">\
                        <li><a href="#" onclick="openRDFa()">Edit</a></li>\
                    </ul>';
        }
        menu_string += '</div>';

        var menu = $(menu_string);

        $(element).parent().after(menu);
    }else{
        $("#menu-form").remove();
    }
}

function doSearch(req){
    $.get(urlBase+"application/search/?searchtext-input="+req, function(data){
        $.get(urlBase+"resource/instances",function(data){
            $("#menu-form").remove();
            $("#searchres-content").html(data);
            $("#searchres-title").text(req);
            jQT.goTo("#searchres-list", "slide");
        });
    });
}

function doLogin(){
    $("#loginform").submit();
}

function openRDFa(){
    var content = $("#properties-content");
    var subject = $("ul", content).attr("about");
   
    var ispred, predicate, object, stmt;
    $("li", content).each(function(index){
        ispred = ( $(this).attr("class") === "sep" );
        if(ispred){
            predicate = $(":first-child", this).attr("about");
            return;
        }
        object = $(":first-child", this).attr("content");
        if( typeof object === "undefined" || object.length < 1){
            object = {value: $(":first-child", this).text(), type: 'literal'};
        }else{
            object = {value: "<"+object+">", type: 'uri'};
        }
        stmt = new Statement({
            subject: "<"+subject+">",
            predicate: "<"+predicate+">",
            object: object
        },{graph:RDFAUTHOR_DEFAULT_GRAPH});
        RDFauthor.addStatement(stmt);
    });

    var options = {
        title: $("#properties-title").text(),
        saveButtonTitle: 'Save',
        cancelButtonTitle: 'Cancel',
        showButtons: true,
        useAnimations: false,
        autoParse: false,
        container: "#rdfa-content", 
        onCancel: function() {
            $("#rdfa-back").click();
        }, 
        onSubmitSuccess: function() {
            $("#rdfa-back").click();
        }
    };
    RDFauthor.setOptions(options);

    RDFauthor.setInfoForGraph(selected_model, "queryEndpoint", urlBase+"sparql");
    RDFauthor.setInfoForGraph(selected_model, "updateEndpoint", urlBase+"update");

    RDFauthor.start();

    $("#prop-menu").click();
    jQT.goTo("#rdfa-list", "slide");

    
}
