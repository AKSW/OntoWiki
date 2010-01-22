var isModelActive = false;

$(document).ready(function () {
	tabTo();
	$('#ei_add').click(eiAdd);
	$('#ei_delete').click(eiDelete);
	$('#ei_generate').click(eiGenerate);
	$('#ei_activate').click(eiActivate);
	
	$('#status_add').click(getMessage);
	$('#status_delete').click(getMessage);
	$('#status_generate').click(getMessage);
	$('#status_activate').click(getMessage);
	
	getAllRules();
    getActiveState();
});

function getAllRules() {
	$.getJSON(eiRequestUrl.replace(/__action__/, 'getallrules'), {},
			function(data) {
				if (data.success == 1) {
					$('#ei_addRulesList').empty();
					$('#ei_deleteRulesList').empty();
					if(data.addRules.length != 0) {
						$.each(data.addRules, function(rule, ruleName) {
							$('<option>'+ ruleName +'</option>').attr('value', rule).appendTo('#ei_addRulesList');
						});		
					}
					else
						$('<option>(none)</option>').appendTo('#ei_addRulesList');
					
					if(data.deleteRules.length != 0) {
						$.each(data.deleteRules, function(rule, ruleName) {
							$('<option>'+ ruleName +'</option>').attr('value', rule).appendTo('#ei_deleteRulesList');
						});					
					}
					else
						$('<option>(none)</option>').appendTo('#ei_deleteRulesList');

				}
				else 
					alert("Es ist ein Fehler beim Laden der Regeln aufgetreten.");
			},
			'json');
		return false;
}

function getActiveState() {
	$.getJSON(eiRequestUrl.replace(/__action__/, 'getactivestate'), {},
			function(data) {
				if (data.active == false) {
					$('#ei_activate').text('Activate');		
				} else {
					$('#ei_activate').text('Deactivate');
                }
                isModelActive = data.active;
			},
			'json');
		return false;
}

function eiAdd() {
	if($('#status_add').hasClass('is-processing') == false) {
		setStatus('is-processing', 'add', 'small');
		setMessage('Processing...');
		$.post(eiRequestUrl.replace(/__action__/, 'add'),
			{ rule : $("#ei_addRulesList").val(), with_inferences : $('#ei_add_with_inferences:checked').length },
			function(data) {
				if (data.success == true) {
					setStatus('success', 'add', 'small');
					getAllRules();
				}
				else if (data.success == 'warning')
					setStatus('warning', 'add', 'small');
				else
					setStatus('error', 'add', 'small');
				
				setMessage(data.msg);
			},
			'json');
		return false;		
	}
}

function eiDelete() {
	if($('#status_delete').hasClass('is-processing') == false) {
		setStatus('is-processing', 'delete', 'small');
		setMessage('Processing...');
		$.post(eiRequestUrl.replace(/__action__/, 'delete'),
			{ rule : $("#ei_deleteRulesList").val(), delete_directly : $('#ei_delete_directly:checked').length },
			function(data) {
				if (data.success) {
					setStatus('success', 'delete', 'small');
					getAllRules();
				}
				else if (data.success == 'warning')
					setStatus('warning', 'delete', 'small');
				else
					setStatus('error', 'delete', 'small');
				
				setMessage(data.msg);
			}, 
			'json');
		return false;		
	}
	
}

function eiGenerate() {
	if($('#status_generate').hasClass('is-processing') == false) {
		setStatus('is-processing', 'generate');
		setMessage('Processing...');
		$.post(eiRequestUrl.replace(/__action__/, 'generate'),
			{ /*rule : $("#ei_ruleList").val()*/ },
			function(data) {
				if (data.success)
					setStatus('success', 'generate');
				else if (data.success == 'warning')
					setStatus('warning', 'generate', 'small');
				else 
					setStatus('error', 'generate');

				setMessage(data.msg);
			},
			'json');
		return false;
	}
}

function eiActivate() {
        $.post(eiRequestUrl.replace(/__action__/, 'activate'),
			{ activate : !isModelActive },
			function(data) {
				if (data.success) {
                    getActiveState ();
                }
			},
			'json');
		return false;
}


function setStatus(status, type, size) {
	$('#status_'+ type).removeClass();

	$('#status_'+ type).addClass(status);
	if(size != null && size == 'small')
		$('#status_'+ type).addClass('ei-loading-small');
	else
		$('#status_'+ type).addClass('ei-loading');
	
	$('#status_'+ type).show();		
}

function clearStatus() {
	$('#status_add').removeClass();
	$('#status_delete').removeClass();
	$('#status_generate').removeClass();
	$('#status_activate').removeClass();
}

function setMessage(msg) {
	$('#ei_message').text(msg);
}

function getMessage() {
	alert($('#ei_message').text());
}

function tabTo(tab) {
	$('#tab_add').hide();
	$('#tab_delete').hide();
	$('#tab_generate').hide();
	$('#tab_activate').hide();
	
	if(tab != null)
		$('#tab_'+ tab).show();
	else
		$('#tab_add').show();
	
	clearStatus();
}
