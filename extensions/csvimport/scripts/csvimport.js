var dimensions = {};
var uribase = '';

// RDFa
var attributeModel = "http://localhost/ontowiki/sdmx-attribute";
var conceptModel   = "http://localhost/ontowiki/sdmx-concept";
var dimensionModel = "http://localhost/ontowiki/sdmx-dimension";

$(document).ready(function () {
    if(typeof isTabular != "undefined" && isTabular == true ) return;
    
    // load RDFa
    var rdf_script = document.createElement( 'script' );
    rdf_script.type = 'text/javascript';
    rdf_script.src = RDFAUTHOR_BASE+"src/rdfauthor.js";
    $('body').append( rdf_script );
        
    // on ready
    RDFAUTHOR_READY_CALLBACK = function () {
        // default graph
        RDFauthor.setInfoForGraph(RDFAUTHOR_DEFAULT_GRAPH, "queryEndpoint", urlBase+"sparql");
        RDFauthor.setInfoForGraph(RDFAUTHOR_DEFAULT_GRAPH, "updateEndpoint", urlBase+"update");
      
        // attribute endpoint
        RDFauthor.setInfoForGraph(attributeModel, "queryEndpoint", urlBase+"sparql");
        RDFauthor.setInfoForGraph(attributeModel, "updateEndpoint", urlBase+"update");
        
        // concept endpoint
        RDFauthor.setInfoForGraph(conceptModel, "queryEndpoint", urlBase+"sparql");
        RDFauthor.setInfoForGraph(conceptModel, "updateEndpoint", urlBase+"update");
        
        // dim endpoint
        RDFauthor.setInfoForGraph(dimensionModel, "queryEndpoint", urlBase+"sparql");
        RDFauthor.setInfoForGraph(dimensionModel, "updateEndpoint", urlBase+"update");
    };
    
    // check for trailing slash
    var checkSlash = function(uri){        
        if( uri.charAt(uri.length - 1) != "/" ){ 
            uri += '/';
        }
        return uri;
    }
        
    // set uribase value
    if( uribase.length < 1 && typeof salt != 'undefined' ){
        uribase = RDFAUTHOR_DEFAULT_GRAPH + '/' + salt + '/';    
    }
    $("#uribase").val(uribase);
    
    $("#uribase").keyup(function(){
        if( $(this).val().length < 3 ) return;
        var oldbase = uribase;
        uribase = $(this).val();
        
        // temp vars for new keys
        var newKey = '';
        var newEKey = '';
        
        for (d in dimensions) {
            newKey = checkSlash( d.replace(oldbase, uribase) );
            dimensions[newKey] = dimensions[d];
            
            for(e in dimensions[d]["elements"]){
                newEKey = checkSlash( e.replace(oldbase, uribase) );
                dimensions[newKey]["elements"][newEKey] = dimensions[d]["elements"][e];
                
                // delete old elements
                delete dimensions[d]["elements"][e];
            }
            
            // delete old stuff            
            delete dimensions[d];
        }
        
        dimensions.uribase = uribase;
    });
    
    /* functions */
    var _getColor = function () {
        var r = Math.round(Math.random() * (90 - 50) + 50);
        var g = Math.round(Math.random() * (90 - 50) + 50);
        var b = Math.round(Math.random() * (90 - 50) + 50);

        return 'rgb(' + r + '%,' + g + '%,' + b + '%)';
    };

    var _getURI = function (name) {
        var prefix = uribase;
        prefix = checkSlash(prefix);
        var URI = prefix + name.replace(/[^A-Za-z0-9_-]/, ''); //RDFAUTHOR_DEFAULT_GRAPH + '/' + salt + '/' + name.replace(/[^A-Za-z0-9_-]/, '');
        return URI;
    };

    /* vars */
    var currentColor;
    var currentDimension;
    var selectionMode = 'dimension';
    var datarange = {};

    $('table.csvimport td').dblclick(function(){
        if (selectionMode != 'dimension') return;

        var id = $(this).attr('id');
        var URI = _getURI(id);
        var ids = id.split('-');
        var row = ids[0].replace('r', '');
        var col = ids[1].replace('c', '');
        var selid = null;

        var select = prompt('(De)Select whole row (r) or column (c)?');
        if(select == "r" || select == "row"){
            selid = 'id*=r'+row+'-';
        }else if(select == "c" || select == "column"){
            selid = 'id$=c'+col;
        }

        $('table.csvimport td['+selid+']').each(function(){
            var id = $(this).attr('id');
            var URI = _getURI(id);
            var ids = id.split('-');
            var row = ids[0].replace('r', '');
            var col = ids[1].replace('c', '');

            if($.trim($(this).text()).length < 1) return;

            if (!$(this).hasClass('csv-highlighted')) {
                $(this).data('dimension', null);
                $(this).css('background-color', currentColor);
                $(this).addClass('csv-highlighted');

                dimensions[currentDimension]['elements'][URI] = {
                    'row': row,
                    'col': col,
                    'label': $.trim($(this).text())
                };
            } else {
                $(this).data('dimension', currentDimension);
                $(this).css('background-color', 'transparent');
                $(this).removeClass('csv-highlighted');

                // undefine
                delete dimensions[currentDimension]['elements'][URI];
            }
        });
    });

    $('table.csvimport td').click(function () {
        var id = $(this).attr('id');
        var URI = _getURI(id);
        var ids = id.split('-');
        var row = ids[0].replace('r', '');
        var col = ids[1].replace('c', '');

        if (selectionMode == 'dimension') {
            
            if ( dimensions[currentDimension].attribute ){
                // attributes stuff here
                if (!$(this).hasClass('csv-highlighted')) {
                    if(dimensions[currentDimension].selected){
                        // clear old stuff
                        $('td[about='+currentDimension+']').data('dimension', currentDimension);
                        $('td[about='+currentDimension+']').css('background-color', 'transparent');
                        $('td[about='+currentDimension+']').removeClass('csv-highlighted');
                        dimensions[currentDimension].value = '';
                        dimensions[currentDimension].selected = false;
                    }
                    
                    $(this).data('dimension', null);
                    $(this).css('background-color', currentColor);
                    $(this).addClass('csv-highlighted');
                    $(this).attr("about", currentDimension);

                    dimensions[currentDimension].row = row;
                    dimensions[currentDimension].col = col;
                    dimensions[currentDimension].value = $.trim($(this).text());
                    dimensions[currentDimension].selected = true;
                } else {
                    $(this).data('dimension', currentDimension);
                    $(this).css('background-color', 'transparent');
                    $(this).removeClass('csv-highlighted');

                    // undefine
                    dimensions[currentDimension].value = '';
                    dimensions[currentDimension].selected = false;
                }
            } else { 
                // dimensions stuff here
                if (!$(this).hasClass('csv-highlighted')) {
                    $(this).data('dimension', null);
                    $(this).css('background-color', currentColor);
                    $(this).addClass('csv-highlighted');

                    dimensions[currentDimension]['elements'][URI] = {
                        'row': row,
                        'col': col,
                        'label': $.trim($(this).text())
                    };
                } else {
                    $(this).data('dimension', currentDimension);
                    $(this).css('background-color', 'transparent');
                    $(this).removeClass('csv-highlighted');

                    // undefine
                    delete dimensions[currentDimension]['elements'][URI];
                }
            }
        } else {
            if (selectionMode == 'start') {
                datarange['start'] = {'row': row, 'col': col};
                selectionMode = 'end';
            } else if (selectionMode == 'end') {
                datarange['end'] = {'row': row, 'col': col};
                selectionMode = 'dimension';
                $('#csvimportDatarange').html(' (' +
                    datarange['start'].row + ',' +
                    datarange['start'].col + ') to (' +
                    datarange['end'].row + ',' +
                    datarange['end'].col + ')');
            }
        }
    });


    /*
     *  DIMENSIONS STUFF
     */
    $('#btn-add-dimension').click(function () {
        var name = prompt('Dimension name:');
        if ( typeof name == 'undefined' || name.length < 1) return;
        
        var eid = name.replace(" ","_");
        var dimensionInfo = {
            color: _getColor(),
            label: $.trim(name),
            elements: {}
        };
        var dimensionURI = _getURI(name);
        dimensions[dimensionURI] = dimensionInfo;
        currentDimension = dimensionURI;
        currentColor = dimensionInfo.color;
        var htmlText = '<tr style="background-color:' + currentColor + '"><td name="'+name+'">' + name; 
        htmlText += '<br/><sub>subPropertyOf:</sub><span id="dim_'+eid+'_0"></span>'+
                    '<sub>concept:</sub><span id="dim_'+eid+'_1"></span>';
        htmlText += '</td></tr>';
        var tr = $(htmlText).data('dimension', name);

        $('#csvimport-dimensions').append(tr);
        
        // property selector
        _subjectURI = "http://"+window.location.host+"/ontowiki/somegraph";
        var input0 = $("#dim_"+eid+"_0");
        selectorOptions = {
            container: input0,
            filterDomain: false,
            selectionCallback: function (uri, label) {
                $("input", input0).val(uri);
                dimensions[dimensionURI].subproperty = uri;
            }
        };
        // FIXME: title hack        
        _propertySelector = new ObjectSelector(dimensionModel, _subjectURI, "http://www.w3.org/2000/01/rdf-schema#subPropertyOf", selectorOptions);
        _propertySelector.presentInContainer();
        
        // property selector 2
        var input1 = $("#dim_"+eid+"_1");
        selectorOptions = {
            container: input1,
            filterDomain: false,
            selectionCallback: function (uri, label) {
                $("input", input1).val(uri);
                dimensions[dimensionURI].concept = uri;
            }
        };
        _propertySelector = new ObjectSelector(conceptModel, _subjectURI, "http://purl.org/linked-data/cube#concept", selectorOptions);
        _propertySelector.presentInContainer();
        
    });
    
    $('#csvimport-dimensions tr').live('click', function () {
        var name = $(this).children('td').eq(0).attr("name");

        var URI = $(this).attr("about");
        if( typeof URI === "undefined" ){
            URI = _getURI(name);
        }

        var dimInfo = dimensions[URI];        
        currentDimension = URI;        
        currentColor = dimInfo.color;        
    });

    $('#csvimport-dimensions tr').live('dblclick', function () {
        var name = $(this).children('td').eq(0).attr("name");
        var URI = _getURI(name);
        var newName = prompt('New name:', name);
        if ( typeof newName == 'undefined' || newName.length < 1) return;
        var newURI = _getURI(newName);

        var dimInfo = dimensions[URI];
        dimInfo.label = $.trim(newName);
        dimensions[newURI] = dimInfo;
        delete dimensions[URI];
        $(this).children('td').eq(0).html( $(this).children('td').eq(0).html().replace(name,newName) );
        $(this).children('td').eq(0).attr("name",newName);
    });


    /*
     * DATA RANGE 
     */
    $('#btn-datarange').live('click', function () {
        alert('Click on the upper left, then on the lower right data cell.');
        selectionMode = 'start';
    });
    
    /*
     * ATTRIBUTES STUFF 
     */
    $('#btn-attribute').live('click', function () {        
        var name = prompt('Attribute name:');
        if ( typeof name == 'undefined' || name.length < 1) return;
        
        var eid = name.replace(" ","_");
        var attributeInfo = {
            color: _getColor(),
            label: $.trim(name),
            attribute: true,
            selected: false,
            row: -1,
            col: -1,
            value: '',
            uri: ''
        };
        var attributeURI = _getURI(name);
        dimensions[attributeURI] = attributeInfo;
        currentDimension = attributeURI;
        currentColor = attributeInfo.color;
        var htmlText = '<tr style="background-color:' + currentColor + '"><td name="'+name+'">' + name; 
        htmlText += '<br/><sub>attribute:</sub><span id="attr_'+eid+'_0"></span>';
        htmlText += '</td></tr>';
        var tr = $(htmlText).data('attribute', name);

        $('#csvimport-attributes').append(tr);
        
        // property selector
        _subjectURI = "http://"+window.location.host+"/ontowiki/somegraph";
        var input0 = $("#attr_"+eid+"_0");
        selectorOptions = {
            container: input0,
            filterDomain: false,
            selectionCallback: function (uri, label) {
                $("input", input0).val(uri);
                dimensions[attributeURI].uri = uri;
            }
        };
        // FIXME: title hack        
        _propertySelector = new ObjectSelector(attributeModel, _subjectURI, "http://www.w3.org/2000/01/rdf-schema#subPropertyOf", selectorOptions);
        _propertySelector.presentInContainer();
        
        
    });
    
    $('#csvimport-attributes tr').live('click', function () {
        var name = $(this).children('td').eq(0).attr("name");

        var URI = $(this).attr("about");
        if( typeof URI === "undefined" ){
            URI = _getURI(name);
        }

        var dimInfo = dimensions[URI];        
        currentDimension = URI;        
        currentColor = dimInfo.color;        
    });

    $('#csvimport-attributes tr').live('dblclick', function () {
        var name = $(this).children('td').eq(0).attr("name");
        var URI = _getURI(name);
        var newName = prompt('New name:', name);
        if ( typeof newName == 'undefined' || newName.length < 1) return;
        var newURI = _getURI(newName);

        var dimInfo = dimensions[URI];
        dimInfo.label = $.trim(newName);
        dimensions[newURI] = dimInfo;
        delete dimensions[URI];
        $(this).children('td').eq(0).html( $(this).children('td').eq(0).html().replace(name,newName) );
        $(this).children('td').eq(0).attr("name",newName);
    });

    
    /*
     * EXTRACT BTN
     */
    $('#extract').click(function () {
        if( typeof(decodedConfig) == 'undefined' || decodedConfig === false ){
            if ($.isEmptyObject(dimensions)) {
                alert('Please select at least one dimension.');
                return false;
            };

            if ($.isEmptyObject(datarange)) {
                alert('Please select data range.');
                return false;
            }

            for (d in dimensions) {
                
                if( typeof dimensions[d].attribute != 'undefined' && dimensions[d].attribute == true ){
                    if(dimensions[d].uri.length < 1){
                        alert('One or several attributes missing URI!');
                        return;
                    }
                }else{
                    for (e in dimensions[d]['elements']) {
                        dimensions[d]['elements'][e]['items'] = datarange;
                    }
                }
            }
            
            dimensions.uribase = uribase;
        }

        dimensionString = $.toJSON(dimensions);

        var url = window.location.href + '/results';
        $.get(url, function(data){
            var div_str = '<div id="import-options" \
            style="width:400px;height:150px;padding:5px;align:center;\
            background:white;position:absolute;left:40%;top:30%;\
            border: 1px solid #900; overflow: auto;">'+
                data + '</div>';
            $('body').append( $(div_str) );
        });
    });

    theight = $(window).height() - $("#csvimport-dimensions").height() - $("#messagebox").height() - 150;
    $("#table-holder").css("height", theight);
});

