<?php
/**
  * DL-Learner start template
  *
  * @author Maria Moritz & Vu Duc Minh
  * @version $Id$
  */

// print message if ContainerManager Plugin isn't on
//session_start();
unset($_SESSION['processes']);
/*if($_SESSION['cmPluginOff']) {
	echo $_SESSION['cmPluginOff'];
	unset($_SESSION['cmPluginOff']);
	return;	
}*/
?>
<div id="Dllearner" class="window">
<h1 class="title"><?php echo 'New Process'; ?></h1>
	
	<ul class="tabs">
    <li class="active"><a id="tab" class="tab" name="start"><?php echo 'New Process'; ?></a></li>
    
    <li ><a id="tab" class="tab" name="stop"><?php echo 'Stopped Process'; ?></a></li>
    </ul>
	
	<div class="content has-innerwindows" id="">
	
		<div class="innercontent">
		<p class="messagebox"></p>
		
		<table cellspacing="0" class="non-editable" width="50%">

			<tr>
				<td><?php echo 'Select Ontologie:'; ?></td>
				<td><select name="" size="1">
        			<option><?php //echo(Zend_Registry::get('config')->activeModel->modelURI); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td><?php echo 'Select Container:'; ?>
				<td>
					<select id="sel" size="1">
					<?php
					foreach ($_SESSION['container'] as $key => $value) {
					echo '<option>'.$key.'</option>' . PHP_EOL;
					}
					?>
					</select>
				</td>
				<td><a class="formbutton addToLearner" name="addToLearner"><?php echo 'Add To Learner'; ?></a>
				</td>
			</tr>
		</table>
		<?php
		// table only displayed if there's at least one container selected
		// prints two radio buttons, the container name and a remove button
		if($_SESSION['view']!= null){
		echo "<table cellspacing='0' class='non-editable'>" .
				"<tr>" .
					"<td width='10%'>positive</td>".
					"<td width='10%'>negative</td>".
				"</tr>";
		
		$odd = true;
		$counter = 0;
		foreach ($_SESSION['view'] as $key => $value) {
			// $odd for better view
			if (!$odd) {
				echo "<tr><td width='10%'><input id='radio".$counter."' i='".$counter."' size='".count($_SESSION['view'])."' name='".$key."' type='radio' value='1'></td>";
				$counter++;
				echo "<td width='10%'><input id='radio".$counter."' i='".$counter."' size='".count($_SESSION['view'])."' name='".$key."' type='radio' value='0' ></td>";
				$counter++;
				echo "<td>".$key." (".count($value).")</td>" .
					"<td><a class='formbutton remove' name='remove' id='".$key."'>Remove</a></td></tr>" . PHP_EOL;
			} else {
				echo "<tr class='odd'><td width='10%'><input id='radio".$counter."' i='".$counter."' size='".count($_SESSION['view'])."' name='".$key."' type='radio' value='1' ></td>";
				$counter++;
				echo "<td width='10%'><input id='radio".$counter."' i='".$counter."' size='".count($_SESSION['view'])."' name='".$key."' type='radio' value='0'></td>";
				$counter++;
				echo "<td>".$key." (".count($value).")</td>" .
					"<td><a class='formbutton remove' name='remove' id='".$key."'>Remove</a></td></tr>" . PHP_EOL;	
				$odd = false;			
			}
		}
		echo "</table>";
		}
		?>
		
		<pre><?php
			//print_r($_SESSION['examp']);
		?></pre>
	
		<p>
			<?php echo 'Learn This:'; ?>
			<a class="formbutton learnThis" name="learnThis"><?php echo 'New Process'; ?></a>
		</p>
		</div>
		
		<?php
		//session_stop();
		// some options
		?>
		<div class="innerwindows width70" >
			<div class="window">
               <h2 class="title"><?php echo 'Options';?></h2>
               <div class="content">
                   	<li><?php echo 'Reasoner'; ?>
                   		<select id="reasoner" name="" size="1" class="width95">
        					
        					<option><?php echo 'owlapi';?></option>
							<option><?php echo 'fastInstanceChecker';?></option>
						</select></li>
                   	<li><?php echo 'Learning Problem'; ?>
                   		<select id="lerningProblem" name="" size="1" class="width95">
        					<option><?php echo 'posNegDefinition'; ?></option>
        					<option><?php echo 'posNegInclusion'; ?></option>
							
						</select></li>
                   	<li><?php echo 'Learning Algorithm'; ?>
                   		<select id="lerningAlgorithm" name="" size="1" class="width95">
        					
        					<option><?php echo 'refinement'; ?></option>
							<option><?php echo 'refexamples'; ?></option>
						</select></li>
               </div>
           	</div>
       	</div><!-- innerwindows -->

	</div><!-- content -->
	<div class="content" id="content-2">
	
	</div>
</div>
<!-- </div> -->