<?php

defined('BASEPATH') OR exit('No direct script access allowed');
/*
  API v1.0.4
  Elite Quiz - WRTeam.in
  WRTeam Developers
 */
require APPPATH . '/libraries/REST_Controller.php';

class Api extends REST_Controller {

    public function __construct() {
        parent::__construct();
        $this->AccessKey = '8525';
        $this->load->database();

        date_default_timezone_set(get_system_timezone());

        $this->toDate = date('Y-m-d');
        $this->toDateTime = date('Y-m-d H:i:s');

        $this->load->library('JWT');
        $jwtKey = $this->db->where('type', 'jwt_key')->get('tbl_settings')->row_array();
        $jwtKey = $jwtKey['message'];
        $this->JWT_SECRET_KEY = "$jwtKey";

        $this->Order_By = 'rand()';
    }

    public function get_guess_the_word_post() {
        if (!$this->verify_token()) {
            return FALSE;
        }
        if ($this->AccessKey != $this->post('access_key')) {
            $response['error'] = true;
            $response['message'] = "101";
        } else {
            if ($this->post('type') && $this->post('type_id')) {
                $type = $this->post('type');
                $type_id = $this->post('type_id');

                if ($this->post('language_id')) {
                    $language_id = $this->post('language_id');
                    $this->db->where('language_id', $language_id);
                }
                $this->db->where($type, $type_id);
                $this->db->limit(10, 0);
                $this->db->order_by($this->Order_By);
                $data = $this->db->get('tbl_guess_the_word c')->result_array();
                if (!empty($data)) {
                    for ($i = 0; $i < count($data); $i++) {
                        $data[$i]['image'] = ($data[$i]['image']) ? base_url() . GUESS_WORD_IMG_PATH . $data[$i]['image'] : '';
                    }
                    $response['error'] = false;
                    $response['data'] = $data;
                } else {
                    $response['error'] = true;
                    $response['message'] = "102";
                }
            } else {
                $response['error'] = true;
                    $response['message'] = "103";
            }
        }
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function get_fun_n_learn_questions_post() {
        if (!$this->verify_token()) {
            return FALSE;
        }
        if ($this->AccessKey != $this->post('access_key')) {
            $response['error'] = true;
            $response['message'] = "101";
        } else {
            if ($this->post('fun_n_learn_id')) {
                $fun_n_learn_id = $this->post('fun_n_learn_id');
                $this->db->where('fun_n_learn_id', $fun_n_learn_id);
                $this->db->order_by($this->Order_By);
                $data = $this->db->get('tbl_fun_n_learn_question')->result_array();
                if (!empty($data)) {
                    for ($i = 0; $i < count($data); $i++) {
                        $data[$i]['optione'] = (is_option_e_mode_enabled() && $data[$i]['optione'] != null) ? trim($data[$i]['optione']) : '';
                        $data[$i]['optiona'] = trim($data[$i]['optiona']);
                        $data[$i]['optionb'] = trim($data[$i]['optionb']);
                        $data[$i]['optionc'] = trim($data[$i]['optionc']);
                        $data[$i]['optiond'] = trim($data[$i]['optiond']);
                    }
                    $response['error'] = false;
                    $response['data'] = $data;
                } else {
                    $response['error'] = true;
                    $response['message'] = "102";
                }
            } else {
                $response['error'] = true;
                $response['message'] = "103";
            }
        }
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function get_fun_n_learn_post() {
        if (!$this->verify_token()) {
            return FALSE;
        }
        if ($this->AccessKey != $this->post('access_key')) {
            $response['error'] = true;
            $response['message'] = "101";
        } else {
            if ($this->post('type') && $this->post('type_id')) {
                $type = $this->post('type');
                $type_id = $this->post('type_id');

                $this->db->select('c.*, (select count(id) from tbl_fun_n_learn_question q where q.fun_n_learn_id=c.id ) as no_of_que');
                if ($this->post('id')) {
                    $id = $this->post('id');
                    $this->db->where('id', $id);
                }
                if ($this->post('language_id')) {
                    $language_id = $this->post('language_id');
                    $this->db->where('language_id', $language_id);
                }
                $this->db->where($type, $type_id);
                $this->db->where('status', 1);
                $this->db->order_by('id', 'DESC');
                $data = $this->db->get('tbl_fun_n_learn c')->result_array();
                if (!empty($data)) {
                    $response['error'] = false;
                    $response['data'] = $data;
                } else {
                    $response['error'] = true;
                    $response['message'] = "102";
                }
            } else {
                $response['error'] = true;
                $response['message'] = "103";
            }
        }
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function invite_friend_post() {
        if (!$this->verify_token()) {
            return FALSE;
        }
        if ($this->AccessKey != $this->post('access_key')) {
            $response['error'] = true;
            $response['message'] = "101";
        } else {
            if ($this->post('user_id') && $this->post('room_id') && $this->post('invited_id') && $this->post('room_key')) {
                $user_id = $this->post('user_id');
                $room_id = $this->post('room_id');
                $invited_id = $this->post('invited_id');
                $room_key = $this->post('room_key');

                //get user name
                $res = $this->db->where('id', $user_id)->get('tbl_users')->row_array();
                $user_name = $res['name'];

                //get fcm_key 
                $data = $this->db->where('type', 'fcm_server_key')->get('tbl_settings')->row_array();
                define('API_ACCESS_KEY', $data['message']);

                //get user fcm_id 
                $fcm_id = $this->get_fcm_id($invited_id);

                $title = 'Quiz';
                $message = $user_name . ' is Inviting for Quiz Battle';

                $newMsg = array();
                $success = $failure = 0;

                $fcmMsg = array(
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'type' => 'invite',
                    'room_key' => $room_key,
                    'title' => $title,
                    'message' => $message,
                    'room_id' => $room_id
                );
//                $newMsg['data'] = $fcmMsg;

                $fcmFields = array(
                    'to' => $fcm_id,
                    'priority' => 'high',
                    'notification' => $fcmMsg,
                    'data' => $fcmMsg
                );
                $headers = array(
                    'Authorization: key=' . API_ACCESS_KEY,
                    'Content-Type: application/json'
                );

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmFields));

                $result = curl_exec($ch);
                curl_close($ch);

                $result = json_decode($result, 1);

                if ($result['success']) {
                    $response['error'] = false;
                    $response['message'] = "123";
                } else {
                    $response['error'] = true;
                    $response['message'] = "122";
                }
            } else {
                $response['error'] = true;
                $response['message'] = "103";
            }
        }
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function get_public_room_post() {
        if (!$this->verify_token()) {
            return FALSE;
        }
        if ($this->AccessKey != $this->post('access_key')) {
            $response['error'] = true;
            $response['message'] = "101";
        } else {
            $this->db->select('r.id, r.room_id, r.user_id, r.room_type, r.category_id, r.no_of_que, r.date_created, u.name');
            $this->db->join('tbl_users u', 'u.id=r.user_id');
            $this->db->where('room_type', 'public')->order_by('r.id', 'DESC');
            $res = $this->db->get('tbl_rooms r')->result_array();
            if (!empty($res)) {
                $response['error'] = false;
                $response['data'] = $res;
            } else {
                $response['error'] = true;
                $response['message'] = "102";
            }
        }
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function destroy_room_by_room_id_post() {
        if (!$this->verify_token()) {
            return FALSE;
        }
        if ($this->AccessKey != $this->post('access_key')) {
            $response['error'] = true;
            $response['message'] = "101";
        } else {
            if ($this->post('room_id')) {
                $room_id = $this->post('room_id');
                $this->db->where('room_id', $room_id)->delete('tbl_rooms');

                $response['error'] = false;
                $response['message'] = "121";
            } else {
                $response['error'] = true;
                $response['message'] = "103";
            }
        }
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function get_question_by_room_id_post() {
        if (!$this->verify_token()) {
            return FALSE;
        }
        if ($this->AccessKey != $this->post('access_key')) {
            $response['error'] = true;
            $response['message'] = "101";
        } else {
            if ($this->post('room_id')) {
                $room_id = $this->post('room_id');

                $res = $this->db->where('room_id', $room_id)->get('tbl_rooms')->row_array();
                if (empty($res)) {
                    $response['error'] = true;
                    $response['message'] = "102";
                } else {
                    $res = json_decode($res['questions'], 1);
                    foreach ($res as $row) {
                        $row['image'] = (!empty($row['image'])) ? base_url() . QUESTION_IMG_PATH . $row['image'] : '';
                        $row['optione'] = (is_option_e_mode_enabled() && $row['optione'] != null) ? $row['optione'] : '';
                        $row['optiona'] = trim($row['optiona']);
                        $row['optionb'] = trim($row['optionb']);
                        $row['optionc'] = trim($row['optionc']);
                        $row['optiond'] = trim($row['optiond']);
                        $temp[] = $row;
                    }
                    $res[0]['questions'] = json_encode($temp);

                    $response['error'] = false;
                    $response['data'] = json_decode($res[0]['questions']);
                }
            } else {
                $response['error'] = true;
                $response['message'] = "103";
            }
        }
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function create_room_post() {
        if (!$this->verify_token()) {
            return FALSE;
        }
        if ($this->AccessKey != $this->post('access_key')) {
            $response['error'] = true;
            $response['message'] = "101";
        } else {
            if ($this->post('user_id') && $this->post('room_id') && $this->post('room_type') && $this->post('no_of_que')) {
                $user_id = $this->post('user_id');
                $room_id = $this->post('room_id');
                $room_type = $this->post('room_type');
                $no_of_que = $this->post('no_of_que');

                $language_id = ($this->post('language_id')) ? $this->post('language_id') : 0;

                if ($this->post('category')) {
                    $category = $this->post('category');
                } else {
                    $category = '';
                }

                $res1 = $this->db->where('room_id', $room_id)->get('tbl_rooms')->row_array();
                if (empty($res1)) {
                    if (!empty($language_id)) {
                        $this->db->where('language_id', $language_id);
                    }
                    if (!empty($category)) {
                        $this->db->where('category', $category);
                    }
                    $this->db->order_by($this->Order_By)->limit($no_of_que);
                    $res = $this->db->get('tbl_question')->result_array();

                    if (empty($res)) {
                        $response['error'] = true;
                        $response['message'] = "102";
                    } else {
                        $questions = json_encode($res);

                        $frm_data = array(
                            'room_id' => $room_id,
                            'user_id' => $user_id,
                            'room_type' => $room_type,
                            'category_id' => $category,
                            'no_of_que' => $no_of_que,
                            'questions' => $questions,
                            'date_created' => $this->toDateTime
                        );
                        $this->db->insert('tbl_rooms', $frm_data);

                        $response['error'] = false;
                        $response['message'] = "120";
                    }
                } else {
                    $response['error'] = true;
                    $response['message'] = "119";
                }
            } else {
                $response['error'] = true;
                $response['message'] = "103";
            }
        }
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function get_contest_leaderboard_post() {
        if (!$this->verify_token()) {
            return FALSE;
        }
        if ($this->AccessKey != $this->post('access_key')) {
            $response['error'] = true;
            $response['message'] = "101";
        } else {
            if ($this->post('contest_id')) {
                $contest_id = $this->post('contest_id');
                $offset = ($this->post('offset') && is_numeric($this->post('offset'))) ? $this->post('offset') : 0;
                $limit = ($this->post('limit') && is_numeric($this->post('limit'))) ? $this->post('limit') : 25;

                $res = $this->db->query("SELECT r.*,u.name,u.profile FROM (SELECT s.*, @user_rank := @user_rank + 1 user_rank FROM ( SELECT user_id, score FROM tbl_contest_leaderboard c join tbl_users u on u.id = c.user_id  WHERE contest_id=" . $contest_id . " ) s, (SELECT @user_rank := 0) init ORDER BY score DESC ) r INNER join tbl_users u on u.id = r.user_id ORDER BY r.user_rank ASC LIMIT $offset,$limit")->result_array();
                for ($i = 0; $i < count($res); $i++) {
                    if (filter_var($res[$i]['profile'], FILTER_VALIDATE_URL) === FALSE) {
                        // Not a valid URL. Its a image only or empty
                        $res[$i]['profile'] = (!empty($res[$i]['profile'])) ? base_url() . USER_IMG_PATH . $res[$i]['profile'] : '';
                    }
                }
                if ($this->post('user_id')) {
                    $user_id = $this->post('user_id');

                    $my_rank = $this->db->query("SELECT r.*,u.name,u.profile FROM (SELECT s.*, @user_rank := @user_rank + 1 user_rank FROM ( SELECT user_id, score FROM tbl_contest_leaderboard c join tbl_users u on u.id = c.user_id  WHERE contest_id=" . $contest_id . " ) s, (SELECT @user_rank := 0) init ORDER BY score DESC ) r INNER join tbl_users u on u.id = r.user_id WHERE user_id = '" . $user_id . "' ORDER BY r.user_rank ASC")->result_array();
                    if (!empty($my_rank)) {
                        if (filter_var($my_rank[0]['profile'], FILTER_VALIDATE_URL) === FALSE) {
                            // Not a valid URL. Its a image only or empty
                            $my_rank[0]['profile'] = (!empty($my_rank[0]['profile'])) ? base_url() . USER_IMG_PATH . $my_rank[0]['profile'] : '';
                        }
                        $response['my_rank'] = $my_rank[0];
                    }
                }
                if (empty($res)) {
                    $response['error'] = true;
                    $response['message'] = "102";
                } else {
                    $response['error'] = false;
                    $response['data'] = $res;
                }
            } else {
                $response['error'] = true;
                $response['message'] = "103";
            }
        }
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function set_contest_leaderboard_post() {
        if (!$this->verify_token()) {
            return FALSE;
        }
        if ($this->AccessKey != $this->post('access_key')) {
            $response['error'] = true;
            $response['message'] = "101";
        } else {
            if ($this->post('user_id') && $this->post('contest_id') && $this->post('questions_attended') != '' && $this->post('correct_answers') != '' && $this->post('score') != '') {
                $user_id = $this->post('user_id');
                $contest_id = $this->post('contest_id');
                $questions_attended = $this->post('questions_attended');
                $correct_answers = $this->post('correct_answers');
                $score = $this->post('score');

                $res = $this->db->where('contest_id', $contest_id)->where('user_id', $user_id)->get('tbl_contest_leaderboard')->result_array();
                if (empty($res)) {
                    $data = array(
                        'user_id' => $user_id,
                        'contest_id' => $contest_id,
                        'questions_attended' => $questions_attended,
                        'correct_answers' => $correct_answers,
                        'score' => $score,
                        'last_updated' => $this->toDateTime,
                        'date_created' => $this->toDateTime
                    );
                    $this->db->insert('tbl_contest_leaderboard', $data);
                } else {
                    $data = array(
                        'questions_attended' => $questions_attended,
                        'correct_answers' => $correct_answers,
                        'score' => $score,
                        'last_updated' => $this->toDateTime,
                    );
                    $this->db->where('id', $res[0]['id'])->where('contest_id', $contest_id)->where('user_id', $user_id)->update('tbl_contest_leaderboard', $data);
                }
                $this->set_monthly_leaderboard($user_id, $score);

                $response['error'] = false;
                $response['message'] = "111";
            } else {
                $response['error'] = true;
                $response['message'] = "103";
            }
        }
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function get_questions_by_contest_post() {
        if (!$this->verify_token()) {
            return FALSE;
        }
        if ($this->AccessKey != $this->post('access_key')) {
            $response['error'] = true;
            $response['message'] = "101";
        } else {
            if ($this->post('contest_id')) {
                $contest_id = $this->post('contest_id');
                $data = $this->db->where('contest_id', $contest_id)->order_by($this->Order_By)->get('tbl_contest_question')->result_array();
                if (!empty($data)) {
                    for ($i = 0; $i < count($data); $i++) {
                        $data[$i]['image'] = ($data[$i]['image']) ? base_url() . CONTEST_QUESTION_IMG_PATH . $data[$i]['image'] : '';
                        $data[$i]['optione'] = (is_option_e_mode_enabled() && $data[$i]['optione'] != null) ? trim($data[$i]['optione']) : '';
                        $data[$i]['optiona'] = trim($data[$i]['optiona']);
                        $data[$i]['optionb'] = trim($data[$i]['optionb']);
                        $data[$i]['optionc'] = trim($data[$i]['optionc']);
                        $data[$i]['optiond'] = trim($data[$i]['optiond']);
                    }
                    $response['error'] = false;
                    $response['data'] = $data;
                } else {
                    $response['error'] = true;
                    $response['message'] = "102";
                }
            } else {
                $response['error'] = true;
                $response['message'] = "103";
            }
        }
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function get_contest_post() {
        if (!$this->verify_token()) {
            return FALSE;
        }
        if ($this->AccessKey != $this->post('access_key')) {
            $response['error'] = true;
            $response['message'] = "101";
        } else {
            if ($this->post('user_id')) {
                $user_id = $this->post('user_id');
                /* selecting live quiz ids */
                $result = $this->db->query("SELECT id FROM tbl_contest where ('$this->toDate') between CAST(start_date AS DATE) and CAST(end_date AS DATE)")->result_array();

                $live_type_ids = $past_type_ids = '';
                if (!empty($result)) {
                    foreach ($result as $type_id) {
                        $live_type_ids .= $type_id['id'] . ', ';
                    }
                    $live_type_ids = rtrim($live_type_ids, ', ');

                    /* getting past quiz ids & its data which user has played */
                    $result = $this->db->query("SELECT contest_id FROM tbl_contest_leaderboard WHERE contest_id in ($live_type_ids) and user_id = $user_id ORDER BY id DESC")->result_array();
                    if (!empty($result)) {
                        foreach ($result as $type_id) {
                            $past_type_ids .= $type_id['contest_id'] . ', ';
                        }
                        $past_type_ids = rtrim($past_type_ids, ', ');

                        $past_result = $this->db->query("SELECT *, (select SUM(points) FROM tbl_contest_prize tcp WHERE tcp.contest_id=c.id) as points, (select count(contest_id) FROM tbl_contest_prize tcp WHERE tcp.contest_id=c.id) as top_users,(SELECT COUNT(*) from tbl_contest_leaderboard tcl where tcl.contest_id = c.id ) as participants FROM tbl_contest c WHERE c.id in ($past_type_ids) ORDER BY c.id DESC")->result_array();
                        unset($result);
                        foreach ($past_result as $quiz) {
                            $quiz['image'] = (!empty($quiz['image'])) ? base_url() . CONTEST_IMG_PATH . $quiz['image'] : '';
                            $quiz['start_date'] = date("d-M", strtotime($quiz['start_date']));
                            $quiz['end_date'] = date("d-M", strtotime($quiz['end_date']));

                            $points = $this->db->query("SELECT top_winner, points FROM tbl_contest_prize WHERE contest_id=" . $quiz['id'])->result_array();
                            $quiz['points'] = $points;
                            $result[] = $quiz;
                        }
                        $past_result = $result;
                        $response['past_contest']['error'] = false;
                        $response['past_contest']['message'] = "117";
                        $response['past_contest']['data'] = (!empty($past_result)) ? $past_result : '';
                    } else {
                        $past_result = $this->db->query("SELECT c.*, (select SUM(points) FROM tbl_contest_prize tcp WHERE tcp.contest_id=c.id) as points, (select count(contest_id) FROM tbl_contest_prize tcp WHERE tcp.contest_id=c.id) as top_users,(SELECT COUNT(*) from tbl_contest_leaderboard where l.contest_id=c.id )as participants FROM tbl_contest_leaderboard as l, tbl_contest as c WHERE l.user_id = '$user_id' and l.contest_id = c.id ORDER BY c.id DESC")->result_array();
                        if (!empty($past_result)) {
                            foreach ($past_result as $quiz) {
                                $quiz['image'] = (!empty($quiz['image'])) ? base_url() . CONTEST_IMG_PATH . $quiz['image'] : '';
                                $quiz['start_date'] = date("d-M", strtotime($quiz['start_date']));
                                $quiz['end_date'] = date("d-M", strtotime($quiz['end_date']));
                                $points = $this->db->query("SELECT top_winner, points FROM tbl_contest_prize WHERE contest_id=" . $quiz['id'])->result_array();
                                $quiz['points'] = $points;
                                $result[] = $quiz;
                            }
                            $past_result = $result;
                            $response['past_contest']['error'] = false;
                            $response['past_contest']['message'] = "117";
                            $response['past_contest']['data'] = (!empty($past_result)) ? $past_result : '';
                        } else {
                            $response['past_contest']['error'] = true;
                            $response['past_contest']['message'] = "116";
                        }
                    }

                    /* getting all quiz details by ids retrieved */
                    $sql = (empty($past_type_ids)) ?
                            "SELECT c.*, (select SUM(points) FROM tbl_contest_prize tcp WHERE tcp.contest_id=c.id) as points, (select count(contest_id) FROM tbl_contest_prize tcp WHERE tcp.contest_id=c.id) as top_users,(SELECT COUNT(*) from tbl_contest_leaderboard tcl WHERE tcl.contest_id=c.id )as participants FROM tbl_contest c WHERE id IN ($live_type_ids) AND status='1' ORDER BY `id` DESC" :
                            "SELECT c.*, (select SUM(points) FROM tbl_contest_prize tcp WHERE tcp.contest_id=c.id) as points, (select count(contest_id) FROM tbl_contest_prize tcp WHERE tcp.contest_id=c.id) as top_users,(SELECT COUNT(*) from tbl_contest_leaderboard tcl WHERE tcl.contest_id=c.id )as participants FROM tbl_contest c WHERE id IN ($live_type_ids) and id NOT IN ($past_type_ids) AND status='1' ORDER BY `id` DESC"
                    ;
                    $live_result = $this->db->query($sql)->result_array();
                    $result = array();
                    if (!empty($live_result)) {
                        foreach ($live_result as $quiz) {
                            $quiz['image'] = (!empty($quiz['image'])) ? base_url() . CONTEST_IMG_PATH . $quiz['image'] : '';
                            $quiz['start_date'] = date("d-M", strtotime($quiz['start_date']));
                            $quiz['end_date'] = date("d-M", strtotime($quiz['end_date']));

                            $points = $this->db->query("SELECT top_winner, points FROM tbl_contest_prize WHERE contest_id=" . $quiz['id'])->result_array();
                            $quiz['points'] = $points;
                            $result[] = $quiz;
                        }
                        $live_result = $result;
                        $response['live_contest']['error'] = false;
                        $response['live_contest']['message'] = "118";
                        $response['live_contest']['data'] = (!empty($live_result)) ? $live_result : '';
                    } else {
                        $response['live_contest']['error'] = true;
                        $response['live_contest']['message'] = "115";
                    }
                } else {
                    $past_result = $this->db->query("SELECT c.*, (select SUM(points) FROM tbl_contest_prize tcp WHERE tcp.contest_id=c.id) as points, (select count(contest_id) FROM tbl_contest_prize tcp WHERE tcp.contest_id=c.id) as top_users,(SELECT COUNT(*) from tbl_contest_leaderboard where l.contest_id=c.id ) as participants FROM tbl_contest_leaderboard as l, tbl_contest as c WHERE l.user_id='$user_id' and l.contest_id=c.id ORDER BY c.id DESC")->result_array();
                    if (!empty($past_result)) {
                        foreach ($past_result as $quiz) {
                            $quiz['image'] = (!empty($quiz['image'])) ? base_url() . CONTEST_IMG_PATH . $quiz['image'] : '';
                            $quiz['start_date'] = date("d-M", strtotime($quiz['start_date']));
                            $quiz['end_date'] = date("d-M", strtotime($quiz['end_date']));

                            $points = $this->db->query("SELECT top_winner, points FROM tbl_contest_prize WHERE contest_id=" . $quiz['id'])->result_array();
                            $quiz['points'] = $points;
                            $result[] = $quiz;
                        }
                        $past_result = $result;
                        $response['past_contest']['error'] = false;
                        $response['past_contest']['message'] = "117";
                        $response['past_contest']['data'] = (!empty($past_result)) ? $past_result : '';
                    } else {
                        $response['past_contest']['error'] = true;
                        $response['past_contest']['message'] = "116";
                    }
                    $response['live_contest']['error'] = true;
                    $response['live_contest']['message'] = "115";
                }

                /* selecting upcoming quiz ids */
                $result = $this->db->query("SELECT id FROM tbl_contest where (CAST(start_date AS DATE) > '$this->toDate')")->result_array();
                $upcoming_type_ids = '';
                if (!empty($result)) {
                    foreach ($result as $type_id) {
                        $upcoming_type_ids .= $type_id['id'] . ', ';
                    }
                    $upcoming_type_ids = rtrim($upcoming_type_ids, ', ');

                    /* getting all quiz details by ids retrieved */
                    $upcoming_result = $this->db->query("SELECT c.*, (select SUM(points) FROM tbl_contest_prize tcp WHERE tcp.contest_id=c.id) as points, (select count(contest_id) FROM tbl_contest_prize tcp WHERE tcp.contest_id=c.id) as top_users FROM tbl_contest c WHERE id IN ($upcoming_type_ids) ORDER BY id DESC")->result_array();
                    $result = array();
                    if (!empty($upcoming_result)) {
                        foreach ($upcoming_result as $quiz) {
                            $quiz['image'] = (!empty($quiz['image'])) ? base_url() . CONTEST_IMG_PATH . $quiz['image'] : '';
                            $quiz['start_date'] = date("d-M", strtotime($quiz['start_date']));
                            $quiz['end_date'] = date("d-M", strtotime($quiz['end_date']));

                            $points = $this->db->query("SELECT top_winner, points FROM tbl_contest_prize WHERE contest_id=" . $quiz['id'])->result_array();
                            $quiz['points'] = $points;
                            $quiz['participants'] = "";
                            $result[] = $quiz;
                        }
                        $upcoming_result = $result;
                    }
                    $response['upcoming_contest']['error'] = false;
                    $response['upcoming_contest']['message'] = "118";
                    $response['upcoming_contest']['data'] = (!empty($upcoming_result)) ? $upcoming_result : '';
                } else {
                    $response['upcoming_contest']['error'] = true;
                    $response['upcoming_contest']['message'] = "114";
                }
            } else {
                $response['error'] = true;
                $response['message'] = "103";
            }
        }
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function get_user_coin_score_post() {
        if (!$this->verify_token()) {
            return FALSE;
        }
        if ($this->AccessKey != $this->post('access_key')) {
            $response['error'] = true;
            $response['message'] = "101";
        } else {
            if ($this->post('user_id')) {
                $user_id = $this->post('user_id');

                $result = $this->db->select('coins')->where('id', $user_id)->get('tbl_users')->row_array();
                if (!empty($result)) {
                    $my_rank = $this->db->query("SELECT r.score,r.user_rank FROM (SELECT s.*, @user_rank := @user_rank + 1 user_rank FROM ( SELECT user_id, sum(score) score FROM tbl_leaderboard_monthly m GROUP BY user_id ) s, (SELECT @user_rank := 0) init ORDER BY score DESC ) r INNER join tbl_users u on u.id = r.user_id WHERE r.user_id=$user_id")->row_array();

                    $result['score'] = ($my_rank) ? $my_rank['score'] : '0';

                    $response['error'] = false;
                    $response['data'] = $result;
                } else {
                    $response['error'] = true;
                    $response['message'] = "102";
                }
            } else {
                $response['error'] = true;
                $response['message'] = "103";
            }
        }
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function set_user_coin_score_post() {
        if (!$this->verify_token()) {
            return FALSE;
        }
        if ($this->AccessKey != $this->post('access_key')) {
            $response['error'] = true;
            $response['message'] = "101";
        } else {
            if ($this->post('user_id')) {
                $user_id = $this->post('user_id');

                if ($this->post('score')) {
                    $score = $this->post('score');
                    $this->set_monthly_leaderboard($user_id, $score);
                }

                if ($this->post('coins')) {
                    $coins = $this->post('coins');
                    $res = $this->db->where('id', $user_id)->get('tbl_users')->row_array();
                    $data = array(
                        'coins' => $res['coins'] + $coins
                    );
                    $this->db->where('id', $user_id)->update('tbl_users', $data);
                }

                $result = $this->db->select('coins')->where('id', $user_id)->get('tbl_users')->row_array();

                if (!empty($result)) {
                    $my_rank = $this->db->query("SELECT r.score,r.user_rank FROM (SELECT s.*, @user_rank := @user_rank + 1 user_rank FROM ( SELECT user_id, sum(score) score FROM tbl_leaderboard_monthly m GROUP BY user_id ) s, (SELECT @user_rank := 0) init ORDER BY score DESC ) r INNER join tbl_users u on u.id = r.user_id WHERE r.user_id=$user_id")->row_array();

                    $result['score'] = ($my_rank) ? $my_rank['score'] : '0';

                    $response['error'] = false;
                    $response['message'] = "111";
                    $response['data'] = $result;
                } else {
                    $response['error'] = true;
                    $response['message'] = "102";
                }
            } else {
                $response['error'] = true;
                $response['message'] = "103";
            }
        }
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function get_daily_quiz_post() {
        if (!$this->verify_token()) {
            return FALSE;
        }
        if ($this->AccessKey != $this->post('access_key')) {
            $response['error'] = true;
            $response['message'] = "101";
        } else {
            if ($this->post('user_id')) {
                $user_id = $this->post('user_id');
                $res1 = $this->db->where('date', $this->toDate)->where('user_id', $user_id)->get('tbl_daily_quiz_user')->row_array();
                if (empty($res1)) {
                    $questions = $response = array();
                    $language_id = ($this->post('language_id') && is_numeric($this->post('language_id'))) ? $this->post('language_id') : '0';
                    $res = $this->db->where('date_published', $this->toDate)->where('language_id', $language_id)->get('tbl_daily_quiz')->row_array();
                    if (!empty($res)) {
                        $res2 = $this->db->where('user_id', $user_id)->get('tbl_daily_quiz_user')->row_array();
                        if (!empty($res2)) {
                            $frm_data = array(
                                'date' => $this->toDate
                            );
                            $this->db->where('user_id', $user_id)->update('tbl_daily_quiz_user', $frm_data);
                        } else {
                            $frm_data = array(
                                'user_id' => $user_id,
                                'date' => $this->toDate
                            );
                            $this->db->insert('tbl_daily_quiz_user', $frm_data);
                        }

                        $questions = $res['questions_id'];

                        $result = $this->db->query("SELECT * FROM tbl_question WHERE id IN (" . $questions . ") ORDER BY FIELD(id," . $questions . ")")->result_array();
                        if (!empty($result)) {
                            for ($i = 0; $i < count($result); $i++) {
                                $result[$i]['image'] = ($result[$i]['image']) ? base_url() . QUESTION_IMG_PATH . $result[$i]['image'] : '';
                                $result[$i]['optione'] = (is_option_e_mode_enabled() && $result[$i]['optione'] != null) ? trim($result[$i]['optione']) : '';
                                $result[$i]['optiona'] = trim($result[$i]['optiona']);
                                $result[$i]['optionb'] = trim($result[$i]['optionb']);
                                $result[$i]['optionc'] = trim($result[$i]['optionc']);
                                $result[$i]['optiond'] = trim($result[$i]['optiond']);
                            }
                            $response['error'] = false;
                            $response['data'] = $result;
                        } else {
                            $response['error'] = true;
                            $response['message'] = "102";
                        }
                    } else {
                        $response['error'] = true;
                        $response['message'] = "102";
                    }
                } else {
                    $response['error'] = true;
                    $response['message'] = "112";
                }
            } else {
                $response['error'] = true;
                $response['message'] = "103";
            }
        }
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function get_bookmark_post() {
        if (!$this->verify_token()) {
            return FALSE;
        }
        if ($this->AccessKey != $this->post('access_key')) {
            $response['error'] = true;
            $response['message'] = "101";
        } else {
            if ($this->post('user_id')) {
                $user_id = $this->post('user_id');

                $this->db->join('tbl_question q', 'q.id=b.question_id');
                $this->db->where('user_id', $user_id)->order_by('b.id', 'DESC');
                $data = $this->db->get('tbl_bookmark b')->result_array();

                if (!empty($data)) {
                    for ($i = 0; $i < count($data); $i++) {
                        $data[$i]['image'] = ($data[$i]['image']) ? base_url() . QUESTION_IMG_PATH . $data[$i]['image'] : '';
                        $data[$i]['optione'] = (is_option_e_mode_enabled() && $data[$i]['optione'] != null) ? trim($data[$i]['optione']) : '';
                        $data[$i]['optiona'] = trim($data[$i]['optiona']);
                        $data[$i]['optionb'] = trim($data[$i]['optionb']);
                        $data[$i]['optionc'] = trim($data[$i]['optionc']);
                        $data[$i]['optiond'] = trim($data[$i]['optiond']);
                    }
                    $response['error'] = false;
                    $response['data'] = $data;
                } else {
                    $response['error'] = false;
                    $response['data'] = $data;
//                    $response['error'] = true;
//                    $response['message'] = "102";
                }
            } else {
                $response['error'] = true;
                $response['message'] = "103";
            }
        }
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function set_bookmark_post() {
        if (!$this->verify_token()) {
            return FALSE;
        }
        if ($this->AccessKey != $this->post('access_key')) {
            $response['error'] = true;
            $response['message'] = "101";
        } else {
            if ($this->post('user_id') && $this->post('question_id') && $this->post('status') != '') {
                $user_id = $this->post('user_id');
                $question_id = $this->post('question_id');
                $status = $this->post('status');

                if ($status == '1') {
                    $frm_data = array(
                        'user_id' => $user_id,
                        'question_id' => $question_id,
                        'status' => $status
                    );
                    $this->db->insert('tbl_bookmark', $frm_data);
                } else {
                    $this->db->where('user_id', $user_id)->where('question_id', $question_id)->delete('tbl_bookmark');
                }
                $response['error'] = false;
                $response['message'] = "111";
            } else {
                $response['error'] = true;
                $response['message'] = "103";
            }
        }
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function get_level_data_post() {
        if (!$this->verify_token()) {
            return FALSE;
        }
        if ($this->AccessKey != $this->post('access_key')) {
            $response['error'] = true;
            $response['message'] = "101";
        } else {
            if ($this->post('user_id') && $this->post('category')) {
                $user_id = $this->post('user_id');
                $category = $this->post('category');
                $subcategory = ($this->post('subcategory')) ? $this->post('subcategory') : 0;

                $this->db->select('level');
                $this->db->where('user_id', $user_id)->where('category', $category)->where('subcategory', $subcategory);
                $res = $this->db->get('tbl_level')->row_array();

                if (!empty($res)) {
                    $response['error'] = false;
                    $response['data'] = $res;
                } else {
                    $res = array("level" => "1");
                    $response['error'] = false;
                    $response['data'] = $res;
                }
            } else {
                $response['error'] = true;
                $response['message'] = "103";
            }
        }
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function set_level_data_post() {
        if (!$this->verify_token()) {
            return FALSE;
        }
        if ($this->AccessKey != $this->post('access_key')) {
            $response['error'] = true;
            $response['message'] = "101";
        } else {
            if ($this->post('user_id') && $this->post('category') && $this->post('level')) {
                $user_id = $this->post('user_id');
                $category = $this->post('category');
                $subcategory = ($this->post('subcategory')) ? $this->post('subcategory') : 0;
                $level = $this->post('level');

                $this->db->where('user_id', $user_id)->where('category', $category)->where('subcategory', $subcategory);
                $res = $this->db->get('tbl_level')->result_array();
                if (!empty($res)) {
                    $data = array(
                        'level' => $level
                    );
                    $this->db->where('user_id', $user_id)->where('category', $category)->where('subcategory', $subcategory)->update('tbl_level', $data);
                } else {
                    $frm_data = array(
                        'user_id' => $user_id,
                        'category' => $category,
                        'subcategory' => $subcategory,
                        'level' => $level
                    );
                    $this->db->insert('tbl_level', $frm_data);
                }
                $response['error'] = false;
                $response['message'] = "111";
            } else {
                $response['error'] = true;
                $response['message'] = "103";
            }
        }
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function get_notifications_post() {
        if (!$this->verify_token()) {
            return FALSE;
        }
        if ($this->AccessKey != $this->post('access_key')) {
            $response['error'] = true;
            $response['message'] = "101";
        } else {
            $limit = ($this->post('limit') && is_numeric($this->post('limit'))) ? $this->post('limit') : 10;
            $offset = ($this->post('offset') && is_numeric($this->post('offset'))) ? $this->post('offset') : 0;

            $sort = ($this->post('sort')) ? $this->post('sort') : 'id';
            $order = ($this->post('order')) ? $this->post('order') : 'DESC';

            $result = $this->db->query("SELECT * FROM tbl_notifications WHERE users = 'all' ORDER BY $sort $order LIMIT $offset,$limit")->result_array();
            $result1 = $this->db->query("SELECT * FROM tbl_notifications WHERE users = 'all'")->result_array();
            $total = count($result1);

            if (!empty($result)) {
                for ($i = 0; $i < count($result); $i++) {
                    if (filter_var($result[$i]['image'], FILTER_VALIDATE_URL) === FALSE) {
                        /* Not a valid URL. Its a image only or empty */
                        $result[$i]['image'] = (!empty($result[$i]['image'])) ? base_url() . NOTIFICATION_IMG_PATH . $result[$i]['image'] : '';
                    }
                }
                $response['error'] = false;
                $response['total'] = "$total";
                $response['data'] = $result;
            } else {
                $response['error'] = true;
                $response['message'] = "102";
            }
        }
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function get_battle_statistics_post() {
        if (!$this->verify_token()) {
            return FALSE;
        }
        if ($this->AccessKey != $this->post('access_key')) {
            $response['error'] = true;
            $response['message'] = "101";
        } else {
            if ($this->post('user_id')) {

                $user_id = $this->post('user_id');
                $offset = ($this->post('offset') && is_numeric($this->post('offset'))) ? $this->post('offset') : 0;
                $limit = ($this->post('limit') && is_numeric($this->post('limit'))) ? $this->post('limit') : 5;

                $sort = ($this->post('sort')) ? $this->post('sort') : 'id';
                $order = ($this->post('order')) ? $this->post('order') : 'DESC';

                $result = $this->db->query("SELECT (SELECT COUNT(*) FROM (SELECT DISTINCT date_created from tbl_battle_statistics WHERE winner_id = $user_id)as w ) AS Victories, (SELECT COUNT(*) FROM (SELECT DISTINCT `date_created` from tbl_battle_statistics WHERE (user_id1= $user_id || user_id2= $user_id)AND is_drawn=1)as d) AS Drawn, (SELECT COUNT(*) FROM (SELECT DISTINCT `date_created` from tbl_battle_statistics WHERE (user_id1= $user_id || user_id2= $user_id) AND winner_id != $user_id and is_drawn = 0)as l )AS Loose")->result_array();
                $response['myreport'] = $result;

                $matches = $temp = array();

                $result = $this->db->query("SELECT *, (select name from tbl_users u WHERE u.id = m.user_id1 ) as user_1, (select name from tbl_users u WHERE u.id = m.user_id2 ) as user_2, (select profile from tbl_users u WHERE u.id = m.user_id1 ) as user_profile1, (select profile from tbl_users u WHERE u.id = m.user_id2 ) as user_profile2 FROM tbl_battle_statistics m where user_id1 = $user_id or user_id2 = $user_id GROUP BY DATE(date_created) ORDER BY $sort $order limit $offset,$limit")->result_array();
                if (!empty($result)) {
                    foreach ($result as $row) {
                        $temp['opponent_id'] = ($row['user_id1'] == $user_id) ? $row['user_id2'] : $row['user_id1'];
                        $temp['opponent_name'] = ($row['user_id1'] == $user_id) ? $row['user_2'] : $row['user_1'];
                        $temp['opponent_profile'] = ($row['user_id1'] == $user_id) ? $row['user_profile2'] : $row['user_profile1'];
                        if (!empty($temp['opponent_profile']) || $temp['opponent_profile'] != null) {
                            if (filter_var($temp['opponent_profile'], FILTER_VALIDATE_URL) === FALSE) {
                                // Not a valid URL. Its a image only or empty
                                $temp['opponent_profile'] = (!empty($temp['opponent_profile'])) ? base_url() . USER_IMG_PATH . $temp['opponent_profile'] : '';
                            }
                        }

                        if ($row['is_drawn'] == 1) {
                            $temp['mystatus'] = "Draw";
                        } else {
                            $temp['mystatus'] = ($row['winner_id'] == $user_id) ? "Won" : "Lost";
                        }
                        $temp['date_created'] = $row['date_created'];
                        $matches[] = $temp;
                    }
                    $response['error'] = false;
                    $response['data'] = $matches;
                } else {
                    $response['error'] = true;
                    $response['message'] = "113";
                }
            } else {
                $response['error'] = true;
                $response['message'] = "103";
            }
        }
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function set_battle_statistics_post() {
        if (!$this->verify_token()) {
            return FALSE;
        }
        if ($this->AccessKey != $this->post('access_key')) {
            $response['error'] = true;
            $response['message'] = "101";
        } else {
            if ($this->post('user_id1') && $this->post('user_id2') && $this->post('winner_id') && $this->post('is_drawn') != '') {
                $user_id1 = $this->post('user_id1');
                $user_id2 = $this->post('user_id2');
                $winner_id = $this->post('winner_id');
                $is_drawn = $this->post('is_drawn');

                $frm_data = array(
                    'user_id1' => $user_id1,
                    'user_id2' => $user_id2,
                    'is_drawn' => $winner_id,
                    'winner_id' => $is_drawn,
                    'date_created' => $this->toDateTime
                );
                $this->db->insert('tbl_battle_statistics', $frm_data);
                $response['error'] = false;
                $response['message'] = "110";
            } else {
                $response['error'] = true;
                $response['message'] = "103";
            }
        }
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function get_users_statistics_post() {
        if (!$this->verify_token()) {
            return FALSE;
        }
        if ($this->AccessKey != $this->post('access_key')) {
            $response['error'] = true;
            $response['message'] = "101";
        } else {
            if ($this->post('user_id')) {
                $user_id = $this->post('user_id');

                $result = $this->db->query("SELECT us.*,u.name,u.profile,(SELECT category_name FROM tbl_category c WHERE c.id=us.strong_category) as strong_category, (SELECT category_name FROM tbl_category c WHERE c.id=us.weak_category) as weak_category FROM tbl_users_statistics us LEFT JOIN tbl_users u on u.id = us.user_id WHERE user_id=$user_id")->result_array();

                if (!empty($result)) {
                    if ($result[0]['strong_category'] == null) {
                        $result[0]['strong_category'] = "0";
                    }
                    if ($result[0]['weak_category'] == null) {
                        $result[0]['weak_category'] = "0";
                    }
                    if (filter_var($result[0]['profile'], FILTER_VALIDATE_URL) === FALSE) {
                        // Not a valid URL. Its a image only or empty
                        $result[0]['profile'] = (!empty($result[0]['profile'])) ? base_url() . USER_IMG_PATH . $result[0]['profile'] : '';
                    }
                    $response['error'] = false;
                    $response['data'] = $result[0];
                } else {
                    $response['error'] = true;
                    $response['message'] = "102";
                }
            } else {
                $response['error'] = true;
                $response['message'] = "103";
            }
        }
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function set_users_statistics_post() {
        if (!$this->verify_token()) {
            return FALSE;
        }
        if ($this->AccessKey != $this->post('access_key')) {
            $response['error'] = true;
            $response['message'] = "101";
        } else {
            if ($this->post('user_id') && $this->post('questions_answered') && $this->post('correct_answers') && $this->post('category_id') && $this->post('ratio')) {

                $user_id = $this->post('user_id');
                $questions_answered = $this->post('questions_answered');
                $correct_answers = $this->post('correct_answers');
                $category_id = $this->post('category_id');
                $ratio = $this->post('ratio');

                // update users coins if set
                if ($this->post('coins') && $this->post('coins') != '' && is_numeric($this->post('coins'))) {
                    $coins = $this->post('coins');
                    $data = array('coins' => $coins);
                    $this->db->where('id', $user_id)->update('tbl_users', $data);
                }

                $res = $this->db->where('id', $user_id)->get('tbl_users_statistics')->row_array();

                if (!empty($res)) {
                    $qa = $res['questions_answered'];
                    $ca = $res['correct_answers'];
                    $sc = $res['strong_category'];
                    $r1 = $res['ratio1'];
                    $wc = $res['weak_category'];
                    $r2 = $res['ratio2'];
                    $bp = $res['best_position'];

                    $my_rank = $this->db->query("SELECT r.* FROM (SELECT s.*, @user_rank := @user_rank + 1 user_rank  FROM (SELECT user_id, sum(score) score FROM tbl_leaderboard_monthly m GROUP BY user_id ) s, (SELECT @user_rank := 0) init ORDER BY score DESC ) r INNER join tbl_users u on u.id = r.user_id WHERE r.user_id=$user_id")->result_array();

                    $rank1 = $my_rank[0]['user_rank'];
                    if ($rank1 < $bp || $bp == 0) {
                        $bp = $rank1;
                        $data = array('best_position' => $bp);
                        $this->db->where('user_id', $user_id)->update('tbl_users_statistics', $data);
                    }

                    if ($ratio > 50) {
                        /* update strong category */
                        /* when ratio is > 50 he is strong in this particular category */
                        $data['questions_answered'] = $qa + $questions_answered;
                        $data['correct_answers'] = $ca + $correct_answers;
                        if ($ratio > $r1 || $sc == 0) {
                            $data['strong_category'] = $category_id;
                            $data['ratio1'] = $ratio;
                        }
                    } else {
                        /* update weak category */
                        /* when ratio is < 50 he is weak in this particular category */
                        $data['questions_answered'] = $qa + $questions_answered;
                        $data['correct_answers'] = $ca + $correct_answers;
                        if ($ratio < $r2 || $wc == 0) {
                            $data['weak_category'] = $category_id;
                            $data['ratio2'] = $ratio;
                        }
                    }
                    $data['best_position'] = $bp;
                    $this->db->where('user_id', $user_id)->update('tbl_users_statistics', $data);

                    $response['error'] = false;
                    $response['message'] = "111";
                } else {
                    if ($ratio > 50) {
                        $frm_data = array(
                            'user_id' => $user_id,
                            'questions_answered' => $questions_answered,
                            'correct_answers' => $correct_answers,
                            'strong_category' => $category_id,
                            'ratio1' => $ratio,
                            'weak_category' => 0,
                            'ratio2' => 0,
                            'best_position' => 0,
                            'date_created' => $this->toDateTime
                        );
                    } else {
                        $frm_data = array(
                            'user_id' => $user_id,
                            'questions_answered' => $questions_answered,
                            'correct_answers' => $correct_answers,
                            'strong_category' => 0,
                            'ratio1' => 0,
                            'weak_category' => $category_id,
                            'ratio2' => $ratio,
                            'best_position' => 0,
                            'date_created' => $this->toDateTime
                        );
                    }
                    $this->db->insert('tbl_users_statistics', $frm_data);
                    $response['error'] = false;
                    $response['message'] = "111";
                }
            } else {
                $response['error'] = true;
                $response['message'] = "103";
            }
        }
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function get_globle_leaderboard_post() {
        if (!$this->verify_token()) {
            return FALSE;
        }
        if ($this->AccessKey != $this->post('access_key')) {
            $response['error'] = true;
            $response['message'] = "101";
        } else {
            $offset = ($this->post('offset')) ? $this->post('offset') : 0;
            $limit = ($this->post('limit')) ? $this->post('limit') : 25;

            $this->db->join('tbl_users u', 'u.id=m.user_id');
            $this->db->group_by('user_id');
            $data_g = $this->db->get('tbl_leaderboard_monthly m')->result_array();
            $total = count($data_g);

            $data = $this->db->query("SELECT r.*, u.email,u.name,u.profile FROM ( SELECT s.*, @user_rank := @user_rank + 1 user_rank FROM ( SELECT user_id, SUM(score) AS score FROM tbl_leaderboard_monthly m join tbl_users u on u.id = m.user_id GROUP BY user_id) s, (SELECT @user_rank := 0) init ORDER BY score DESC ) r INNER join tbl_users u on u.id = r.user_id ORDER BY r.user_rank ASC LIMIT $offset,$limit")->result_array();

            if (!empty($data)) {
                for ($i = 0; $i < count($data); $i++) {
                    if (filter_var($data[$i]['profile'], FILTER_VALIDATE_URL) === FALSE) {
                        // Not a valid URL. Its a image only or empty
                        $data[$i]['profile'] = ($data[$i]['profile']) ? base_url() . USER_IMG_PATH . $data[$i]['profile'] : '';
                    }
                }

                if ($this->post('user_id')) {
                    $user_id = $this->post('user_id');
                    $my_rank = $this->db->query("SELECT r.*, u.email,u.name,u.profile FROM ( SELECT s.*, @user_rank := @user_rank + 1 user_rank FROM ( SELECT user_id, SUM(score) AS score FROM tbl_leaderboard_monthly m join tbl_users u on u.id = m.user_id GROUP BY user_id) s, (SELECT @user_rank := 0) init ORDER BY score DESC ) r INNER join tbl_users u on u.id = r.user_id WHERE user_id=$user_id")->result_array();

                    if (!empty($my_rank)) {
                        if (filter_var($my_rank[0]['profile'], FILTER_VALIDATE_URL) === FALSE) {
                            // Not a valid URL. Its a image only or empty
                            $my_rank[0]['profile'] = (!empty($my_rank[0]['profile'])) ? base_url() . USER_IMG_PATH . $my_rank[0]['profile'] : '';
                        }
                        $user_rank['my_rank'] = $my_rank[0];
                    } else {
                        $my_rank = array(
                            'user_id' => $user_id,
                            'score' => '0',
                            'user_rank' => '0',
                            'email' => '',
                            'name' => '',
                            'profile' => ''
                        );
                        $user_rank['my_rank'] = $my_rank;
                    }
                    array_unshift($data, $user_rank);
                }
                $response['error'] = false;
                $response['total'] = "$total";
                $response['data'] = $data;
            } else {
                $response['error'] = true;
                $response['message'] = "102";
            }
        }
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function get_monthly_leaderboard_post() {
        if (!$this->verify_token()) {
            return FALSE;
        }
        if ($this->AccessKey != $this->post('access_key')) {
            $response['error'] = true;
            $response['message'] = "101";
        } else {
            $offset = ($this->post('offset')) ? $this->post('offset') : 0;
            $limit = ($this->post('limit')) ? $this->post('limit') : 25;

            $month = date('m', strtotime($this->toDate));
            $year = date('Y', strtotime($this->toDate));

            $this->db->join('tbl_users u', 'u.id=m.user_id');
            $this->db->where('MONTH(m.date_created)', $month)->where('YEAR(m.date_created)', $year);
            $data_m = $this->db->get('tbl_leaderboard_monthly m')->result_array();
            $total = count($data_m);

            $data = $this->db->query("SELECT r.*, u.email,u.name,u.profile FROM ( SELECT s.*, @user_rank := @user_rank + 1 user_rank FROM ( SELECT user_id, score FROM tbl_leaderboard_monthly m join tbl_users u on u.id = m.user_id WHERE (MONTH(m.date_created) = '$month') AND (YEAR(m.date_created) = '$year') GROUP BY user_id) s, (SELECT @user_rank := 0) init ORDER BY score DESC ) r INNER join tbl_users u on u.id = r.user_id ORDER BY r.user_rank ASC LIMIT $offset,$limit")->result_array();
            if (!empty($data)) {
                for ($i = 0; $i < count($data); $i++) {
                    if (filter_var($data[$i]['profile'], FILTER_VALIDATE_URL) === FALSE) {
                        // Not a valid URL. Its a image only or empty
                        $data[$i]['profile'] = ($data[$i]['profile']) ? base_url() . USER_IMG_PATH . $data[$i]['profile'] : '';
                    }
                }

                if ($this->post('user_id')) {
                    $user_id = $this->post('user_id');
                    $my_rank = $this->db->query("SELECT r.*, u.email,u.name,u.profile FROM ( SELECT s.*, @user_rank := @user_rank + 1 user_rank FROM ( SELECT user_id, score FROM tbl_leaderboard_monthly m join tbl_users u on u.id = m.user_id WHERE (MONTH(m.date_created) = '$month') AND (YEAR(m.date_created) = '$year') GROUP BY user_id) s, (SELECT @user_rank := 0) init ORDER BY score DESC ) r INNER join tbl_users u on u.id = r.user_id WHERE user_id=$user_id")->result_array();

                    if (!empty($my_rank)) {
                        if (filter_var($my_rank[0]['profile'], FILTER_VALIDATE_URL) === FALSE) {
                            // Not a valid URL. Its a image only or empty
                            $my_rank[0]['profile'] = (!empty($my_rank[0]['profile'])) ? base_url() . USER_IMG_PATH . $my_rank[0]['profile'] : '';
                        }
                        $user_rank['my_rank'] = $my_rank[0];
                    } else {
                        $my_rank = array(
                            'user_id' => $user_id,
                            'score' => '0',
                            'user_rank' => '0',
                            'email' => '',
                            'name' => '',
                            'profile' => ''
                        );
                        $user_rank['my_rank'] = $my_rank;
                    }
                    array_unshift($data, $user_rank);
                }
                $response['error'] = false;
                $response['total'] = "$total";
                $response['data'] = $data;
            } else {
                $response['error'] = true;
                $response['message'] = "102";
            }
        }
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function get_daily_leaderboard_post() {
        if (!$this->verify_token()) {
            return FALSE;
        }
        if ($this->AccessKey != $this->post('access_key')) {
            $response['error'] = true;
            $response['message'] = "101";
        } else {
            $offset = ($this->post('offset')) ? $this->post('offset') : 0;
            $limit = ($this->post('limit')) ? $this->post('limit') : 25;

            $this->db->join('tbl_users u', 'u.id=d.user_id');
            $this->db->where('DATE(d.date_created)', $this->toDate);
            $data_d = $this->db->get('tbl_leaderboard_daily d')->result_array();
            $total = count($data_d);

            $data = $this->db->query("SELECT r.*,u.email,u.name,u.profile FROM ( SELECT s.*, @user_rank := @user_rank + 1 user_rank FROM ( SELECT user_id, score FROM tbl_leaderboard_daily d join tbl_users u on u.id = d.user_id WHERE (DATE(d.date_created) = '$this->toDate')) s, (SELECT @user_rank := 0) init ORDER BY score DESC ) r INNER join tbl_users u on u.id = r.user_id ORDER BY r.user_rank ASC LIMIT $offset,$limit")->result_array();

            if (!empty($data)) {
                for ($i = 0; $i < count($data); $i++) {
                    if (filter_var($data[$i]['profile'], FILTER_VALIDATE_URL) === FALSE) {
                        // Not a valid URL. Its a image only or empty
                        $data[$i]['profile'] = ($data[$i]['profile']) ? base_url() . USER_IMG_PATH . $data[$i]['profile'] : '';
                    }
                }

                if ($this->post('user_id')) {
                    $user_id = $this->post('user_id');
                    $my_rank = $this->db->query("SELECT r.*, u.email,u.name,u.profile FROM ( SELECT s.*, @user_rank := @user_rank + 1 user_rank FROM ( SELECT user_id, score FROM tbl_leaderboard_daily d join tbl_users u on u.id = d.user_id WHERE (DATE(d.date_created) = '$this->toDate')) s, (SELECT @user_rank := 0) init ORDER BY score DESC ) r INNER join tbl_users u on u.id = r.user_id WHERE user_id=$user_id")->result_array();

                    if (!empty($my_rank)) {
                        if (filter_var($my_rank[0]['profile'], FILTER_VALIDATE_URL) === FALSE) {
                            // Not a valid URL. Its a image only or empty
                            $my_rank[0]['profile'] = (!empty($my_rank[0]['profile'])) ? base_url() . USER_IMG_PATH . $my_rank[0]['profile'] : '';
                        }
                        $user_rank['my_rank'] = $my_rank[0];
                    } else {
                        $my_rank = array(
                            'user_id' => $user_id,
                            'score' => '0',
                            'user_rank' => '0',
                            'email' => '',
                            'name' => '',
                            'profile' => ''
                        );
                        $user_rank['my_rank'] = $my_rank;
                    }
                    array_unshift($data, $user_rank);
                }
                $response['error'] = false;
                $response['total'] = "$total";
                $response['data'] = $data;
            } else {
                $response['error'] = true;
                $response['message'] = "102";
            }
        }
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function set_leaderboard_post() {
        if (!$this->verify_token()) {
            return FALSE;
        }
        if ($this->AccessKey != $this->post('access_key')) {
            $response['error'] = true;
            $response['message'] = "101";
        } else {
            if ($this->post('user_id') && $this->post('score') && $this->post('score') != '') {
                $user_id = $this->post('user_id');
                $score = $this->post('score');
                $this->set_monthly_leaderboard($user_id, $score);
                $response['error'] = false;
                $response['message'] = "111";
            } else {
                $response['error'] = true;
                $response['message'] = "103";
            }
        }
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function report_question_post() {
        if (!$this->verify_token()) {
            return FALSE;
        }
        if ($this->AccessKey != $this->post('access_key')) {
            $response['error'] = true;
            $response['message'] = "101";
        } else {
            if ($this->post('question_id') && $this->post('user_id') && $this->post('message')) {
                $frm_data = array(
                    'question_id' => $this->post('question_id'),
                    'user_id' => $this->post('user_id'),
                    'message' => $this->post('message'),
                    'date' => $this->toDateTime
                );
                $this->db->insert('tbl_question_reports', $frm_data);
                $response['error'] = false;
                $response['message'] = "109";
            } else {
                $response['error'] = true;
                $response['message'] = "103";
            }
        }
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function get_random_questions_for_computer_post() {
        if (!$this->verify_token()) {
            return FALSE;
        }
        if ($this->AccessKey != $this->post('access_key')) {
            $response['error'] = true;
            $response['message'] = "101";
        } else {
            /* if match does not exist read and store the questions */
            $language_id = ($this->post('language_id') && is_numeric($this->post('language_id'))) ? $this->post('language_id') : '';

            if ($this->post('category')) {
                $category = $this->post('category');
            } else {
                $category = '0';
            }

            if (!empty($language_id)) {
                $this->db->where('language_id', $language_id);
            }
            if (!empty($category)) {
                $this->db->where('category', $category);
            }
            $this->db->order_by('rand()')->limit(10);
            $data = $this->db->get('tbl_question')->result_array();

            if (!empty($data)) {
                for ($i = 0; $i < count($data); $i++) {
                    $data[$i]['image'] = ($data[$i]['image']) ? base_url() . QUESTION_IMG_PATH . $data[$i]['image'] : '';
                    $data[$i]['optione'] = (is_option_e_mode_enabled() && $data[$i]['optione'] != null) ? trim($data[$i]['optione']) : '';
                    $data[$i]['optiona'] = trim($data[$i]['optiona']);
                    $data[$i]['optionb'] = trim($data[$i]['optionb']);
                    $data[$i]['optionc'] = trim($data[$i]['optionc']);
                    $data[$i]['optiond'] = trim($data[$i]['optiond']);
                }
                $response['error'] = false;
                $response['data'] = $data;
            } else {
                $response['error'] = true;
                $response['message'] = "102";
            }
        }
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function get_random_questions_post() {
        if (!$this->verify_token()) {
            return FALSE;
        }
        if ($this->AccessKey != $this->post('access_key')) {
            $response['error'] = true;
            $response['message'] = "101";
        } else {
            if ($this->post('match_id')) {
                $match_id = $this->post('match_id');
                if ($this->post('destroy_match') && $this->post('destroy_match') == 1) {
                    $this->db->where('match_id', $match_id)->delete('tbl_battle_questions');
                    $response['error'] = false;
                    $response['message'] = "108";
                } else {
                    $this->db->where('date_created <', $this->toDate)->delete('tbl_battle_questions');

                    $language_id = ($this->post('language_id')) ? $this->post('language_id') : 0;
                    if ($this->post('category')) {
                        $category = $this->post('category');
                    } else {
                        $category = '0';
                    }

                    if (!$this->checkBattleExists($match_id)) {
                        /* if match does not exist read and store the questions */

                        if (!empty($language_id)) {
                            $this->db->where('language_id', $language_id);
                        }
                        if (!empty($category)) {
                            $this->db->where('category', $category);
                        }
                        $this->db->order_by('rand()')->limit(10);
                        $res = $this->db->get('tbl_question')->result_array();

                        if (empty($res)) {
                            $response['error'] = true;
                            $response['message'] = "102";
                        } else {
                            $questions = json_encode($res);

                            $frm_data = array(
                                'match_id' => $match_id,
                                'questions' => $questions,
                                'date_created' => $this->toDateTime
                            );
                            $this->db->insert('tbl_battle_questions', $frm_data);

                            foreach ($res as $row) {
                                $row['image'] = (!empty($row['image'])) ? base_url() . QUESTION_IMG_PATH . $row['image'] : '';
                                $row['optione'] = (is_option_e_mode_enabled() && $row['optione'] != null) ? $row['optione'] : '';
                                $row['optiona'] = trim($row['optiona']);
                                $row['optionb'] = trim($row['optionb']);
                                $row['optionc'] = trim($row['optionc']);
                                $row['optiond'] = trim($row['optiond']);
                                $temp[] = $row;
                            }
                            $res = $temp;
                            $response['error'] = false;
                            $response['data'] = $res;
                        }
                    } else {
                        /* read the questions and send it. */
                        $res = $this->db->where('match_id', $match_id)->get('tbl_battle_questions')->result_array();

                        $res = json_decode($res[0]['questions'], 1);
                        foreach ($res as $row) {
                            $row['image'] = (!empty($row['image'])) ? base_url() . QUESTION_IMG_PATH . $row['image'] : '';
                            $row['optione'] = (is_option_e_mode_enabled() && $row['optione'] != null) ? $row['optione'] : '';
                            $row['optiona'] = trim($row['optiona']);
                            $row['optionb'] = trim($row['optionb']);
                            $row['optionc'] = trim($row['optionc']);
                            $row['optiond'] = trim($row['optiond']);
                            $temp[] = $row;
                        }
                        $res[0]['questions'] = json_encode($temp);
                        $response['error'] = false;
                        $response['data'] = json_decode($res[0]['questions']);
                    }
                }
            } else {
                $response['error'] = true;
                $response['message'] = "103";
            }
        }
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function get_questions_for_self_challenge_post() {
        if (!$this->verify_token()) {
            return FALSE;
        }
        if ($this->AccessKey != $this->post('access_key')) {
            $response['error'] = true;
            $response['message'] = "101";
        } else {
            if ($this->post('limit') && ($this->post('category') || $this->post('subcategory'))) {
                $language_id = ($this->post('language_id')) ? $this->post('language_id') : 0;
                $id = ($this->post('category')) ? $this->post('category') : $this->post('subcategory');
                $limit = $this->post('limit');

                if ($this->post('category')) {
                    $this->db->where('category', $id);
                } else {
                    $this->db->where('subcategory', $id);
                }
                if (!empty($language_id)) {
                    $this->db->where('language_id', $language_id);
                }
                $this->db->order_by($this->Order_By)->limit($limit, 0);
                $data = $this->db->get('tbl_question')->result_array();
                if (!empty($data)) {
                    for ($i = 0; $i < count($data); $i++) {
                        $data[$i]['image'] = ($data[$i]['image']) ? base_url() . QUESTION_IMG_PATH . $data[$i]['image'] : '';
                        $data[$i]['optione'] = (is_option_e_mode_enabled() && $data[$i]['optione'] != null) ? trim($data[$i]['optione']) : '';
                        $data[$i]['optiona'] = trim($data[$i]['optiona']);
                        $data[$i]['optionb'] = trim($data[$i]['optionb']);
                        $data[$i]['optionc'] = trim($data[$i]['optionc']);
                        $data[$i]['optiond'] = trim($data[$i]['optiond']);
                    }
                    $response['error'] = false;
                    $response['data'] = $data;
                } else {
                    $response['error'] = true;
                    $response['message'] = "102";
                }
            } else {
                $response['error'] = true;
                $response['message'] = "103";
            }
        }
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function get_questions_by_type_post() {
        if (!$this->verify_token()) {
            return FALSE;
        }
        if ($this->AccessKey != $this->post('access_key')) {
            $response['error'] = true;
            $response['message'] = "101";
        } else {
            if ($this->post('type') && $this->post('limit')) {
                $type = $this->post('type');
                $limit = $this->post('limit');
                $language_id = ($this->post('language_id')) ? $this->post('language_id') : 0;

                $this->db->where('question_type', $type);
                if (!empty($language_id)) {
                    $this->db->where('language_id', $language_id);
                }
                $this->db->order_by($this->Order_By);
                $this->db->limit($limit, 0);
                $data = $this->db->get('tbl_question')->result_array();
                if (!empty($data)) {
                    for ($i = 0; $i < count($data); $i++) {
                        $data[$i]['image'] = ($data[$i]['image']) ? base_url() . QUESTION_IMG_PATH . $data[$i]['image'] : '';
                        $data[$i]['optione'] = (is_option_e_mode_enabled() && $data[$i]['optione'] != null) ? trim($data[$i]['optione']) : '';
                        $data[$i]['optiona'] = trim($data[$i]['optiona']);
                        $data[$i]['optionb'] = trim($data[$i]['optionb']);
                        $data[$i]['optionc'] = trim($data[$i]['optionc']);
                        $data[$i]['optiond'] = trim($data[$i]['optiond']);
                    }
                    $response['error'] = false;
                    $response['data'] = $data;
                } else {
                    $response['error'] = true;
                    $response['message'] = "102";
                }
            } else {
                $response['error'] = true;
                $response['message'] = "103";
            }
        }
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function get_questions_by_level_post() {
        if (!$this->verify_token()) {
            return FALSE;
        }
        if ($this->AccessKey != $this->post('access_key')) {
            $response['error'] = true;
            $response['message'] = "101";
        } else {
            if ($this->post('level') && ($this->post('category') || $this->post('subcategory'))) {
                $level = $this->post('level');
                $language_id = ($this->post('language_id')) ? $this->post('language_id') : 0;
                $id = ($this->post('category')) ? $this->post('category') : $this->post('subcategory');
                $fix_question = is_settings('fix_question');
                $limit = is_settings('total_question');

                $this->db->where('level', $level);
                if ($this->post('category')) {
                    $this->db->where('category', $id);
                } else {
                    $this->db->where('subcategory', $id);
                }
                if (!empty($language_id)) {
                    $this->db->where('language_id', $language_id);
                }
                $this->db->order_by($this->Order_By);
                if ($fix_question == 1) {
                    $this->db->limit($limit, 0);
                }
                $data = $this->db->get('tbl_question')->result_array();
                if (!empty($data)) {
                    for ($i = 0; $i < count($data); $i++) {
                        $data[$i]['image'] = ($data[$i]['image']) ? base_url() . QUESTION_IMG_PATH . $data[$i]['image'] : '';
                        $data[$i]['optione'] = (is_option_e_mode_enabled() && $data[$i]['optione'] != null) ? trim($data[$i]['optione']) : '';
                        $data[$i]['optiona'] = trim($data[$i]['optiona']);
                        $data[$i]['optionb'] = trim($data[$i]['optionb']);
                        $data[$i]['optionc'] = trim($data[$i]['optionc']);
                        $data[$i]['optiond'] = trim($data[$i]['optiond']);
                    }
                    $response['error'] = false;
                    $response['data'] = $data;
                } else {
                    $response['error'] = true;
                    $response['message'] = "102";
                }
            } else {
                $response['error'] = true;
                $response['message'] = "103";
            }
        }
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function get_questions_post() {
        if (!$this->verify_token()) {
            return FALSE;
        }
        if ($this->AccessKey != $this->post('access_key')) {
            $response['error'] = true;
            $response['message'] = "101";
        } else {
            if ($this->post('type') && $this->post('id')) {
                $type = $this->post('type');
                $id = $this->post('id');

                $this->db->where($type, $id);
                $this->db->order_by($this->Order_By);
                $data = $this->db->get('tbl_question')->result_array();
                if (!empty($data)) {
                    for ($i = 0; $i < count($data); $i++) {
                        $data[$i]['image'] = ($data[$i]['image']) ? base_url() . QUESTION_IMG_PATH . $data[$i]['image'] : '';
                        $data[$i]['optione'] = (is_option_e_mode_enabled() && $data[$i]['optione'] != null) ? trim($data[$i]['optione']) : '';
                        $data[$i]['optiona'] = trim($data[$i]['optiona']);
                        $data[$i]['optionb'] = trim($data[$i]['optionb']);
                        $data[$i]['optionc'] = trim($data[$i]['optionc']);
                        $data[$i]['optiond'] = trim($data[$i]['optiond']);
                    }
                    $response['error'] = false;
                    $response['data'] = $data;
                } else {
                    $response['error'] = true;
                    $response['message'] = "102";
                }
            } else {
                $response['error'] = true;
                $response['message'] = "103";
            }
        }
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function get_subcategory_by_maincategory_post() {
        if (!$this->verify_token()) {
            return FALSE;
        }
        if ($this->AccessKey != $this->post('access_key')) {
            $response['error'] = true;
            $response['message'] = "101";
        } else {
            if ($this->post('category')) {
                $category = $this->post('category');

                $this->db->select('s.*, (select count(id) from tbl_question q where q.subcategory=s.id ) as no_of_que, (select max(`level` + 0) from tbl_question q where q.subcategory=s.id ) as maxlevel');
                $this->db->where('maincat_id', $category);
                $this->db->where('status', 1);
                $this->db->order_by('row_order', 'ASC');
                $data = $this->db->get('tbl_subcategory s')->result_array();
                if (!empty($data)) {
                    for ($i = 0; $i < count($data); $i++) {
                        $data[$i]['image'] = ($data[$i]['image']) ? base_url() . SUBCATEGORY_IMG_PATH . $data[$i]['image'] : '';
                        $data[$i]['maxlevel'] = ($data[$i]['maxlevel'] == '' || $data[$i]['maxlevel'] == null ) ? '0' : $data[$i]['maxlevel'];
                    }
                    $response['error'] = false;
                    $response['data'] = $data;
                } else {
                    $response['error'] = true;
                    $response['message'] = "102";
                }
            } else {
                $response['error'] = true;
                $response['message'] = "103";
            }
        }
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function get_categories_post() {
        if (!$this->verify_token()) {
            return FALSE;
        }
        if ($this->AccessKey != $this->post('access_key')) {
            $response['error'] = true;
            $response['message'] = "101";
        } else {
            $this->db->select('c.*,(SELECT @no_of_subcategories := count(`id`) from tbl_subcategory s WHERE s.maincat_id = c.id and s.status = 1 ) as no_of,(select count(id) from tbl_question q where q.category=c.id ) as no_of_que, if(@no_of_subcategories = 0, (SELECT @maxlevel := MAX(`level`+0) from tbl_question q WHERE c.id = q.category ),@maxlevel := 0) as maxlevel');
            if ($this->post('id')) {
                $id = $this->post('id');
                $this->db->where('id', $id);
            }
            if ($this->post('language_id')) {
                $language_id = $this->post('language_id');
                $this->db->where('language_id', $language_id);
            }
            $this->db->order_by('row_order', 'ASC');
            $data = $this->db->get('tbl_category c')->result_array();
            if (!empty($data)) {
                for ($i = 0; $i < count($data); $i++) {
                    $data[$i]['image'] = ($data[$i]['image']) ? base_url() . CATEGORY_IMG_PATH . $data[$i]['image'] : '';
                    $data[$i]['maxlevel'] = ($data[$i]['maxlevel'] == '' || $data[$i]['maxlevel'] == null ) ? '0' : $data[$i]['maxlevel'];
                }
                $response['error'] = false;
                $response['data'] = $data;
            } else {
                $response['error'] = true;
                $response['message'] = "102";
            }
        }
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function get_languages_post() {
        if (!$this->verify_token()) {
            return FALSE;
        }
        if ($this->AccessKey != $this->post('access_key')) {
            $response['error'] = true;
            $response['message'] = "101";
        } else {
            if ($this->post('id')) {
                $id = $this->post('id');
                $this->db->where('id', $id);
            }
            $data = $this->db->select('id, language, code')->where('status', 1)->where('type', 1)->order_by('id', 'ASC')->get('tbl_languages')->result_array();
            if (!empty($data)) {
                $response['error'] = false;
                $response['data'] = $data;
            } else {
                $response['error'] = true;
                $response['message'] = "102";
            }
        }
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function upload_profile_image_post() {
        if (!$this->verify_token()) {
            return FALSE;
        }
        if ($this->AccessKey != $this->post('access_key')) {
            $response['error'] = true;
            $response['message'] = "101";
        } else {
            $id = $this->post('user_id');
            if ($id && $_FILES['image']['name'] != '') {
                // create folder 
                if (!is_dir(USER_IMG_PATH)) {
                    mkdir(USER_IMG_PATH, 0777, TRUE);
                }
                $config['upload_path'] = USER_IMG_PATH;
                $config['allowed_types'] = IMG_ALLOWED_TYPES;
                $config['file_name'] = time();
                $this->load->library('upload', $config);
                $this->upload->initialize($config);

                if (!$this->upload->do_upload('image')) {
                    $response['error'] = true;
                    $response['message'] = "107";
                } else {
                    $sql1 = $this->db->select('profile')->where('id', $id)->get('tbl_users')->row_array();
                    if ($sql1['profile'] != "") {
                        $full_url = USER_IMG_PATH . $sql1['profile'];
                        if (file_exists($full_url)) {
                            unlink($full_url);
                        }
                    }

                    $data = $this->upload->data();
                    $img = $data['file_name'];

                    //image compress
                    $this->load->library('Compress');  // load the codeginiter library

                    $compress = new Compress();
                    $compress->file_url = base_url() . USER_IMG_PATH . $img;
                    $compress->new_name_image = $img;
                    $compress->quality = 80;
                    $compress->destination = base_url() . USER_IMG_PATH;
                    $compress->compress_image();

                    $insert_data = array(
                        'profile' => $img
                    );
                    $this->db->where('id', $id)->update('tbl_users', $insert_data);

                    $res = $this->db->select('profile')->where('id', $id)->get('tbl_users')->row_array();
                    if (filter_var($res['profile'], FILTER_VALIDATE_URL) === FALSE) {
                        // Not a valid URL. Its a image only or empty
                        $res['profile'] = ($res['profile']) ? base_url() . USER_IMG_PATH . $res['profile'] : '';
                    }
                    $response['error'] = false;
                    $response['message'] = '106';
                    $response['data'] = $res;
                }
            } else {
                $response['error'] = true;
                $response['message'] = "103";
            }
        }
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function update_profile_post() {
        if (!$this->verify_token()) {
            return FALSE;
        }
        if ($this->AccessKey != $this->post('access_key')) {
            $response['error'] = true;
            $response['message'] = "101";
        } else {
            if ($this->post('user_id') && $this->post('name')) {
                $user_id = $this->post('user_id');
                $data = array();
                $data['name'] = $this->post('name');

                if ($this->post('email')) {
                    $data['email'] = $this->post('email');
                }
                if ($this->post('mobile')) {
                    $data['mobile'] = $this->post('mobile');
                }
                $this->db->where('id', $user_id)->update('tbl_users', $data);

                $response['error'] = false;
                $response['message'] = "106";
            } else {
                $response['error'] = true;
                $response['message'] = "103";
            }
        }
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function update_fcm_id_post() {
        if (!$this->verify_token()) {
            return FALSE;
        }
        if ($this->AccessKey != $this->post('access_key')) {
            $response['error'] = true;
            $response['message'] = "101";
        } else {
            if ($this->post('fcm_id') && $this->post('user_id')) {
                $fcm_id = $this->post('fcm_id');
                $user_id = $this->post('user_id');

                $data = array(
                    'fcm_id' => $fcm_id,
                );
                $this->db->where('id', $user_id)->update('tbl_users', $data);
                $response['error'] = false;
                $response['message'] = "111";
            } else {
                $response['error'] = true;
                $response['message'] = "103";
            }
        }
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function get_user_by_id_post() {
        if (!$this->verify_token()) {
            return FALSE;
        }
        if ($this->AccessKey != $this->post('access_key')) {
            $response['error'] = true;
            $response['message'] = "101";
        } else {
            if ($this->post('firebase_id')) {
//                $user_id = $this->post('user_id');
                $firebase_id = $this->post('firebase_id');
                $res = $this->db->where('firebase_id', $firebase_id)->get('tbl_users')->row_array();

                if ($res) {
                    if (filter_var($res['profile'], FILTER_VALIDATE_URL) === FALSE) {
                        // Not a valid URL. Its a image only or empty
                        $res['profile'] = ($res['profile']) ? base_url() . USER_IMG_PATH . $res['profile'] : '';
                    }
                    $my_rank = $this->db->query("SELECT r.score,r.user_rank FROM (SELECT s.*, @user_rank := @user_rank + 1 user_rank FROM ( SELECT user_id, sum(score) score  FROM tbl_leaderboard_monthly m join tbl_users u on u.id = m.user_id GROUP BY user_id ) s, (SELECT @user_rank := 0) init ORDER BY score DESC ) r INNER join tbl_users u on u.id = r.user_id WHERE r.user_id=" . $res['id'] . "")->row_array();

                    $res['all_time_score'] = ($my_rank) ? $my_rank['score'] : '0';
                    $res['all_time_rank'] = ($my_rank) ? $my_rank['user_rank'] : '0';

                    $response['error'] = false;
                    $response['data'] = $res;
                } else {
                    $response['error'] = true;
                    $response['message'] = "102";
                }
            } else {
                $response['error'] = true;
                $response['message'] = "103";
            }
        }
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function user_signup_post() {
        if (!$this->verify_token()) {
            return FALSE;
        }
        if ($this->AccessKey != $this->post('access_key')) {
            $response['error'] = true;
            $response['message'] = "101";
        } else {
            if ($this->post('firebase_id') && $this->post('type') && ($this->post('firebase_id') != 'null') && ($this->post('firebase_id') != 'NULL')) {
                $firebase_id = $this->post('firebase_id');
                $type = $this->post('type');
                $email = ($this->post('email')) ? $this->post('email') : '';
                $name = ($this->post('name')) ? $this->post('name') : '';
                $mobile = ($this->post('mobile')) ? $this->post('mobile') : '';
                $profile = ($this->post('profile')) ? $this->post('profile') : '';
                $fcm_id = ($this->post('fcm_id')) ? $this->post('fcm_id') : '';
                $friends_code = ($this->post('friends_code')) ? $this->post('friends_code') : '';
                $status = ($this->post('status')) ? $this->post('status') : '1';
                $refer_coin = is_settings('refer_coin');

                if (!empty($friends_code)) {
                    $code = valid_friends_refer_code($friends_code);
                    if (!$code['is_valid']) {
                        $friends_code = '';
                    }
                }
                $res = $this->db->where('firebase_id', $firebase_id)->get('tbl_users')->row_array();
                if (!empty($res)) {
                    $user_id = $res['id'];
                    $refer_code = $this->random_string(4) . $res['refer_code'];

                    $friends_code_is_used = check_friends_code_is_used_by_user($user_id);
                    if (!$friends_code_is_used['is_used'] && $friends_code != '') {
                        $data = array(
                            'friends_code' => $friends_code,
                            'coins' => $res['coins'] + $refer_coin
                        );
                        $this->db->where('id', $user_id)->update('tbl_users', $data);
                        $credited = credit_coins_to_friends_code($friends_code);
                    }
                    if (!empty($fcm_id)) {
                        $data = array('fcm_id' => $fcm_id);
                        $this->db->where('id', $user_id)->update('tbl_users', $data);
                    }
                    if (!is_refer_code_set($user_id) && !empty($refer_code)) {
                        $data = array('refer_code' => $refer_code);
                        $this->db->where('id', $user_id)->update('tbl_users', $data);
                    }
                    if (!empty($name)) {
                        $data = array('name' => $name);
                        $this->db->where('id', $user_id)->update('tbl_users', $data);
                    }

                    $res1 = $this->db->where('firebase_id', $firebase_id)->get('tbl_users')->row_array();

                    if (filter_var($res['profile'], FILTER_VALIDATE_URL) === FALSE) {
                        $res1['profile'] = ($res1['profile']) ? base_url() . USER_IMG_PATH . $res1['profile'] : '';
                    }
                    $response['error'] = false;
                    $response['message'] = "105";
                    $response['data'] = $res1;
                } else {
                    $data = array(
                        'firebase_id' => $firebase_id,
                        'name' => $name,
                        'email' => $email,
                        'mobile' => $mobile,
                        'type' => $type,
                        'profile' => $profile,
                        'fcm_id' => $fcm_id,
                        'friends_code' => $friends_code,
                        'coins' => '0',
                        'status' => $status,
                        'date_registered' => $this->toDateTime
                    );
                    $this->db->insert('tbl_users', $data);
                    $insert_id = $this->db->insert_id();

                    $refer_code = $this->random_string(4) . $insert_id;
                    $dataR = array('refer_code' => $refer_code);
                    $this->db->where('id', $insert_id)->update('tbl_users', $dataR);

                    if ($friends_code != '') {
                        $data = array('coins' => $refer_coin);
                        $this->db->where('id', $insert_id)->update('tbl_users', $data);
                        $credited = credit_coins_to_friends_code($friends_code);
                    }

                    $res1 = $this->db->where('id', $insert_id)->get('tbl_users')->row_array();

                    if (filter_var($res1['profile'], FILTER_VALIDATE_URL) === FALSE) {
                        $res1['profile'] = ($res1['profile']) ? base_url() . USER_IMG_PATH . $res1['profile'] : '';
                    }
                    $response['error'] = false;
                    $response['message'] = "104";
                    $response['data'] = $res1;
                }
            } else {
                $response['error'] = true;
                $response['message'] = "103";
            }
        }
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function get_settings_post() {
        if (!$this->verify_token()) {
            return FALSE;
        }
        if ($this->AccessKey != $this->post('access_key')) {
            $response['error'] = true;
            $response['message'] = "101";
        } else {
            if ($this->post('type')) {
                $type = $this->post('type');
                $res = $this->db->where('type', $type)->get('tbl_settings')->row_array();
                if (!empty($res)) {
                    $response['error'] = false;
                    $response['data'] = $res['message'];
                } else {
                    $response['error'] = true;
                    $response['message'] = "102";
                }
            } else {
                $response['error'] = true;
                $response['message'] = "103";
            }
        }
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function get_system_configurations_post() {
        if (!$this->verify_token()) {
            return FALSE;
        }
        if ($this->AccessKey != $this->post('access_key')) {
            $response['error'] = true;
            $response['message'] = "101";
        } else {
            $setting = [
                'system_timezone', 'system_timezone_gmt',
                'app_link', 'more_apps',
                'ios_app_link', 'ios_more_apps',
                'refer_coin', 'earn_coin', 'reward_coin', 'app_version',
                'true_value', 'false_value',
                'answer_mode', 'language_mode', 'option_e_mode',
                'force_update',
                'daily_quiz_mode', 'contest_mode',
                'fix_question', 'total_question',
                'shareapp_text',
                'battle_random_category_mode', 'battle_group_category_mode'
            ];
            foreach ($setting as $row) {
                $data = $this->db->where('type', $row)->get('tbl_settings')->row_array();
                $res[$row] = ($data) ? $data['message'] : '';
            }
            if (!empty($res)) {
                $response['error'] = false;
                $response['data'] = $res;
            } else {
                $response['error'] = true;
                $response['message'] = "102";
            }
        }
        $this->response($response, REST_Controller::HTTP_OK);
    }

    //function
    public function checkBattleExists($match_id) {
        $res = $this->db->where('match_id', $match_id)->get('tbl_battle_questions')->result_array();
        if (empty($res)) {
            return false;
        } else {
            return true;
        }
    }

    public function set_monthly_leaderboard($user_id, $score) {
        $month = date('m', strtotime($this->toDate));
        $year = date('Y', strtotime($this->toDate));

        // set data in mothly leaderboard
        $data_m = $this->db->where('user_id', $user_id)->where('MONTH(date_created)', $month)->where('YEAR(date_created)', $year)->get('tbl_leaderboard_monthly')->row_array();
        if (!empty($data_m)) {
            $old1 = $data_m['score'];
            $new1 = $old1 + $score;
            $score1 = ($new1 <= 0) ? 0 : $score;

            $data['score'] = ($new1 <= 0) ? $score1 : $new1;
            $data['last_updated'] = $this->toDateTime;

            $this->db->where('id', $data_m['id'])->where('user_id', $user_id)->update('tbl_leaderboard_monthly', $data);
        } else {
            $score1 = ($score <= 0) ? 0 : $score;
            $data = array(
                'user_id' => $user_id,
                'score' => $score1,
                'last_updated' => $this->toDateTime,
                'date_created' => $this->toDateTime
            );
            $this->db->insert('tbl_leaderboard_monthly', $data);
        }

        // set data in daily leaderboard
        $data_d = $this->db->where('user_id', $user_id)->get('tbl_leaderboard_daily')->row_array();
        if (!empty($data_d)) {
            $data_d1 = $this->db->where('user_id', $user_id)->where('DATE(date_created)', $this->toDate)->get('tbl_leaderboard_daily')->row_array();
            if (!empty($data_d1)) {
                $old = $data_d1['score'];
                $new = $old + $score;
                $score1 = ($new <= 0) ? 0 : $score;

                $data1['score'] = ($new <= 0) ? $score1 : $new;

                $this->db->where('id', $data_d1['id'])->where('user_id', $user_id)->update('tbl_leaderboard_daily', $data1);
            } else {
                $score1 = ($score <= 0) ? 0 : $score;
                $data2 = array(
                    'score' => $score1,
                    'date_created' => $this->toDateTime
                );
                $this->db->where('id', $data_d['id'])->where('user_id', $user_id)->update('tbl_leaderboard_daily', $data2);
            }
        } else {
            $score1 = ($score <= 0) ? 0 : $score;
            $data = array(
                'user_id' => $user_id,
                'score' => $score1,
                'date_created' => $this->toDateTime
            );
            $this->db->insert('tbl_leaderboard_daily', $data);
        }
    }

    public function get_fcm_id($user_id) {
        $res = $this->db->where('id', $user_id)->get('tbl_users')->row_array();
        return $res['fcm_id'];
    }

    public function generate_token() {
        $payload = [
            'iat' => time(), /* issued at time */
            'iss' => 'Quiz',
            'exp' => time() + (30 * 60 * 60 * 24), /* expires after 1 minute */
            'sub' => 'Quiz Authentication'
        ];
        return $this->jwt->encode($payload, $this->JWT_SECRET_KEY);
    }

    public function verify_token() {
        try {
            $token = $this->jwt->getBearerToken();
        } catch (Exception $e) {
            $response['error'] = true;
            $response['message'] = $e->getMessage();
            $this->response($response, REST_Controller::HTTP_OK);
            return false;
        }
        if (!empty($token)) {
            try {
                $payload = $this->jwt->decode($token, $this->JWT_SECRET_KEY, ['HS256']);
                if (!isset($payload->iss) || $payload->iss != 'Quiz') {
                    $response['error'] = true;
                    $response['message'] = '124';
                    $this->response($response, REST_Controller::HTTP_OK);
                    return false;
                } else {
                    return true;
                }
            } catch (Exception $e) {
                $response['error'] = true;
                $response['message'] = $e->getMessage();
                $this->response($response, REST_Controller::HTTP_OK);
                return false;
            }
        } else {
            $response['error'] = true;
            $response['message'] = "125";
            $this->response($response, REST_Controller::HTTP_OK);
            return false;
        }
    }

    public function random_string($length) {
        $characters = 'abC0DefGHij1KLMnop2qR3STu4vwxY5ZABc6dEFgh7IJ8klm9NOPQrstUVWXyz';
        $string = '';
        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[mt_rand(0, strlen($characters) - 1)];
        }
        return $string;
    }

}
