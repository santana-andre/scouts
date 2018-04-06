<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
<div class="container" style="margin-top:60px;">
    <div class="row">
        <div class="col-lg-12">
            <h1>Edit Category in <?php echo strtolower($content_language);?></h1>
            <?php echo form_open('',array('class'=>'form-horizontal'));?>
            <div class="form-group">
                <?php
                echo form_label('Parent category','parent_id');
                echo form_dropdown('parent_id',$parent_categories,set_value('parent_id',$category->parent_id),'class="form-control"');
                ?>
            </div>
            <div class="form-group">
                <?php
                echo form_label('Title','title');
                echo form_error('title');
                echo form_input('title',set_value('title',$translation->title),'class="form-control"');
                ?>
            </div>
            <div class="form-group">
                <?php
                echo form_label('Menu title','menu_title');
                echo form_error('menu_title');
                echo form_input('menu_title',set_value('menu_title',$translation->menu_title),'class="form-control"');
                ?>
            </div>
            <div class="form-group">
                <?php
                echo form_label('Slug','slug');
                echo form_error('slug');
                echo form_input('slug',set_value('slug'),'class="form-control"');
                ?>
            </div>
            <?php
            echo '<div class="panel panel-primary">';
            echo '<div class="panel-heading">Currently active slugs</div>';
            echo '<div class="panel-body">';
            foreach($slugs as $slug)
            {
                echo $slug->url.'<br />';
            }
            echo '</div>';
            echo '</div>';
            ?>
            <div class="form-group">
                <?php
                echo form_label('Order','order');
                echo form_error('order');
                echo form_input('order',set_value('order', $category->order),'class="form-control"');
                ?>
            </div>
            <div class="form-group">
                <?php
                echo form_label('Page title','page_title');
                echo form_error('page_title');
                echo form_input('page_title',set_value('page_title',$translation->page_title),'class="form-control"');
                ?>
            </div>
            <div class="form-group">
                <?php
                echo form_label('Page description','page_description');
                echo form_error('page_description');
                echo form_input('page_description',set_value('page_description',$translation->page_description),'class="form-control"');
                ?>
            </div>
            <div class="form-group">
                <?php
                echo form_label('Keywords','page_keywords');
                echo form_error('page_keywords');
                echo form_input('page_keywords',set_value('page_keywords',$translation->page_keywords),'class="form-control"');
                ?>
            </div>
            <?php echo form_error('category_id');?>
            <?php echo form_hidden('category_id',set_value('category_id',$translation->category_id));?>
            <?php echo form_error('language_slug');?>
            <?php echo form_hidden('language_slug',set_value('language_slug',$translation->language_slug));?>
            <?php echo form_error('translation_id');?>
            <?php echo form_hidden('translation_id',set_value('translation_id',$translation->id));?>
            <?php
            $submit_button = 'Edit translation';
            echo form_submit('submit', $submit_button, 'class="btn btn-primary btn-lg btn-block"');?>
            <?php echo anchor('/admin/categories', 'Cancel','class="btn btn-default btn-lg btn-block"');?>
            <?php echo form_close();?>
        </div>
    </div>
</div>