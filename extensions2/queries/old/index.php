<?
//require_once('lib/Sajax.php');
//include ('ajax.php');

//make default 
//$default[] = array("s"=>"?what","p"=>"dbpprop:latestReleaseDate","o"=>"?released");
//$default[] = array("s"=>"?what","p"=>"db-ont:birthplace","o"=>"?released");

//$s=$_REQUEST['s']?$_REQUEST['s']:array('?what');
//$p=$_REQUEST['p']?$_REQUEST['p']:array('dbpprop:latestReleaseDate');
//$o=$_REQUEST['o']?$_REQUEST['o']:array('?released');
//$o=$_REQUEST['o']?$_REQUEST['o']:array('yago:PolishNurses');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
	<title>Semantic Wikipedia Query</title>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />

<link rel="stylesheet" href="css/blue.css" type="text/css" media="print, projection, screen" />
<link rel="stylesheet" href="css/jquery.autocomplete.css"/>
<link rel="stylesheet" href="css/querybuilder.css"/>

<link rel="shortcut icon" href="favicon.ico" >

<?/*

<script src="javascript/querybuilder.js" type="text/javascript"></script>
<script src="javascript/jquery-1.3.2.min.js" type="text/javascript"></script>
<script src="javascript/jquery.tablesorter.min.js" type="text/javascript"></script>
<script src="javascript/jquery.autocomplete.min.js" type="text/javascript"></script>
<script src="javascript/json2.js" type="text/javascript"></script>
*/?>
<script src="resources/querybuilder.js" type="text/javascript"></script>
<script src="resources/jquery-1.3.2.min.js" type="text/javascript"></script>
<script src="resources/jquery.tablesorter.min.js" type="text/javascript"></script>
<script src="resources/jquery.autocomplete.min.js" type="text/javascript"></script>
<script src="resources/json2.js" type="text/javascript"></script>

<script type="text/javascript">
<? //sajax_show_javascript();
?>
$(document).ready(function() {
	  updateResults();
      initPatterns(".pattern");
      $("#resulttable").tablesorter({sortList:[[0,0]], widgets: ['zebra']});
	  var url = "autocompletion.php";
	  var autocompOptions = getAutocompleteOptions();
	  $(".pattern").autocomplete(url, autocompOptions);
  });//end ready block 	

  
    /*
	$("#showquery").click(function() {
		displayQuery();
		
		$('#showquerytextarea').attr("style","").focus();
	});
	
	$("#showquery").mouseover(function() {
		displayQuery();
	});
	
	$('#showquerytextarea').focus(function() {
		$('#showquerytextarea').attr('value',$("#showquery").attr('title') );
		$('#showquerytextarea').attr("style","");
	});
	
	$('#showquerytextarea').blur(function() {
		$('#showquerytextarea').attr("style","display:none");
	});
	*/
	/*$("#gp div:last  > input[name='o']").result( 
		function(event, data, formatted) {
 			//$("#gp div:last  > input[name='o']").attr('value', 'noenes');
 			//alert(formatted);
 			alert(event);
 			//$("#result").html( !data ? "No match!" : "Selected: " + formatted);
		});
	*/	
//		$("#gp div:last  > input[name='o']").search(alert("ss"));


	 




	
 </script>

</head>
<body >
<!--<textarea id='autocompletionquery' cols="100" rows="1"  ></textarea><br>-->
<? include('templates/querybuilder/manage.phtml');?>

</body>
</html>
