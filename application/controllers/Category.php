<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Category extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('isLoggedIn')) {
            redirect('/');
        }
        $this->load->config('quiz');
        date_default_timezone_set(get_system_timezone());

        $this->result['full_logo'] = $this->db->where('type', 'full_logo')->get('tbl_settings')->row_array();
        $this->result['half_logo'] = $this->db->where('type', 'half_logo')->get('tbl_settings')->row_array();
        $this->result['app_name'] = $this->db->where('type', 'app_name')->get('tbl_settings')->row_array();

        $this->result['system_key'] = $this->db->where('type', 'system_key')->get('tbl_settings')->row_array();
        $this->result['configuration_key'] = $this->db->where('type', 'configuration_key')->get('tbl_settings')->row_array();
    }

    public function index() {
        if (!has_permissions('read', 'categories')) {
            redirect('/', 'refresh');
        } else {
            if ($this->input->post('btnadd')) {
                if (!has_permissions('create', 'categories')) {
                    $this->session->set_flashdata('error', PERMISSION_ERROR_MSG);
                } else {
                    $data = $this->Category_model->add_data();
                    if ($data == FALSE) {
                        $this->session->set_flashdata('error', IMAGE_ALLOW_MSG);
                    } else {
                        $this->session->set_flashdata('success', 'Category created successfully.! ');
                    }
                }
                redirect('main-category', 'refresh');
            }
            if ($this->input->post('btnupdate')) {
                if (!has_permissions('update', 'categories')) {
                    $this->session->set_flashdata('error', PERMISSION_ERROR_MSG);
                } else {
                    $data1 = $this->Category_model->update_data();
                    if ($data1 == FALSE) {
                        $this->session->set_flashdata('error', IMAGE_ALLOW_MSG);
                    } else {
                        $this->session->set_flashdata('success', 'Category updated successfully.!');
                    }
                }
                redirect('main-category', 'refresh');
            }
            $this->result['language'] = $this->Language_model->get_data();
            $this->load->view('category', $this->result);
        }
    }

    public function delete_category() {
        if (!has_permissions('delete', 'categories')) {
            echo FALSE;
        } else {
            $id = $this->input->post('id');
            $image_url = $this->input->post('image_url');
            $this->Category_model->delete_data($id, $image_url);
            echo TRUE;
        }
    }

    public function category_order() {
        if (!has_permissions('read', 'category_order')) {
            redirect('/', 'refresh');
        } else {
            if ($this->input->post('btnaddcategory')) {
                if (!has_permissions('update', 'category_order')) {
                    $this->session->set_flashdata('error', PERMISSION_ERROR_MSG);
                } else {
                    $this->Category_model->update_order();
                    $this->session->set_flashdata('success', 'Category order update successfully.! ');
                }
                redirect('category-order', 'refresh');
            }
            if ($this->input->post('btnaddsubcategory')) {
                if (!has_permissions('update', 'category_order')) {
                    $this->session->set_flashdata('error', PERMISSION_ERROR_MSG);
                } else {
                    $this->Subcategory_model->update_order();
                    $this->session->set_flashdata('success', 'Subcategory order update successfully.! ');
                }
                redirect('category-order', 'refresh');
            }
            $this->result['language'] = $this->Language_model->get_data();
            $this->result['category'] = $this->Category_model->get_data();
            $this->result['subcategory'] = $this->Subcategory_model->get_data();
            $this->load->view('category_order', $this->result);
        }
    }

}
