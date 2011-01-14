// selected model uri
var selected_model = '';

// selected navigation entry data
var selectedNavigationEntry = {};

// selected instance
var selectedInstance = {};

// TODO: add to PHP and remove here (or not?)
var RDFAUTHOR_MOBILE = true;

// redraw mobile views
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

var redrawRDFauthor = function(){
    var page = $("#rdfa-list");
        page.page("destroy");
        page.page();
}

// selects database
function getBase(element){
    $.mobile.pageLoading();
    
    if( selected_model != $(element).attr('about') ){
        // select base
        selected_model = $(element).attr('about');
        // set rdfa vars
        RDFAUTHOR_DEFAULT_GRAPH = selected_model;
        RDFAUTHOR_DEFAULT_SUBJECT = selected_model;

        // set title
        $('#nav-title').text( $(element).text() );
        
        // set change page event        
        $(document).bind("navigation.done", function(e, status){
            $(document).unbind(e);        
            //location.hash = "nav";
            $.mobile.changePage("#nav", "slide", false, true ); //$(".ui-page-active"), $(
        });
        
        // get nav
        provider.getNavigation('reset', selected_model);
    }else{
        //location.hash = "nav";
        $.mobile.changePage("#nav", "slide", false, true );
    }
    
    
}

// navigate
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

// show instances
function showInstances(){
    // show load progress
    $.mobile.pageLoading();
    
    var url = selectedNavigationEntry.url;
    // set rdfa
    RDFAUTHOR_DEFAULT_SUBJECT = url;
    // set title
    var title = selectedNavigationEntry.title;
    $("#instance-title").text(title);
    
    // handle page change
    $(document).bind("instance.list.done", function(e, status){
        $(document).unbind(e); 
        
        // remove loader
        $.mobile.pageLoading(true);

        // switch page
        $.mobile.changePage("#instance-list");
    })
    
    // get
    provider.getInstanceList(url);
}

// change instance page
function pageList(entry, animate){
    $.mobile.pageLoading();
    
    url = $(entry).attr('about');
    
    console.log(url);
    
    // handle page change
    $(document).bind("instance.list.done", function(e, status){
        $(document).unbind(e); 
        
        $.mobile.pageLoading(true);
        
        if(animate){
            //location.hash = "instance-list";
            $.mobile.changePage("#instance-list", "slide", false, true );
        }else{
            redrawInstances();
        }
    })
    
    // get
    provider.getInstanceList(url);
}

// navigate deeper
function navigateDeeper(){
    // show load progress
    $.mobile.pageLoading();
    provider.getNavigation('navigateDeeper', selectedNavigationEntry.parent);
}

// show properties
function onInstanceClick(entry, animate){
    if( typeof(animate) == 'undefined' ) animate = true;
    
    // check for available instance
    if( !(typeof entry != 'undefined' || (typeof selectedInstance != 'undefined' && selectedInstance.uri.length > 1) ) ) return;
    
    // loading 
    $.mobile.pageLoading();

    // get data
    var url, title;
    if( typeof entry != 'undefined' ){
        url = $(entry).attr('about');
        title = $(entry).text();
        
        // set current instance
        selectedInstance = {uri:url, title: title};
    }else{
        url = selectedInstance.uri;
        title = selectedInstance.title;
    }
    
    // set title
    $("#properties-title").text(title);
    
    // handle page change
    $(document).bind("instance.done", function(e, status){
        $(document).unbind(e); 
        
        $.mobile.pageLoading(true);
        
        if(animate){ 
            //location.hash = "properties-list";
            $.mobile.changePage("#properties-list", "slide", false, true );
        }else{
            // refresh page
            redrawProperties();
        }
    })
    
    // get
    provider.getInstance(url);
}

// toggle menu links
function toggleMenu(){
    if( $("#instance-list").hasClass("ui-page-active") == true ){
        $("#menu-filters-btn").show();
    }else{
        $("#menu-filters-btn").hide();
    }
    
    if( $("#properties-list").hasClass("ui-page-active") == true ){
        $("#menu-edit-btn").show();
    }else{
        $("#menu-edit-btn").hide();
    }
}

// do login
function doLogin(){
    $("#loginform").submit();
}

// search function
function doSearch(){
    var req = $("#search").val();
    if(req.length < 3){
        alert('request too short');
        return;
    }
    // loading 
    $.mobile.pageLoading();
    // get results
    $.get(urlBase+"application/search/?searchtext-input="+req, function(data){
        $.get(urlBase+"resource/instances",function(data){
            $("#menu-form").remove();
            $("#searchres-content").html(data);
            $("#searchres-title").text(req);
            
            //location.hash = "searchres-list";
            $.mobile.changePage("#searchres-list", "slide", false, true );
            $.mobile.pageLoading(true);
        });
    });
}

function getFilters(){
    // loading 
    $.mobile.pageLoading();
    // get results
    $.get(urlBase+"module/get?name=filter&json=true", function(data){
        data = $.parseJSON(data);
        
        $.mobile.changePage("#filters-view", "slide", false, true );
        $.mobile.pageLoading(true);
        
        var container = "#filters-list";
        var template = "#filtersTemplate";
        // clear
        $(container).empty();
        // render
        $(template).tmpl(data).appendTo(container);
        // try refresh listview
        try{
            $(container).listview("refresh");
        }catch(e){} // ignore all errors
    });
};

function openRDFa(){
    $.mobile.pageLoading();
    
    var content = $("#properties-content");
    var subject = $("ul", content).attr("about");
   
    var ispred, predicate, object, stmt;
    $("li", content).each(function(index){
        ispred = ( $(this).attr("data-role") === "list-divider" );
        if(ispred){
            predicate = $(this).attr("about");
            return;
        }
        object = $("a", this).attr("content");
        if( typeof object === "undefined" || object.length < 1){
            object = {value: $("a", this).text(), type: 'literal'};
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
        onCancel: function() {
            //console.log('cancel');
            $.mobile.changePage("#properties-list", "slide", false, true );
        }, 
        onSubmitSuccess: function() {
            //console.log('ok');
            $.mobile.changePage("#properties-list", "slide", false, true );
        },
        title: $("#properties-title").text(),
        saveButtonTitle: 'Save',
        cancelButtonTitle: 'Cancel',
        showButtons: true,
        useAnimations: false,
        autoParse: false,
        container: "#rdfa-content", 
        viewOptions: {
            type: 'mobile' /* inline or popover */
        }
    };
    RDFauthor.setOptions(options);

    RDFauthor.setInfoForGraph(selected_model, "queryEndpoint", urlBase+"sparql");
    RDFauthor.setInfoForGraph(selected_model, "updateEndpoint", urlBase+"update");

    RDFauthor.start();

    //location.hash = "#rdfa-list";
    $.mobile.changePage("#rdfa-list", "slide", false, true );
}