function useCSVConfiguration(config) {
    // clear
    clearAll();

    // decode JSON
    decodedConfig = true;
    dimensions = $.evalJSON(config);
    
    uribase = '0';
    if( typeof dimensions["uribase"] == "undefined" || dimensions["uribase"].length < 1 ){ 
        uribase = RDFAUTHOR_DEFAULT_GRAPH + '/' + salt + '/';
    }else{
        uribase = dimensions["uribase"];
    }
    $("#uribase").val(uribase);

    for (var dim in dimensions) {
        if(dim != 'uribase'){
            if( typeof dimensions[dim].attribute != 'undefined' && dimensions[dim].attribute == true ){
                //console.log(dimensions[dim]);
                appendAttribute(dim, dimensions[dim].label, dimensions[dim].color, dimensions[dim].uri);
                drawAttribute(dimensions[dim].row, dimensions[dim].col, dimensions[dim].color)
            }else{
                appendDimension(dim, dimensions[dim].label, dimensions[dim].color, dimensions[dim].subproperty, dimensions[dim].concept);
                drawElements(dimensions[dim].elements, dimensions[dim].color);
            }
        }
    }
}


function appendDimension(dim, label, color, subproperty, concept){
    var eid = $.trim(label);
    var htmlText = '<tr style="background-color:' + color + '" about="'+dim+'"><td name="'+label+'">' + label; 
        htmlText += '<br/><sub>subPropertyOf:</sub><span id="dim_'+eid+'_0"></span>';
        htmlText += '<sub>concept:</sub><span id="dim_'+eid+'_1"></span>';
        htmlText += '</td></tr>';
    
    var tr = $(htmlText).data('dimension', label);
    $('#csvimport-dimensions').append(tr);
    
    var dimensionURI = dim;
    // property selector
    _subjectURI = "http://"+window.location.host+"/ontowiki/somegraph";
    var input0 = $("#dim_"+eid+"_0");
    selectorOptions = {
        container: input0,
        filterDomain: false,
        selectionCallback: function (uri, label) {
            $("input", input0).val(uri);
            dimensions[dimensionURI].subproperty = uri;
        }
    };
    // FIXME: title hack        
    _propertySelector = new ObjectSelector(dimensionModel, _subjectURI, "http://www.w3.org/2000/01/rdf-schema#subPropertyOf", selectorOptions);
    _propertySelector.presentInContainer();
    
    $("input", input0).val(subproperty);
    
    // property selector 2
    var input1 = $("#dim_"+eid+"_1");
    selectorOptions = {
        container: input1,
        filterDomain: false,
        selectionCallback: function (uri, label) {
            $("input", input1).val(uri);
            dimensions[dimensionURI].concept = uri;
        }
    };
    _propertySelector = new ObjectSelector(conceptModel, _subjectURI, "http://purl.org/linked-data/cube#concept", selectorOptions);
    _propertySelector.presentInContainer();
    
    $("input", input1).val(concept);
}

