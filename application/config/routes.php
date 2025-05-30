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
// session_start();
$route['default_controller'] = 'auth';
$route['login'] = 'auth/index';
$route['logout'] = 'auth/logout';
$route['doLogin'] = 'auth/doLogin';
$route['dashboard'] = 'dashboard/index';
$route['agenda/agenda'] = 'dashboard/agenda';
$route['agenda/agenda/add'] = 'dashboard/add_agenda';
$route['agenda/agenda/add/(:any)'] = 'dashboard/add_agenda/$1';
$route['agenda/report_agenda'] = 'dashboard/report_agenda';
$route['agenda/show_participant/(:any)'] = 'dashboard/show_participant/$1';
$route['show_form_participant/(:any)'] = 'dashboard/show_form_participant/$1';
$route['warga/data-warga'] = 'dashboard/warga';
$route['warga/data-warga/(:any)'] = 'dashboard/warga/$1';
// Pendataan
$route['pendataan-keluarga'] = 'dashboard/pendataan_keluarga';
$route['pendataan-keluarga-koordinator'] = 'dashboard/pendataan_keluarga_koordinator';
$route['edit-pendataan-keluarga/(:any)'] = 'dashboard/edit_pendataan_keluarga/$1';
$route['edit-pendataan-keluarga-koordinator/(:any)'] = 'dashboard/edit_pendataan_keluarga_koordinator/$1';
$route['keterisian'] = 'dashboard/keterisian_pendataan';
// Pembayaran
$route['pembayaran'] = 'dashboard/show_form_pembayaran';
$route['pembayaran/(:any)'] = 'dashboard/show_form_pembayaran';
$route['pembayaran/(:any)/(:any)'] = 'dashboard/show_form_pembayaran';
$route['proses-pembayaran'] = 'dashboard/save_pembayaran';
$route['pembayaran-sukses'] = 'dashboard/pembayaran_sukses';
$route['warga/laporan-pembayaran'] = 'dashboard/laporan_pembayaran';
$route['warga/verifikasi-pembayaran'] = 'dashboard/verifikasi_pembayaran';
// kas
$route['edit-pengeluaran/(:any)'] = 'kas/form/$1';
$route['404_override'] = '';
$route['cli/migrate'] = 'migration/index';
$route['translate_uri_dashes'] = FALSE;
