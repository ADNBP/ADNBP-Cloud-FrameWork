<article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
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
    
            <!-- widget content -->
            <div class="widget-body">
                <p><?=htmlentities($data->subtitle)?></p>
                <div class="table-responsive">
                
                    <table class="table table-bordered">
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
                                <?php if($value) foreach ($value as $key2 => $value2) {?>
                                <td><?=htmlentities($value2)?></td>    
                                <?php } ?>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    
                </div>
            </div>
            <!-- end widget content -->
    
        </div>
        <!-- end widget div -->
    
    </div>
    <!-- end widget -->


</article>
<!-- WIDGET END -->
