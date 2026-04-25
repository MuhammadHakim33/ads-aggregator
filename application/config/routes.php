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
$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['account/create'] = 'account/create';
$route['account/update/(:id)'] = 'account/update/$1';
$route['account/delete/(:id)'] = 'account/delete/$1';
$route['account'] = 'account/index';

$route['client/create'] = 'client/create';
$route['client/update/(:id)'] = 'client/update/$1';
$route['client/delete/(:id)'] = 'client/delete/$1';
$route['client'] = 'client/index';

$route['config/filter-keyword/create']['POST'] = 'configuration/create_filter_keyword';
$route['config/filter-keyword/update/(:num)']['PUT'] = 'configuration/update_filter_keyword/$1';
$route['config/filter-keyword/delete/(:num)']['DELETE'] = 'configuration/delete_filter_keyword/$1';
$route['config/filter-keyword']['GET'] = 'configuration/filter_keywords';
$route['config/filter-keyword/(:any)']['GET'] = 'configuration/filter_keywords/$1';

$route['client/identifier/create/(:num)']['POST'] = 'client/create_identifier/$1';
$route['client/identifier/update/(:num)']['PUT'] = 'client/update_identifier/$1';
$route['client/identifier/delete/(:num)']['DELETE'] = 'client/delete_identifier/$1';
$route['client/identifier/(:num)']['GET'] = 'client/identifiers/$1';

