<?php
/**
  * DL-Learner model template
  *
  * Displays the added Model.
  *
  * @author Vu Duc Minh
  * @version $Id$
  */
 ?>
<div id="Dllearner" class="window">

<h1 class="title"><?php echo $this->title; ?></h1>
<ul class="tabs">
    <li ><a id="tab" class="tab" name="start"><?php echo 'New Process'; ?></a></li>
    <li class="active"><a id="tab" class="tab" name="model"><?php echo "Model" ?></a></li>
	</ul>

	<div class="content" id="">
	
		<div class="innercontent">		
        <p> Concept successful added into Model, under is the Concept in table form:</p><br>
		<?php 
		echo $this->model->writeAsHtml();
		foreach ($this->cl as $k=>$v){
		echo $v;
		}
		//echo $this->model->writeAsHtmlTable();
		?>

		</div><!-- innercontent -->
	</div><!-- content -->
	</div><!-- window -->