function appendAttribute(dim, label, color, uri){
    var eid = $.trim(label);
    var htmlText = '<tr style="background-color:' + color + '" about="'+dim+'"><td name="'+label+'">' + label;
        htmlText += '<br/><sub>attribute:</sub><span id="attr_'+eid+'_0"></span>';
        htmlText += '</td></tr>';
    
    var tr = $(htmlText).data('dimension', label);
    $('#csvimport-attributes').append(tr);
    
    // property selector
    var attributeURI = dim;
    _subjectURI = "http://"+window.location.host+"/ontowiki/somegraph";
    var input0 = $("#attr_"+eid+"_0");
    selectorOptions = {
        container: input0,
        filterDomain: false,
        selectionCallback: function (uri, label) {
            $("input", input0).val(uri);
            dimensions[attributeURI].uri = uri;
        }
    };
    // FIXME: title hack
    _propertySelector = new ObjectSelector(attributeModel, _subjectURI, "http://www.w3.org/2000/01/rdf-schema#subPropertyOf", selectorOptions);
    _propertySelector.presentInContainer();
    
    $("input", input0).val(uri);
}

function drawAttribute(row, col, color){
    var id = "#r"+row+"-c"+col;

    $(id).data('dimension', null);
    $(id).css('background-color', color);
    $(id).addClass('csv-highlighted');
}

function drawElements(elements, color){
    for(var elem in elements){
        var id = "#r"+elements[elem].row+"-c"+elements[elem].col;

        $(id).data('dimension', null);
        $(id).css('background-color', color);
        $(id).addClass('csv-highlighted');

        setDatarange(elements[elem].items);
    }
}

function setDatarange(range){
    datarange = range;
    $('#csvimportDatarange').html(' (' +
                    datarange['start'].row + ',' +
                    datarange['start'].col + ') to (' +
                    datarange['end'].row + ',' +
                    datarange['end'].col + ')');
}

function clearAll(){
    $('#csvimport-dimensions').children().remove();
    $('table.csvimport td').each(function(){
        if ($(this).hasClass('csv-highlighted')){
            $(this).css('background-color', 'transparent');
            $(this).removeClass('csv-highlighted');
        }
    })
}
