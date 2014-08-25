    <h1>CloudFrameWorkServices RESTful Web Service APIs</h1>
    <b>The following public RESTful Web Service APIs are accesible:</b>
    <h2><?=$docServicesTitle?></h2>
    <p><?=$docServicesSubtitle?></p>
    <?php foreach ($docServices as $key => $value) {?>
	  <div class="panel panel-default">
	  <div class="panel-heading">
		<h3><?=(isset($value['title']))?$value['title']:''?></h3>
		<p><?=(isset($value['description']))?$value['description']:''?></p>
		</div>
	  <div class="panel-body">
	    	<?php foreach ($value['api'] as $index => $value2) {?>
	    	<b><?=$value2['title']?></b>
	    	<table class="table table-bordered  table-striped">
	    	<tr>
	    		<?php if(isset($value2['GET'])) {?>
	    		<td width='10%'><button onclick='window.location="/CloudFrameWorkService<?=$value2['api']?>";' type="button" class="btn btn-primary">GET: <?=$value2['api']?></button></th><td valign="bottom"><?=$value2['GET']?></td>
	    		<?php }?>
	    	</tr>
	    	</table>
	    	<?php }?>
	   </div>
	</div>   
    <?php } ?>
