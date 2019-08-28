<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation');
		$this->load->library('session');
        $this->load->library('pagination');
        $this->load->helper(array('form', 'url'));
		$this->load->model('lister');
	}
        
        public function checkses()
        {
            if($this->session->userdata('user') == false)
            {
                redirect('index/login');
            }
            else
            {
                $ses_user = $this->session->userdata('user');			
                return $ses_user;
            }
        }
        
        public function page()
        {
            $ses_user = $this->checkses();            
            $this->load->view("dashboard");
            $this->load->view('footer');
        }
               

        public function module_management()
        {
            $this->load->view('header');
            $ses_user =  $this->checkses();
            $data['modulelist'] = $this->lister->getModules();
            $this->load->view('module_management', $data);
            $this->load->view('footer');
        }
        
        public function add_module_management()
        {
            $this->load->view('header');
            $ses_user =  $this->checkses();
            $data = array(
                    "id" => -1,
                    "title" => '',
                    "price" => '',
                    "features" => '',
                    "dependent_module" => '',
                    "module_status" => -1,
                    "description" => '',
                    "module_code" => '',
                    "action" => 'add_form',
                    "type" => 'Add',
                    "module_used" => ''
                );
            $data['employeerange'] = $this->lister->getEmployees();
            $this->load->view('add_module_management', $data);
            $this->load->view('footer');
        }

        public function save_module_management()
        {
            $ses_user = $this->checkses();
            $this->form_validation->set_rules('title','Title', 'required|min_length[2]');
            $this->form_validation->set_rules('price[]','Price', 'required|numeric');
            $this->form_validation->set_rules('module_status','Status', 'required');
            $pos_v = $this->input->post();          
            if ($this->form_validation->run() == FALSE)
            {     
                $this->load->view('header');
                $ses_user =  $this->checkses();
                $data = array(
                        "id" => -1,
                        "title" => '',
                        "features" => '',
                        "dependent_module" => '',
                        "module_status" => -1,
                        "module_code" => '',
                        "description" => '',
                        "action" => 'add_form',
                        "type" => 'Add',
                        "module_used" => ''
                    );
                $data['employeerange'] = $this->lister->getEmployees();
                $this->load->view('add_module_management', $data);
                $this->load->view('footer');
            } else {
                //print_r($pos_v['hideprice']);                
                $newarr = array(
                    "module_title" => $pos_v['title'],
                    "module_price" => '',
                    "module_features" => $pos_v['features'],
                    "dependent_module" => '',
                    "status" => $pos_v['module_status'],
                    "module_code" => $pos_v['module_code'],
                    "dels" => 0,
                    "module_description" => $pos_v['description'],
                    "created_date" => created_date(),
                    "updated_date" => created_date()
                );
                if($pos_v['id'] == -1){
                    $res = $this->lister->saveModule($newarr);
                    $this->lister->deleteModulePricing($res);
                    if(isset($pos_v['price']) && !empty($pos_v['price'])){                        
                        foreach($pos_v['hideprice'] as $key => $value){
                            $newarr = array(
                                "module_id" => $res,
                                "emp_range_id" => $value,
                                "amount" => $pos_v['price'][$key],
                                "updated_on" => date("Y-m-d H:i:s")
                            );
                            $this->lister->saveModulePricing($newarr);
                        }
                    }
                    $this->session->set_flashdata('error','Module added successfully');
                } else if($pos_v['id'] != -1){
                    $newarr['id'] = $pos_v['id'];
                    unset($newarr['created_date']);
                    $res = $this->lister->updateModule($newarr);
                    $this->lister->deleteModulePricing($pos_v['id']);
                    //print_r($pos_v['hideprice']); print_r($pos_v['price']); exit;
                    if(isset($pos_v['price']) && !empty($pos_v['price'])){
                        foreach($pos_v['hideprice'] as $key => $value){
                            //echo $value; exit;
                            $newarr = array(
                                "module_id" => $pos_v['id'],
                                "emp_range_id" => $value,
                                "amount" => $pos_v['price'][$key],
                                "updated_on" => date("Y-m-d H:i:s")
                            );                            
                            $this->lister->saveModulePricing($newarr);
                        }
                        //echo '<pre>'; print_r($newarr); exit;
                    }
                    $this->session->set_flashdata('error','Module updated successfully');
                }                
                redirect('user/module_management');
            }
        }

        public function edit_module_management()
        {
            $this->load->view('header');
            $mvalue = array();
            $ses_user =  $this->checkses();
            $lister = $this->lister->getModules(base64_decode($_GET['id']));
            $data = array(
                    "id" => $lister[0]->id,
                    "title" => $lister[0]->module_title,
                    "price" => $lister[0]->module_price,
                    "features" => $lister[0]->module_features,
                    "dependent_module" => $lister[0]->dependent_module,
                    "module_status" => $lister[0]->status,
                    "module_code" => $lister[0]->module_code,
                    "description" => $lister[0]->module_description,
                    "action" => 'edit_form',
                    "type" => 'Edit'
                );
            $employeerange = $this->lister->getModulePricing(base64_decode($_GET['id']));
            $data['employeerange'] = (empty($employeerange)) ? $this->lister->getEmployees() : $employeerange;

            if(!empty($employeerange)){
                $emprange = $this->lister->getEmployees();
                $newemprange = $this->lister->getModulePricing(base64_decode($_GET['id']));
                foreach($emprange as $key => $value) {
                    if(!isset($newemprange[$key]->emp_range_id)){
                        if(!empty($value)){
                            array_push($data['employeerange'], $value);    
                        }
                    }
                }
            }
            $data['module_used'] = $this->lister->getModuleUsed(base64_decode($_GET['id']));            
            $data['module_used'] = ($data['module_used'] > 0) ? 'view_form' : '';
            $this->load->view('add_module_management', $data);
            $this->load->view('footer');
        }

        public function view_module_management()
        {
            $this->load->view('header');
            $ses_user =  $this->checkses();
            $lister = $this->lister->getModules(base64_decode($_GET['id']));
            $data = array(
                    "id" => $lister[0]->id,
                    "title" => $lister[0]->module_title,
                    "price" => $lister[0]->module_price,
                    "features" => $lister[0]->module_features,
                    "dependent_module" => $lister[0]->dependent_module,
                    "module_status" => $lister[0]->status,
                    "module_code" => $lister[0]->module_code,
                    "description" => $lister[0]->module_description,
                    "action" => 'view_form',
                    "type" => 'View',
                    "module_used" => ''
                );
            $data['employeerange'] = $this->lister->getModulePricing(base64_decode($_GET['id']));
            $this->load->view('add_module_management', $data);
            $this->load->view('footer');
        }

        public function offer_management()
        {
            $this->load->view('header');
            $ses_user =  $this->checkses();
            $data['modulelist'] = $this->lister->getOffers();
            $this->load->view('offer_management', $data);
            $this->load->view('footer');
        }

        public function add_offer_management()
        {
            $this->load->view('header');
            $ses_user =  $this->checkses();
            $data = array(
                    "id" => -1,
                    "offer_code" => '',
                    "offer_title" => '',
                    "offer_type" => '',
                    "offer_validity" => '',
                    "offer_validity_starts" => '',
                    "upfront_period" => '',
                    "offer_valid_period" => '',
                    "offer_valid_free" => '',
                    "upfront_freeperiod" => '',
                    "percentage_value" => '',
                    "addon_modules" => '',
                    "status" => -1,
                    "offer_description" => '',
                    "action" => 'add_form',
                    "type" => 'Add',
                    "choosen_addon_modules" => ''
                );
            $getModules = $this->lister->getAddons();
            $data['modulelist'][' " disabled="disabled'] = "Select";
            foreach($getModules as $modulelist){
                $data['modulelist'][$modulelist->id] = $modulelist->addon_title;
            }
            $this->load->view('add_offer_management', $data);
            $this->load->view('footer');
        }

        function check_offer_code($email) {
            $return_value = $this->lister->check_offer_code($email, $this->input->post('id'));
            if ($return_value){
                $this->form_validation->set_message('check_offer_code', 'sorry, offer code used, please select one');
                return false;
            } else {
                return true;
            }
        }

        public function save_offer_management()
        {
            $ses_user = $this->checkses();
            $upfront_period = $this->input->post('offer_type') == 'upfront' ? '|required' : '' ;
            $percentage = $this->input->post('offer_type') == 'percentage' ? '|required' : '' ;
            $addon = $this->input->post('offer_type') == 'addon' ? '|required' : '' ;

            $this->form_validation->set_rules('offer_code','offer code', 'required|min_length[2]|max_length[250]|callback_check_offer_code');
            $this->form_validation->set_rules('offer_title','offer title', 'required|min_length[2]|max_length[250]');
            $this->form_validation->set_rules('status','status', 'required');
            $this->form_validation->set_rules('offer_type','offer type', 'required');
            $this->form_validation->set_rules('offer_validity_starts','offer validity starts', 'required');
            $this->form_validation->set_rules('offer_validity','offer validity ends', 'required');

            $this->form_validation->set_rules('upfront_period','period', 'trim|numeric'.$upfront_period);
            $this->form_validation->set_rules('upfront_freeperiod','free period', 'trim|numeric'.$upfront_period);
            $this->form_validation->set_rules('percentage_value','percentage', 'trim|numeric'.$percentage);
            $this->form_validation->set_rules('addon_modules[]','addon', 'trim'.$addon);
            $pos_v = $this->input->post();
            if ($this->form_validation->run() == FALSE)
            {     
                if($pos_v['id'] == -1){
                    $this->load->view('header');
                    $ses_user =  $this->checkses();
                    $data = array(
                            "id" => -1,
                            "offer_code" => '',
                            "offer_title" => '',
                            "offer_type" => '',
                            "offer_validity" => '',
                            "offer_validity_starts" => '',
                            "upfront_period" => '',
                            "upfront_freeperiod" => '',
                            "offer_valid_period" => '',
                            "offer_valid_free" => '',
                            "percentage_value" => '',
                            "addon_modules" => '',
                            "status" => -1,
                            "offer_description" => '',
                            "action" => 'add_form',
                            "type" => 'Add',
                            "choosen_addon_modules" => ''
                        );
                    $getModules = $this->lister->getAddons();
                    $data['modulelist'][' " disabled="disabled'] = "Select";
                    foreach($getModules as $modulelist){
                        $data['modulelist'][$modulelist->id] = $modulelist->addon_title;
                    }
                    $this->load->view('add_offer_management', $data);
                    $this->load->view('footer');   
                } else {
                    //print_r($_GET['id']);
                    $this->load->view('header');
                    $ses_user =  $this->checkses();
                    $lister = $this->lister->getOffers($pos_v['id']);
                    $data = (array)$lister[0];
                    $data['action'] = 'edit_form';
                    $data['id'] = $pos_v['id'];
                    $data['type'] = 'Edit';
                    $data['offer_validity'] = date('Y-m-d', strtotime($data['offer_validity']));
                    $data['offer_validity_starts'] = date('Y-m-d', strtotime($data['offer_validity_starts']));            
                    $data['choosen_addon_modules'] = '';
                    if($data['addon_modules'] != ''){
                        $data['choosen_addon_modules'] = explode(",", $data['addon_modules']);
                    }
                    $getModules = $this->lister->getAddons();
                    $data['modulelist'][' " disabled="disabled'] = "Select";
                    foreach($getModules as $modulelist){
                        $data['modulelist'][$modulelist->id] = $modulelist->addon_title;
                    }
                    $this->load->view('add_offer_management', $data);
                    $this->load->view('footer');                    
                }
                
                /*$this->load->view('header');
                $ses_user =  $this->checkses();
                $data = array(
                        "id" => -1,
                        "offer_code" => '',
                        "offer_title" => '',
                        "offer_type" => '',
                        "offer_validity" => '',
                        "offer_validity_starts" => '',
                        "upfront_period" => '',
                        "upfront_freeperiod" => '',
                        "offer_valid_period" => '',
                        "offer_valid_free" => '',
                        "percentage_value" => '',
                        "addon_modules" => '',
                        "status" => -1,
                        "offer_description" => '',
                        "action" => 'add_form',
                        "type" => 'Add',
                        "choosen_addon_modules" => ''
                    );
                $getModules = $this->lister->getAddons();
                $data['modulelist'][' " disabled="disabled'] = "Select";
                foreach($getModules as $modulelist){
                    $data['modulelist'][$modulelist->id] = $modulelist->addon_title;
                }
                $this->load->view('add_offer_management', $data);
                $this->load->view('footer');*/
            } else {
                $pos_v['offer_code'] = strtoupper($pos_v['offer_code']);
                $pos_v['offer_validity'] = $pos_v['offer_validity'].' 23:59:59';
                $pos_v['created_on'] = created_date();
                $pos_v['updated_on'] = created_date();
                $pos_v['dels'] = 0;                
                $newarr = $pos_v;
                $newarr['addon_modules'] = implode(',', $newarr['addon_modules']);
                if($pos_v['id'] == -1){
                    unset($newarr['id']);
                    //unset($newarr['addon_modules']);
                    if($newarr['offer_type'] == 'upfront'){
                       $newarr['percentage_value'] = 0;
                       $newarr['addon_modules'] = '';
                    } else if($newarr['offer_type'] == 'percentage'){
                       $newarr['upfront_period'] = 0;
                       $newarr['upfront_freeperiod'] = 0;
                       $newarr['addon_modules'] = '';
                    } else if($newarr['offer_type'] == 'addon'){
                       $newarr['upfront_period'] = 0;
                       $newarr['upfront_freeperiod'] = 0;
                       $newarr['percentage_value'] = '';
                    }
                    //print_r($newarr); exit;
                    $res = $this->lister->saveOffer($newarr);
                    $this->session->set_flashdata('error','Offer added successfully');
                } else if($pos_v['id'] != -1){
                    if($newarr['offer_type'] == 'upfront'){
                       $newarr['percentage_value'] = 0;
                       $newarr['addon_modules'] = '';
                    } else if($newarr['offer_type'] == 'percentage'){
                       $newarr['upfront_period'] = 0;
                       $newarr['upfront_freeperiod'] = 0;
                       $newarr['addon_modules'] = '';
                    } else if($newarr['offer_type'] == 'addon'){
                       $newarr['upfront_period'] = 0;
                       $newarr['upfront_freeperiod'] = 0;
                       $newarr['percentage_value'] = '';
                    }
                    unset($newarr['created_on']);     
                    //print_r($newarr); exit;               
                    $res = $this->lister->updateOffer($newarr);
                    $this->session->set_flashdata('error','Offer updated successfully');
                }                
                redirect('user/offer_management');
            }
        }

        public function edit_offer_management()
        {
            $this->load->view('header');
            $ses_user =  $this->checkses();
            $lister = $this->lister->getOffers(base64_decode($_GET['id']));
            $data = (array)$lister[0];
            $data['action'] = 'edit_form';
            $data['type'] = 'Edit';
            $data['offer_validity'] = date('Y-m-d', strtotime($data['offer_validity']));
            $data['offer_validity_starts'] = date('Y-m-d', strtotime($data['offer_validity_starts']));            
            $data['choosen_addon_modules'] = '';
            if($data['addon_modules'] != ''){
                $data['choosen_addon_modules'] = explode(",", $data['addon_modules']);
            }
            $getModules = $this->lister->getAddons();
            $data['modulelist'][' " disabled="disabled'] = "Select";
            foreach($getModules as $modulelist){
                $data['modulelist'][$modulelist->id] = $modulelist->addon_title;
            }
            $this->load->view('add_offer_management', $data);
            $this->load->view('footer');
        }

        public function view_offer_management()
        {
            $this->load->view('header');
            $ses_user =  $this->checkses();
            $lister = $this->lister->getOffers(base64_decode($_GET['id']));
            $data = (array)$lister[0];
            $data['action'] = 'view_form';
            $data['type'] = 'View';
            $data['offer_validity'] = date('Y-m-d', strtotime($data['offer_validity']));
            $data['offer_validity_starts'] = date('Y-m-d', strtotime($data['offer_validity_starts']));
            $data['choosen_addon_modules'] = '';
            if($data['addon_modules'] != ''){
                $data['choosen_addon_modules'] = explode(",", $data['addon_modules']);
            }
            $getModules = $this->lister->getAddons();
            $data['modulelist'][' " disabled="disabled'] = "Select";
            foreach($getModules as $modulelist){
                $data['modulelist'][$modulelist->id] = $modulelist->addon_title;
            }
            $this->load->view('add_offer_management', $data);
            $this->load->view('footer');
        }

        public function update_password()
        {
            $ses_user = $this->checkses();
            $this->form_validation->set_rules('oldpassword','old password', 'required');
            $this->form_validation->set_rules('newpassword','new password', 'required|min_length[8]|max_length[20]');            
            $this->form_validation->set_rules('confirmpassword','confirm password', 'required|min_length[8]|max_length[20]|matches[newpassword]');
            $pos_v = $this->input->post();          
            if ($this->form_validation->run() == FALSE)
            {     
               $this->load->view('header');
                $this->load->view('change_password');
                $this->load->view('footer');
            } else {                
                if($this->session->userdata('user') == false)
                {
                    redirect('index/login');
                }
                else
                {
                    $ses_user = $this->session->userdata('user');           
                    //print_r($ses_user);
                    $userid = $ses_user->id;
                    $checkoldpassword = $this->lister->checkpassword(md5($this->input->post('oldpassword')) , $userid);
                    print_r($checkoldpassword);
                    if($checkoldpassword){

                    } else {
                        $this->session->set_flashdata('error','Invalid old password');
                    }
                    
                }
                print_r($pos_v); exit;
            }            
        }        

        public function addon_management()
        {
            $this->load->view('header');
            $ses_user =  $this->checkses();
            $data['modulelist'] = $this->lister->getAddons();
            $this->load->view('addon_management', $data);
            $this->load->view('footer');
        }

        public function add_addon_management()
        {
            $this->load->view('header'); //test
            $ses_user =  $this->checkses();
            $data = array(
                    "id" => -1,
                    "addon_title" => '',
                    "addon_price" => '',
                    "addon_features" => '',
                    "dependent_module" => '',
                    "default_addon" => 1,
                    "status" => -1,
                    "addon_description" => '',
                    "action" => 'add_form',
                    "type" => 'Add',
                    "choosen_addon_modules" => '',
                    "addonused" => ''
                );
            $getModules = $this->lister->getActiveModules();
            $data['modulelist'][' " disabled="disabled "selected="selected'] = "Select";
            foreach($getModules as $modulelist){
                $data['modulelist'][$modulelist->id] = $modulelist->module_title;
            }
            $data['employeerange'] = $this->lister->getEmployees();
            $this->load->view('add_addon_management', $data);
            $this->load->view('footer');
        }

        public function save_addon_management()
        {
            $ses_user = $this->checkses();
            $this->form_validation->set_rules('addon_title','Title', 'required|min_length[2]');
            $this->form_validation->set_rules('price[]','Price', 'required|numeric');
            $this->form_validation->set_rules('status','Status', 'required');
            $pos_v = $this->input->post();          
            if ($this->form_validation->run() == FALSE)
            {     
                $this->load->view('header');
                $ses_user =  $this->checkses();
                $data = array(
                        "id" => -1,
                        "addon_title" => '',
                        "addon_features" => '',
                        "dependent_module" => '',
                        "status" => -1,
                        "addon_description" => '',
                        "action" => 'add_form',
                        "type" => 'Add',
                        "choosen_addon_modules" => '',
                        "addonused" => ''
                    );
                $getModules = $this->lister->getModules();
                $data['modulelist'][' " disabled="disabled "selected="selected'] = "Select";
                foreach($getModules as $modulelist){
                    $data['modulelist'][$modulelist->id] = $modulelist->module_title;
                }
                $data['employeerange'] = $this->lister->getEmployees();
                $this->load->view('add_addon_management', $data);
                $this->load->view('footer');
            } else {
                $pos_v['created_on'] = created_date();
                $pos_v['updated_on'] = created_date();
                $pos_v['dels'] = 0;                
                $newarr = $pos_v;                
                if($pos_v['id'] == -1){
                    unset($newarr['id']);
                    unset($newarr['price']);
                    unset($newarr['hideprice']);
                    $res = $this->lister->saveAddon($newarr);
                    $this->lister->deleteAddonPricing($res);                    
                    if(isset($pos_v['price']) && !empty($pos_v['price'])){
                        foreach($pos_v['hideprice'] as $key => $value){
                            $newarr = array(
                                "module_id" => $pos_v['dependent_module'],
                                "addon_id" => $res,
                                "emp_range_id" => $value,
                                "amount" => $pos_v['price'][$key],
                                "updated_on" => date("Y-m-d H:i:s")
                            );
                            $this->lister->saveAddonPricing($newarr);
                        }
                    }
                    $this->session->set_flashdata('error','Addon added successfully');
                } else if($pos_v['id'] != -1){                    
                    unset($newarr['created_on']);
                    unset($newarr['price']);
                    unset($newarr['hideprice']);
                    $res = $this->lister->updateAddon($newarr);
                    $this->lister->deleteAddonPricing($pos_v['id']);
                    //print_r($pos_v['hideprice']); print_r($pos_v['price']); exit;
                    if(isset($pos_v['price']) && !empty($pos_v['price'])){
                        foreach($pos_v['price'] as $key => $value){
                            $newarr = array(
                                "module_id" => $pos_v['dependent_module'],
                                "addon_id" => $pos_v['id'],
                                "emp_range_id" => $pos_v['hideprice'][$key],
                                "amount" => $pos_v['price'][$key],
                                "updated_on" => date("Y-m-d H:i:s")
                            );
                            print_r($newarr);
                            $this->lister->saveAddonPricing($newarr);
                        }
                        //exit;
                    }
                    $this->session->set_flashdata('error','Addon updated successfully');
                }
                redirect('user/addon_management');
            }
        }

        public function edit_addon_management()
        {
            $this->load->view('header');
            $ses_user =  $this->checkses();
            $lister = $this->lister->getAddons(base64_decode($_GET['id']));
            $data = (array)$lister[0];
            $data['action'] = 'edit_form';
            $data['type'] = 'Edit';
            $data['choosen_addon_modules'] = $data['dependent_module']; 
            $getModules = $this->lister->getModules();
            $data['modulelist'][' " disabled="disabled'] = "Select";
            foreach($getModules as $modulelist){
                $data['modulelist'][$modulelist->id] = $modulelist->module_title;
            }            
            $data['employeerange'] = $this->lister->getAddonPricing(base64_decode($_GET['id']), $data['dependent_module']);
            $data['employeerange'] = (empty($data['employeerange'])) ? $this->lister->getEmployees() : $data['employeerange'];
            $data['addonused'] = $this->lister->getAddonUsed(base64_decode($_GET['id']));
            $data['addonused'] = ($data['addonused']) ? 'view_form' : '';
            $this->load->view('add_addon_management', $data);
            $this->load->view('footer');
        }

        public function view_addon_management()
        {
            $this->load->view('header');
            $ses_user =  $this->checkses();
            $lister = $this->lister->getAddons(base64_decode($_GET['id']));
            $data = (array)$lister[0];
            $data['action'] = 'view_form';
            $data['type'] = 'View';
            $data['addonused'] = '';
            $data['modulelist'][' " disabled="disabled'] = "Select";
            $data['choosen_addon_modules'] = $data['dependent_module'];
            $getModules = $this->lister->getModules();
            foreach($getModules as $modulelist){
                $data['modulelist'][$modulelist->id] = $modulelist->module_title;
            }
            $data['employeerange'] = $this->lister->getAddonPricing(base64_decode($_GET['id']), $data['dependent_module']);
            $data['employeerange'] = (empty($data['employeerange'])) ? $this->lister->getEmployees() : $data['employeerange'];
            $this->load->view('add_addon_management', $data);
            $this->load->view('footer');
        }

        public function trial_users()
        {
            $this->load->view('header');
            $ses_user =  $this->checkses();
            $data['modulelist'] = $this->lister->getTrialUsers();
            $this->load->view('trial_user_management', $data);
            $this->load->view('footer');
        }

        public function add_trial_users_management()
        {
            $this->load->view('header');
            $ses_user =  $this->checkses();
            $data = array(
                    "id" => -1,
                    "first_name" => '',
                    "last_name" => '',
                    "email_address" => '',
                    "company_name" => '',
                    "status" => -1,
                    "phone_number" => '',
                    "action" => 'add_form',
                    "type" => 'Add'
                );            
            $this->load->view('add_trial_users_management', $data);
            $this->load->view('footer');
        }


        public function save_trial_users_management()
        {
            $ses_user = $this->checkses();
            $this->form_validation->set_rules('first_name','First name', 'required|min_length[2]');
            $this->form_validation->set_rules('last_name','Last name', 'required|min_length[2]');
            $this->form_validation->set_rules('email_address','Email address', 'required|email');
            $this->form_validation->set_rules('company_name','Company Name', 'required|min_length[2]');
            $this->form_validation->set_rules('phone_number','Phone Number', 'required|numeric');
            $this->form_validation->set_rules('status','Status', 'required');
            $pos_v = $this->input->post();          
            if ($this->form_validation->run() == FALSE)
            {     
                $this->load->view('header');
                $ses_user =  $this->checkses();
                $data = array(
                    "id" => -1,
                    "first_name" => '',
                    "last_name" => '',
                    "email_address" => '',
                    "company_name" => '',
                    "status" => -1,
                    "phone_number" => '',
                    "action" => 'add_form',
                    "type" => 'Add'
                );                
                $this->load->view('add_trial_users_management', $data);
                $this->load->view('footer');
            } else {
                $pos_v['created_on'] = created_date();
                $pos_v['updated_on'] = created_date();
                $pos_v['dels'] = 0;                
                $newarr = $pos_v;                
                if($pos_v['id'] == -1){
                    unset($newarr['id']);
                    $res = $this->lister->saveTrialUsers($newarr);
                    $this->session->set_flashdata('error','Trial users added successfully');
                } else if($pos_v['id'] != -1){                    
                    unset($newarr['created_on']);                    
                    $res = $this->lister->updateTrialUsers($newarr);
                    $this->session->set_flashdata('error','Trial users updated successfully');
                }
                redirect('user/trial_users');
            }
        }

        public function edit_trial_users_management()
        {
            $this->load->view('header');
            $ses_user =  $this->checkses();
            $lister = $this->lister->getTrialUsers(base64_decode($_GET['id']));
            $data = (array)$lister[0];
            $data['action'] = 'edit_form';
            $data['type'] = 'Edit';            
            $this->load->view('add_trial_users_management', $data);
            $this->load->view('footer');
        }

        public function view_trial_users_management()
        {
            $this->load->view('header');
            $ses_user =  $this->checkses();
            $lister = $this->lister->getTrialUsers(base64_decode($_GET['id']));
            $data = (array)$lister[0];
            $data['action'] = 'view_form';
            $data['type'] = 'View';            
            $this->load->view('add_trial_users_management', $data);
            $this->load->view('footer');
        }

        public function generate_payment_url()
        {
            $this->load->view('header');
            $ses_user =  $this->checkses();
            $data['userdetails'] = $this->lister->getTrialUsers(base64_decode($_GET['id']));
            $noemployees = $data['userdetails'][0]->employees;
            $data['listemprange'] = $this->lister->getEmployees();            
            //echo $noemployees;
            //print_r($data['listemprange']);
            $emprangeid = 1;
            foreach ($data['listemprange'] as $key => $value) {
                if (strpos($value->emp_range, '+') !== false) {
                    $range = explode("+", $value->emp_range);
                    if($noemployees > $range[0]){
                        $emprangeid = $value->id;
                    }
                } else if (strpos($value->emp_range, '-') !== false) {
                    $range = explode("-", $value->emp_range);
                    if($range[0] <= $noemployees && $noemployees <= $range[1]){
                        $emprangeid = $value->id;
                    }           
                }
            }
            $data['employee_range_id'] = $emprangeid;
            $data['modulelist'] = $this->lister->getModuleWisePricing($emprangeid);

            //print_r($data['pricinglist']); //exit;
            /*foreach ($data['pricinglist'] as $key => $value) {
                $data['modulelist'][] = $this->lister->getModulePricing($value->module_id);
            }*/            
            $this->load->view('generate_payment_url', $data);
            $this->load->view('footer');
        }

        public function getModuleAddons()
        {
            if(isset($_REQUEST)){
                return $this->lister->getModuleAddons($_REQUEST);
            }            
        }

        public function getAddonsList(){
            if(isset($_REQUEST)){
                return $this->lister->getAddonsList($_REQUEST);
            }
        }

        public function payment_management()
        {
            $this->load->view('header');
            $ses_user =  $this->checkses();
            $data['modulelist'] = $this->lister->getPayments();
            $this->load->view('payment_management', $data);
            $this->load->view('footer');
        }

        public function employee_management()
        {
            $this->load->view('header');
            $ses_user =  $this->checkses();
            $data['modulelist'] = $this->lister->getEmployees();
            $this->load->view('employee_management', $data);
            $this->load->view('footer');
        }

        public function add_employee_management()
        {
            $this->load->view('header');
            $ses_user =  $this->checkses();
            $data = array(
                    "id" => -1,
                    "from_range" => '',
                    "to_range" => '',
                    "amount" => '',
                    "status" => -1,
                    "action" => 'add_form',
                    "type" => 'Add'
            );
            $data['employeerange'] = $this->lister->getEmployees();
            $price = array();
            foreach($data['employeerange'] as $key => $value){
                $aprice[$value->to_range] = $value;
                $price[$value->from_range] = $value;
            }

            if(isset($data['employeerange']) && !empty($data['employeerange']) && count($data['employeerange']) > 0){
                $data['from_range'] = $data['employeerange'][count($data['employeerange'])-1]->to_range+1;
            } else {
                $data['from_range'] = 1;
            }

            if(count($data['employeerange']) > 0){                
                $nprice = array_keys($price); 
                $bprice = array_keys($aprice); 
                asort($nprice);
                asort($bprice);
                $bprice = array_values($bprice);
                $nprice = array_values($nprice);                                
                $value_from_range = '';            
                foreach($nprice as $key => $value){
                    if($key > 0){
                        $value_from_range = $nprice[$key];
                        $value_to_range = $bprice[$key-1]+1;
                        if($value_to_range != $value_from_range){
                            //$data['from_range'] = $value_to_range;
                            //$range_to = $nprice[$key]-1;
                            //$data['to_range'] = $range_to;
                            //$data['to_range_max'] = $range_to;
                        }
                    }
                }
            }
            $data['modulelist'] = $this->lister->getModules();
            $data['addonlist'] = $this->lister->getAddons();
            $this->load->view('add_employee_management', $data);
            $this->load->view('footer');
        }

        public function save_employee_management()
        {
            $ses_user = $this->checkses();
            $this->form_validation->set_rules('from_range','from range', 'required|numeric');
            $this->form_validation->set_rules('to_range','to range', 'required|numeric');
            //$this->form_validation->set_rules('amount','Phone Number', 'required|numeric');
            //$this->form_validation->set_rules('status','Status', 'required');
            $pos_v = $this->input->post();          
            //print_r($pos_v); exit;
            if ($this->form_validation->run() == FALSE)
            {     
                $this->load->view('header');
                $ses_user =  $this->checkses();
                $data = array(
                    "id" => -1,
                    "amount" => '',
                    "from_range" => '',
                    "to_range" => '',
                    "status" => -1,
                    "action" => 'add_form',
                    "type" => 'Add'
                );                
                $data['employeerange'] = $this->lister->getEmployees();
                if(isset($data['employeerange']) && !empty($data['employeerange']) && count($data['employeerange']) > 0){
                    $data['from_range'] = $data['employeerange'][count($data['employeerange'])-1]->to_range+1;
                } else {
                    $data['from_range'] = 1;
                }                
                $this->load->view('add_employee_management', $data);
                $this->load->view('footer');
            } else {
                $pos_v['created_on'] = created_date();
                $pos_v['status'] = 1;
                $pos_v['emp_range'] = $pos_v['from_range'].'-'.$pos_v['to_range'];
                $newarr = $pos_v;                
                if($pos_v['id'] == -1){
                    unset($newarr['id']);              
                    $priceaarray = $newarr['price'];
                    $hidepricearray = $newarr['hideprice'];

                    $hideaddonprice = $newarr['addonprice'];
                    $hidedependentmodule = $newarr['dependentmodule'];
                    $hideaddonhideprice = $newarr['addonhideprice'];
                    unset($newarr['price']);
                    unset($newarr['hideprice']);
                    unset($newarr['addonprice']);
                    unset($newarr['dependentmodule']);
                    unset($newarr['addonhideprice']);
                    
                    $res = $this->lister->saveEmployee($newarr);
                    foreach($priceaarray as $key => $value){
                        $insmodule[] = array(
                                    "module_id" => $hidepricearray[$key],
                                    "emp_range_id" => $res,
                                    "amount" => $value
                                    );
                    }
                    $this->lister->saveModulePricingBatch($insmodule);
                    
                    foreach($hideaddonprice as $key => $value){
                        $insaddon[] = array(
                                    "module_id" => $hidedependentmodule[$key],
                                    "addon_id" => $hideaddonhideprice[$key],
                                    "emp_range_id" => $res,
                                    "amount" => $value
                                    );
                    }
                    $this->lister->saveAddonPricingBatch($insaddon);    

                    $this->session->set_flashdata('error','Employee range added successfully');
                } else if($pos_v['id'] != -1){                    
                    unset($newarr['created_on']);                    
                    $res = $this->lister->updateEmployee($newarr);
                    $this->session->set_flashdata('error','Employee range updated successfully');
                }
                redirect('user/employee_management');
            }

        }

        public function edit_employee_management()
        {
            $this->load->view('header');
            $ses_user =  $this->checkses();
            $lister = $this->lister->getEmployees(base64_decode($_GET['id']));
            $data = (array)$lister[0];
            $data['action'] = 'edit_form';
            $data['type'] = 'Edit';
            $data['modulelist'] = '';
            $data['addonlist'] = '';
            $data['employeerange'] = $this->lister->getEmployees();
            if(count($data['employeerange']) == 1) {
                $data['to_range_max'] = '';
            } else if(count($data['employeerange']) > 0) {
                foreach($data['employeerange'] as $key => $value){
                    $price[$value->id] = $value;
                }
                $test = array_keys($price);
                //echo count($test);
                foreach ($test as $key => $value) {
                    if($key < count($test)-1){
                        if($value == base64_decode($_GET['id'])){
                            $data['to_range_max'] = $price[$test[$key+1]]->to_range-1;
                        }
                    }
                }                
            }
            $this->load->view('add_employee_management', $data);
            $this->load->view('footer');
        }

        public function view_employee_management()
        {
            $this->load->view('header');
            $ses_user =  $this->checkses();
            $lister = $this->lister->getEmployees(base64_decode($_GET['id']));
            $data = (array)$lister[0];
            $data['action'] = 'view_form';
            $data['type'] = 'View';            
            $this->load->view('add_employee_management', $data);
            $this->load->view('footer');
        }

        public function checkCouponCode()
        {
            if(isset($_REQUEST)){
                return $this->lister->checkCouponCode($_REQUEST);
            }
        }

        public function save_generate_payment_url()
        {
            $newarr = array();
            $pos_v = $this->input->post();
            //print_r($pos_v); exit;
            $resultadd = mysql_query("SELECT `try_nomisma_new`.* FROM `try_nomisma_new` WHERE `try_nomisma_new`.customer_reference = ".$pos_v['reference_code']);
            $rowadd = mysql_fetch_array($resultadd, MYSQL_ASSOC);
            $fullname = $rowadd['name'].''.$rowadd['lname'];
            $toemail = $rowadd['email'];
            $company = $rowadd['company'];
            $phone = $rowadd['phone'];
            $customer_reference = $rowadd['customer_reference'];
            
            $addons = '';
            if(isset($pos_v['module']) && !empty($pos_v['module'])){
                foreach ($pos_v['module'] as $key => $value) {
                    if(isset($pos_v['addons'.$value]) && !empty($pos_v['addons'.$value])){
                        foreach ($pos_v['addons'.$value] as $k => $val) {
                            $resultadd = mysql_query("SELECT `nomisma_addon_management`.*, `nomisma_addon_pricing`.`amount` FROM `nomisma_addon_management` LEFT JOIN `nomisma_addon_pricing` ON `nomisma_addon_management`.`id` =  `nomisma_addon_pricing`.`addon_id` WHERE `nomisma_addon_pricing`.`emp_range_id` = '".$pos_v['employees']."' and `nomisma_addon_management`.status = 1 and `nomisma_addon_management`.id = ".$val);
                            while ($rowadd = mysql_fetch_array($resultadd, MYSQL_ASSOC)) {
                                $list_modules .= '<div class="row">
                                                <div class="col-lg-10">
                                                    <p class="addon_title"> <i class="fa fa-gift" aria-hidden="true"></i> '.$rowadd['addon_title'].'</p>
                                                </div>
                                                <div class="col-lg-2">
                                                    &pound; '.$rowadd['amount'].'
                                                </div>
                                            </div>';
                                $moduleprice = $moduleprice + $rowadd['amount'];
                            }       
                        }
                        $addons['addons'.$value] = $pos_v['addons'.$value];
                    }
                }
            }
            $newarr['modules'] = serialize($pos_v['module']);
            $newarr['addons'] = serialize(array("addons" => $addons));
            $newarr['reference_code'] = $pos_v['reference_code'];
            $newarr['offer_code'] = $pos_v['offer_code'];
            $newarr['employee_range_id'] = $pos_v['employee_range_id'];
            $newarr['status'] = 1;
            $newarr['created_on'] = date('Y-m-d H:i:s');
            $newarr['updated_on'] = date('Y-m-d H:i:s');
            $newarr['subscription_period'] = $pos_v['subscription_period'];
            //print_r($newarr); exit;

            /***************************************** MAIL STARTS ****************************************************/
                $from = "Nomismasolution<no-reply@nomismasolution.co.uk>";
                $reply = "Nomismasolution<info@nomismasolution.co.uk>";
                $subject = 'Nomismasolution Payment';                
                $data = array(
                        "userName" => $fullname,
                        "company"  => $company,
                        "phone"      => $phone,
                        "customer_reference" => $customer_reference
                        );
                $body = $this->load->view('emailcontent.php',$data,TRUE);

                $config = array();
                $config['api_key'] = "key-90b0c3f6e5508147a055f1a30e5cad35";
                $config['api_url'] = "https://api.mailgun.net/v3/dnsassociates.co/messages";
                $message = array();
                $message['from'] = 'info@q2m.in';
                //$message['to'] = 'mprabhu@q2m.in';
                //$message['h:Reply-To'] = 'mprabhu@q2m.in';

                $message['to'] = 'sneha@nomismasolution.co.uk';
                $message['h:Reply-To'] = 'sneha@nomismasolution.co.uk';
                $message['subject'] = $subject;
                $message['html'] = $body;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $config['api_url']);
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt($ch, CURLOPT_USERPWD, "api:{$config['api_key']}");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $message);
                $result = curl_exec($ch);
                curl_close($ch);

            /***************************************** MAIL ENDS ****************************************************/

            $this->lister->save_generate_payment_url($newarr);
            $this->session->set_flashdata('error','Payment generate mail sent successfully');
            redirect('user/trial_users');
        }

        public function subscription_management()
        {
            $this->load->view('header');
            $ses_user =  $this->checkses();
            $data['modulelist'] = $this->lister->getSubscription();
            $this->load->view('subscription_management', $data);
            $this->load->view('footer');
        }

        public function view_subscription_management()
        {
            $this->load->view('header');
            $ses_user =  $this->checkses();
            $data = array(
                "action" => 'view_form',
                "type" => 'View',
                'modulelist' => $this->lister->getSubscription(base64_decode($_GET['id']))
            );
            //print_r($data); exit;
            if(isset($data['modulelist'][0]->modules) && !empty($data['modulelist'][0]->modules)){
                $modlist = unserialize($data['modulelist'][0]->modules);
                $addonlist = unserialize($data['modulelist'][0]->addons);
                $list_modules = '';    
                //print_r($addonlist['addons']); 
                $emprangeid = $data['modulelist'][0]->employee_range_id;
                if(isset($modlist) && !empty($modlist)){
                    foreach ($modlist as $key => $value) {           
                        $result = mysql_query("SELECT `nomisma_module_management`.*, `nomisma_module_pricing`.`amount` FROM `nomisma_module_management` LEFT JOIN `nomisma_module_pricing` ON  `nomisma_module_management`.`id` = `nomisma_module_pricing`.`module_id` WHERE `nomisma_module_pricing`.`emp_range_id` = '".$emprangeid."' and `nomisma_module_management`.status = 1 and `nomisma_module_management`.id = ".$value);
                        echo mysql_error();
                        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                            $disp_mod_price = $row['amount'];
                            $list_modules .= '<div style="border-top: 1px solid #CCC;" class="row">
                                <div class="col-lg-3">
                                    <p class="module_title" style="line-height:40px;"><b>'.$row['module_title'].'</b></p>
                                </div>
                                <div class="col-lg-2" style="line-height:40px;">
                                    &pound; '.$disp_mod_price.'
                                </div>
                            </div>';
                            if(isset($addonlist['addons']) && !empty($addonlist['addons'])){
                                foreach ($addonlist as $k => $val) {
                                    if(isset($val['addons'.$value]) && !empty($val['addons'.$value])){
                                        foreach ($val['addons'.$value] as $kv => $valv) {
                                            $resultadd = mysql_query("SELECT `nomisma_addon_management`.*, `nomisma_addon_pricing`.`amount` FROM `nomisma_addon_management` LEFT JOIN `nomisma_addon_pricing` ON `nomisma_addon_management`.`id` =  `nomisma_addon_pricing`.`addon_id` WHERE `nomisma_addon_pricing`.`emp_range_id` = '".$emprangeid."' and `nomisma_addon_management`.status = 1 and `nomisma_addon_management`.id = ".$valv);
                                            while ($rowadd = mysql_fetch_array($resultadd, MYSQL_ASSOC)) {
                                                $list_modules .= '<div class="row">
                                                                <div class="col-lg-3">
                                                                    <p class="addon_title" style="padding-left: 30px; line-height:30px;"><b></b> '.$rowadd['addon_title'].'</p>
                                                                </div>
                                                                <div class="col-lg-2" style="line-height:30px;">
                                                                    &pound; '.$rowadd['amount'].'
                                                                </div>
                                                            </div>';
                                            }    
                                        }
                                    }
                                }   
                            }
                        }
                    }
                }
            }
            $data['list_modules'] = $list_modules;
            //print_r($data); exit;
            $this->load->view('view_subscription_management', $data);
            $this->load->view('footer');
        }

        public function updateSubscriptionTrialDate()
        {
            $pos_v = $this->input->post();
            $updated_date = $pos_v['updated_date'];
            $updated_id = $pos_v['updated_id'];
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, 'http://sandbox.nomismasolution.co.uk/AccountREST/AccountService.svc/sandbox/AuthoriseAdmin?LoginName=smeadmin&Password=334727');
            //curl_setopt($curl, CURLOPT_URL, 'http://live.nomismasolution.co.uk/AccountREST/AccountService.svc/live/AuthoriseAdmin?LoginName=smeadmin&Password=334727');
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET'); 
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt ($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($curl);
            curl_close($curl);
            $result = json_decode($response);
            if(isset($result) && !empty($result)){
                //$chkKey =  $result->Description;
                $authKey =  $result->ResultInfo->AuthToken;
            } else {
                $authKey =  '';
            }
            if($authKey=="") 
            {
                //$error_message = curl_strerror($errno);
                $this->session->set_flashdata('error','Sorry! Some technical issue, please try after some time.');
                redirect('user/trial_users'); exit;
            } else {
                $udpate_query = mysql_query($con,"UPDATE try_nomisma_new SET expiry_date='".$updated_date."' WHERE id = '".$updated_id."'");
                //$udpate_query = mysqli_query($con,"UPDATE try_nomisma_new SET expiry_date='".2018-01-."' WHERE id = '".$_POST['id']."'");
                $result = mysql_query($con,"SELECT customer_reference from try_nomisma_new WHERE id = '".$updated_id."'");
                $row = mysql_fetch_array($result,MYSQLI_NUM);
                $myObj = "";
                $myObj->AgentCode = 1163;
                $myObj->CustomerRefNo = $row[0];
                $myObj->TrialPeriodEnd = $updated_date;
                $myJSON = json_encode($myObj);
                $myJSON = urlencode($myJSON);
                $url = 'http://sandbox.nomismasolution.co.uk/AccountREST/AccountService.svc/sandbox/'.$authKey.'/UpdateCompanyData?companyInfo='
                //$url = 'http://live.nomismasolution.co.uk/AccountREST/AccountService.svc/live/'.$authKey.'/UpdateCompanyData?companyInfo='
                .$myJSON;
                $curlbu = curl_init();
                curl_setopt($curlbu, CURLOPT_URL,$url); 
                curl_setopt($curlbu, CURLOPT_CUSTOMREQUEST, 'GET'); 
                curl_setopt($curlbu, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curlbu, CURLOPT_HEADER, false);
                curl_setopt ($curlbu, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($curlbu, CURLOPT_SSL_VERIFYPEER, false);
                $response1 = curl_exec($curlbu);
                curl_close($curlbu);
                $bu_result = json_decode($response1);
                $this->session->set_flashdata('error','Successfully Trial Period Updated!');
                redirect('user/trial_users'); exit;
            }
        }

        public function configuration()
        {
            $this->load->view('header');
            $ses_user =  $this->checkses();
            $data['modulelist'] = $this->lister->getConfiguration();
            $this->load->view('configuration', $data);
            $this->load->view('footer');
        }

        public function edit_configuration()
        {
            $this->load->view('header');
            $ses_user =  $this->checkses();
            $lister = $this->lister->getConfiguration(base64_decode($_GET['id']));        
            $data = (array)$lister[0];
            $data['action'] = 'edit_form';
            $data['type'] = 'Edit';
            //print_r($data); exit;
            $this->load->view('add_configuration', $data);
            $this->load->view('footer');
        }

        public function save_configuration()
        {
            $ses_user = $this->checkses();
            $pos_v = $this->input->post();
            //print_r($pos_v['hideprice']);                
            $newarr = array(
                "config_key" => $pos_v['config_key'],
                "config_value" => $pos_v['config_value']                
            );
            if($pos_v['id'] == -1){
                /*$res = $this->lister->saveModule($newarr);
                $this->lister->deleteModulePricing($res);
                if(isset($pos_v['price']) && !empty($pos_v['price'])){                        
                    foreach($pos_v['hideprice'] as $key => $value){
                        $newarr = array(
                            "module_id" => $res,
                            "emp_range_id" => $value,
                            "amount" => $pos_v['price'][$key],
                            "updated_on" => date("Y-m-d H:i:s")
                        );
                        $this->lister->saveModulePricing($newarr);
                    }
                }
                $this->session->set_flashdata('error','Module added successfully');*/
            } else if($pos_v['id'] != -1){
                $newarr['id'] = $pos_v['id'];
                //print_r($newarr); exit;
                $res = $this->lister->updateConfiguration($newarr);                
                $this->session->set_flashdata('error','Conguration updated successfully');
            }                
            redirect('user/configuration');
        
        }

    public function login(){ 
        
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400');    // cache for 1 day
        }

        // Access-Control headers are received during OPTIONS requests
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
                header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
                header("Access-Control-Allow-Headers:        {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

            exit(0);
        }        

        $postdata = file_get_contents("php://input");
        if(isset($_REQUEST) && !empty($_REQUEST)){
            $admin_username = $_REQUEST['admin_username'];
            $admin_password = $_REQUEST['admin_password'];            
        } else if(isset($postdata) && $postdata != ''){
            $pdata = json_decode($postdata);
            $admin_username = $pdata->admin_username;
            $admin_password = $pdata->admin_password;
        }
        
        if((isset($_REQUEST) && !empty($_REQUEST)) || (isset($postdata) && $postdata != '')){ 
            if((isset($admin_username) && $admin_username != '') && (isset($admin_password) && $admin_password != '') ){
                $data = array("admin_username" => $admin_username,
                              "admin_password" => md5($admin_password)
                            );
                $res = $this->lister->login($data);
                echo $res;
            }
        } else {
            echo json_encode(array("return" => false, "message" => 'Invalid Credentials2'));
        }
    }    
}   