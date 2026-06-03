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

// ================================================================
// API ROUTES (SRS v4.0 Section 5.6)
// ================================================================

// Vehicle API endpoints
$route['api/vehicle/list'] = 'api/vehicle/list';
$route['api/vehicle/detail/(:num)'] = 'api/vehicle/detail/$1';
$route['api/vehicle/create'] = 'api/vehicle/create';
$route['api/vehicle/update/(:num)'] = 'api/vehicle/update/$1';
$route['api/vehicle/delete/(:num)'] = 'api/vehicle/delete/$1';
$route['api/vehicle/set_primary/(:num)'] = 'api/vehicle/set_primary/$1';

// Schedule API endpoints
$route['api/schedule/available/(:num)'] = 'api/schedule/available/$1';
$route['api/schedule/slots/(:num)/(:any)'] = 'api/schedule/slots/$1/$2';
$route['api/schedule/book'] = 'api/schedule/book';
$route['api/schedule/cancel/(:num)'] = 'api/schedule/cancel/$1';

// Review API endpoints
$route['api/review/create'] = 'api/review/create';
$route['api/review/list/(:num)'] = 'api/review/list/$1';
$route['api/review/report'] = 'api/review/report';
$route['api/review/photo/upload'] = 'api/review/photo/upload';

// Emergency API endpoints
$route['api/emergency/request'] = 'api/emergency/request';
$route['api/emergency/status/(:any)'] = 'api/emergency/status/$1';
$route['api/emergency/cancel/(:any)'] = 'api/emergency/cancel/$1';
$route['api/emergency/nearby'] = 'api/emergency/nearby';

// Booking Approval API endpoints
$route['api/booking/approval/pending'] = 'api/booking/approval/pending';
$route['api/booking/approval/approve/(:num)'] = 'api/booking/approval/approve/$1';
$route['api/booking/approval/reject/(:num)'] = 'api/booking/approval/reject/$1';
$route['api/booking/approval/history/(:num)'] = 'api/booking/approval/history/$1';

// User Notifications API endpoints
$route['api/user/notifications/list'] = 'api/user/notifications/list';
$route['api/user/notifications/unread_count'] = 'api/user/notifications/unread_count';
$route['api/user/notifications/mark_read/(:num)'] = 'api/user/notifications/mark_read/$1';
$route['api/user/notifications/mark_all_read'] = 'api/user/notifications/mark_all_read';
$route['api/user/notifications/snooze'] = 'api/user/notifications/snooze';

// Enable query strings for some legacy support
$config['enable_query_strings'] = FALSE;
