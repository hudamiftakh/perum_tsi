<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['pagination'] = [
    'full_tag_open'     => '<nav><ul class="pagination justify-content-center">',
    'full_tag_close'    => '</ul></nav>',
    'first_link'        => '&laquo;',
    'first_tag_open'    => '<li class="page-item">',
    'first_tag_close'   => '</li>',
    'last_link'         => '&raquo;',
    'last_tag_open'     => '<li class="page-item">',
    'last_tag_close'   => '</li>',
    'next_link'         => '&rsaquo;',
    'next_tag_open'    => '<li class="page-item">',
    'next_tag_close'   => '</li>',
    'prev_link'         => '&lsaquo;',
    'prev_tag_open'    => '<li class="page-item">',
    'prev_tag_close'   => '</li>',
    'cur_tag_open'     => '<li class="page-item active"><a class="page-link" href="#">',
    'cur_tag_close'    => '</a></li>',
    'num_tag_open'     => '<li class="page-item">',
    'num_tag_close'    => '</li>',
    'attributes'       => ['class' => 'page-link']
];