/*
 * Patternmanager View Javascript
 *
 * @package    
 * @author     Christoph Rieß <c.riess.dev@googlemail.com>
 * @copyright  Copyright (c) 2010, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id$
 */

function copyVariables(selfId, patternId) {
	if (patternId == null) {
		
	} else {
		
	}
}

function addPMPattern(name, desc) {
    
    pattern = $('div#subpattern').clone();
    count = $('div#patternmanager > div').size();
    
    html = '<div id="subpattern-' + count +'">' + pattern.html() + '</div>';
    
    $('div#patternmanager').append(html);
    
    $('div#subpattern-' + count + ' input#patternlabel-').val(name);
    $('div#subpattern-' + count + ' input#patterndesc-').val(desc);
    
    reindexPM();
    
}

function delPMPattern(id) {
    
    $('div#patternmanager > div#subpattern-'+id).remove();
    
    reindexPM();
    
}

function hidePMPattern(id) {
    
    $('div#patternmanager > div#subpattern-'+id + '> table').hide();
    $('div#patternmanager > div#subpattern-'+id + '> div:has(>input)').hide();
    
}

function showPMPattern(id) {
    
    $('div#patternmanager > div#subpattern-'+id + '> table').show();
    $('div#patternmanager > div#subpattern-'+id + '> div:has(>input)').show();
    
}

function addPMvar(p,unused,varname,vartype,vardesc) {
    
    input = $('div#subpattern > table').eq(0).find('tbody > tr').clone();
    varcount = $('div#subpattern-' + p + ' > table').eq(0).find('tbody > tr').size();
    
    table = $('div#subpattern-' + p + ' > table:eq(0) > tbody');
    table.append('<tr>' + input.html() + '</tr>');
    table.find('tr:eq(' + varcount + ') input:eq(0)').val(varname);
    table.find('tr:eq(' + varcount + ') input:eq(1)').val(vardesc);
    table.find('tr:eq(' + varcount + ') select > option').each( function (i) {
        if ($(this).text() == vartype) {
            $(this).attr('selected','true');
            return;
        } else {
            // do nothing
        }
    });
    
    reindexPM();
    
}

function delPMvar(p,c) {
    
    $('div#subpattern-' + p + ' > table').eq(0).find('tbody > tr').eq(c - 1).remove();
    
    reindexPM();
    
}

function addPMselect(p, c, select) {
    
    input = $('div#subpattern > table').eq(1).find('tbody > tr').clone();
    varcount = $('div#subpattern-' + p + ' > table').eq(1).find('tbody > tr').size();
    
    table = $('div#subpattern-' + p + ' > table:eq(1) > tbody');
    table.append('<tr>' + input.html() + '</tr>');
    table.find('tr:eq(' + varcount + ') input').val(select);
    $('div#subpattern-' + p + ' > table:eq(1) > thead a').hide();
    
    reindexPM();
    
}

function delPMselect(p,c) {
    
    $('div#subpattern-' + p + ' > table').eq(1).find('tbody > tr').eq(c - 1).remove();
    $('div#subpattern-' + p + ' > table:eq(1) > thead a').show();
    
    reindexPM();
}

function addPMinsert(p, c, insert) {
    
    input = $('div#subpattern > table').eq(2).find('tbody > tr').clone();
    varcount = $('div#subpattern-' + p + ' > table').eq(2).find('tbody > tr').size();
    
    table = $('div#subpattern-' + p + ' > table:eq(2) > tbody');
    table.append('<tr>' + input.html() + '</tr>');
    table.find('tr:eq(' + varcount + ') input').val(insert);
    
    reindexPM();
    
}

function delPMinsert(p,c) {
    
    $('div#subpattern-' + p + ' > table').eq(2).find('tbody > tr').eq(c - 1).remove();
    
    reindexPM();
}

function addPMdelete(p, c, del) {
    
    input = $('div#subpattern > table').eq(3).find('tbody > tr').clone();
    varcount = $('div#subpattern-' + p + ' > table').eq(3).find('tbody > tr').size();
    
    table = $('div#subpattern-' + p + ' > table:eq(3) > tbody');
    table.append('<tr>' + input.html() + '</tr>');
    table.find('tr:eq(' + varcount + ') input').val(del);    
    
    reindexPM();
    
}

