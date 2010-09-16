/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

var jQT = new $.jQTouch();

var loader_src = '<img id="loader" src="extensions/themes/mobile/image/spinner.gif">'; // <small id="loader"></small>
var loader_small_src = '<small id="loader"><img src="extensions/themes/mobile/image/spinner.gif"></small>';
var selected_model = '';

// TODO: add to PHP and remove here
var RDFAUTHOR_MOBILE = true;

//$("#navigation").bind('pageAnimationEnd', function(e, info){
//   getBase(e);
//});

$(document).ready(function(){
    // load rdfauthor
    var rdf_script = document.createElement( 'script' );
    rdf_script.type = 'text/javascript';
    rdf_script.src = RDFAUTHOR_BASE+"src/rdfauthor.js";
    $('body').append( rdf_script );
});

function getBase(element){
    if( selected_model != $(element).attr('about') ){
        // select base
        selected_model = $(element).attr('about');
        // set rdfa vars
        RDFAUTHOR_DEFAULT_GRAPH = selected_model;
        RDFAUTHOR_DEFAULT_SUBJECT = selected_model;
        // get
        // 'http://'+location.host+'/ontowiki/
        var url = urlBase + 'model/select/?m=' + $(element).attr('about');
        var title = $(element).text();
        //$(element).append(loader_small_src);
        $(element).attr("class","loading");

        $.get(url, function(data){
            $('#nav-title').text(title);
            navigationEvent('reset');

            $(document).bind("navigation.done", function(e, status){
                //$("#loader").remove();
                $(element).attr("class","");
                $(document).unbind(e);

                jQT.goTo("#nav", "slide");
            });
        })
    }else{
        jQT.goTo("#nav", "slide");
    }
}

function onNavigationEntryClick(entry){
    //$(entry).append(loader_small_src);
    $(entry).attr("class","loading");

    if($(entry).parent().attr('class') == "arrow"){
        $(document).bind("navigation.done", function(e, status){
            //$("#loader").remove();
            $(entry).attr("class","");
            $(document).unbind(e);
        });
        navigationEvent('navigateDeeper', $(entry).parent().attr('about'));
        //$("#loader").remove();
    }else{
        url = $(entry).attr('about');
        // set rdfa
        RDFAUTHOR_DEFAULT_SUBJECT = url;
        // get
        title = $(entry).text();
        $.get(url, function(data){
            $("#instance-title").text(title);
            $('#instance-content').html(data);
            //$("#loader").remove();
            $(entry).attr("class","");
            //navigationEvent('reset');

            jQT.goTo("#instance-list", "slide");
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
    //$(entry).append(loader_src);
    $(entry).attr("class","loading");

    url = $(entry).attr('about');
    title = $(entry).text();
    $.get(url, function(data){
        $("#properties-title").text(title);
        $('#properties-content').html(data);
        //$("#loader").remove();
        $(entry).attr("class","");

        if(animate) jQT.goTo("#properties-list", "slide");
    })
}

function addLoader(entry){
    //$(entry).html(loader_src);
    $(entry).attr("class","loading");
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
        container: "#rdfa-content"
    };
    RDFauthor.setOptions(options);

    RDFauthor.setInfoForGraph(selected_model, "queryEndpoint", urlBase+"sparql");
    RDFauthor.setInfoForGraph(selected_model, "updateEndpoint", urlBase+"update");

    RDFauthor.start();

    $( RDFauthor.eventTarget() ).bind("rdfauthor.cancel", function(){
        $( RDFauthor.eventTarget() ).unbind("rdfauthor.cancel");
        $("#rdfa-back").click();
    });

    $( RDFauthor.eventTarget() ).bind("rdfauthor.commit", function(){
        $( RDFauthor.eventTarget() ).unbind("rdfauthor.commit");
        $("#rdfa-back").click();
    })

    $("#prop-menu").click();
    jQT.goTo("#rdfa-list", "slide");

    
}