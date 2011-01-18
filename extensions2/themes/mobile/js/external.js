// selected model uri
var selected_model = '';

// selected navigation entry data
var selectedNavigationEntry = {};

// selected instance
var selectedInstance = {};

// TODO: add to PHP and remove here (or not?)
var RDFAUTHOR_MOBILE = true;

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
            redrawPage();
        }
    })
    
    // get
    provider.getInstanceList(url);
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
