<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Language_model extends CI_Model {

    public function get_all_lang() {
        return $this->db->order_by('language', 'ASC')->get('tbl_languages')->result();
    }

    public function get_data() {
        return $this->db->where('status', 1)->order_by('id', 'DESC')->get('tbl_languages')->result();
    }

    public function add_data() {
        $language_id = $this->input->post('language_id');
        $data = array(
            'status' => 1,
            'type' => 1
        );
        $this->db->where('id', $language_id)->update('tbl_languages', $data);
//        $data = array(
//            'language' => $this->input->post('language'),
//            'status' => 1,
//            'date_created' => date('Y-m-d H:i:s')
//        );
//        $this->db->insert('tbl_languages', $data);
    }

    public function update_data() {
        $id = $this->input->post('edit_id');
        $data = array('status' => $this->input->post('status'));
        $this->db->where('id', $id)->update('tbl_languages', $data);
    }

    public function delete_data($id) {
        $data = array(
            'status' => 0,
            'type' => 0
        );
        $this->db->where('id', $id)->update('tbl_languages', $data);

        $cat = $this->db->where('language_id', $id)->get('tbl_category')->result();
        foreach ($cat as $cat1) {
            if (!empty($cat1->image) && file_exists(CATEGORY_IMG_PATH . $cat1->image)) {
                unlink(CATEGORY_IMG_PATH . $cat1->image);
            }
        }
        $this->db->where('language_id', $id)->delete('tbl_category');

        $subcat = $this->db->where('language_id', $id)->get('tbl_subcategory')->result();
        foreach ($subcat as $subcat1) {
            if (!empty($subcat1->image) && file_exists(SUBCATEGORY_IMG_PATH . $subcat1->image)) {
                unlink(SUBCATEGORY_IMG_PATH . $subcat1->image);
            }
        }
        $this->db->where('language_id', $id)->delete('tbl_subcategory');

        $que = $this->db->where('language_id', $id)->get('tbl_question')->result();
        foreach ($que as $que1) {
            if (!empty($que1->image) && file_exists(QUESTION_IMG_PATH . $que1->image)) {
                unlink(QUESTION_IMG_PATH . $que1->image);
            }
        }
        $this->db->where('language_id', $id)->delete('tbl_question');

        $compre = $this->db->where('language_id', $id)->get('tbl_fun_n_learn')->result();
        foreach ($compre as $compre1) {
            $this->db->where('fun_n_learn_id', $compre1->id)->delete('tbl_fun_n_learn_question');
        }
        $this->db->where('language_id', $id)->delete('tbl_fun_n_learn');

        $guess = $this->db->where('language_id', $id)->get('tbl_guess_the_word')->result();
        foreach ($guess as $guess1) {
            if (!empty($guess1->image) && file_exists(GUESS_WORD_IMG_PATH . $guess1->image)) {
                unlink(GUESS_WORD_IMG_PATH . $guess1->image);
            }
        }
        $this->db->where('language_id', $id)->delete('tbl_guess_the_word');

        $this->db->where('language_id', $id)->delete('tbl_daily_quiz');
    }

}

?>