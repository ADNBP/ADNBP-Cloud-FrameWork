<article >
<?php if(!$simple) {?>
    <!-- Widget ID (each widget will need unique ID)-->
    <div class="jarviswidget jarviswidget-color-blueDark" id="wid-id-<?=$key?>" data-widget-editbutton="false">
        <!-- widget options:
        usage: <div class="jarviswidget" id="wid-id-0" data-widget-editbutton="false">
    
        data-widget-colorbutton="false"
        data-widget-editbutton="false"
        data-widget-togglebutton="false"
        data-widget-deletebutton="false"
        data-widget-fullscreenbutton="false"
        data-widget-custombutton="false"
        data-widget-collapsed="true"
        data-widget-sortable="false"
    
        -->
        <header>
            <span class="widget-icon"> <i class="fa fa-table"></i> </span>
            <h2><?=htmlentities($data->title)?></h2>
    
        </header>
    
        <!-- widget div-->
        <div>
    
            <!-- widget edit box -->
            <div class="jarviswidget-editbox">
                <!-- This area used as dropdown edit box -->
    
            </div>
            <!-- end widget edit box -->
<?php } ?>
    
            <!-- widget content -->
            <div class="widget-body">
                <p><?=htmlentities($data->subtitle)?></p>
                <div class="table-responsive">
                
                    <table id="report-<?=$key?>" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <?php if(is_array($data->columns)) foreach ($data->columns as $key => $value) {?>
                                <th><?=htmlentities($value['title'])?></th>    
                                <?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(is_array($data->rows)) foreach ($data->rows as $key => $value) {?>
                            <tr>
                                <?php if(is_array($value)) foreach ($value as $key2 => $cell) {
	                                	$align = $cell['align'];
										if(!$align && (isset($cell['currency']))) $align='right';
                                	?>
                                <td<?=($align)?' class="text-'.$align.'"':''?><?=($cell['anchor'])?' id="'.htmlentities($cell['anchor']).'"':''?>><?php
                                	if(isset($cell['currency'])) $cell['value'] = $adnbp->numberFormat($cell['value'],2).' '.$cell['currency'];
									// save echo
									$cell['value'] =htmlentities($cell['value']);
									if(isset($cell['bold'])) $cell['value'] = '<strong>'.$cell['value'].'</strong>';
									if(isset($cell['small'])) $cell['value'] = '<small>'.$cell['value'].'</small>';
                                    if(isset($cell['link'])) $cell['value'] = '<a href="'.htmlentities($cell['link']).'">'.$cell['value'].'</a>';
									echo $cell['value'];
									
                                ?></td>
                                <?php } ?>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    
                </div>
                
            </div>
            <!-- end widget content -->
<?php if(!$simple) {?>
    
        </div>
        <!-- end widget div -->
    
    </div>
    <!-- end widget -->

<?php } ?>
</article>
<!-- WIDGET END -->
