<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Fun_N_Learn extends CI_Controller {

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
        if (!has_permissions('read', 'fun_n_learn')) {
            redirect('/', 'refresh');
        } else {
            if ($this->input->post('btnadd')) {
                if (!has_permissions('create', 'fun_n_learn')) {
                    $this->session->set_flashdata('error', PERMISSION_ERROR_MSG);
                } else {
                    $this->Fun_N_Learn_model->add_data();
                    $this->session->set_flashdata('success', 'Fun N Learn created successfully.! ');
                }
                redirect('fun-n-learn', 'refresh');
            }
            if ($this->input->post('btnupdate')) {
                if (!has_permissions('update', 'fun_n_learn')) {
                    $this->session->set_flashdata('error', PERMISSION_ERROR_MSG);
                } else {
                    $this->Fun_N_Learn_model->update_data();
                    $this->session->set_flashdata('success', 'Fun N Learn updated successfully.!');
                }
                redirect('fun-n-learn', 'refresh');
            }
            if ($this->input->post('btnupdatestatus')) {
                if (!has_permissions('update', 'fun_n_learn')) {
                    $this->session->set_flashdata('error', PERMISSION_ERROR_MSG);
                } else {
                    $contest_id = $this->input->post('update_id');
                    $res = $this->db->where('fun_n_learn_id', $contest_id)->get('tbl_fun_n_learn_question')->result();
                    if (empty($res)) {
                        $this->session->set_flashdata('error', 'No enought question for active fun n learn.!');
                    } else {
                        $this->Fun_N_Learn_model->update_fun_n_learn_status();
                        $this->session->set_flashdata('success', 'Fun N Learn updated successfully.!');
                    }
                }
                redirect('fun-n-learn', 'refresh');
            }
            $this->result['language'] = $this->Language_model->get_data();
            $this->load->view('fun_n_learn', $this->result);
        }
    }

    public function delete_fun_n_learn() {
        if (!has_permissions('delete', 'fun_n_learn')) {
            echo FALSE;
        } else {
            $id = $this->input->post('id');
            $this->Fun_N_Learn_model->delete_data($id);
            echo TRUE;
        }
    }

    public function fun_n_learn_questions($id) {
        if (!has_permissions('read', 'fun_n_learn')) {
            redirect('/', 'refresh');
        } else {
            if ($this->input->post('btnadd')) {
                if (!has_permissions('create', 'fun_n_learn')) {
                    $this->session->set_flashdata('error', PERMISSION_ERROR_MSG);
                } else {
                    $this->Fun_N_Learn_model->add_fun_n_learn_question();
                    $this->session->set_flashdata('success', 'Question created successfully.! ');
                }
                redirect('fun-n-learn-questions/' . $id, 'refresh');
            }
            if ($this->input->post('btnupdate')) {
                if (!has_permissions('update', 'fun_n_learn')) {
                    $this->session->set_flashdata('error', PERMISSION_ERROR_MSG);
                } else {
                    $this->Fun_N_Learn_model->update_fun_n_learn_question();
                    $this->session->set_flashdata('success', 'Question updated successfully.!');
                }
                redirect('fun-n-learn-questions/' . $id, 'refresh');
            }

            $this->result['fun_n_learn'] = $this->Fun_N_Learn_model->get_data();
            $this->load->view('fun_n_learn_questions', $this->result);
        }
    }

    public function delete_fun_n_learn_questions() {
        if (!has_permissions('delete', 'fun_n_learn')) {
            echo FALSE;
        } else {
            $id = $this->input->post('id');
            $this->Fun_N_Learn_model->delete_fun_n_learn_questions($id);
            echo TRUE;
        }
    }

}

?>