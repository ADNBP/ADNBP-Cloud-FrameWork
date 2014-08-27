    <div class="jumbotron">
    <h1>CloudFrameWorkServices RESTful Web Service APIs</h1>
    <b>The following public RESTfull APIs are accesible:</b>
    <h2><?=$docServicesTitle?></h2>
    <p><?=$docServicesSubtitle?></p>
    </div>
    
    
      <div class="panel-group" id="accordion">
    <?php foreach ($docServices as $key => $value) {?>
	    <div class="panel panel-default">
	  	<div class="panel-heading">
		<h4 class="panel-title">
			<a data-toggle="collapse" data-parent="#accordion" href="#collapse<?=$key?>"><?=(isset($value['title']))?$value['title']:''?></a>
		</h4>
		<p><?=(isset($value['description']))?$value['description']:''?></p>
		</div>
		<div id="collapse<?=$key?>" class="panel-collapse collapse">
		<div class="panel-body">
	    	<?php if(is_array($value['api']))  foreach ($value['api'] as $index => $value2) {?>
	    	<b><?=$value2['title']?></b>
	    	<table class="table table-bordered  table-striped">
	    	<tr>
	    		<?php if(isset($value2['GET'])) {?>
	    		<td width='10%'><button onclick='window.location="/CloudFrameWorkService<?=(strlen($value2['example']))?$value2['example']:$value2['api']?>";' type="button" class="btn btn-primary" data-toggle="collapse" data-target="#<?=$key?>_api_<?=$index?>">GET: <?=$value2['api']?></button></th><td valign="bottom"><?=$value2['GET']?></td>
				<!-- onclick='window.location="/CloudFrameWorkService<?=(strlen($value2['example']))?$value2['example']:$value2['api']?>";'  -->
	    		</tr>
	    		<tr>
	    			<td colspan="2">
	    				<div  class="collapse in" id='<?=$key?>_api_<?=$index?>'>
	    				<table class="table table-bordered ">
	    				<?php if(isset($value2['params'])) {?>
	    					<tr>
	    						<th> Name</th><th> Description</th><th> Details</th>
	    					</tr>
	    					<?php if(is_array($value2['params'])) foreach ($value2['params'] as $index2 => $value3) {?>
	    					<tr>
	    						<td><?=$value3['paramName']?></td><td><?=$value3['paramDescription']?></td><td><?=$value3['paramDetails']?></td>
	    					</tr>
	    					<?php }?>
			    		<?php }?>
	    					<tr>
	    						<td colspan="3"><span class="label label-default">Response:</span>
	    							<pre><?=$value2['GET-response-header']?></pre>
	    							<pre><?=$value2['GET-response-content']?></pre>
	    						</td>
	    					</tr>
	    				</table>
	    				</div>
	    			</td>
	    		<?php }?>
	    	</tr>
	    	</table>
	    	<?php }?>
	   </div>
	   </div>
	</div>   
    <?php } ?>
	</div>   