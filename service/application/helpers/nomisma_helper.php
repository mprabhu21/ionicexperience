<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('created_date'))
{
    function created_date()
    {
    	date_default_timezone_set('UTC');
        return date('Y-m-d H:i:s');
    }   
}