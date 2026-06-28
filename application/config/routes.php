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
|	https://codeigniter.com/userguide3/general/routing.html
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
$route['default_controller'] = 'dashboard';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['account/create'] = 'account/create';
$route['account/edit/(:num)'] = 'account/edit/$1';
$route['account/delete/(:num)'] = 'account/delete/$1';
$route['account'] = 'account/index';

$route['client/create'] = 'client/create';
$route['client/edit/(:num)'] = 'client/edit/$1';
$route['client/delete/(:num)'] = 'client/delete/$1';
$route['client'] = 'client/index';

$route['contract/create'] = 'contract/create';
$route['contract/edit/(:num)'] = 'contract/edit/$1';
$route['contract/delete/(:num)'] = 'contract/delete/$1';
$route['contract/download/(:num)'] = 'contract/download/$1';
$route['contract'] = 'contract/index';

$route['campaign/create'] = 'campaign/create';
$route['campaign/edit/(:num)'] = 'campaign/edit/$1';
$route['campaign/detail/(:num)'] = 'campaign/detail/$1';
$route['campaign/delete/(:num)'] = 'campaign/delete/$1';
$route['campaign/export/(:any)/(:num)'] = 'campaign/export/$1/$2';
$route['campaign/unconnect_ad/(:num)/(:num)'] = 'campaign/unconnect_ad/$1/$2';
$route['campaign'] = 'campaign/index';

$route['ads'] = 'ads/index';

$route['config/platforms/meta'] = 'Config/Platforms/meta';
$route['config/platforms/ga4'] = 'Config/Platforms/ga4';
$route['config/platforms/youtube'] = 'Config/Platforms/youtube';
$route['config/platforms/gam'] = 'Config/Platforms/gam';

$route['config/platforms/save-credential/(:any)'] = 'Config/Platforms/save_credential/$1';

$route['config/platforms/(:any)/keyword/create'] = 'Config/Platforms/create_keyword/$1';
$route['config/platforms/(:any)/keyword/edit/(:num)'] = 'Config/Platforms/edit_keyword/$1/$2';
$route['config/platforms/(:any)/keyword/delete/(:num)'] = 'Config/Platforms/delete_keyword/$1/$2';

$route['config/role/create'] = 'Config/role/create';
$route['config/role/edit/(:num)'] = 'Config/role/edit/$1';
$route['config/role/delete/(:num)'] = 'Config/role/delete/$1';
$route['config/role'] = 'Config/role/index';

$route['auth/login'] = 'auth/login';
$route['auth/logout'] = 'auth/logout';

$route['login'] = 'auth/index';

$route['auth/forgot_password'] = 'auth/forgot_password';
$route['auth/send_reset_link'] = 'auth/send_reset_link';
$route['auth/reset_password/(:any)'] = 'auth/reset_password/$1';
$route['auth/update_password'] = 'auth/update_password';
