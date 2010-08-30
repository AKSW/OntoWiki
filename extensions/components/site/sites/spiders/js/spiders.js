$(document).ready(function() {
	$('.checklist').dataTable( {
        "sDom": '<"top"if>rt<"bottom"lp<"clear">',
		"bPaginate": false,
		"bLengthChange": true,
		"bFilter": true,
		"bSort": true,
		"bInfo": true,
		"bAutoWidth": true } );

} );
