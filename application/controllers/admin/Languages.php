<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Languages extends Admin_Controller
{
  function __construct()
  {
    parent::__construct();
    $this->load->library('form_validation');
    $this->load->model('language_model');
  }

  public function index()
  {
    $this->data['languages'] = $this->language_model->get_all();
    $this->render('admin/languages/index_view');
  }

  public function create()
  {
    $this->form_validation->set_rules('language_name', 'Language name','trim|required|is_unique[languages.language_name]');
    $this->form_validation->set_rules('language_slug','Slug','trim|alpha_dash|required|is_unique[languages.slug]');
    $this->form_validation->set_rules('language_directory', 'Language directory','trim|required');
    $this->form_validation->set_rules('language_code','Language code','trim|alpha_dash|required|is_unique[languages.language_code]');
    $this->form_validation->set_rules('default','Default','trim|in_list[0,1]');

    if($this->form_validation->run()===FALSE)
    {
      $this->render('admin/languages/create_view');
    }
    else
    {
      $new_language = array(
        'language_name' => $this->input->post('language_name'),
        'slug' => $this->input->post('language_slug'),
        'language_directory' => $this->input->post('language_directory'),
        'language_code' => $this->input->post('language_code'),
        'default' => $this->input->post('default')
      );
      $this->session->set_flashdata('message', 'Language added successfully');
      if (!$this->language_model->create($new_language))
      {
        $this->session->set_flashdata('message', 'There was an error inserting the new language');
      }
      redirect('admin/languages', 'refresh');
    }
  }

  public function update($language_id = NULL)
  {
    $this->form_validation->set_rules('language_name', 'Language name','trim|required');
    $this->form_validation->set_rules('language_slug','Slug','trim|alpha_dash|required');
    $this->form_validation->set_rules('language_directory','Language directory','trim|required');
    $this->form_validation->set_rules('language_code','Language code','trim|alpha_dash|required');
    $this->form_validation->set_rules('default','Default','trim|in_list[0,1]');
    $this->form_validation->set_rules('language_id','Language ID','trim|integer');

    $language_id = isset($language_id) ? (int) $language_id : (int) $this->input->post('language_id');

    if($this->form_validation->run()===FALSE)
    {
      $language = $this->language_model->get_by_id($language_id);
      if($language!==FALSE)
      {
        $this->data['language'] = $language;
        $this->render('admin/languages/edit_view');
      }
      else
      {
        $this->session->set_flashdata('message', 'The ID for the language doesn\'t exist');
        redirect('admin/languages', 'refresh');
      }
    }
    else
    {
      $new_data = array(
        'language_name' => $this->input->post('language_name'),
        'slug' => $this->input->post('language_slug'),
        'language_directory' => $this->input->post('language_directory'),
        'language_code' => $this->input->post('language_code'),
  'default' => $this->input->post('default')
      );
      $this->session->set_flashdata('message', 'Language updated successfuly');
      if (!$this->language_model->update($language_id, $new_data))
      {
        $this->session->set_flashdata('message', 'There was an error in updating the language');
      }
      redirect('admin/languages', 'refresh');
    }
  }

  public function delete($language_id)
  {
    if(($language = $this->language_model->get_by_id($language_id)) && $language->default == '1')
    {
      $this->session->set_flashdata('message','I can\'t delete a default language. First set another default language.');
    }
    elseif($this->language_model->delete($language_id) === FALSE)
    {
      $this->session->set_flashdata('message', 'There was an error in deleting the language');
    }
    else
    {
      $this->session->set_flashdata('message', 'Language deleted successfuly');
    }
    redirect('admin/languages','refresh');
  }
}