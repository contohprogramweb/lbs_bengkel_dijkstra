<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
*/

// Default controller
$route['default_controller'] = 'welcome';

// Auth routes
$route['login'] = 'auth/login';
$route['register'] = 'auth/register';
$route['logout'] = 'auth/logout';
$route['auth/process_login'] = 'auth/process_login';
$route['auth/process_register'] = 'auth/process_register';

// User (Customer) routes
$route['user/dashboard'] = 'user/dashboard';
$route['user/profile'] = 'user/profile';
$route['user/edit_profile'] = 'user/edit_profile';
$route['user/change_password'] = 'user/change_password';

// Workshop Owner routes
$route['workshop/dashboard'] = 'workshop/dashboard';
$route['workshop/profile'] = 'workshop/profile';
$route['workshop/edit_profile'] = 'workshop/edit_profile';

// Admin routes
$route['admin'] = 'admin/dashboard';
$route['admin/dashboard'] = 'admin/dashboard';
$route['admin/users'] = 'admin/users';
$route['admin/settings'] = 'admin/settings';

// Enable query strings for some legacy support
$config['enable_query_strings'] = FALSE;
