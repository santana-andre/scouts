<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
<div class="container" style="margin-top:60px;">
    <div class="row">
        <div class="col-lg-12">
            <!-- Single button -->
            <div class="btn-group">
                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Add page <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" role="menu">
                    <?php
                    foreach($langs as $slug => $language)
                    {
                        echo '<li>'.anchor('admin/pages/create/'.$slug,$language['name']).'</li>';
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12" style="margin-top: 10px;">
            <?php
            echo '<table class="table table-hover table-bordered table-condensed">';
            echo '<tr>';
            echo '<td rowspan="2">ID</td>';
            echo '<td rowspan="2">Page title</td>';
            echo '<td colspan="'.sizeof($langs).'">Translations</td>';
            echo '<td rowspan="2">Created at</td>';
            echo '<td rowspan="2">Last update</td>';
            echo '<td rowspan="2">Operations</td>';
            echo '</tr>';
            echo '<tr>';
            foreach($langs as $slug => $language)
            {
                echo '<td>'.$slug.'</td>';
            }
            echo '</tr>';
            if(!empty($pages))
            {

                foreach($pages as $page_id => $page)
                {
                    echo '<tr>';
                    echo '<td>'.$page_id.'</td><td>'.$page['title'].'</td>';
                    foreach($langs as $slug=>$language)
                    {
                        echo '<td>';
                        if(array_key_exists($slug,$page['translations']))
                        {
                            echo anchor('admin/pages/edit/'.$slug.'/'.$page_id,'<span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>');
                            echo ' '.anchor('admin/pages/delete/'.$slug.'/'.$page_id,'<span class="glyphicon glyphicon-remove" aria-hidden="true"></span>','onclick="return confirm(\'Are you sure you want to delete?\')"');
                            echo '<br />'.$page['translations'][$slug]['created_at'];
                            echo '<br />'.$page['translations'][$slug]['last_update'];
                        }
                        else
                        {
                            echo anchor('admin/pages/create/'.$slug.'/'.$page_id,'<span class="glyphicon glyphicon-plus" aria-hidden="true"></span>');
                        }
                        echo '</td>';
                    }
                    echo '<td>'.$page['created_at'].'</td>';
                    echo '<td>'.$page['last_update'].'</td>';
                    echo '<td>'.anchor('admin/pages/delete/all/'.$page_id,'<span class="glyphicon glyphicon-remove" aria-hidden="true"></span>','onclick="return confirm(\'Are you sure you want to delete?\')"').'</td>';
                    echo '</tr>';
                }
            }
            echo '</table>';
            echo '<nav><ul class="pagination">';
            echo $next_previous_pages;
            echo '</ul></nav>';
            ?>
        </div>
    </div>
</div>