//
// This file is part of the RDFauthor Widget Library
//
// Loading Helper Script
// 
// Copyright (c) 2008 Norman Heino <norman.heino@gmail.com>
//

var widgetBase = widgetBase || 'http://localhost/ontowiki/libraries/RDFauthor/';

(function() {
    window.onerror = function (msg, url, line) {
        alert(line);
    };
    
    var loadScripts = function(urls, callback) {
        // make sure array
        if (urls.constructor !== Array) {
            urls = [urls];
        }
        
        var max = urls.length;
        for (var i = 0; i < max; i++) {
            var s = document.createElement('script');
            s.type = 'text/javascript';
            s.src = urls[i];

            if ((i === (max - 1)) && (typeof callback == 'function')) {
                if (s.onreadystatechange) {
                    s.onreadystatechange = function () {
                        if (this.readyState === 'loaded' || this.readyState === 'complete') {
                            callback();
                        }
                    };
                } else {
                    // works: Safari, Chrome, Firefox
                    s.onload = callback;
                }
            }

            document.getElementsByTagName('head')[0].appendChild(s);
        }
    };
    
    // RDFauthor
    var loadRdfAuthor = function() {
        if (typeof jQuery != 'undefined' && typeof jQuery.toJSON != 'undefined') {
            loadScripts(widgetBase + 'src/rdfauthor.js');
        }
    }
    
    // jQuery toJSON
    var loadjQueryJson = function() {
        if (typeof jQuery != 'undefined' && typeof jQuery.toJSON == 'undefined') {
            loadScripts(widgetBase + 'libraries/jquery.json.js', loadRdfAuthor);
        }
    }
    
    // jQuery UI
    var loadjQueryUi = function() {
        if (typeof jQuery != 'undefined' && typeof jQuery.UI == 'undefined') {
            loadScripts(widgetBase + 'libraries/jquery-ui.js', loadjQueryJson);
        }
    }
    
    // jQuery
    var loadjQuery = function() {
        if (typeof jQuery == 'undefined') {
            loadScripts(widgetBase + 'libraries/jquery.min.js', loadjQueryUi);
        }
    }
    
    loadjQuery();
    loadjQueryUi();
    loadjQueryJson();
    loadRdfAuthor();
})();