<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
*/

// Default controller
$route['default_controller'] = 'home/index';

// Home routes (public)
$route['home'] = 'home/index';
$route['home/cara_pakai'] = 'home/cara_pakai';
$route['home/tentang'] = 'home/tentang';
$route['home/workshop_detail/(:num)'] = 'home/workshop_detail/$1';

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

$route['user/bookings'] = 'booking/my_bookings';
$route['user/bookings/detail/(:num)'] = 'booking/detail/$1';
$route['user/bookings/cancel/(:num)'] = 'booking/cancel/$1';
$route['user/bookings/reschedule/(:num)'] = 'booking/reschedule/$1';
$route['user/bookings/create'] = 'booking/index';




// Workshop Owner routes
$route['workshop/dashboard'] = 'workshop/dashboard';
$route['workshop/profile'] = 'workshop/profile';
$route['workshop/edit_profile'] = 'workshop/edit_profile';

// Mechanic routes
$route['mechanic/dashboard'] = 'mechanic_dashboard/dashboard';
$route['mechanic/bookings'] = 'mechanic_dashboard/my_bookings';
$route['mechanic/booking_detail/(:num)'] = 'mechanic_dashboard/booking_detail/$1';
$route['mechanic/productivity'] = 'mechanic_dashboard/my_productivity';
$route['mechanic/profile'] = 'mechanic_dashboard/profile';
$route['mechanic/update_profile'] = 'mechanic_dashboard/update_profile';
$route['mechanic/toggle_availability'] = 'mechanic_dashboard/toggle_availability';
$route['mechanic/change_password'] = 'mechanic_dashboard/change_password';


// Admin routes
$route['admin'] = 'admin/dashboard'; 
$route['admin/dashboard'] = 'admin/dashboard';
$route['admin/users'] = 'admin/users';
$route['admin/users_data'] = 'admin/users_data';
$route['admin/view_user/(:num)'] = 'admin/view_user/$1';
$route['admin/reset_password/(:num)'] = 'admin/reset_password/$1';
$route['admin/activate_user/(:num)'] = 'admin/activate_user/$1';
$route['admin/deactivate_user/(:num)'] = 'admin/deactivate_user/$1';
$route['admin/delete_user/(:num)'] = 'admin/delete_user/$1';
$route['admin/workshops'] = 'admin/workshops';
$route['admin/workshops_data'] = 'admin/workshops_data';
$route['admin/view_workshop/(:num)'] = 'admin/view_workshop/$1';
$route['admin/verify_workshop/(:num)'] = 'admin/verify_workshop/$1';
$route['admin/set_featured/(:num)'] = 'admin/set_featured/$1';
$route['admin/get_csrf_token'] = 'admin/get_csrf_token';
$route['admin/pending_verification'] = 'admin/pending_verification';
$route['admin/review_moderation'] = 'admin/review_moderation';
$route['admin/pending_reviews_data'] = 'admin/pending_reviews_data';
$route['admin/approve_review/(:num)'] = 'admin/approve_review/$1';
$route['admin/reject_review/(:num)'] = 'admin/reject_review/$1';
$route['admin/activity_logs'] = 'admin/activity_logs';
$route['admin/settings'] = 'admin/settings';

// Road Graph routes (Admin)
$route['admin/road_graph'] = 'admin/road_graph/index';
$route['admin/road_graph/nodes'] = 'admin/road_graph/nodes';
$route['admin/road_graph/create_node'] = 'admin/road_graph/create_node';
$route['admin/road_graph/edit_node/(:num)'] = 'admin/road_graph/edit_node/$1';
$route['admin/road_graph/delete_node/(:num)'] = 'admin/road_graph/delete_node/$1';
$route['admin/road_graph/edges'] = 'admin/road_graph/edges';
$route['admin/road_graph/create_edge'] = 'admin/road_graph/create_edge';
$route['admin/road_graph/edit_edge/(:num)'] = 'admin/road_graph/edit_edge/$1';
$route['admin/road_graph/delete_edge/(:num)'] = 'admin/road_graph/delete_edge/$1';

// Report routes (Admin)
$route['admin/report/global'] = 'report/global';
$route['admin/report/workshop_detail/(:num)'] = 'report/workshop_detail/$1';
$route['admin/report/export_global_csv'] = 'report/export_global_csv';


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
