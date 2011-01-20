/*
 * On document ready, prepare needed stuff
 */
$(document).ready(function(){
    getKBList();
    
    $(document).bind("RDFauthor.added", function(){
        //redrawPage();
    });
    
    // prepare page on navigation done
    $(document).bind("navigation.done", function(){        
        // redraw page
        $("#nav").page("destroy").page();
        
        // remove loader
        $.mobile.pageLoading(true);
    });
    
    // filters
    // on addfilter btn click
    $("#add-filter").click(function(){
        $.mobile.changePage("#filters-add-view", "slide", false, true );
    });
    
    
    $("#get-filters").click(function(){
        // loading 
        $.mobile.pageLoading();
        // get results
        $.get(urlBase+"module/get?name=filter&json=true", function(data){
            data = $.parseJSON(data);
            
            $.mobile.changePage("#filters-view", "slide", false, true );
            $.mobile.pageLoading(true);
            
            // render active stuff
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
            
            // render available stuff
            container = "#filters-add-list";
            template = "#filtersAvailableTemplate";
            // clear
            $(container).empty();
            // render
            $(template).tmpl(data).appendTo(container);
            // try refresh listview
            try{
                $(container).listview("refresh");
            }catch(e){} // ignore all errors            
        });
    });
    
    $(".addfilter").live('click', function(event){
        var listName = "instances";
        var url = urlBase+"filter/getpossiblevalues?predicate="+escape( $(this).attr("about") )+"&inverse="+$(this).attr("inverse")+"&list="+listName;
        
        // loading 
        $.mobile.pageLoading();
        // get results
        $.get(url, function(data){
            data = $.parseJSON(data);
            
            $.mobile.changePage("#filters-values-view", "slide", false, true );
            $.mobile.pageLoading(true);
            
            // render active stuff
            var container = "#filters-values-list";
            var template = "#filtersValuesTemplate";
            // clear
            $(container).empty();
            // render
            $(template).tmpl(data).appendTo(container);
            // try refresh listview
            try{
                $(container).listview("refresh");
            }catch(e){} // ignore all errors
        });
    });
    
    $(".applyfilter").live('click', function(event){
        var prop = $(this).attr("about");
        var propLabel = $(this).html();
        var inverse = ( $(this).attr("inverse") == "true" );

        var filtertype = $(this).attr("type");
        var value1 = $(this).attr("value");
        if(typeof value1 == "undefined"){
            value1 = null;
        }

        var type = $(this).attr("type");
        var typedata;
        
        if(type == "literal" && typeof $(this).attr("language") != 'undefined'){
            typedata = $(this).attr("language");
        } else if(type == "typed-literal"){
            typedata = $(this).attr("datatype");
        }
        
        var id = Math.random()*100;
            
        var data =
            {
            filter:
                [
                    {
                        "mode" : "box",
                        "action" : "add",
                        "id" : "filter"+id,
                        "property" : prop,
                        "isInverse" : typeof inverse != 'undefined' ? inverse : false,
                        "propertyLabel" : typeof propLabel != 'undefined' ? propLabel : null,
                        "filter" : filtertype,
                        "value1": value1,
                        "value2": null,
                        "valuetype": typeof type != 'undefined' ? type : null,
                        "literaltype" : typeof typedata != 'undefined' ? typedata : null,
                        "hidden" : false,
                        "negate" : false
                    }
                ]
        };

        var dataserialized = $.toJSON(data);
        var url = urlBase + "resource/instances?instancesconfig=" + encodeURIComponent(dataserialized)+"&list=instances";
        $.post(url, function(data){
            console.log(data);
        });
    });
    
    // navigation buttons events
    // the link to the root
    $('.navFirst').live('click', function(event){
        $.mobile.pageLoading();
        provider.getNavigation('navigateRoot', selected_model);
        return false;
    })
    
    // the link to higher level
    $('.navBack').live('click', function(event){
        $.mobile.pageLoading();
        if(navigationStateSetup['state']['path'].length > 0){
            provider.getNavigation('navigateHigher', navigationStateSetup['state']['parent']);
        }else{
            provider.getNavigation('navigateHigher', selected_model);
        }
        return false;
    })

    // selects database
    $(".kbEntry").live('click', function(event){
        $.mobile.pageLoading();
        
        if( selected_model != $(this).attr('about') ){
            // select base
            selected_model = $(this).attr('about');
            // set rdfa vars
            RDFAUTHOR_DEFAULT_GRAPH = selected_model;
            RDFAUTHOR_DEFAULT_SUBJECT = selected_model;

            // set title
            $('#nav-title').text( $(this).text() );
            
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
    });
    
    // navigate
    $(".navEntry").live('click', function(event){
        selectedNavigationEntry = {
            parent: $(this).parents("li").attr('about'),
            url: $(this).attr('about'),
            title: $(this).text()
        };
        
        if( $(this).parents("li").hasClass("arrow") ){
            $("#item-nav-deep").show();
        }else{
            $("#item-nav-deep").hide();
        }
    });
    
    
    // show instances
    $("#show-instances").click(function(event){
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

            // redraw page
            $("#instance-list").page('destroy').page();

            // switch page
            $.mobile.changePage("#instance-list", "slide", false, true );
        })
        
        // get
        provider.getInstanceList(url);
    });
    
    $("#item-nav-deep").click(function(event){
        // show load progress
        $.mobile.pageLoading();
        provider.getNavigation('navigateDeeper', selectedNavigationEntry.parent);
    });
    
    $(".instanceEntry").live('click', function(event){
        var animate = $(this).attr('animate');
        console.log(animate);
        if( typeof(animate) == 'undefined' ) animate = "true";
        
        // check for available instance
        var entry = this;
        if( !(typeof entry != 'undefined' || (typeof selectedInstance != 'undefined' && selectedInstance.uri.length > 1) ) ) return;
        
        // check if contains url
        if( $(entry).attr('about').length < 1 ) return;
        
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
            
            if(animate == "true"){ 
                //location.hash = "properties-list";
                $.mobile.changePage("#properties-list", "slide", false, true );
            }else{
                // refresh page
                //redrawPage();
            }
        })
        
        // get
        provider.getInstance(url);
    });
});
