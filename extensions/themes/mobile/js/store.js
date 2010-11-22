// create object
function dataProvider(){};

// get knowledgeBase List
dataProvider.prototype.getKBList = function(){
    var uri = urlBase+"module/get?name=modellist&json=true";
    var temp = "#kbTemplate";
    var cont = "#kb-listholder";
    
    if( typeof localStorage["ontowiki.bases"] != 'undefined' && localStorage["ontowiki.bases"] != null){
        this.renderData($.parseJSON(localStorage["ontowiki.bases"]), temp, cont);
    }else{
        this.getModule(uri, temp, cont, "bases");
    }
}

// renders data
dataProvider.prototype.renderData = function(data, template, container){
    // render
    $(template).tmpl(data).appendTo(container);
    // try refresh listview
    try{
        $(container).listview("refresh");
    }catch(e){} // ignore all errors
}

// get module data and render it
dataProvider.prototype.getModule = function(uri, template, container, localVar){
    var dp = this;
    // show loader
    $.mobile.pageLoading();
    $.get(uri, function(data){
        $.mobile.pageLoading(true);
        
        var objdata = $.parseJSON(data);
        objdata = objdata["data"];
        
        // save to store
        localStorage["ontowiki."+localVar] = $.toJSON(objdata);
        
        // renderData
        dp.renderData(objdata, template, container);
    });
}
var provider = new dataProvider();

function getKBList(){
    provider.getKBList();
}
