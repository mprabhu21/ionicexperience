<?php

    class phpmailer_library
    {
        public function __construct()
        {
            log_message('Debug', 'PHPMailer class is loaded.');
        }

        public function load()
        {
            require_once(APPPATH."third_party/PHPMailer-master/src/PHPMailer.php");
            require_once(APPPATH.'third_party/PHPMailer-master/src/SMTP.php');
            $objMail = new PHPMailer\PHPMailer\PHPMailer();
            return $objMail;            
        }
    }

?>