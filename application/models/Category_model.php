<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Category_model extends CI_Model {

    public function get_data() {
        return $this->db->order_by('row_order', 'ASC')->get('tbl_category')->result();
    }

    public function update_order() {
        $id_ary = explode(",", $this->input->post('row_order'));
        for ($i = 0; $i < count($id_ary); $i++) {
            $this->db->query("UPDATE tbl_category SET row_order='$i' WHERE id='$id_ary[$i]'");
        }
    }

    public function add_data() {
        if (!is_dir(CATEGORY_IMG_PATH)) {
            mkdir(CATEGORY_IMG_PATH, 0777, TRUE);
        }
        $language = ($this->input->post('language_id')) ? $this->input->post('language_id') : 0;
        if ($_FILES['file']['name'] == '') {
            $frm_data = array(
                'language_id' => $language,
                'category_name' => $this->input->post('name'),
                'image' => '',
                'row_order' => 0
            );
            $this->db->insert('tbl_category', $frm_data);
            return TRUE;
        } else {
            $config['upload_path'] = CATEGORY_IMG_PATH;
            $config['allowed_types'] = IMG_ALLOWED_TYPES;
            $config['file_name'] = time();
            $this->load->library('upload', $config);
            $this->upload->initialize($config);

            if (!$this->upload->do_upload('file')) {
                return FALSE;
            } else {
                $data = $this->upload->data();
                $img = $data['file_name'];
                $frm_data = array(
                    'language_id' => $language,
                    'category_name' => $this->input->post('name'),
                    'image' => $img,
                    'row_order' => 0
                );
                $this->db->insert('tbl_category', $frm_data);
                return TRUE;
            }
        }
    }

    public function update_data() {
        if (!is_dir(CATEGORY_IMG_PATH)) {
            mkdir(CATEGORY_IMG_PATH, 0777, TRUE);
        }
        $id = $this->input->post('edit_id');

        if (is_language_mode_enabled()) {
            $language = ($this->input->post('language_id')) ? $this->input->post('language_id') : 0;
            $data = array('language_id' => $language);
            $this->db->where('id', $id)->update('tbl_category', $data);

            $this->db->where('maincat_id', $id)->update('tbl_subcategory', $data);
            $this->db->where('category', $id)->update('tbl_question', $data);
            $this->db->where('category', $id)->update('tbl_guess_the_word', $data);
            $this->db->where('category', $id)->update('tbl_fun_n_learn', $data);
        }

        $name = $this->input->post('name');

        if ($_FILES['update_file']['name'] == '') {
            $frm_data = array('category_name' => $name);
            $this->db->where('id', $id)->update('tbl_category', $frm_data);
            return TRUE;
        } else {
            $config['upload_path'] = CATEGORY_IMG_PATH;
            $config['allowed_types'] = IMG_ALLOWED_TYPES;
            $config['file_name'] = time();
            $this->load->library('upload', $config);
            $this->upload->initialize($config);

            if (!$this->upload->do_upload('update_file')) {
                return FALSE;
            } else {
                $image_url = $this->input->post('image_url');
                if (file_exists($image_url)) {
                    unlink($image_url);
                }

                $data = $this->upload->data();
                $img = $data['file_name'];
                $frm_data = array(
                    'category_name' => $name,
                    'image' => $img,
                );
                $this->db->where('id', $id)->update('tbl_category', $frm_data);
                return TRUE;
            }
        }
    }

    public function delete_data($id, $image_url) {
        //delete subcategory of this category
        $subcat = $this->db->where('maincat_id', $id)->get('tbl_subcategory')->result();
        foreach ($subcat as $value) {
            if (!empty($value->image) && file_exists(SUBCATEGORY_IMG_PATH . $value->image)) {
                unlink(SUBCATEGORY_IMG_PATH . $value->image);
            }
        }
        $this->db->where('maincat_id', $id)->delete('tbl_subcategory');

        //delete question of this category
        $que = $this->db->where('category', $id)->get('tbl_question')->result();
        foreach ($que as $que1) {
            if (!empty($que1->image) && file_exists(QUESTION_IMG_PATH . $que1->image)) {
                unlink(QUESTION_IMG_PATH . $que1->image);
            }
        }
        $this->db->where('category', $id)->delete('tbl_question');

        if (file_exists($image_url)) {
            unlink($image_url);
        }
        $this->db->where('id', $id)->delete('tbl_category');
        
         $compre = $this->db->where('category', $id)->get('tbl_fun_n_learn')->result();
        foreach ($compre as $compre1) {
            $this->db->where('fun_n_learn_id', $compre1->id)->delete('tbl_fun_n_learn_question');
        }
        $this->db->where('category', $id)->delete('tbl_fun_n_learn');

        $guess = $this->db->where('category', $id)->get('tbl_guess_the_word')->result();
        foreach ($guess as $guess1) {
            if (!empty($guess1->image) && file_exists(GUESS_WORD_IMG_PATH . $guess1->image)) {
                unlink(GUESS_WORD_IMG_PATH . $guess1->image);
            }
        }
        $this->db->where('category', $id)->delete('tbl_guess_the_word');
    }

}

?>