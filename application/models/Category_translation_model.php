<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Category_translation_model extends MY_Model
{

    public function __construct()
    {
        $this->has_one['category'] = array('Category_model','id','category_id');
        parent::__construct();
    }
}