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
| example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
| $route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
| $route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
| $route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples: my-controller/index -> my_controller/index
|   my-controller/my-method -> my_controller/my_method
*/
$route['default_controller'] = 'Cust404';
$route['404_override'] = 'Cust404';
$route['translate_uri_dashes'] = TRUE;
/*
| -------------------------------------------------------------------------
| SALESFORCE Routes
| -------------------------------------------------------------------------
*/

$route['api/v1/page'] = 'Pages/list';
$route['api/v1/page/(:any)'] = 'Pages/detail/$1';

$route['api/v1/info/setting'] = 'Setting/list';
$route['api/v1/info/badges'] = 'Setting/info';

$route['api/v1/banner'] = 'Banner/list';
$route['api/v1/banner/(:any)'] = 'Banner/detail/$1';

$route['api/v1/page'] = 'Pages/list';
$route['api/v1/page/(:any)'] = 'Pages/detail/$1';

$route['api/v1/notification/list'] = 'Notif/list';
$route['api/v1/notification/read'] = 'Notif/read';
$route['api/v1/notification/read/(:any)'] = 'Notif/read/$1';

$route['api/v1/auth/token'] = 'OAuth/token';
$route['api/v1/auth/logout'] = 'OAuth/logout';
$route['api/v1/auth/check'] = 'OAuth/check_user';

// $route['api/v1/otp/call'] = 'Otp/misscall';
$route['api/v1/otp/send'] = 'Otp/send';
$route['api/v1/otp/phone/request'] = 'Otp/req_phone';
$route['api/v1/otp/phone/confirm'] = 'Otp/confirm_req';
$route['api/v1/otp/check'] = 'OAuth/check_otp';
$route['api/v1/otp/confirm'] = 'OAuth/confirm_otp';
$route['bisa/private-staff/otp/view'] = 'OAuth/look_otp';

$route['api/v1/forgot/otp'] = 'Otp/forgot';
$route['api/v1/forgot/confirm'] = 'Otp/confirm';
$route['api/v1/forgot/pass/change'] = 'Otp/password';

$route['api/v1/profile'] = 'Sales/info';
$route['api/v1/profile/info'] = 'Sales/info';
$route['api/v1/profile/bio'] = 'Sales/mini';
$route['api/v1/profile/aboutme'] = 'Keywords/profile';
$route['api/v1/profile/info/(:any)'] = 'Sales/info/$1';
$route['api/v1/profile/bio/(:any)'] = 'Sales/mini/$1';
$route['api/v1/profile/ktp'] = 'Sales/ktp';
$route['api/v1/profile/edit'] = 'Sales/profile';
$route['api/v1/profile/edit/phone'] = 'Sales/phone';
$route['api/v1/profile/point'] = 'Sales/point_history';
$route['api/v1/profile/insight'] = 'Sales/insight';

$route['api/v1/profile/rate'] = 'Sales/rate';
$route['api/v1/profile/rate/(:any)'] = 'Sales/rate/$1';

$route['api/v1/profile/experience/list'] = 'Experience/list';
$route['api/v1/profile/experience/add'] = 'Experience/manage';
$route['api/v1/profile/experience/(:any)'] = 'Experience/detail/$1';
$route['api/v1/profile/experience/(:any)/update'] = 'Experience/manage/$1';
$route['api/v1/profile/experience/(:any)/delete'] = 'Experience/hapus/$1';
$route['api/v1/profile/experience/(:any)/file/delete'] = 'Experience/ref/$1';

$route['api/v1/experience/list'] = 'Experience/list';
$route['api/v1/experience/add'] = 'Experience/manage';
$route['api/v1/experience/(:any)'] = 'Experience/detail/$1';
$route['api/v1/experience/(:any)/update'] = 'Experience/manage/$1';
$route['api/v1/experience/(:any)/delete'] = 'Experience/hapus/$1';
$route['api/v1/experience/(:any)/file/delete'] = 'Experience/ref/$1';

$route['api/v1/profile/password'] = 'Sales/password';
$route['api/v1/profile/upload/avatar'] = 'Sales/upload/avatar';
$route['api/v1/profile/upload/identity'] = 'Sales/upload/identity';
$route['api/v1/profile/resume'] = 'Resume/sales';
$route['api/v1/profile/resume/info'] = 'Resume/info';
$route['api/v1/profile/resume/delete'] = 'Resume/hapus';
$route['api/v1/profile/desire/set'] = 'Sales/desire';
$route['api/v1/profile/desire'] = 'Sales/desires';
$route['api/v1/profile/skill'] = 'Skill/user';
$route['api/v1/profile/skill/set'] = 'Skill/userset';
$route['api/v1/profile/vehicle'] = 'Vehicle/user';
$route['api/v1/profile/vehicle/set'] = 'Vehicle/userset';
$route['api/v1/profile/education'] = 'Education/user';
$route['api/v1/profile/education/add'] = 'Education/manage';
$route['api/v1/profile/education/detail/(:any)'] = 'Education/detail/$1';
$route['api/v1/profile/education/detail/(:any)/update'] = 'Education/manage/$1';
$route['api/v1/profile/education/detail/(:any)/delete'] = 'Education/hapus/$1';
$route['api/v1/profile/language'] = 'Language/user';
$route['api/v1/profile/language/set'] = 'Language/userset';
$route['api/v1/profile/identity'] = 'Identity/user';
$route['api/v1/profile/identity/set'] = 'Identity/userset';
$route['api/v1/profile/avatar/delete'] = 'Sales/avatar';

