// find installation path
var base_url = document.location.href;
base_url  = base_url.substr(0, base_url.search("index.php"));

// load plugins 
$.get(base_url+"/index.php/plugins/pluglist", function(data){
  $("#pluginsContainer").html(data);
});
// load news
$.get(base_url+"/index.php/index/newsshort", function(data){
  $("#newsContainer").html(data);
});