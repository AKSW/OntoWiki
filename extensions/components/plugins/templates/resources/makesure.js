
function to_uninstall(uninstall_p){
	var plugin_name = uninstall_p;
	var install_abort = document.getElementById('id_install_abort');
	if(confirm("Are you really want to uninstall plugin:" + plugin_name + "?")){
		alert('Ready to uninstall!');
		return true;
	}
	else {
		alert('Uninstall abort!');
		alert('Back to installed-page');
		window.navigate("plugins/installed");
	}
}

function ask_for_uninstall(uninstall_p,touninstall_url, back_url){
	var plugin_name = uninstall_p;
	var uninstall = touninstall_url;
	var back = back_url;
	var uninstall_form = document.getElementById(plugin_name);

	if (confirm("Are you really want to uninstall plugin:" + plugin_name + "?")){
		alert('Ready to uninstall!');
		uninstall_form.action = uninstall;
	}
	else {
		alert('Uninstall abort!');
		uninstall_form.action = back;
	}
	return true;
}
	