function delPMdelete(p,c) {
    
    $('div#subpattern-' + p + ' > table').eq(3).find('tbody > tr').eq(c - 1).remove();
    
    reindexPM();
}

function loadPMpattern(uri,id) {
    
    $.getJSON(urlBase + 'patternmanager/loadpattern', { uri: uri , type: 'rdf'}, function(data) {
        current = data;

        $('div#subpattern-' + id + ' input#patternlabel-' + id).val(data.label);
        $('div#subpattern-' + id + ' input#patterndesc-' + id).val(data.desc);
        for (j in current['V']) {
            addPMvar(id,null,current['V'][j]['name'],current['V'][j]['type'],current['V'][j]['desc']);
        }
        
        if (current['S'].length > 0) {
            addPMselect(id,null,current['S']);
        } else {
            delPMselect(id,null);
        }
        for (pkey in current['U']) {
            if (current['U'][pkey]['type'] === 'insert' ) {
                addPMinsert(id, null, current['U'][pkey]['pattern']);
            } else if ( current['U'][pkey]['type'] === 'delete' ) {
                addPMdelete(id, null, current['U'][pkey]['pattern']);
            } else {
                // do nothing
            }
        }
    });
}

function reindexPM() {

    $('div#patternmanager input.BasicPattern').each( function (i) {
        node = $(this);
        if (i != 0 && !node.hasClass('ac_input')) {
            node.autocomplete(
                urlBase + 'patternmanager/autocomplete',
                {
                    loadingClass : 'is-processing',
                    minChars: 3 ,
                    delay: 1000 ,
                    max: 10 ,
                    extraParams: {
                        mode : 'view', vartype : 'BasicPattern' 
                    },
                    formatItem: function(row,pos,max,str) {
                        data = $.secureEvalJSON(row[0]);
                        return data[1];
                    },
                    formatResult: function(row,pos,max) {
                        data = $.secureEvalJSON(row[0]);
                        return data[1];
                    }
                }
            );
            node.result( function(event, item) {
                data = $.secureEvalJSON(item[0]);
                bp = $('div#patternmanager > div#subpattern-' + i);
                //
                if ( confirm('Load this pattern? (will overwrite current)') ) {
                    bp.find('table:eq(0) > tbody > tr').remove();
                    bp.find('table:eq(1) > tbody > tr').remove();
                    bp.find('table:eq(2) > tbody > tr').remove();
                    bp.find('table:eq(3) > tbody > tr').remove();
                    loadPMpattern(data[0],i);
                } else {
                    // do nothing
                }
            });
        }
    });
    
    $('div#patternmanager > div').each( function (i) {
        
        if (i != 0) {
            
            currentId = $(this).attr('id');
            $(this).attr('id',currentId.substr(0,currentId.indexOf('-')) + '-' + i);
            
            $(this).children('a').each( function (k) {
                currentHref = $(this).attr('href');
                $(this).attr('href', currentHref.substr(0,currentHref.indexOf('(')) + '(' + i + ');' );
            });
            
            // (re)index input fields for pattern label and pattern description
            $(this).find('div > input').each( function () {
                currentName = $(this).attr('name');
                currentName = currentName.substr(0, currentName.indexOf('-')) + '-' + i;
                $(this).attr('name', currentName);
                currentId   = $(this).attr('id');
                currentId   = currentId.substr(0, currentId.indexOf('-')) + '-' + i;
                $(this).attr('id',currentId);
            });
            
            $(this).children('table').each( function (l) {
                $(this).find('tr').each( function (m) {
                    
                    $(this).find('[name]').each( function () {
                        currentName = $(this).attr('name');
                        $(this).attr('name', currentName.substr(0,currentName.indexOf('-')) + '-' + i + '-' + m);
                    });
                    
                    $(this).find('[id]').each( function () {
                        currentId = $(this).attr('id');
                        $(this).attr('id', currentId.substr(0,currentName.indexOf('-')) + '-' + i + '-' + m);
                    });

                    $(this).find('[href]').each( function() {
                        currentHref = $(this).attr('href');
                        currentHref = currentHref.substr(0,currentHref.indexOf('(')) + '(' + i + ', ' + m + ');';
                        $(this).attr('href', currentHref);
                    });
                    
                });
            });
        
        }
    
    });
    
}