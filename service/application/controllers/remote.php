<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Remote extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('panel');
               
		$this->load->library('session');
		
	}
	
	public function index()
	{
		if($this->session->userdata('user') == false)
		{
			$this->login();
		}
		else
		{
		$this->load->view('page');
		}
	}
	
	public function check_pass()
	{
		if($this->session->userdata('user') == false)
		{
                    redirect('index/login');
		}
		else
		{
                    $ses_user = $this->session->userdata('user');			
                    $userid = $ses_user->usr_id;
                    echo $this->panel->checkpassword(md5($this->input->post('opass')) , $userid);
			
		}
	}
        
        public function dealStatus()
	{
            if($this->session->userdata('user') == false)
            {
                echo 0;
                redirect('index/login');
            }
            else
            {
                if(isset($_POST['id'],$_POST['status']))
                {
                   echo $this->product-> changeStatus($this->input->post('id'),  $this->input->post('status'));
                }
                else
                    echo 0;
                   
            }
	}
	
	public function updatepass()
	{
		if($this->session->userdata('user') == false)
		{
			redirect('index/login');
		}
		else
		{
			$ses_user = $this->session->userdata('user');			
			$userid = $ses_user->usr_id;
			echo $this->panel->updatepassword(md5($this->input->post('newpass')) , $userid) ;
			
		}
	}
	
	//function to get city by country id
	public function getCity()
	{
		$country_id = $this->input->post('cid');
		
		$cityArr = $this->merchant->getCity( $country_id );
                    var_dump($cityArr);
                    echo "<option value='0'>Select City</option>";
		if( is_array($cityArr) && !empty( $cityArr )	)	
		{
			foreach( $cityArr as $cities )
			{		
				echo "<option value=".$cities->cty_id.">".$cities->cty_title."</option>";
			}
		}	
		else { echo '';}		
	}
        //Update Store status "Active/Block"
        public function strStatus()
        {
            $sid = $this->input->post('sid');
            $status = ( $this->input->post('status')==0)?1:0;
          
           // $this->load->model('stores');
            echo  $up = $this->stores->upStatus( $sid , $status );
         
        }
        
        public function delFundReq()
        {
            
            $fr_id = $this->input->post('fid');
           // echo  $fr_id ;  
            $this->load->model('funds');
            echo $dele = $this->funds->delRequest($fr_id);
        }
                
}