$route['api/v1/profile/certificate'] = 'Certificate/list';
$route['api/v1/profile/certificate/add'] = 'Certificate/manage';

$route['api/v1/profile/socmed'] = 'Socmed/info';
$route['api/v1/profile/socmed/manage'] = 'Socmed/manage';

$route['api/v1/profile/certificate/(:any)'] = 'Certificate/detail/$1';
$route['api/v1/profile/certificate/(:any)/update'] = 'Sales/certificate/$1';
$route['api/v1/profile/certificate/(:any)/delete'] = 'Certificate/hapus/$1';
$route['api/v1/profile/certificate/(:any)/file'] = 'Certificate/image/$1';

$route['api/v1/certificate/add'] = 'Sales/certificate';
$route['api/v1/certificate/list'] = 'Certificate/list';
$route['api/v1/certificate/(:any)/update'] = 'Sales/certificate/$1';
$route['api/v1/certificate/(:any)/detail'] = 'Certificate/detail/$1';
$route['api/v1/certificate/(:any)/delete'] = 'Certificate/hapus/$1';
$route['api/v1/certificate/(:any)/file'] = 'Certificate/image/$1';

$route['api/v1/news'] = 'Newsfeed/list';
$route['api/v1/news/read/(:any)'] = 'Newsfeed/detail/$1';
$route['api/v1/news/read/(:any)/comment'] = 'Comments/add/$1';
$route['api/v1/news/read/(:any)/react'] = 'Newsfeed/react/$1';
$route['api/v1/news/read/(:any)/comment/more'] = 'Comments/more/$1';

$route['api/v1/jobs'] = 'Jobs/list';
$route['api/v1/jobs/filter'] = 'Jobs/filter';
$route['api/v1/jobs/detail/(:any)'] = 'Jobs/detail/$1';
$route['api/v1/jobs/bookmark'] = 'Bookmark/list';
$route['api/v1/jobs/bookmark/(:any)'] = 'Bookmark/job/$1';
$route['api/v1/jobs/detail/(:any)/comment'] = 'Comments/addjob/$1';
$route['api/v1/jobs/detail/(:any)/react'] = 'Jobs/react/$1';
$route['api/v1/jobs/detail/(:any)/comment/more'] = 'Comments/morejob/$1';

$route['api/v1/jobs/application'] = 'Apply/list';
$route['api/v1/jobs/application/(:any)/approve'] = 'Apply/approval/$1/approve';
$route['api/v1/jobs/application/(:any)/reject'] = 'Apply/approval/$1/reject';
$route['api/v1/jobs/application/(:any)/detail'] = 'Apply/detail/$1';

$route['api/v1/jobs/apply/(:any)'] = 'Apply/job/$1';


$route['api/v1/job/position'] = 'Position/list';

$route['api/v1/job/category'] = 'Position/category';

$route['api/v1/job/type'] = 'Position/type';

$route['api/v1/skill'] = 'Skill/list';
$route['api/v1/vehicle'] = 'Vehicle/list';
$route['api/v1/education'] = 'Education/list';
$route['api/v1/language'] = 'Language/list';
$route['api/v1/identity'] = 'Identity/list';
$route['api/v1/keyword'] = 'Keywords/list';

$route['api/v1/location'] = 'Location/latlong';
$route['api/v1/location/search'] = 'Location/cari';
$route['api/v1/region/province'] = 'Location/province';
$route['api/v1/region/city/(:any)'] = 'Location/city/$1';
$route['api/v1/location/city'] = 'Location/onlycity';
$route['api/v1/region/district/(:any)'] = 'Location/district/$1';
$route['api/v1/region/subdistrict/(:any)'] = 'Location/subdistrict/$1';


$route['pintap/princ/dashboard/notif/test'] = 'Application/notif';

$route['pintap/princ/dashboard/apply'] = 'Application/list';
$route['pintap/princ/dashboard/apply/approval'] = 'Application/list/approval';
$route['pintap/princ/dashboard/apply/(:any)'] = 'Application/list/$1';
$route['pintap/princ/dashboard/apply/(:any)/approve'] = 'Application/approval/$1/approve';
$route['pintap/princ/dashboard/apply/(:any)/reject'] = 'Application/approval/$1/reject';
$route['pintap/princ/dashboard/apply/(:any)/interview/accept'] = 'Application/nextapproval/$1/approve';
$route['pintap/princ/dashboard/apply/(:any)/interview/reject'] = 'Application/nextapproval/$1/reject';
