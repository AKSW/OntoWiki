/**
 * This file is part of the tagging extension for OntoWiki
 *
 * @author     Atanas Alexandrov <sirakov@gmail.com>
 * @author     Sebastian Dietzold <dietzold@informatik.uni-leipzig.de>
 * @copyright  Copyright (c) 2009, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id: $
 *
 */
function reloadExploreTagsModule(param){
    taglistContainer.addClass("is-processing");
    var checkedparam =  (typeof param != 'undefined') ? param : "";
    taglistContainer.load(
        urlBase+"tagging/explore" + checkedparam,
        function() {
            taglistContainer.removeClass("is-processing");
        }
    );
}

/**
 * The main document.ready assignments and code
 */
$(document).ready(function() {
    taglistContainer = $('#exploretags-content');
    taggedResource = taglistContainer.attr('about');
    taggingInput = $("#exploretags-content");

    taglistContainer.droppable({
        accept: '.show-property',
        scope: 'Resource',
        activeClass: 'ui-droppable-accepted-destination',
        hoverClass: 'ui-droppable-hovered',
        drop:
        function(event, ui) {
            var data = {
                "uri" : $(ui.draggable).attr("about"),
                "label" : $(ui.draggable).attr('title'),
                "isInverse" : $(ui.draggable).hasClass("InverseProperty")
            }

            var getUri = urlBase+"service/session?method=setArrayValue&name=cloudproperties&valueIsSerialized=true";
            $.post(getUri,
             {
                 "key": $(ui.draggable).attr("about"),
                 "value" : $.toJSON(data)
             }, //as post because of possible size
            function(res) {
                if(res==""){
                    reloadExploreTagsModule();
                } else alert('could not add cloudproperty\nReason:\n'+res)
            })
        }});

    taggingUrl = urlBase + 'tagging/';
    taggingAction('exploretags'); // make ajax request after pageload
    
    $('.cloudvalue').live('click', function(event) {
        toggleTag(this);
    });

    $('.delete-cloudproperty').live('click', function(event) {
        $.post(urlBase+"service/session?name=cloudproperties&method=unsetArrayKey",
            {"key" : $(this).attr("about")},
            function(res){
                if(res==""){
                    reloadExploreTagsModule();
                } else alert('could not remove cloudproperty\nReason:\n'+res)
            }
        );
    });
    
    // at the moment, we refresh the complete page, so we do not need callbacks here
    //filter.addCallback(function(filter){ selectTags('exploretags') })
});

/**
 * set selected tags
 */
function toggleTag(tag) {
    var tagvalue = $(tag).attr('value');
    var inverse = $(tag).parent().parent().hasClass("InverseProperty");
    var proplabel = $(tag).parent().parent().attr('title');
    var type = $(tag).attr('type');
    var datatype = $(tag).attr('datatype');
    var language = $(tag).attr('language');
    if(type=="typed-literal"){
        var literaltype = datatype;
    } else if(language!=""){
        var literaltype = language;
    }

    //propertyuri = 'http://www.holygoat.co.uk/owl/redwood/0.1/tags/taggedWithTag';
    propertyuri = $(tag).attr('cloudproperty');
    
    filterid = 'explore-'+(inverse ? "inverse" : "normal")+'-'+tagvalue+'-'+propertyuri;
    //alert(filter.filters[filterid]);
    if(!filter.filterExists(filterid)) {
        // id, property, isInverse, propertyLabel, filter, value1, value2, valuetype, literaltype, callback, hidden
        $(tag).addClass("selected");
        filter.add(
            filterid,
            propertyuri,
            inverse,
            proplabel,
            'equals',
            tagvalue,
            null,
            type,
            literaltype,
            function(){},
            false);
    } else {
        filter.remove(
            filterid,
            function(){$(tag).removeClass("selected");}
        );
    }

}

/**
 * set selected tags
 */
function selectTags(type) {
    // first we set the processing status
    taggingInput.addClass('is-processing');

    var allSelectedTags = "";
    var i = 0;
	
	$('.select').each(function(){
        allSelectedTags += "\""+i+"\" : \""+$(this).attr('about')+"\" , ";
        i++;
    });	
	
    // remove the last comma and build the
    // complete selectedTags parameter
    allSelectedTags = "{"+allSelectedTags.substring(0,allSelectedTags.lastIndexOf(',')-1)+"}";
    params = {selectedTags: allSelectedTags};

    /* Debug
    if (window.console){
        window.console.log(allSelectedTags);
    }
    */
	
    $.post(taggingUrl + type, params,
        function (data) {
            taglistContainer.empty();
            taglistContainer.append(data);
            // remove the processing status
            taggingInput.removeClass('is-processing');
        }
    );
    
	return true;
}


/**
 * request a tagging action
 */
function taggingAction(type, typeparam) {	
    // first we set the processing status
    taggingInput.addClass('is-processing');
	
    if (type == 'exploretags') {
        params = { resources: taggedResource };
    }
    // reset selectedTags - not working
	else if (type == 'exploretags' && typeparam == 'reset') {		
		$('.select').each(function(){
            $(this).toggleClass("select");
        });
        params = {resources: taggedResource, selectedTags: '' };
	}
    // sort by name - not working
    else if (type == 'exploretags' && typeparam == 'name') {
        params = {sort : "name" };
    }
    // sort by frequency - not working
	else if (type == 'exploretags' && typeparam == 'count') {
        params = {sort : "count" };
	}
    // show specific number of tags in the tagcloud - not working
    else if (type == 'exploretags' && typeparam > 4) {
		params = {count: typeparam };
	} else {
        taggingInput.removeClass('is-processing');
        return false;
    }

    //TODO fixme
    if(type =="exploretags"){
        type ="explore";
    }

    $.post(taggingUrl + type, params,
        function (data) {
            taglistContainer.empty();
            taglistContainer.append(data);
            // remove the processing status
            taggingInput.removeClass('is-processing');
        }
    );

    return true;
}

/**
 * Deselect tags
 */
function resetSelectedTags() {
    for(key in filter.filters){
        if(key.substr(0, 7) == "explore"){
            filter.remove(key);
        } else alert(key);
    }
    //$('.cloudvalue').removeClass("selected");

    reloadExploreTagsModule();
}

function resetExploreTags() {
    $.post(urlBase+"service/session?name=cloudproperties&method=unsetArray",
        function(res){
            if(res==""){
                reloadExploreTagsModule();
            } else alert('could not remove cloudproperties\nReason:\n'+res)
        }
    );
}

// show number of tags
function count(number) {
    reloadExploreTagsModule("?count=" + number);
}

// sort tagcloud
function sortTagCloud(sortparameter) {
    if (sortparameter == 1) {
        param = "?sort=name" ;
    } else if (sortparameter == 2) {
        param =  "?sort=count" ;
    }
    reloadExploreTagsModule(param);
}