<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Subcategory extends CI_Controller {

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

    public function sub_category() {
        if (!has_permissions('read', 'subcategories')) {
            redirect('/', 'refresh');
        } else {
            if ($this->input->post('btnadd')) {
                if (!has_permissions('create', 'subcategories')) {
                    $this->session->set_flashdata('error', PERMISSION_ERROR_MSG);
                } else {
                    $data = $this->Subcategory_model->add_data();
                    if ($data == FALSE) {
                        $this->session->set_flashdata('error', IMAGE_ALLOW_MSG);
                    } else {
                        $this->session->set_flashdata('success', 'Subcategory created successfully.! ');
                    }
                }
                redirect('sub-category', 'refresh');
            }
            if ($this->input->post('btnupdate')) {
                if (!has_permissions('update', 'subcategories')) {
                    $this->session->set_flashdata('error', PERMISSION_ERROR_MSG);
                } else {
                    $data1 = $this->Subcategory_model->update_data();
                    if ($data1 == FALSE) {
                        $this->session->set_flashdata('error', IMAGE_ALLOW_MSG);
                    } else {
                        $this->session->set_flashdata('success', 'Subcategory updated successfully.!');
                    }
                }
                redirect('sub-category', 'refresh');
            }
            $this->result['language'] = $this->Language_model->get_data();
            $this->result['category'] = $this->Category_model->get_data();
            $this->load->view('sub_category', $this->result);
        }
    }

    public function delete_subcategory() {
        if (!has_permissions('delete', 'subcategories')) {
            echo FALSE;
        } else {
            $id = $this->input->post('id');
            $image_url = $this->input->post('image_url');
            $this->Subcategory_model->delete_data($id, $image_url);
            echo TRUE;
        }
    }

}

?>