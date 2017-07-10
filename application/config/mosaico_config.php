<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Name:  Mosaico PHP Codeigniter Backend Configuration
* 
* Version v1.0.0
* 
* Author: Ahmed Rehan
*		  ar27111994@gmail.com
*         @ar27111994
*
* Location: http://github.com/ar27111994/Mosaico-CodeIgniter-Ion-Auth
*
* Created:  16.06.2017
* 
* Description:  Mosaico Server Path Configuration for Image Gallery Upload, Download and Static / Dynamic Serving. 
* note that all _URL and _DIR configurations below must end with a forward slash (/) 
*/

$config['mosaico_backend'] = [
    
    	/* Url for image serving in final download */
    	'SERVE_URL' => "http://mosaicoci.ar27111994.com/mosaico/img/",
    
    	/* Base Url for accessing Mosaco */
    	'BASE_URL' => "http://mosaicoci.ar27111994.com/mosaico/",
    	
    	/* local file system base path to where image directories are located */
    	'BASE_DIR' => "/home/arcom/public_html/mosaicoci/mosaico/",
    	
    	/* url to the uploads folder (relative to BASE_URL) */
    	'UPLOADS_URL' => "upload/",
    	
    	/* local file system path to the uploads folder (relative to BASE_DIR) */
    	'UPLOADS_DIR' => "upload/",
    	
    	/* url to the static images folder (relative to SERVE_URL) */
    	'STATIC_URL' => "static/",
    
    	/* local file system path to the static images folder (relative to BASE_DIR) */
    	'STATIC_DIR' => "img/static/",
    	
    	/* url to the thumbnail images folder (relative to BASE_URL */
    	'THUMBNAILS_URL' => "upload/thumbnail/",
    	
    	/* local file system path to the thumbnail images folder (relative to BASE_DIR) */
    	'THUMBNAILS_DIR' => "upload/thumbnail/",
    	
    	/* width and height of generated thumbnails */
    	'THUMBNAIL_WIDTH' => 90,
    	'THUMBNAIL_HEIGHT' => 90
];