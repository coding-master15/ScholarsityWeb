<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Guess_Word extends CI_Controller {

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

        $this->result['category'] = $this->Category_model->get_data();
        $this->result['subcategory'] = $this->Subcategory_model->get_data();
        
        $this->result['system_key'] = $this->db->where('type', 'system_key')->get('tbl_settings')->row_array();
        $this->result['configuration_key'] = $this->db->where('type', 'configuration_key')->get('tbl_settings')->row_array();
    }

    public function index() {
        if (!has_permissions('read', 'guess_the_word')) {
            redirect('/', 'refresh');
        } else {
            if ($this->input->post('btnadd')) {
                if (!has_permissions('create', 'guess_the_word')) {
                    $this->session->set_flashdata('error', PERMISSION_ERROR_MSG);
                } else {
                    $data = $this->Guess_Word_model->add_data();
                    if ($data == FALSE) {
                        $this->session->set_flashdata('error', IMAGE_ALLOW_MSG);
                    } else {
                        $this->session->set_flashdata('success', 'Question created successfully.! ');
                    }
                }
                redirect('guess-the-word', 'refresh');
            }
            if ($this->input->post('btnupdate')) {
                if (!has_permissions('update', 'guess_the_word')) {
                    $this->session->set_flashdata('error', PERMISSION_ERROR_MSG);
                } else {
                    $data1 = $this->Guess_Word_model->update_data();
                    if ($data1 == FALSE) {
                        $this->session->set_flashdata('error', IMAGE_ALLOW_MSG);
                    } else {
                        $this->session->set_flashdata('success', 'Question updated successfully.!');
                    }
                }
                redirect('guess-the-word', 'refresh');
            }
            $this->result['language'] = $this->Language_model->get_data();
            $this->load->view('guess_the_word', $this->result);
        }
    }

    public function delete_guess_word() {
        if (!has_permissions('delete', 'guess_the_word')) {
            echo FALSE;
        } else {
            $id = $this->input->post('id');
            $image_url = $this->input->post('image_url');
            $this->Guess_Word_model->delete_data($id, $image_url);
            echo TRUE;
        }
    }

}
