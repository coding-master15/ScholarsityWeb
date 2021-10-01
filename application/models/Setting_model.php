<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Setting_model extends CI_Model {

    public function update_profile() {

        $app_name = $this->input->post('app_name');
        $name = $this->db->where('type', 'app_name')->get('tbl_settings')->row_array();
        if ($name) {
            $frm_name = ['message' => $app_name];
            $this->db->where('type', 'app_name')->update('tbl_settings', $frm_name);
        } else {
            $frm_name = array(
                'type' => 'app_name',
                'message' => $app_name
            );
            $this->db->insert('tbl_settings', $frm_name);
        }

        $jwt_key = $this->input->post('jwt_key');
        $j_key = $this->db->where('type', 'jwt_key')->get('tbl_settings')->row_array();
        if ($j_key) {
            $frm_jwt_key = ['message' => $jwt_key];
            $this->db->where('type', 'jwt_key')->update('tbl_settings', $frm_jwt_key);
        } else {
            $frm_jwt_key = array(
                'type' => 'jwt_key',
                'message' => $jwt_key
            );
            $this->db->insert('tbl_settings', $frm_jwt_key);
        }

        $full_url = $this->input->post('full_url');
        $half_url = $this->input->post('half_url');

        if ($_FILES['full_file']['name'] != '' && $_FILES['half_file']['name'] != '') {
            //Full logo upload
            $config = array();
            $config['upload_path'] = LOGO_IMG_PATH;
            $config['allowed_types'] = IMG_ALLOWED_TYPES;
            $config['file_name'] = time();
            $this->load->library('upload', $config, 'fullupload'); // Create custom object for cover upload
            $this->fullupload->initialize($config);

            // half logo upload
            $config1 = array();
            $config1['upload_path'] = LOGO_IMG_PATH;
            $config1['allowed_types'] = IMG_ALLOWED_TYPES;
            $config1['file_name'] = time();
            $this->load->library('upload', $config1, 'halfupload');  // Create custom object for catalog upload
            $this->halfupload->initialize($config1);

            // Check uploads success
            if ($this->fullupload->do_upload('full_file') && $this->halfupload->do_upload('half_file')) {

                // Data of your full logo file
                $full_data = $this->fullupload->data();
                $full_file = $full_data['file_name'];

                if (file_exists($full_url)) {
                    unlink($full_url);
                }

                // Data of your half logo file
                $half_data = $this->halfupload->data();
                $half_file = $half_data['file_name'];

                if (file_exists($half_url)) {
                    unlink($half_url);
                }

                $Flogo = $this->db->where('type', 'full_logo')->get('tbl_settings')->row_array();
                if ($Flogo) {
                    $frm_Flogo = ['message' => $full_file];
                    $this->db->where('type', 'full_logo')->update('tbl_settings', $frm_Flogo);
                } else {
                    $frm_Flogo = array(
                        'type' => 'full_logo',
                        'message' => $full_file
                    );
                    $this->db->insert('tbl_settings', $frm_Flogo);
                }

                $Hlogo = $this->db->where('type', 'half_logo')->get('tbl_settings')->row_array();
                if ($Hlogo) {
                    $frm_Hlogo = ['message' => $half_file];
                    $this->db->where('type', 'half_logo')->update('tbl_settings', $frm_Hlogo);
                } else {
                    $frm_Hlogo = array(
                        'type' => 'half_logo',
                        'message' => $half_file
                    );
                    $this->db->insert('tbl_settings', $frm_Hlogo);
                }
                return TRUE;
            } else {
                return FALSE;
            }
        }

        if ($_FILES['full_file']['name'] != '' && $_FILES['half_file']['name'] == '') {
            $config['upload_path'] = LOGO_IMG_PATH;
            $config['allowed_types'] = IMG_ALLOWED_TYPES;
            $config['file_name'] = time();
            $this->load->library('upload', $config);
            $this->upload->initialize($config);

            if (!$this->upload->do_upload('full_file')) {
                return FALSE;
            } else {
                if (file_exists($full_url)) {
                    unlink($full_url);
                }

                $data = $this->upload->data();
                $img = $data['file_name'];
                $logo = $this->db->where('type', 'full_logo')->get('tbl_settings')->row_array();
                if ($logo) {
                    $frm_logo = ['message' => $img];
                    $this->db->where('type', 'full_logo')->update('tbl_settings', $frm_logo);
                } else {
                    $frm_logo = array(
                        'type' => 'full_logo',
                        'message' => $img
                    );
                    $this->db->insert('tbl_settings', $frm_logo);
                }
                return TRUE;
            }
        }

        if ($_FILES['half_file']['name'] != '' && $_FILES['full_file']['name'] == '') {
            $config['upload_path'] = LOGO_IMG_PATH;
            $config['allowed_types'] = IMG_ALLOWED_TYPES;
            $config['file_name'] = time();
            $this->load->library('upload', $config);
            $this->upload->initialize($config);

            if (!$this->upload->do_upload('half_file')) {
                return FALSE;
            } else {
                if (file_exists($half_url)) {
                    unlink($half_url);
                }
                $data = $this->upload->data();
                $img = $data['file_name'];
                $logo = $this->db->where('type', 'half_logo')->get('tbl_settings')->row_array();
                if ($logo) {
                    $frm_logo = ['message' => $img];
                    $this->db->where('type', 'half_logo')->update('tbl_settings', $frm_logo);
                } else {
                    $frm_logo = array(
                        'type' => 'half_logo',
                        'message' => $img
                    );
                    $this->db->insert('tbl_settings', $frm_logo);
                }
                return TRUE;
            }
        }
        return TRUE;
    }

    public function delete_multiple($ids, $is_image, $table) {
        if ($is_image) {
            $path = array(
                'tbl_category' => CATEGORY_IMG_PATH,
                'tbl_subcategory' => SUBCATEGORY_IMG_PATH,
                'tbl_question' => QUESTION_IMG_PATH,
                'tbl_notifications' => NOTIFICATION_IMG_PATH,
                'tbl_contest' => CONTEST_IMG_PATH,
                'tbl_contest_question' => CONTEST_QUESTION_IMG_PATH,
            );
            $query = $this->db->query("SELECT `image` FROM " . $table . " WHERE id in ( " . $ids . " )");
            $res = $query->result();
            foreach ($res as $image) {
                if (!empty($image->image) && file_exists($path[$table] . $image->image)) {
                    unlink($path[$table] . $image->image);
                }
            }
        }
        $delete = $this->db->query("DELETE FROM `" . $table . "` WHERE `id` in ( " . $ids . " ) ");
        return $delete ? 1 : 0;
    }

    public function update_settings() {

        $shareapp_text = $this->input->post('shareapp_text');
        $shareapp = $this->db->where('type', 'shareapp_text')->get('tbl_settings')->row_array();
        if ($shareapp) {
            $frm_shareapp_text = ['message' => $shareapp_text];
            $this->db->where('type', 'shareapp_text')->update('tbl_settings', $frm_shareapp_text);
        } else {
            $frm_shareapp_text = array(
                'type' => 'shareapp_text',
                'message' => $shareapp_text
            );
            $this->db->insert('tbl_settings', $frm_shareapp_text);
        }

        $battle_random_category_mode = $this->input->post('battle_random_category_mode');
        $battle = $this->db->where('type', 'battle_random_category_mode')->get('tbl_settings')->row_array();
        if ($battle) {
            $frm_battle = ['message' => $battle_random_category_mode];
            $this->db->where('type', 'battle_random_category_mode')->update('tbl_settings', $frm_battle);
        } else {
            $frm_battle = array(
                'type' => 'battle_random_category_mode',
                'message' => $battle_random_category_mode
            );
            $this->db->insert('tbl_settings', $frm_battle);
        }

        $battle_group_category_mode = $this->input->post('battle_group_category_mode');
        $room = $this->db->where('type', 'battle_group_category_mode')->get('tbl_settings')->row_array();
        if ($room) {
            $frm_room = ['message' => $battle_group_category_mode];
            $this->db->where('type', 'battle_group_category_mode')->update('tbl_settings', $frm_room);
        } else {
            $frm_room = array(
                'type' => 'battle_group_category_mode',
                'message' => $battle_group_category_mode
            );
            $this->db->insert('tbl_settings', $frm_room);
        }

        $contest_mode = $this->input->post('contest_mode');
        $contest = $this->db->where('type', 'contest_mode')->get('tbl_settings')->row_array();
        if ($contest) {
            $frm_contest_mode = ['message' => $contest_mode];
            $this->db->where('type', 'contest_mode')->update('tbl_settings', $frm_contest_mode);
        } else {
            $frm_contest_mode = array(
                'type' => 'contest_mode',
                'message' => $contest_mode
            );
            $this->db->insert('tbl_settings', $frm_contest_mode);
        }

        $daily_quiz_mode = $this->input->post('daily_quiz_mode');
        $daily_quiz = $this->db->where('type', 'daily_quiz_mode')->get('tbl_settings')->row_array();
        if ($daily_quiz) {
            $frm_daily_quiz_mode = ['message' => $daily_quiz_mode];
            $this->db->where('type', 'daily_quiz_mode')->update('tbl_settings', $frm_daily_quiz_mode);
        } else {
            $frm_daily_quiz_mode = array(
                'type' => 'daily_quiz_mode',
                'message' => $daily_quiz_mode
            );
            $this->db->insert('tbl_settings', $frm_daily_quiz_mode);
        }

        $force_update = $this->input->post('force_update');
        $force = $this->db->where('type', 'force_update')->get('tbl_settings')->row_array();
        if ($force) {
            $frm_force_update = ['message' => $force_update];
            $this->db->where('type', 'force_update')->update('tbl_settings', $frm_force_update);
        } else {
            $frm_force_update = array(
                'type' => 'force_update',
                'message' => $force_update
            );
            $this->db->insert('tbl_settings', $frm_force_update);
        }

        $total_question = $this->input->post('total_question');
        $question = $this->db->where('type', 'total_question')->get('tbl_settings')->row_array();
        if ($question) {
            $frm_total_question = ['message' => $total_question];
            $this->db->where('type', 'total_question')->update('tbl_settings', $frm_total_question);
        } else {
            $frm_total_question = array(
                'type' => 'total_question',
                'message' => $total_question
            );
            $this->db->insert('tbl_settings', $frm_total_question);
        }

        $fix_question = $this->input->post('fix_question');
        $fix = $this->db->where('type', 'fix_question')->get('tbl_settings')->row_array();
        if ($fix) {
            $frm_fix_question = ['message' => $fix_question];
            $this->db->where('type', 'fix_question')->update('tbl_settings', $frm_fix_question);
        } else {
            $frm_fix_question = array(
                'type' => 'fix_question',
                'message' => $fix_question
            );
            $this->db->insert('tbl_settings', $frm_fix_question);
        }

        $answer_mode = $this->input->post('answer_mode');
        $answer = $this->db->where('type', 'answer_mode')->get('tbl_settings')->row_array();
        if ($answer) {
            $frm_answer_mode = ['message' => $answer_mode];
            $this->db->where('type', 'answer_mode')->update('tbl_settings', $frm_answer_mode);
        } else {
            $frm_answer_mode = array(
                'type' => 'answer_mode',
                'message' => $answer_mode
            );
            $this->db->insert('tbl_settings', $frm_answer_mode);
        }

        $false_value = $this->input->post('false_value');
        $fvalue = $this->db->where('type', 'false_value')->get('tbl_settings')->row_array();
        if ($fvalue) {
            $frm_false_value = ['message' => $false_value];
            $this->db->where('type', 'false_value')->update('tbl_settings', $frm_false_value);
        } else {
            $frm_false_value = array(
                'type' => 'false_value',
                'message' => $false_value
            );
            $this->db->insert('tbl_settings', $frm_false_value);
        }

        $true_value = $this->input->post('true_value');
        $tvalue = $this->db->where('type', 'true_value')->get('tbl_settings')->row_array();
        if ($tvalue) {
            $frm_true_value = ['message' => $true_value];
            $this->db->where('type', 'true_value')->update('tbl_settings', $frm_true_value);
        } else {
            $frm_true_value = array(
                'type' => 'true_value',
                'message' => $true_value
            );
            $this->db->insert('tbl_settings', $frm_true_value);
        }

        $app_version = $this->input->post('app_version');
        $version = $this->db->where('type', 'app_version')->get('tbl_settings')->row_array();
        if ($version) {
            $frm_app_version = ['message' => $app_version];
            $this->db->where('type', 'app_version')->update('tbl_settings', $frm_app_version);
        } else {
            $frm_app_version = array(
                'type' => 'app_version',
                'message' => $app_version
            );
            $this->db->insert('tbl_settings', $frm_app_version);
        }

        $reward_coin = $this->input->post('reward_coin');
        $reward = $this->db->where('type', 'reward_coin')->get('tbl_settings')->row_array();
        if ($reward) {
            $frm_reward_coin = ['message' => $reward_coin];
            $this->db->where('type', 'reward_coin')->update('tbl_settings', $frm_reward_coin);
        } else {
            $frm_reward_coin = array(
                'type' => 'reward_coin',
                'message' => $reward_coin
            );
            $this->db->insert('tbl_settings', $frm_reward_coin);
        }

        $earn_coin = $this->input->post('earn_coin');
        $earn = $this->db->where('type', 'earn_coin')->get('tbl_settings')->row_array();
        if ($earn) {
            $frm_earn_coin = ['message' => $earn_coin];
            $this->db->where('type', 'earn_coin')->update('tbl_settings', $frm_earn_coin);
        } else {
            $frm_earn_coin = array(
                'type' => 'earn_coin',
                'message' => $earn_coin
            );
            $this->db->insert('tbl_settings', $frm_earn_coin);
        }

        $refer_coin = $this->input->post('refer_coin');
        $refer = $this->db->where('type', 'refer_coin')->get('tbl_settings')->row_array();
        if ($refer) {
            $frm_refer_coin = ['message' => $refer_coin];
            $this->db->where('type', 'refer_coin')->update('tbl_settings', $frm_refer_coin);
        } else {
            $frm_refer_coin = array(
                'type' => 'refer_coin',
                'message' => $refer_coin
            );
            $this->db->insert('tbl_settings', $frm_refer_coin);
        }

        $ios_more_apps = $this->input->post('ios_more_apps');
        $ios_more = $this->db->where('type', 'ios_more_apps')->get('tbl_settings')->row_array();
        if ($ios_more) {
            $frm_ios_more = ['message' => $ios_more_apps];
            $this->db->where('type', 'ios_more_apps')->update('tbl_settings', $frm_ios_more);
        } else {
            $frm_ios_more = array(
                'type' => 'ios_more_apps',
                'message' => $ios_more_apps
            );
            $this->db->insert('tbl_settings', $frm_ios_more);
        }

        $ios_app_link = $this->input->post('ios_app_link');
        $ios_app = $this->db->where('type', 'ios_app_link')->get('tbl_settings')->row_array();
        if ($ios_app) {
            $frm_ios_app = ['message' => $ios_app_link];
            $this->db->where('type', 'ios_app_link')->update('tbl_settings', $frm_ios_app);
        } else {
            $frm_ios_app = array(
                'type' => 'ios_app_link',
                'message' => $ios_app_link
            );
            $this->db->insert('tbl_settings', $frm_ios_app);
        }

        $more_apps = $this->input->post('more_apps');
        $app_m = $this->db->where('type', 'more_apps')->get('tbl_settings')->row_array();
        if ($app_m) {
            $frm_more_apps = ['message' => $more_apps];
            $this->db->where('type', 'more_apps')->update('tbl_settings', $frm_more_apps);
        } else {
            $frm_more_apps = array(
                'type' => 'more_apps',
                'message' => $more_apps
            );
            $this->db->insert('tbl_settings', $frm_more_apps);
        }

        $app_link = $this->input->post('app_link');
        $app = $this->db->where('type', 'app_link')->get('tbl_settings')->row_array();
        if ($app) {
            $frm_app_link = ['message' => $app_link];
            $this->db->where('type', 'app_link')->update('tbl_settings', $frm_app_link);
        } else {
            $frm_app_link = array(
                'type' => 'app_link',
                'message' => $app_link
            );
            $this->db->insert('tbl_settings', $frm_app_link);
        }

        $system_timezone_gmt = $this->input->post('system_timezone_gmt');
        $timezone_gmt = $this->db->where('type', 'system_timezone_gmt')->get('tbl_settings')->row_array();
        if ($timezone_gmt) {
            $frm_timezone_gmte = ['message' => $system_timezone_gmt];
            $this->db->where('type', 'system_timezone_gmt')->update('tbl_settings', $frm_timezone_gmte);
        } else {
            $frm_timezone_gmte = array(
                'type' => 'system_timezone_gmt',
                'message' => $system_timezone_gmt
            );
            $this->db->insert('tbl_settings', $frm_timezone_gmte);
        }

        $system_timezone = $this->input->post('system_timezone');
        $timezone = $this->db->where('type', 'system_timezone')->get('tbl_settings')->row_array();
        if ($timezone) {
            $frm_timezone = ['message' => $system_timezone];
            $this->db->where('type', 'system_timezone')->update('tbl_settings', $frm_timezone);
        } else {
            $frm_timezone = array(
                'type' => 'system_timezone',
                'message' => $system_timezone
            );
            $this->db->insert('tbl_settings', $frm_timezone);
        }

        $language_mode = $this->input->post('language_mode');
        $language = $this->db->where('type', 'language_mode')->get('tbl_settings')->row_array();
        if ($language) {
            $frm_language = ['message' => $language_mode];
            $this->db->where('type', 'language_mode')->update('tbl_settings', $frm_language);
        } else {
            $frm_language = array(
                'type' => 'language_mode',
                'message' => $language_mode
            );
            $this->db->insert('tbl_settings', $frm_language);
        }

        $option_e_mode = $this->input->post('option_e_mode');
        $e_mode = $this->db->where('type', 'option_e_mode')->get('tbl_settings')->row_array();
        if ($e_mode) {
            $frm_e_mode = ['message' => $option_e_mode];
            $this->db->where('type', 'option_e_mode')->update('tbl_settings', $frm_e_mode);
        } else {
            $frm_e_mode = array(
                'type' => 'option_e_mode',
                'message' => $option_e_mode
            );
            $this->db->insert('tbl_settings', $frm_e_mode);
        }
    }

    public function update_contact_us() {
        $message = $this->input->post('message');
        $data = $this->db->where('type', 'contact_us')->get('tbl_settings')->row_array();
        if ($data) {
            $frm_data = ['message' => $message];
            $this->db->where('type', 'contact_us')->update('tbl_settings', $frm_data);
        } else {
            $frm_data = array(
                'type' => 'contact_us',
                'message' => $message
            );
            $this->db->insert('tbl_settings', $frm_data);
        }
    }

    public function update_terms_conditions() {
        $message = $this->input->post('message');
        $data = $this->db->where('type', 'terms_conditions')->get('tbl_settings')->row_array();
        if ($data) {
            $frm_data = ['message' => $message];
            $this->db->where('type', 'terms_conditions')->update('tbl_settings', $frm_data);
        } else {
            $frm_data = array(
                'type' => 'terms_conditions',
                'message' => $message
            );
            $this->db->insert('tbl_settings', $frm_data);
        }
    }

    public function update_privacy_policy() {
        $message = $this->input->post('message');
        $data = $this->db->where('type', 'privacy_policy')->get('tbl_settings')->row_array();
        if ($data) {
            $frm_data = ['message' => $message];
            $this->db->where('type', 'privacy_policy')->update('tbl_settings', $frm_data);
        } else {
            $frm_data = array(
                'type' => 'privacy_policy',
                'message' => $message
            );
            $this->db->insert('tbl_settings', $frm_data);
        }
    }

    public function update_instructions() {
        $message = $this->input->post('message');
        $data = $this->db->where('type', 'instructions')->get('tbl_settings')->row_array();
        if ($data) {
            $frm_data = ['message' => $message];
            $this->db->where('type', 'instructions')->update('tbl_settings', $frm_data);
        } else {
            $frm_data = array(
                'type' => 'instructions',
                'message' => $message
            );
            $this->db->insert('tbl_settings', $frm_data);
        }
    }

    public function update_about_us() {
        $message = $this->input->post('message');
        $data = $this->db->where('type', 'about_us')->get('tbl_settings')->row_array();
        if ($data) {
            $frm_data = ['message' => $message];
            $this->db->where('type', 'about_us')->update('tbl_settings', $frm_data);
        } else {
            $frm_data = array(
                'type' => 'about_us',
                'message' => $message
            );
            $this->db->insert('tbl_settings', $frm_data);
        }
    }

    public function update_fcm_key() {
        $message = $this->input->post('message');
        $data = $this->db->where('type', 'fcm_server_key')->get('tbl_settings')->row_array();
        if ($data) {
            $frm_data = ['message' => $message];
            $this->db->where('type', 'fcm_server_key')->update('tbl_settings', $frm_data);
        } else {
            $frm_data = array(
                'type' => 'fcm_server_key',
                'message' => $message
            );
            $this->db->insert('tbl_settings', $frm_data);
        }
    }

}
