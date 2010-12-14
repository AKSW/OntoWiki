<?php
/**
  * DL-Learner stpped prozesses template
  *
  * Displays the terminated processes.
  * Till now only the tab navi and the table headline.
  *
  * @author Maria Moritz & Vu Duc Minh
  * @version $Id$
  */
?>
<div id="Dllearner" class="window">
<h1 class="title"><?php echo 'New Process'; ?></h1>
	
	<ul class="tabs">
    <li ><a id="tab" class="tab" name="start"><?php echo 'New Process'; ?></a></li>
   
    <li class="active"><a id="tab" class="tab" name="stop"><?php echo 'Stopped Process'; ?></a></li>
    </ul>
	
	<div class="content has-innerwindows" id="">
	
		<div class="innercontent">		
		<p class="messagebox"><?php //echo "'x' ".$this->strings->dl->stop->message;?></p>
	    
		<table cellspacing="0" class="separated-vertical non-editable">
			
			
			<tr>
				<th><?php echo 'Started at'; ?></th>
				<th><?php echo 'Stopped at'; ?></th>
			</tr>
			
			<tr>
			    <td><span property="" content=""><?php echo $this->startAt;?></span></td>
				<td><span property="" content=""></span><?php echo $this->stopAt;?></td>
			</tr>	
		</table>		
		
		<p><b><?php echo 'Top Solutions:'; ?></b></p>
		
		<table cellspacing="0" class="separated-vertical non-editable">		
		
        
		<?php //$var=explode("\n",$this->concept);$i=1;
		      foreach ($var as $k=>$v){
				 if (preg_match ('/descriptionManchesterSyntax/',$v)){
				  $s=str_replace(array('{','}','"','descriptionManchesterSyntax:',','),array('','','','',''),Dllearner_Util::getShort($v));
				 // echo "<td width='3%'><input id='radio' type='radio' name=".$s."></td>";
				  echo "<td width='20%'><b> solution ".$i.":</b></td>";
				  $i++;
                  echo "<td width='70%'>".$s."</td>";
				  }
                  elseif (preg_match ('/accuracy/',$v)){
                  $pro=str_replace(array('{','}','"','accuracy:'),array('','','',''),$v);
				  $procent=$pro*100;
				  echo '<td><a class="has-contextmenu dlRes" name="'.$this->posE.'" >accuracy: '.$procent.'%<span class="button" /></a></td></tr>';
                  }
                } 
		?>

		</table>
<p>
			
		</p>
			
		</div><!-- innercontent -->
	</div><!-- content -->
</div><!-- window -->
<!-- </div> section-mainwindow -->