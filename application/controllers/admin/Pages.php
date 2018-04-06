<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Pages extends Admin_Controller
{

    function __construct()
    {
        parent::__construct();
        if(!$this->ion_auth->in_group('admin'))
        {
            $this->session->set_flashdata('message','You are not allowed to visit the Pages page');
            redirect('admin','refresh');
        }
        $this->load->model('page_model');
        $this->load->model('page_translation_model');
        $this->load->model('slug_model');
        $this->load->model('language_model');
        $this->load->library('form_validation');
        $this->load->helper('text');
    }

    public function index()
    {
        $total_pages = $this->page_model->count_rows();
        $list_pages = array();
        if($pages = $this->page_model->order_by('created_at, updated_at','desc')->with('translations')->paginate(30,$total_pages))
        {
            foreach ($pages as $page)
            {
                $list_pages[$page->id] = array('created_at' => $page->created_at, 'last_update' => $page->updated_at, 'deleted' => $page->deleted_at, 'translations' => array(), 'title'=>'');
                $page->translations = $this->page_translation_model->where('id', $page->id)->get();
                if(isset($page->translations))
                {
                    if (isset($page->translations->id)) {
                        $translation = $page->translations;
                        $list_pages[$page->id]['translations'][$translation->language_slug] = array('translation_id' => $translation->id, 'title' => $translation->title, 'created_at' => $translation->created_at, 'last_update' => $translation->updated_at, 'deleted' => $translation->deleted_at);
                        if ($translation->language_slug == $this->default_lang) {
                            $list_pages[$page->id]['title'] = $translation->title;
                        } elseif (strlen($list_pages[$page->id]['title']) == 0) {
                            $list_pages[$page->id]['title'] = $translation->title;
                        }
                    }else{
                        foreach ($page->translations as $translation)
                        {
                            $list_pages[$page->id]['translations'][$translation->language_slug] = array('translation_id' => $translation->id, 'title' => $translation->title, 'created_at' => $translation->created_at, 'last_update' => $translation->updated_at, 'deleted' => $translation->deleted_at);
                            if ($translation->language_slug == $this->default_lang) {
                                $list_pages[$page->id]['title'] = $translation->title;
                            } elseif (strlen($list_pages[$page->id]['title']) == 0) {
                                $list_pages[$page->id]['title'] = $translation->title;
                            }
                        }
                    }
                    
                }
            }
        }
        $this->data['pages'] = $list_pages;
        $this->data['next_previous_pages'] = $this->page_model->all_pages;
        $this->render('admin/pages/index_view');
    }

    public function create($language_slug = NULL, $page_id = 0)
    {
        $language_slug = (isset($language_slug) && array_key_exists($language_slug, $this->langs)) ? $language_slug : $this->current_lang;

        $this->data['content_language'] = $this->langs[$language_slug]['name'];
        $this->data['language_slug'] = $language_slug;
        $page = $this->page_model->get($page_id);
        if($page_id != 0 && $page==FALSE)
        {
            $page_id = 0;
        }
        if($this->page_translation_model->where(array('page_id'=>$page_id,'language_slug'=>$language_slug))->get())
        {
            $this->session->set_flashdata('message', 'A translation for that page already exists.');
            redirect('admin/pages', 'refresh');
        }
        $this->data['page'] = $page;
        $this->data['page_id'] = $page_id;
        $pages = $this->page_translation_model->where('language_slug',$language_slug)->order_by('menu_title')->fields('page_id,id,menu_title')->get_all();
        $this->data['parent_pages'] = array('0'=>'No parent page');
        if(!empty($pages))
        {
            foreach($pages as $page)
            {
                $this->data['parent_pages'][$page->page_id] = $page->menu_title;
            }
        }

        $rules = $this->page_model->rules;
        $this->form_validation->set_rules($rules['insert']);
        if($this->form_validation->run()===FALSE)
        {
            $this->render('admin/pages/create_view');
        }
        else
        {
            $parent_id = $this->input->post('parent_id');
            $title = $this->input->post('title');
            $menu_title = (strlen($this->input->post('menu_title')) > 0) ? $this->input->post('menu_title') : $title;
            $slug = (strlen($this->input->post('slug')) > 0) ? url_title($this->input->post('slug'),'-',TRUE) : url_title(convert_accented_characters($title),'-',TRUE);
            $order = $this->input->post('order');
            $content = $this->input->post('content');
            $teaser = (strlen($this->input->post('teaser')) > 0) ? $this->input->post('teaser') : substr($content, 0, strpos($content, '<!--more-->'));
            $page_title = (strlen($this->input->post('page_title')) > 0) ? $this->input->post('page_title') : $title;
            $page_description = (strlen($this->input->post('page_description')) > 0) ? $this->input->post('page_description') : ellipsize($teaser, 160);
            $page_keywords = $this->input->post('page_keywords');
            $page_id = $this->input->post('page_id');
            $language_slug = $this->input->post('language_slug');
            if ($page_id == 0)
            {
                $page_id = $this->page_model->insert(array('parent_id' => $parent_id, 'order' => $order, 'created_by'=>$this->user_id));
            }

            $insert_data = array('page_id'=>$page_id,'title' => $title, 'menu_title' => $menu_title, 'teaser' => $teaser,'content' => $content,'page_title' => $page_title, 'page_description' => $page_description,'page_keywords' => $page_keywords,'language_slug' => $language_slug,'created_by'=>$this->user_id);

            if($translation_id = $this->page_translation_model->insert($insert_data))
            {
                $this->page_model->update(array('parent_id'=>$parent_id, 'order'=>$order,'updated_by'=>$this->user_id),$page_id);
                $url = $this->_verify_slug($slug,$language_slug);
                $this->slug_model->insert(array(
                    'content_type'=>'page',
                    'content_id'=>$page_id,
                    'translation_id'=>$translation_id,
                    'language_slug'=>$language_slug,
                    'url'=>$url,
                    'created_by'=>$this->user_id));
                //$this->slug_model->where(array('content_type'=>'page','content_id'=>$page_id,'id !='=>$slug_id))->update(array('redirect'=>$slug_id));
            }

            redirect('admin/pages','refresh');

        }


    }

    public function edit($language_slug, $page_id)
    {
        $translation = $this->page_translation_model->where(array('page_id'=>$page_id, 'language_slug'=>$language_slug))->get();
        $this->data['content_language'] = $this->langs[$language_slug]['name'];
        if($translation == FALSE)
        {
            $this->session->set_flashdata('message', 'There is no translation for that page.');
            redirect('admin/pages', 'refresh');
        }
        $page = $this->page_model->get($page_id);
        if($page == FALSE)
        {
            $this->session->set_flashdata('message', 'There is no page to translate.');
            redirect('admin/pages', 'refresh');
        }
        $this->data['translation'] = $translation;
        $this->data['page'] = $page;
        $this->data['slugs'] = $this->slug_model->where(array('content_type'=>'page','translation_id'=>$translation->id))->get_all();
        $pages = $this->page_translation_model->where(array('language_slug'=>$language_slug,'page_id !='=>$page_id))->order_by('menu_title')->fields('page_id,id,menu_title')->get_all();
        $this->data['parent_pages'] = array('0'=>'No parent page');
        if(!empty($pages))
        {
            foreach($pages as $page)
            {
                $this->data['parent_pages'][$page->page_id] = $page->menu_title;
            }
        }

        $rules = $this->page_model->rules;
        $this->form_validation->set_rules($rules['update']);
        if($this->form_validation->run()===FALSE)
        {
            $this->render('admin/pages/edit_view');
        }
        else
        {
            $translation_id = $this->input->post('translation_id');
            if($translation = $this->page_translation_model->get($translation_id))
            {
                $parent_id = $this->input->post('parent_id');
                $title = $this->input->post('title');
                $menu_title = $this->input->post('menu_title');
                $slug = $this->input->post('slug');
                $order = $this->input->post('order');
                $content = $this->input->post('content');
                $teaser = (strlen($this->input->post('teaser')) > 0) ? $this->input->post('teaser') : substr($content, 0, strpos($content, '<!--more-->'));
                $page_title = (strlen($this->input->post('page_title')) > 0) ? $this->input->post('page_title') : $title;
                $page_description = (strlen($this->input->post('page_description')) > 0) ? $this->input->post('page_description') : ellipsize($teaser, 160);
                $page_keywords = $this->input->post('page_keywords');
                $page_id = $this->input->post('page_id');
                $language_slug = $this->input->post('language_slug');


                $update_data = array(
                    'title' => $title,
                    'menu_title' => $menu_title,
                    'teaser' => $teaser,
                    'content' => $content,
                    'page_title' => $page_title,
                    'page_description' => $page_description,
                    'page_keywords' => $page_keywords,
                    'updated_by' => $this->user_id);

                if ($this->page_translation_model->update($update_data, $translation_id))
                {
                    $this->page_model->update(array('parent_id' => $parent_id, 'order' => $order, 'updated_by' => $this->user_id), $page_id);
                    if(strlen($slug)>0)
                    {
                        $url = $this->_verify_slug($slug, $language_slug);
                        $new_slug = array(
                            'content_type' => 'page',
                            'content_id' => $page_id,
                            'translation_id' => $translation_id,
                            'language_slug' => $language_slug,
                            'url' => $url,
                            'created_by' => $this->user_id);
                        if($slug_id =  $this->slug_model->insert($new_slug))
                        {
                            $this->slug_model->where(array('content_type'=>'page', 'translation_id'=>$translation_id,'id != '=>$slug_id))->update(array('redirect'=>$slug_id,'updated_by'=>$this->user_id));
                        }
                    }
                    $this->session->set_flashdata('message', 'The translation was updated successfully.');
                }
            }
            else
            {
                $this->session->set_flashdata('message', 'There is no translation to update.');
            }
            redirect('admin/pages','refresh');
        }
    }
    private function _verify_slug($str,$language)
    {
        if($this->slug_model->where(array('url'=>$str,'language_slug'=>$language))->get() !== FALSE)
        {
            $parts = explode('-',$str);
            if(is_numeric($parts[sizeof($parts)-1]))
            {
                $parts[sizeof($parts)-1] = $parts[sizeof($parts)-1]++;
            }
            else
            {
                $parts[] = '1';
            }
            $str = implode('-',$parts);
            $this->_verify_slug($str,$language);
        }
        return $str;
    }
    public function delete($language_slug, $page_id)
    {
        if($page = $this->page_model->get($page_id))
        {
            if($language_slug=='all')
            {
                if($deleted_translations = $this->page_translation_model->where('page_id',$page_id)->delete())
                {
                    $deleted_slugs = $this->slug_model->where(array('content_type'=>'page','content_id'=>$page_id))->delete();
                    $deleted_pages = $this->page_model->delete($page_id);
                    $this->session->set_flashdata('message', $deleted_pages.' page deleted. There were also '.$deleted_translations.' translations and '.$deleted_slugs.' slugs deleted.');
                }
                else
                {
                    $deleted_pages = $this->page_model->delete($page_id);
                    $this->session->set_flashdata('message', $deleted_pages.' page was deleted');
                }
            }
            else
            {
                if($this->page_translation_model->where(array('page_id'=>$page_id,'language_slug'=>$language_slug))->delete())
                {
                    $deleted_slugs = $this->slug_model->where(array('content_type'=>'page','language_slug'=>$language_slug,'content_id'=>$page_id))->delete();
                    $this->session->set_flashdata('message', 'The translation and '.$deleted_slugs.' slugs were deleted.');
                }
            }
        }
        else
        {
            $this->session->set_flashdata('message', 'There is no translation to delete.');
        }
        redirect('admin/pages','refresh');

    }
}