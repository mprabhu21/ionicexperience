<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Index extends CI_Controller {

        public function __construct()
        {
            parent::__construct();
            $this->load->library('form_validation');
            $this->load->library('session');
            $this->load->model('panel');           
            $this->load->model('lister');
        }
	
        public function index()
        {
                if($this->session->userdata('user') == false)
                {
                       echo 'Please login'; //$this->login();
                }
                else
                {
                    $ses_user = $this->session->userdata('user');			
                    $userid = $ses_user->id;
                    //Process for fund requests
                    $data['dashboardcount'] = $this->lister->getDashboardCount();
	                $this->load->view( 'page', $data);
	                $this->load->view('footer');
                }
        }
	
	
	
	public function login()
	{
		$this->load->view('login');
	}
	
	public function logout()
	{
		$this->session->unset_userdata('user');
		$this->login();
	}
	
	public function update_password() 
	{
		if($this->session->userdata('user') == false)
		{
			$this->login();
		}
		else
		{
			$this->load->view('header');
			$this->load->view('change_password');
			$this->load->view('footer');
		}

	}
	
	public function dlogin() {	
	$this->form_validation->set_rules('username', 'Email', 'required|valid_email');
	$this->form_validation->set_rules('password', 'Password', 'required');
	if ($this->form_validation->run() == FALSE)
	{
		$this->load->view('login');
	}
	else
	{
		
		$var = $this->panel->login();
               
		if(!empty($var))
		{
			if($var[0]->user_status =='b')
			{
				$this->session->set_flashdata("error", 'Your account is blocked.please contact administrator');
				redirect('index/login');
			}
			elseif($var[0]->user_status =='w')
			{
				$this->session->set_flashdata("error", 'Your account is not activated');
				redirect('index/login');
			}
			elseif($var[0]->user_status =='a')
			{
				$this->session->set_userdata("user",$var[0]);
				redirect("index");
			}				
			
		}
		else 
		{
			$this->session->set_flashdata("error", 'Username / Password is wrong, Please try again.');
			redirect('index/login');
		}
	}
	}		
}

?>