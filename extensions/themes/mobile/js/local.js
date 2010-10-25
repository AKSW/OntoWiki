/*
 * Create all local db stuff
 */
var connectionStatus = 'online';
 
$(document).ready(function(){
    // prepares cache event handlers
    var prepareCacheEvents = function(){
        // local cache 
        var cache = window.applicationCache;
        
        // cache event
        cache.addEventListener("cached", function () {
            console.log("All resources for this web app have now been downloaded. You can run this application while not connected to the internet");
        }, false);
        cache.addEventListener("checking", function () {
            console.log("Checking manifest");
        }, false);
        cache.addEventListener("downloading", function () {
            console.log("Starting download of cached files");
        }, false);
        cache.addEventListener("error", function (e) {
            console.log("There was an error in the manifest, downloading cached files or you're offline: " + e);
        }, false);
        cache.addEventListener("noupdate", function () {
            console.log("There was no update needed");
        }, false);
        cache.addEventListener("progress", function () {
            console.log("Downloading cached files");
        }, false);
        cache.addEventListener("updateready", function () {
            cache.swapCache();
            console.log("Updated cache is ready");
            // Even after swapping the cache the currently loaded page won't use it
            // until it is reloaded, so force a reload so it is current.
            window.location.reload(true);
            console.log("Window reloaded");
        }, false);
    };
    
    // prepares connection event handlers
    var prepareConnectionEvents = function(){
        // connection status
        $(document.body).bind("online", checkNetworkStatus);
        $(document.body).bind("offline", checkNetworkStatus);
    }
    
    // status check
    var checkNetworkStatus = function(){
         if (navigator.onLine) {
            // Just because the browser says we're online doesn't mean we're online. The browser lies.
            // Check to see if we are really online by making a call for a static JSON resource on
            // the originating Web site. If we can get to it, we're online. If not, assume we're
            // offline.
            // TODO: More checks here
            connectionStatus = 'online';
        } else {
            connectionStatus = 'offline';
            knowledgeBaseList.loadList();
        }
        console.log(connectionStatus);
    };
    
    /* 
     * 
     * Knowledge base list function 
     * 
     */
    var knowledgeBaseList = {};
    // save list to local DB
    knowledgeBaseList.saveList = function(){
        // get elements
        var container = $("#modellist");
        var list = $("ul", container);
        // create list 
        var kbList = [];
        
        $("li", list).each(function(index,item){
            var a = $("a", item);
            var text = $.trim(a.text());
            var alink = $.trim(a.attr("about"));
            
            kbList.push({text: text, alink:alink});
            
            localStorage["ontowiki.bases"] = kbList;
        });
    };
    // load stuff
    knowledgeBaseList.loadList = function(){
        var kbList = localStorage["ontowiki.bases"];
        console.log(kbList);
        
        if(typeof kbList != 'undefined' && kbList != null){
            for(var i = 0; i < kbList.length; i++){
                console.log(kbList[i].text + ' - ' + kbList[i].alink);
            }
        }else{
            console.log('no kbList saved');
        }
    };
    // clear kb
    knowledgeBaseList.clearList = function(){
        localStorage["ontowiki.bases"] = null;
    }
    
    /*
     * 
     * Navigation entries
     *
     */
    var navigationEntriesList = {};
    navigationEntriesList.saveEntry = function(entry){
        
    }
    
    // prepare all
    prepareCacheEvents();
    prepareConnectionEvents();
    
    // on load save new knowledge base list
    if( navigator.onLine ){
        knowledgeBaseList.saveList();
    }
});








