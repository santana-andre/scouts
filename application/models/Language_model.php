<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Language_model extends CI_Model
{

  public function __construct()
  {
    parent::__construct();
  }

  public function get_all($where = NULL)
  {
    if(isset($where))
    {
      $this->db->where($where);
    }
    $this->db->order_by('language_name','ASC');
    $query = $this->db->get('languages');
    if($query->num_rows()>0)
    {
      return $query->result();
    }

    return FALSE;
  }

  public function get_by_id($id = NULL)
  {
    if(isset($id) && is_int($id))
    {
      $this->db->where('id',$id);
      $query = $this->db->get('languages');
      if($query->num_rows()==1)
      {
        return $query->row();
      }
    }
    
    return FALSE;
  }

  public function create($data)
  {
    if($data['default']=='1')
    {
      $this->db->where('default', '1');
      $this->db->update('languages', array('default'=>'0'));
    }
    return $this->db->insert('languages',$data);
  }

  public function update($language_id, $data)
  {
    if($data['default']=='1')
    {
      $this->db->where('default', '1');
      $this->db->update('languages', array('default'=>'0'));
    }
    $this->db->where('id',$language_id);
    return $this->db->update('languages',$data);
  }

  public function delete($language_id)
  {
    return $this->db->delete('languages', array('id'=>$language_id));
  }

}