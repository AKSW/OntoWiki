/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

isTabular = true;

RDFAUTHOR_READY_CALLBACK = function () {
    RDFauthor.setInfoForGraph(graphURI, "queryEndpoint", urlBase+"sparql");
    RDFauthor.setInfoForGraph(graphURI, "updateEndpoint", urlBase+"update");

    // create property selector
    _subjectURI = "http://"+window.location.host+"/ontowiki/somegraph";

    $(".import-cell").each(function(index, element){
        selectorOptions = {
            container: $(element),
            filterDomain: false,
            selectionCallback: function (uri, label) {
                $("input",element).val(uri);
            }
        };
        _propertySelector = new Selector(graphURI, _subjectURI, selectorOptions);
        _propertySelector.presentInContainer();
    })
};

var rdf_script = document.createElement( 'script' );
rdf_script.type = 'text/javascript';
rdf_script.src = RDFAUTHOR_BASE+"src/rdfauthor.js";
$('body').append( rdf_script );

$(document).ready(function () {
    $('#extract').click(function () {
        var dimensions = [];
        var dim, id;
        $("input[id^='resource-input']").each(function(i){
            // get id
            id = $(this).parents("td").attr("about");
            // create object
            dim = {};
            dim.property = $(this).val();
            dim.contains_id = $("."+id+"[name=ResourceIdentifier]").is(":checked");
            dim.is_url = $("."+id+"[name=ObjectIsUri]").is(":checked");
            dim.contains_prefix = $("."+id+"[name=ObjectUriContainPrefix]").is(":checked");
            
            dimensions[i] = dim;
        });
        var fragm = $("input[name=uriFragment]").val();
        var dimensionString = $.toJSON({fragment: fragm,dimensions: dimensions});

        $.post(actionURL, {dimensions: dimensionString}, function (data) {
            //console.log(data);
            alert('Success');
        });
    });
});







