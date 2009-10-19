
var to_open = "nothing";
function select_plugin(id){
	s_id = id+"_s";
	var s_plugin = document.getElementById(s_id);
	var u_plugin = document.getElementById(id);
	s_plugin.style.display ="block";
	u_plugin.style.display ="none";
	if ( typeof to_open == 'undefined' ){
		alert('nothing opend');
	}
	else if (to_open == "nothing"){
		to_open = id;
	}
	else {
		to_close = to_open + "_s";
		var to_open_plugin = document.getElementById(to_open);
		var to_close_plugin = document.getElementById(to_close);
		to_open_plugin.style.display = "block";
		to_close_plugin.style.display = "none";
		to_open = id;
	}
}

