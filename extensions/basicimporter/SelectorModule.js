/*global document,$,alert,console */
/*jslint browser: true, vars: true, plusplus: true */
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2013, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki module - selector module script compontent
 *
 * @category OntoWiki
 * @package  OntoWiki_Extensions_basicimporter
 * @author   Sebastian Tramp <mail@sebastian.tramp.name>
 */

$(document).ready(function () {
    'use strict';

    /* defined entry points */
    var lovSearchInput = $("#lov-search-input"),
        lovSearchResults = $(".lov-search-result");

    /*
     * convert xml (esp. sparql xml resultset) to json objects
     * http://stackoverflow.com/questions/7769829/
     * */
    var xmlToJson = function (xml) {
        var obj = {};
        if (xml.nodeType === 1) {
            if (xml.attributes.length > 0) {
                var j = 0;
                obj["@attributes"] = {};
                for (j = 0; j < xml.attributes.length; j++) {
                    var attribute = xml.attributes.item(j);
                    obj["@attributes"][attribute.nodeName] = attribute.nodeValue;
                }
            }
        } else if (xml.nodeType === 3) {
            obj = xml.nodeValue;
        }
        if (xml.hasChildNodes()) {
            var i = 0;
            for (i = 0; i < xml.childNodes.length; i++) {
                var item = xml.childNodes.item(i);
                var nodeName = item.nodeName;
                if (typeof (obj[nodeName]) === "undefined") {
                    obj[nodeName] = xmlToJson(item);
                } else {
                    if (typeof (obj[nodeName].push) === "undefined") {
                        var old = obj[nodeName];
                        obj[nodeName] = [];
                        obj[nodeName].push(old);
                    }
                    obj[nodeName].push(xmlToJson(item));
                }
            }
        }
        return obj;
    };

    var flushSchemaTable = function (data, textStatus, jqXHR, callback) {
        var xmlDocument = xmlToJson(data),
            results = xmlDocument.sparql.results.result,
            i = 0;

        if ((typeof results !== 'undefined') && (results.length > 0)) {
            var prefix, namespace, title;
            for (i = 0; i < results.length; i++) {
                prefix    = results[i].binding[0].literal['#text'];
                namespace = results[i].binding[1].uri['#text'];
                title     = results[i].binding[2].literal['#text'];

                // create list
                // <li><a class="even lov-search-result" data-prefix="skos" data-namespace="http://www.w3.org/2004/02/skos/core#">SKOS Vocabulary</a></li>
                $('#lov-search-output').append(
                    '<li> ' +
                        '<a class="even lov-search-result" ' +
                        'data-prefix="' + prefix + '" ' +
                        'data-namespace="' + namespace + '">' +
                        title +
                        '</a>' +
                        '</li>'
                );
            }
        } else {
            $('#lov-search-output').append('<li>Sorry, nothing found</li>');
        }
        $('#lov-search-input').removeClass('is-processing');
        $('#lov-search-output').slideDown();
    };

    /* query the LOV endpoint with SPARQL and provide a callback */
    var lovQuery = function (query, callback) {
        console.log(query);
        var parameters = {
            'service-uri': "http://lov.okfn.org/endpoint/lov",
            query: query
        };
        var ajaxOptions = {
            type: 'GET',
            timeout: 2000,
            url: 'http://cstadler.aksw.org/services/sparql-proxy.php',
            data: parameters,
            async: true,
            headers: {
                'Accept': 'application/sparql-results+xml'
            },
            success: flushSchemaTable
        };
        $('#lov-search-input').addClass('is-processing');
        $('#lov-search-output').hide().empty();
        $.ajax(ajaxOptions);
    };

    /*
     * here start the livequery assignments
     */

    // click to add the data to other input fields
    lovSearchResults.livequery('click', function (event) {
        // query for data
        var prefix    = $(event.target).data('prefix'),
            namespace = $(event.target).data('namespace'),
            title     = $(event.target).text();

        // do nothing on incomplete data
        if ((namespace !== undefined) && (prefix !== undefined)) {
            // fill and submit the modelconfig form if present
            var formModelConfig = $("form[name|='modelconfig']");
            if (formModelConfig.length === 1) {
                formModelConfig.find("input[name|='new_prefix_prefix']").attr("value", prefix);
                formModelConfig.find("input[name|='new_prefix_namespace']").attr("value", namespace);
                formModelConfig.find("a.submit").trigger('click');
            }
            // fill and submit the createmodel form if present
            var formCreateModel = $("form[name|='createmodel']");
            if (formCreateModel.length === 1) {
                formCreateModel.find("input[name|='title']").attr("value", title);
                formCreateModel.find("input[name|='modeluri']").attr("value", namespace);
                formCreateModel.find("input[name|='importOptions']").attr("value", 'walkthrough');
                $("#import-basicimporter-rdfweb").attr('checked', 'checked');
                formCreateModel.find("a.submit").trigger('click');
            }
            // fill and submit the Import from the web form if present
            if ($("#importdata").length === 1) {
                if ($("#location-input").length === 1) {
                    $("#location-input").attr("value", namespace);
                    $("form[name|='importdata'] a.submit").trigger('click');
                }
            }

        }
    });

    // do not search until user pressed enter
    lovSearchInput.livequery('keypress', function (event) {
        if ((event.which === 13) && (event.currentTarget.value !== '')) {
            // do search here
            var queryString = $(event.currentTarget).val();
            $(event.currentTarget).val('');
            lovQuery(
                'PREFIX vann:<http://purl.org/vocab/vann/> ' +
                    'PREFIX voaf:<http://purl.org/vocommons/voaf#> ' +
                    'PREFIX dcterms:<http://purl.org/dc/terms/> ' +
                    'SELECT DISTINCT ?prefix ?namespace ?title ' +
                    'WHERE{ ' +
                    '    ?namespace a voaf:Vocabulary . ' +
                    '    ?namespace dcterms:title ?title . ' +
                    '    ?namespace vann:preferredNamespacePrefix ?prefix . ' +
                    '    ?namespace ?property ?value . ' +
                    '    FILTER regex(?value, ".*' + queryString + '.*", "i") }',
                flushSchemaTable
            );
            return false;
        }
        if (event.which === 13) {
            return false;
        }
        return true;
    });

});
