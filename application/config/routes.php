<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/*
  | -------------------------------------------------------------------------
  | URI ROUTING
  | -------------------------------------------------------------------------
  | This file lets you re-map URI requests to specific controller functions.
  |
  | Typically there is a one-to-one relationship between a URL string
  | and its corresponding controller class/method. The segments in a
  | URL normally follow this pattern:
  |
  |	example.com/class/method/id/
  |
  | In some instances, however, you may want to remap this relationship
  | so that a different class/function is called than the one
  | corresponding to the URL.
  |
  | Please see the user guide for complete details:
  |
  |	https://codeigniter.com/user_guide/general/routing.html
  |
  | -------------------------------------------------------------------------
  | RESERVED ROUTES
  | -------------------------------------------------------------------------
  |
  | There are three reserved routes:
  |
  |	$route['default_controller'] = 'welcome';
  |
  | This route indicates which controller class should be loaded if the
  | URI contains no data. In the above example, the "welcome" class
  | would be loaded.
  |
  |	$route['404_override'] = 'errors/page_missing';
  |
  | This route will tell the Router which controller/method to use if those
  | provided in the URL cannot be matched to a valid route.
  |
  |	$route['translate_uri_dashes'] = FALSE;
  |
  | This is not exactly a route, but allows you to automatically route
  | controller and method names that contain dashes. '-' isn't a valid
  | class or method name character, so it requires translation.
  | When you set this option to TRUE, it will replace ALL dashes in the
  | controller and method URI segments.
  |
  | Examples:	my-controller/index	-> my_controller/index
  |		my-controller/my-method	-> my_controller/my_method
 */

$route['default_controller'] = 'Login';
$route['404_override'] = 'errors';
$route['translate_uri_dashes'] = FALSE;


/* * ********* USER DEFINED ROUTES FOR ADMIN PANEL ****************** */
$route['loginMe'] = 'Login/loginMe';

$route['resetpassword'] = 'Login/resetpassword';
$route['checkOldPass'] = 'Login/checkOldPass';

$route['logout'] = 'Login/logout';

$route['Dashboard'] = 'Dashboard';

$route['users'] = 'Dashboard/users';
$route['battle-statistics/(:num)'] = 'Dashboard/battle_statistics/$1';

$route['global-leaderboard'] = 'Dashboard/global_leaderboard';
$route['monthly-leaderboard'] = 'Dashboard/monthly_leaderboard';
$route['monthly-leaderboard/(:num)'] = 'Dashboard/monthly_leaderboard/$1';
$route['daily-leaderboard'] = 'Dashboard/daily_leaderboard';

$route['delete_multiple'] = 'Dashboard/delete_multiple';

$route['get_categories_of_language'] = 'Dashboard/get_categories_of_language';
$route['get_subcategories_of_category'] = 'Dashboard/get_subcategories_of_category';

$route['user-accounts-rights'] = 'Dashboard/users_accounts_rights';
$route['delete_accounts_rights'] = 'Dashboard/delete_accounts_rights';
$route['edit_accounts_rights'] = 'Dashboard/edit_accounts_rights';

$route['languages'] = 'Languages';
$route['delete_language'] = 'Languages/delete_language';

$route['main-category'] = 'Category';
$route['delete_category'] = 'Category/delete_category';
$route['category-order'] = 'Category/category_order';

$route['sub-category'] = 'Subcategory/sub_category';
$route['delete_subcategory'] = 'Subcategory/delete_subcategory';

$route['create-questions'] = 'Questions';
$route['manage-questions'] = 'Questions/manage_questions';
$route['delete_questions'] = 'Questions/delete_questions';
$route['daily-quiz'] = 'Questions/daily_quiz';
$route['get_daily_quiz'] = 'Questions/get_daily_quiz';
$route['add_daily_quiz'] = 'Questions/add_daily_quiz';
$route['question-reports'] = 'Questions/question_reports';
$route['delete_question_report'] = 'Questions/delete_question_report';
$route['import-questions'] = 'Questions/import_questions';

$route['contest'] = 'Contest';
$route['delete_contest'] = 'Contest/delete_contest';
$route['contest-prize/(:num)'] = 'Contest/contest_prize/$1';
$route['delete_contest_prize'] = 'Contest/delete_contest_prize';
$route['contest-leaderboard/(:num)'] = 'Contest/contest_leaderboard/$1';
$route['contest-prize-distribute/(:num)'] = 'Contest/contest_prize_distribute/$1';
$route['contest-questions'] = 'Contest/contest_questions';
$route['delete_contest_questions'] = 'Contest/delete_contest_questions';
$route['contest-questions-import'] = 'Contest/contest_questions_import';

$route['fun-n-learn'] = 'Fun_N_Learn';
$route['delete_fun_n_learn'] = 'Fun_N_Learn/delete_fun_n_learn';
$route['fun-n-learn-questions/(:num)'] = 'Fun_N_Learn/fun_n_learn_questions/$1';
$route['delete_fun_n_learn_questions'] = 'Fun_N_Learn/delete_fun_n_learn_questions';

$route['guess-the-word'] = 'Guess_Word';
$route['delete_guess_word'] = 'Guess_Word/delete_guess_word';

$route['send-notifications'] = 'Settings/send_notifications';
$route['delete_notification'] = 'Settings/delete_notification';
$route['system-configurations'] = 'Settings/system_configurations';
$route['notification-settings'] = 'Settings/notification_settings';
$route['about-us'] = 'Settings/about_us';
$route['instructions'] = 'Settings/instructions';
$route['upload_img'] = 'Settings/upload_img';
$route['privacy-policy'] = 'Settings/privacy_policy';
$route['play-store-privacy-policy'] = 'Settings/play_store_privacy_policy';
$route['terms-conditions'] = 'Settings/terms_conditions';
$route['play-store-terms-conditions'] = 'Settings/play_store_terms_conditions';
$route['contact-us'] = 'Settings/contact_us';
$route['play-store-contact-us'] = 'Settings/play_store_contact_us';
$route['profile'] = 'Settings/profile';

$route['system-updates'] = 'System_Update';
$route['set_setting'] = 'System_Update/set_setting';

