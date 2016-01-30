<?php # -*- coding: utf-8 -*-
/**
 * Plugin Name: Egig Contact Form
 * Plugin URI:  http://egig.org
 * Description: A contact from to email
 * Author:      Egi Gundari
 * Author URI:  http://egig.org
 * Version:     0.1.0
 * Text Domain: egigcontactform
 * Domain Path: /languages
 * License:     GPLv3
 */

require __DIR__.'/vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;

$request = Request::createFromGlobals();

(new EgigContactForm)->init($request);