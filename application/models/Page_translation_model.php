<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Page_translation_model extends MY_Model
{

    public function __construct()
    {
        $this->has_one['page'] = array('Page_model','id','page_id');
        parent::__construct();
    }
}