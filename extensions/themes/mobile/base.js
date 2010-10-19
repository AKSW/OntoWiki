/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

// selected model uri
var selected_model = '';

// selected navigation entry data
var selectedNavigationEntry = {};

// TODO: add to PHP and remove here (or not?)
var RDFAUTHOR_MOBILE = true;

var redrawNavigation = function(){
    var nav = $("#nav");
    nav.page("destroy");
    nav.page();
};

var redrawProperties = function(){
    var page = $("#properties-list");
        page.page("destroy");
        page.page();
}

var redrawInstances = function(){
    var page = $("#instance-list");
        page.page("destroy");
        page.page();
}

$(document).ready(function(){
    // quickfix for rdfa
    $.ui = true;
    
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
    selectedNavigationEntry = {
        parent: $(entry).parents("li").attr('about'),
        url: $(entry).attr('about'),
        title: $(entry).text()
    };
    
    if( $(entry).parents("li").hasClass("arrow") ){
        $("#item-nav-deep").show();
    }else{
        $("#item-nav-deep").hide();
    }
}

function showInstances(){
    // show load progress
    $.pageLoading();
    
    url = selectedNavigationEntry.url;
    // set rdfa
    RDFAUTHOR_DEFAULT_SUBJECT = url;
    // get
    title = selectedNavigationEntry.title;
    $.get(url, function(data){
        $("#instance-title").text(title);
        $('#instance-content').html(data);
        
        // remove loader
        $.pageLoading(true);

        // switch page
        location.hash = "instance-list";
    })
}

function navigateDeeper(){
    // show load progress
    $.pageLoading();
    navigationEvent('navigateDeeper', selectedNavigationEntry.parent);
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

        if(animate){ 
            location.hash = "properties-list";
        }else{
            redrawProperties();
        }
    })
}

function toggleMenu(){
    if( $("#properties-list").hasClass("ui-page-active") == true ){
        $("#menu-edit-btn").show();
    }else{
        $("#menu-edit-btn").hide();
    }
}

function doLogin(){
    $("#loginform").submit();
}

function doSearch(){
    var req = $("#search").val();
    if(req.length < 3){
        alert('request too short');
        return;
    }
    // loading 
    $.pageLoading();
    // get results
    $.get(urlBase+"application/search/?searchtext-input="+req, function(data){
        $.get(urlBase+"resource/instances",function(data){
            $("#menu-form").remove();
            $("#searchres-content").html(data);
            $("#searchres-title").text(req);
            
            location.hash = "searchres-list";
            $.pageLoading(true);
        });
    });
}

function pageList(entry, animate){
    $.pageLoading();
    
    url = $(entry).attr('about');
    $.get(url, function(data){
        $('#instance-content').html(data);

        if(animate){
            location.hash = "instance-list";
        }else{
            redrawInstances();
        }
        $.pageLoading(true);
    })
}

/*****************************  TO REWRITE  *****************************************/

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
