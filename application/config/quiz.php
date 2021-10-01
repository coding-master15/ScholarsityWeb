<?php

defined('BASEPATH') or exit('No direct script access allowed');

$config['system_modules'] = [
    'users' => array('read', 'update'),
    'languages' => array('create', 'read', 'update', 'delete'),
    'categories' => array('create', 'read', 'update', 'delete'),
    'subcategories' => array('create', 'read', 'update', 'delete'),
    'category_order' => array('read', 'update'),
    'questions' => array('create', 'read', 'update', 'delete'),
    'daily_quiz' => array('read', 'update'),
    'manage_contest' => array('create', 'read', 'update', 'delete'),
    'manage_contest_question' => array('create', 'read', 'update', 'delete'),
    'import_contest_question' => array('update'),
    'fun_n_learn' => array('create', 'read', 'update', 'delete'),
    'guess_the_word' => array('create', 'read', 'update', 'delete'),
    'question_report' => array('read', 'update', 'delete'),
    'send_notification' => array('create', 'read', 'delete'),
    'import_question' => array('update'),
    'system_configuration' => array('read', 'update'),
];

?>