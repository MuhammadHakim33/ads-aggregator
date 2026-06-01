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

$route['ads'] = 'ads/index';
$route['ads/(:num)'] = 'ads/index/$1';

$route['config/filter-keyword/create'] = 'configuration/filter_keyword/create';
$route['config/filter-keyword/edit/(:num)'] = 'configuration/filter_keyword/edit/$1';
$route['config/filter-keyword/delete/(:num)'] = 'configuration/filter_keyword/delete/$1';
$route['config/filter-keyword'] = 'configuration/filter_keyword/index';

$route['config/credentials/save/(:any)'] = 'configuration/credentials/save/$1';
$route['config/credentials'] = 'configuration/credentials/index';

$route['config/cron-health/history/(:any)'] = 'configuration/cron_health/history/$1';
$route['config/cron-health'] = 'configuration/cron_health/index';

$route['auth/login']  = 'auth/login';
$route['auth/logout'] = 'auth/logout';

$route['login'] = 'auth/index';


