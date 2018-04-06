<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Categories extends Admin_Controller
{

	function __construct()
	{
		parent::__construct();
        if(!$this->ion_auth->in_group('admin'))
        {
            $this->session->set_flashdata('message','You are not allowed to visit the Categories page');
            redirect('admin','refresh');
        }
        $this->load->model('category_model');
        $this->load->model('category_translation_model');
        $this->load->model('slug_model');
        $this->load->model('language_model');
        $this->load->library('form_validation');
        $this->load->helper('text');
	}

	public function index()
	{
        $total_categories = $this->category_model->count_rows();
        $list_categories = array();
        if($categories = $this->category_model->order_by('created_at, updated_at','desc')->with('translations')->paginate(30,$total_categories))
        {
            foreach ($categories as $category)
            {
                $list_categories[$category->id] = array('created_at' => $category->created_at, 'last_update' => $category->updated_at, 'deleted' => $category->deleted_at, 'translations' => array(), 'title'=>'');
                if(isset($category->translations))
                {
                    foreach ($category->translations as $translation)
                    {
                        $list_categories[$category->id]['translations'][$translation->language_slug] = array('translation_id' => $translation->id, 'title' => $translation->title, 'created_at' => $translation->created_at, 'last_update' => $translation->updated_at, 'deleted' => $translation->deleted_at);
                        if ($translation->language_slug == $this->default_lang) {
                            $list_categories[$category->id]['title'] = $translation->title;
                        } elseif (strlen($list_categories[$category->id]['title']) == 0) {
                            $list_categories[$category->id]['title'] = $translation->title;
                        }
                    }
                }
            }
        }
        $this->data['categories'] = $list_categories;
        $this->data['next_previous_pages'] = $this->category_model->all_pages;
		$this->render('admin/categories/index_view');
	}

    public function create($language_slug = NULL, $category_id = 0)
    {
        $language_slug = (isset($language_slug) && array_key_exists($language_slug, $this->langs)) ? $language_slug : $this->current_lang;

        $this->data['content_language'] = $this->langs[$language_slug]['name'];
        $this->data['language_slug'] = $language_slug;
        $category = $this->category_model->get($category_id);
        if($category_id != 0 && $category==FALSE)
        {
            $category_id = 0;
        }
        if($this->category_translation_model->where(array('category_id'=>$category_id,'language_slug'=>$language_slug))->get())
        {
            $this->session->set_flashdata('message', 'A translation for that category already exists.');
            redirect('admin/categories', 'refresh');
        }
        $this->data['category'] = $category;
        $this->data['category_id'] = $category_id;
        $categories = $this->category_translation_model->where('language_slug',$language_slug)->order_by('menu_title')->fields('category_id,id,menu_title')->get_all();
        $this->data['parent_categories'] = array('0'=>'No parent category');
        if(!empty($categories))
        {
            foreach($categories as $category)
            {
                $this->data['parent_categories'][$category->category_id] = $category->menu_title;
            }
        }

        $rules = $this->category_model->rules;
        $this->form_validation->set_rules($rules['insert']);
        if($this->form_validation->run()===FALSE)
        {
            $this->render('admin/categories/create_view');
        }
        else
        {
            $parent_id = $this->input->post('parent_id');
            $title = $this->input->post('title');
            $menu_title = (strlen($this->input->post('menu_title')) > 0) ? $this->input->post('menu_title') : $title;
            $slug = (strlen($this->input->post('slug')) > 0) ? url_title($this->input->post('slug'),'-',TRUE) : url_title(convert_accented_characters($title),'-',TRUE);
            $order = $this->input->post('order');
            $page_title = (strlen($this->input->post('page_title')) > 0) ? $this->input->post('page_title') : $title;
            $page_description = $this->input->post('page_description');
            $page_keywords = $this->input->post('page_keywords');
            $category_id = $this->input->post('category_id');
            $language_slug = $this->input->post('language_slug');
            if ($category_id == 0)
            {
                $category_id = $this->category_model->insert(array('parent_id' => $parent_id, 'order' => $order, 'created_by'=>$this->user_id));
            }

            $insert_data = array('category_id'=>$category_id,'title' => $title, 'menu_title' => $menu_title, 'page_title' => $page_title, 'page_description' => $page_description,'page_keywords' => $page_keywords,'language_slug' => $language_slug,'created_by'=>$this->user_id);

            if($translation_id = $this->category_translation_model->insert($insert_data))
            {
                $this->category_model->update(array('parent_id'=>$parent_id, 'order'=>$order,'updated_by'=>$this->user_id),$category_id);
                $url = $this->_verify_slug($slug,$language_slug);
                $this->slug_model->insert(array(
                    'content_type'=>'category',
                    'content_id'=>$category_id,
                    'translation_id'=>$translation_id,
                    'language_slug'=>$language_slug,
                    'url'=>$url,
                    'created_by'=>$this->user_id));
                //$this->slug_model->where(array('content_type'=>'page','content_id'=>$page_id,'id !='=>$slug_id))->update(array('redirect'=>$slug_id));
            }

            redirect('admin/categories','refresh');

        }


    }

    public function edit($language_slug, $category_id)
    {
        $translation = $this->category_translation_model->where(array('category_id'=>$category_id, 'language_slug'=>$language_slug))->get();
        $this->data['content_language'] = $this->langs[$language_slug]['name'];
        if($translation == FALSE)
        {
            $this->session->set_flashdata('message', 'There is no translation for that category.');
            redirect('admin/categories', 'refresh');
        }
        $category = $this->category_model->get($category_id);
        if($category == FALSE)
        {
            $this->session->set_flashdata('message', 'There is no category to translate.');
            redirect('admin/categories', 'refresh');
        }
        $this->data['translation'] = $translation;
        $this->data['category'] = $category;
        $this->data['slugs'] = $this->slug_model->where(array('content_type'=>'category','translation_id'=>$translation->id))->get_all();
        $categories = $this->category_translation_model->where(array('language_slug'=>$language_slug,'category_id !='=>$category_id))->order_by('menu_title')->fields('category_id,id,menu_title')->get_all();
        $this->data['parent_categories'] = array('0'=>'No parent category');
        if(!empty($categories))
        {
            foreach($categories as $category)
            {
                $this->data['parent_categories'][$category->category_id] = $category->menu_title;
            }
        }

        $rules = $this->category_model->rules;
        $this->form_validation->set_rules($rules['update']);
        if($this->form_validation->run()===FALSE)
        {
            $this->render('admin/categories/edit_view');
        }
        else
        {
            $translation_id = $this->input->post('translation_id');
            if($translation = $this->category_translation_model->get($translation_id))
            {
                $parent_id = $this->input->post('parent_id');
                $title = $this->input->post('title');
                $menu_title = $this->input->post('menu_title');
                $slug = $this->input->post('slug');
                $order = $this->input->post('order');
                $page_title = (strlen($this->input->post('page_title')) > 0) ? $this->input->post('page_title') : $title;
                $page_description = $this->input->post('page_description');
                $page_keywords = $this->input->post('page_keywords');
                $category_id = $this->input->post('category_id');
                $language_slug = $this->input->post('language_slug');


                $update_data = array(
                    'title' => $title,
                    'menu_title' => $menu_title,
                    'page_title' => $page_title,
                    'page_description' => $page_description,
                    'page_keywords' => $page_keywords,
                    'updated_by' => $this->user_id);

                if ($this->category_translation_model->update($update_data, $translation_id))
                {
                    $this->category_model->update(array('parent_id' => $parent_id, 'order' => $order, 'updated_by' => $this->user_id), $category_id);
                    if(strlen($slug)>0)
                    {
                        $url = $this->_verify_slug($slug, $language_slug);
                        $new_slug = array(
                            'content_type' => 'category',
                            'content_id' => $category_id,
                            'translation_id' => $translation_id,
                            'language_slug' => $language_slug,
                            'url' => $url,
                            'created_by' => $this->user_id);
                        if($slug_id =  $this->slug_model->insert($new_slug))
                        {
                            $this->slug_model->where(array('content_type'=>'category', 'translation_id'=>$translation_id))->update(array('redirect'=>$slug_id,'updated_by'=>$this->user_id));
                        }
                    }
                    $this->session->set_flashdata('message', 'The translation was updated successfully.');
                }
            }
            else
            {
                $this->session->set_flashdata('message', 'There is no translation to update.');
            }
            redirect('admin/categories','refresh');
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
    public function delete($language_slug, $category_id)
    {
        if($category = $this->category_model->get($category_id))
        {
            if($language_slug=='all')
            {
                if($deleted_translations = $this->category_translation_model->where('category_id',$category_id)->delete())
                {
                    $deleted_slugs = $this->slug_model->where(array('content_type'=>'category','content_id'=>$category_id))->delete();
                    $deleted_categories = $this->category_model->delete($category_id);
                    $this->session->set_flashdata('message', $deleted_categories.' category deleted. There were also '.$deleted_translations.' translations and '.$deleted_slugs.' slugs deleted.');
                }
                else
                {
                    $deleted_categories = $this->category_model->delete($category_id);
                    $this->session->set_flashdata('message', $deleted_categories.' category was deleted');
                }
            }
            else
            {
                if($this->category_translation_model->where(array('category_id'=>$category_id,'language_slug'=>$language_slug))->delete())
                {
                    $deleted_slugs = $this->slug_model->where(array('content_type'=>'category','language_slug'=>$language_slug,'content_id'=>$category_id))->delete();
                    $this->session->set_flashdata('message', 'The translation and '.$deleted_slugs.' slugs were deleted.');
                }
            }
        }
        else
        {
            $this->session->set_flashdata('message', 'There is no translation to delete.');
        }
        redirect('admin/categories','refresh');

    }
}