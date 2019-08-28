<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Deal extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation');
		$this->load->library('session');
        $this->load->library('pagination');
		$this->load->model('panel');
        $this->load->model('lister');
//      $this->load->model('stores');
//      $this->load->model('product');
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
                if($ses_user->usr_type == 'A')
                    return $ses_user;
                else
                {
                    redirect("index");
                    
                }
            }
        }
        public function newcat()
	{
            $ses_user = $this->checkses();           
            $data['cates'] = $this->panel->getCategory(0,-1);
              $data['curpage'] = 'cat';
            $this->load->view('add_category',$data);                
            
	}
       public function add_category()
       {
            $this->checkses();
            $this->form_validation->set_rules('category_name','Category name', 'required');
            $pos_v = $this->input->post();
            
            if($this->input->post('check_parent') == 'YES')
                 $this->form_validation->set_rules('cat_parent','Parent category', 'required');
            if ($this->form_validation->run() == FALSE)
            {
                $data['cates'] = $this->panel->getCategory(0,-1);
                $data['curpage'] = 'cat';
                if(isset($pos_v['prid']))
                {
                  $mydeals = $this->panel->getcategory( 0,-1,$this->input->post("prid")); 
                   $this->load->view('updatecat', $data);
                }
                else
                {
                $this->load->view('add_category', $data);
                }
               
            }
            else
            {
                $parent_ = $_POST['check_parent'] == 'YES' ? $_POST['cat_parent'] : 0;
                 $newarr  =array("cat_title"=>  $this->input->post("category_name"),
                  "cat_parentid"=>  $parent_);
                if(isset($pos_v['hideimage']))
                { 
                    
                    $wconfig['source_image']	= 'assets/catimages/'.$pos_v['hideimage'];
                    $wconfig['wm_text'] = $pos_v['category_name'];
                    $wconfig['wm_type'] = 'text';
                    $wconfig['wm_font_path'] = 'assets/BrannbollSmal.ttf';
                    $wconfig['wm_font_size']	= '20';
                    $wconfig['wm_font_color'] = 'ffffff';
                    $wconfig['wm_vrt_alignment'] = 'middle';
                    $wconfig['wm_hor_alignment'] = 'center';
                    $wconfig['wm_padding'] = '20';
                    $wconfig['wm_vrt_offset'] = '20';
                    $this->load->library('image_lib', $wconfig);
                    $this->image_lib->initialize($wconfig); 
                    $this->image_lib->watermark();
                    $newarr['cat_image'] = $pos_v['hideimage'];
                }
                    
               
                if(isset($pos_v['prid']))
                {
                  $newarr = array_merge($newarr, array("cat_id"=>  $this->input->post("prid")));
                  
                     $res = $this->panel->updateCategory($newarr);
                }
                else
                {
                    if(isset($pos_v['hideimage']))
                        $newarr['cat_image'] = $pos_v['hideimage'];
                     $res = $this->panel->saveCategory($newarr);
                }
               
                if($res == 0)
                    $this->session->set_flashdata("error","Error occured!");
                else
                {
                    if(isset($pos_v['prid']))
                        $this->session->set_flashdata("error","Category updated successfully");
                    else
                    $this->session->set_flashdata("error","Category added successfully");
                }
                redirect('deal/list_category');
            }
        }
        
        public function platinum($start=0) {           
            $this->list_ads(2,'Platinum',$start);
        }
         public function silver($start=0) {           
            $this->list_ads(4,'Silver',$start);
        }
         public function gold($start=0) {           
            $this->list_ads(3,'Gold',$start);
        }
        public function alllist($start=0) {           
            $this->list_ads(0,'All',$start);
        }
        public function new_ad()
        {
            $ses_user = $this->checkses();
            $data['planlist'] = $this->lister->getPlans();  
            $data['catlist'] = $this->lister->getCategory(0);
            if(!empty($data['catlist']))
            {
                foreach ($data['catlist'] as $key => $value)
                {
                    $data['inlist'][$key] = $this->lister->getCategory($value->cat_id);
                }
            }
            $data['count'] =array('platinum'=>  $this->panel->countAds(2),"gold"=>  $this->panel->countAds(3),
              'silver'=>  $this->panel->countAds(4));
            $data['curpage'] = 'ad';
            $this->load->view('post_ad', $data);
        }
        
        public function list_ads($id=0,$title='All',$start=0)
        {
            $this->checkses();
            $data['adlist'] = $this->panel->getallads($id,$start,20);
            $this->load->library('pagination');
            $urllast = $title == 'All' ? 'alllist' : strtolower($title);
            $config['base_url'] = base_url().'deal/'.$urllast;
            $cc = $this->panel->getallads($id,0,20,1);
            $config['total_rows'] = $cc[0]->total;
            $config['per_page'] = 20; 
            $config['num_links'] = 2;
            $config['uri_segment'] = 4;
            $this->pagination->initialize($config); 
            $data['links'] = $this->pagination->create_links();
            $data['page'] = $start;
            $data['viewpage'] = $title;
            $data['curpage'] = 'ad';
            $this->load->view('list_advertisement', $data);
        }
        
        public function addpage()
        {
            $this->checkses();          
            $data['page'] = 0;
            $data['curpage'] = 'cms';
            $this->load->view('add_page', $data);
        }
        
        public function list_user()
        {
            $this->checkses();
            $data['userlist'] = $this->panel->getUser();
            $data['page'] = 0;
            $data['curpage'] = 'user';
            $this->load->view('list_users', $data);
        }
        
        public function savead()
        {
            $ses_user = $this->checkses();
            $this->form_validation->set_rules('ad_title','Company', 'required');
            $this->form_validation->set_rules('ad_cat','Category', 'required');  
            $this->form_validation->set_rules('ad_city','City', 'required'); 
            $this->form_validation->set_rules('ad_state','State', 'required'); 
            $this->form_validation->set_rules('addr','Address', 'required'); 
            $this->form_validation->set_rules('ad_country','country', 'required'); 
            $this->form_validation->set_rules('zipcode','zipcode', 'required|regex_match[/^[0-9-]+$/]'); 
            $this->form_validation->set_rules('ad_plan','Plan', 'required'); 
            $this->form_validation->set_rules('ad_url','website', 'valid_url'); 
            $this->form_validation->set_rules('email','Email', 'valid_email'); 
            $this->form_validation->set_rules('ad_phone','Phone', 'regex_match[/^[0-9-+.]+$/]'); 
            $this->form_validation->set_rules('ad_phone','Phone', 'regex_match[/^[0-9-+.]+$/]'); 
            
            $pos_v = $this->input->post();        
          
            if ($this->form_validation->run() == FALSE)
            {               
                $data['curpage'] = 'ad';
                $data['planlist'] = $this->lister->getPlans();  
                $data['catlist'] = $this->lister->getCategory(0);
                 $data['count'] =array('platinum'=>  $this->panel->countAds(2),"gold"=>  $this->panel->countAds(3),
              'silver'=>  $this->panel->countAds(4));
                if(!empty($data['catlist']))
                {
                    foreach ($data['catlist'] as $key => $value)
                    {
                        $data['inlist'][$key] = $this->lister->getCategory($value->cat_id);
                    }
                }
                if(isset($pos_v['plid']))
                {
                  $mydeals = $this->lister->getad($this->input->post("plid")); 
                   $this->load->view('update_ad', $data);
                }
                else
                {
                $this->load->view('post_ad', $data);
                }
               
            }
            else
            {
                $newarr  =array("ad_company"=>$pos_v['ad_title'],"ad_category"=>$pos_v['ad_cat'],"ad_status"=>"A",
                  "ad_image"=>$pos_v['hideimage'],"ad_plan"=>$pos_v['ad_plan'],"ad_website"=>$pos_v['ad_url'],
                  "ad_phone"=>$pos_v['ad_phone'],"ad_address"=>$pos_v['addr'],"ad_city"=>$pos_v['ad_city'],
                  "ad_state"=>$pos_v['ad_state'],"ad_country"=>$pos_v['ad_country'],"ad_zip"=>$pos_v['zipcode'],
                  "ad_email"=>$pos_v['email'],"ad_fax"=>$pos_v['ad_fax'],"ad_revenue"=>$pos_v['revenue'],
                  "ad_employees"=>$pos_v['employee']);
                if(isset($pos_v['plid']))
                {
                  $newarr["ad_id"]=  $pos_v["plid"];                  
                     $res = $this->lister->updatelist($newarr);
                }
                else
                {
                  //  $newarr['ad_userid'] = $ses_user->usr_id;
                    $newarr['ad_posteddate']=date("Y-m-d h:i:s");
                     $res = $this->lister->saveAd($newarr);
                }
               
                if($res == 0)
                    $this->session->set_flashdata("error","Error occured!");
                else
                {
                    if(isset($pos_v['plid']))
                        $this->session->set_flashdata("error","Ad updated successfully");
                    else
                    $this->session->set_flashdata("error","Ad added successfully");
                }
                redirect('deal/alllist');
            }  
        }
        
        public function cmspages()
        {
            $this->checkses();
            $data['userlist'] = $this->panel->getcmspage();
            $data['page'] = 0;
            $data['curpage'] = 'cms';
            $this->load->view('list_pages', $data);
        }
        
        public function list_category($id=0)
        {
            $this->checkses();
            if(isset($id))
                $data['catlist'] = $this->panel->getcategory($id,1);
            else
            $data['catlist'] = $this->panel->getcategory();
            $data['catname']= $id == 0 ? 'Main' : $this->panel->getcategory(-1,0,$id);
            $data['page'] = 0;
            $data['curpage'] = 'cat';
            $this->load->view('list_category', $data);
        }
        
        public function allplans()
        {
            $this->checkses();
            $data['planlist'] = $this->panel->getplans();
            $data['page'] = 0;
            $data['curpage'] = 'plan';
            $this->load->view('list_plans', $data);
        }
        
        public function nplan()
        {
            $this->checkses();            
            $data['curpage'] = 'plan';
            $this->load->view('add_plans', $data);
        }
        
         public function narticle()
        {
            $this->checkses();            
            $data['curpage'] = 'news';
            $this->load->view('add_article', $data);
        }
        
         public function newsarticle()
        {
            $this->checkses();            
            $data['curpage'] = 'surf';
            $this->load->view('add_article', $data);
        }
        
        public function saveevent()
        {
          $this->checkses();
           $this->form_validation->set_rules('at_title','Title', 'required');
            $this->form_validation->set_rules('at_desc','description', 'required');  
            $this->form_validation->set_rules('at_date','Event date', 'required');  
            $this->form_validation->set_rules('enddate','Event end date', 'required'); 
            $this->form_validation->set_rules('venue','Event venue', 'required');    
            
            $pos_v = $this->input->post();        
          
            if ($this->form_validation->run() == FALSE)
            {               
                $data['curpage'] = 'event';
                if(isset($pos_v['plid']))
                {
                  $mydeals = $this->panel->getevent($this->input->post("plid")); 
                   $this->load->view('updateevent', $data);
                }
                else
                {
                $this->load->view('add_event', $data);
                }
               
            }
            else
            {
                $newarr  =array("evt_title"=>$pos_v['at_title'],"evt_desc"=>$pos_v['at_desc'],
                  "evt_enddate"=>$pos_v['enddate'],"evt_date"=>$pos_v['at_date'],"evt_venue"=>$pos_v['venue'],
                  "evt_seats"=>$pos_v['seat'],"evt_weblink"=>$pos_v['weblink'],"evt_ticketlink"=>
                  $pos_v['evtlink'],'evt_viewembed'=>$pos_v['video']);
                if(isset($pos_v['hideimage']))
                    $newarr['evt_image'] = $pos_v['hideimage'];
                if(isset($pos_v['plid']))
                {
                  $newarr["evt_id"]=  $pos_v["plid"];                  
                     $res = $this->panel->updateevent($newarr);
                }
                else
                {
                    $newarr['evt_posteddate']=date("Y-m-d h:i:s");
                     $res = $this->panel->saveevent($newarr);
                }
               
                if($res == 0)
                    $this->session->set_flashdata("error","Error occured!");
                else
                {
                    if(isset($pos_v['plid']))
                        $this->session->set_flashdata("error","Event updated successfully");
                    else
                    $this->session->set_flashdata("error","Event added successfully");
                }
                redirect('deal/alevents');
            }  
        }
        
        
         public function addevent()
        {
            $this->checkses();            
            $data['curpage'] = 'event';
            $this->load->view('add_event', $data);
        }
        
        public function savearticle()
        {
          $this->checkses();
            $this->form_validation->set_rules('at_title','title', 'required');
            $this->form_validation->set_rules('at_desc','description', 'required');           
            
            $pos_v = $this->input->post();            
           $data['curpage'] = $pos_v['ntype'] == 'S'?"surf":"news";
                $rep_txt = $pos_v['ntype'] == 'S'?"Surf report":"Article";
            if ($this->form_validation->run() == FALSE)
            {               
               
                if(isset($pos_v['plid']))
                {
                  $mydeals = $this->panel->getnews($this->input->post("plid")); 
                   $this->load->view('updatearticle', $data);
                }
                else
                {
                $this->load->view('add_article', $data);
                }
               
            }
            else
            {
                $newarr  =array("ns_title"=>$pos_v['at_title'],"ns_desc"=>$pos_v['at_desc'],
                  "ns_image"=>$pos_v['hideimage'],"ns_type"=>$pos_v['ntype']);
                if(isset($pos_v['plid']))
                {
                  $newarr["ns_id"]=  $pos_v["plid"];                  
                     $res = $this->panel->updatenews($newarr);
                }
                else
                {
                    $newarr['ns_date']=date("Y-m-d h:i:s");
                     $res = $this->panel->savenews($newarr);
                }
               
                if($res == 0)
                    $this->session->set_flashdata("error","Error occured!");
                else
                {
                    if(isset($pos_v['plid']))
                        $this->session->set_flashdata("error",$rep_txt."updated successfully");
                    else
                    $this->session->set_flashdata("error",$rep_txt." added successfully");
                }
                if($pos_v['ntype']=='N')
                redirect('deal/articles');
                else
                    redirect("deal/sarticles");
            }  
        }
        
         public function savepage()
        {
          $this->checkses();
            $this->form_validation->set_rules('at_title','title', 'required');
            $this->form_validation->set_rules('at_desc','description', 'required');           
            
            $pos_v = $this->input->post();           
          
            if ($this->form_validation->run() == FALSE)
            {               
               
                if(isset($pos_v['plid']))
                {
                  $mydeals = $this->panel->getpage($this->input->post("plid")); 
                   $this->load->view('updatepage', $data);
                }
                else
                {
                $this->load->view('add_page', $data);
                }
               
            }
            else
            {
                $newarr  =array("pag_title"=>$pos_v['at_title'],"pag_content"=>$pos_v['at_desc']);
                if(isset($pos_v['plid']))
                {
                  $newarr["pag_id"]=  $pos_v["plid"];                  
                     $res = $this->panel->updatepage($newarr);
                }
                else
                {                   
                     $res = $this->panel->savepage($newarr);
                }
               
                if($res == 0)
                    $this->session->set_flashdata("error","Error occured!");
                else
                {
                    if(isset($pos_v['plid']))
                        $this->session->set_flashdata("error","Page updated successfully");
                    else
                    $this->session->set_flashdata("error","Page added successfully");
                }
               
                    redirect("deal/cmspages");
            }  
        }
        
        public function articles()
        {
            $this->checkses();
            $data['userlist'] = $this->panel->getnews();
            $data['page'] = 0;
            $data['curpage'] = 'news';
            $this->load->view('list_article', $data);
        }
        
        public function sarticles()
        {
            $this->checkses();
            $data['userlist'] = $this->panel->getnews(-1,"S");
            $data['page'] = 0;
            $data['curpage'] = 'surf';
            $this->load->view('list_article', $data);
        }
        
        public function alevents()
        {
            $this->checkses();
            $data['userlist'] = $this->panel->getevents();
            $data['page'] = 0;
            $data['curpage'] = 'event';
            $this->load->view('list_events', $data);
        }
        
        public function imageUpload($ag=0)
        {
            if($ag==1)
            $upconfig['upload_path'] = 'assets/eventimages/';
            elseif($ag==2)
            $upconfig['upload_path'] = 'assets/catimages/';
            else
            $upconfig['upload_path'] = 'assets/articleimages/';
            $upconfig['allowed_types'] = 'gif|jpg|png|jpeg';
            $upconfig['max_size'] = '2048';
            $upconfig['max_width'] = '1024';
            $upconfig['max_height'] = '768';
            $newname = uniqid('dl').".".pathinfo($_FILES['copy_photo']['name'] , PATHINFO_EXTENSION);
            $upconfig['file_name'] = $newname;                  
            $this->load->library('upload', $upconfig);
            $this->upload->initialize($upconfig);
            if($this->upload->do_upload('copy_photo') != FALSE)
            {
                
                $config['image_library'] = 'gd2';
                $config['source_image']	= $upconfig['upload_path'].$newname;
                $config['new_image']	= $upconfig['upload_path']."thumbs/".$newname;
                $config['create_thumb'] = TRUE;
                $config['maintain_ratio'] = TRUE;
                $config['width']	 = 100;
                $config['height']	= 100;
                $this->load->library('image_lib',$config);
                $this->image_lib->resize();                
                echo json_encode(array("file"=>$newname));
            }
            else
                echo json_encode (array("error"=>  strip_tags($this->upload->display_errors())));
            
        }
        
        public function updatelist($param=0) 
        {
           $ses_user =  $this->checkses();
           if($param > 0)
            {
                $mydeals = $this->panel->editad($param);
                if(!empty($mydeals))
                {
                    $data['mydeal']  = $mydeals[0];
                   $data['planlist'] = $this->lister->getPlans(); 
                      $data['count'] =array('platinum'=>  $this->panel->countAds(2),"gold"=>  $this->panel->countAds(3),
              'silver'=>  $this->panel->countAds(4));
                    $data['catlist'] = $this->lister->getCategory(0);
                    if(!empty($data['catlist']))
                    {
                        foreach ($data['catlist'] as $key => $value)
                        {
                            $data['inlist'][$key] = $this->lister->getCategory($value->cat_id);
                        }
                    }
                    $data['curpage'] = 'ad';
                    $data['update'] = 1;                    
                    $this->load->view('adm_updatead',$data); 
                 
                }
                else
                {
                    $this->session->set_flashdata('error','Invalid ID');
                    $this->list_ads();
                }

            }
            else
                 $this->list_ads();
        }
        
        public function adUpload()
        {
           
            $upconfig['upload_path'] = 'assets/adimages/';
            $upconfig['allowed_types'] = 'gif|jpg|png|jpeg';
            $upconfig['max_size'] = '2048';
            if($_POST['hide_ad_plan'] == 2)
            {
                $upconfig['max_width'] = '700';
                $upconfig['max_height'] = '400';
            }
            else
            {
                $upconfig['max_width'] = '130';
                $upconfig['max_height'] = '210';
            }
            
            $newname = uniqid('dl').".".pathinfo($_FILES['copy_photo']['name'] , PATHINFO_EXTENSION);
            $upconfig['file_name'] = $newname;                  
            $this->load->library('upload', $upconfig);
            $this->upload->initialize($upconfig);
            if($this->upload->do_upload('copy_photo') != FALSE)
            {
                
                $config['image_library'] = 'gd2';
                $config['source_image']	= $upconfig['upload_path'].$newname;
                $config['new_image']	= $upconfig['upload_path']."thumbs/".$newname;
                $config['create_thumb'] = TRUE;
                $config['maintain_ratio'] = TRUE;
                $config['width']	 = 100;
                $config['height']	= 100;
                $this->load->library('image_lib',$config);
                $this->image_lib->resize();                
                echo json_encode(array("file"=>$newname));
            }
            else
                echo json_encode (array("error"=>  strip_tags($this->upload->display_errors())));
            
        }
        
       public function add_plan()
       {
            $this->checkses();
            $this->form_validation->set_rules('p_title','plan name', 'required');
            $this->form_validation->set_rules('p_cost','plan cost', 'required|numeric');
            $this->form_validation->set_rules('p_limit','plan user limit', 'required|numeric');
            
            $pos_v = $this->input->post();            
           
            if ($this->form_validation->run() == FALSE)
            {               
                $data['curpage'] = 'plan';
                if(isset($pos_v['plid']))
                {
                  $mydeals = $this->panel->getplans($this->input->post("plid")); 
                   $this->load->view('updateplan', $data);
                }
                else
                {
                $this->load->view('add_plans', $data);
                }
               
            }
            else
            {
                $newarr  =array("pl_title"=>$pos_v['p_title'],"pl_cost"=>$pos_v['p_cost'],
                  "pl_limit"=>$pos_v['p_limit']);
                if(isset($pos_v['plid']))
                {
                  $newarr = array_merge($newarr, array("pl_id"=>  $pos_v["plid"]));
                  
                     $res = $this->panel->updatePlan($newarr);
                }
                else
                {
                     $res = $this->panel->savePlan($newarr);
                }
               
                if($res == 0)
                    $this->session->set_flashdata("error","Error occured!");
                else
                {
                    if(isset($pos_v['plid']))
                        $this->session->set_flashdata("error","Plan updated successfully");
                    else
                    $this->session->set_flashdata("error","Plan added successfully");
                }
                redirect('deal/allplans');
            }
        }
        
        public function Updateplan( $d_id = 0)
	{
            
            $ses_user = $this->checkses();		
                  
            if($d_id > 0)
            {
                $mydeals = $this->panel->getplans($d_id);
                if(!empty($mydeals))
                {
                    $data['mydeal']  = $mydeals[0];
                  
                    $data['curpage'] = 'plan';
                    $data['update'] = 1;
                    $this->load->view('updateplan',$data); 
                }
                else
                {
                    $this->session->set_flashdata('error','Invalid plan ID');
                    $this->allplans();
                }

            }
            else
                $this->allplans();
           
	}
        
        public function Updatepage( $d_id = 0)
	{            
            $ses_user = $this->checkses();
            if($d_id > 0)
            {
                $mydeals = $this->panel->getcmspage($d_id);
                if(!empty($mydeals))
                {
                    $data['mydeal']  = $mydeals[0];                  
                    $data['curpage'] = 'cms';
                    $data['update'] = 1;
                    $this->load->view('updatepage',$data); 
                }
                else
                {
                    $this->session->set_flashdata('error','Invalid page ID');
                    $this->cmspages();
                }
            }
            else
                $this->cmspages();           
	}
        
        
       public function catStatus()
	{
            $ses_user = $this->checkses();
            if(isset($_POST['id'],$_POST['status']))
            {
               echo $this->panel->catStatus($this->input->post('id'),  $this->input->post('status'));
            }
            else
                echo 0;                  
            
	}
        
        public function userStatus()
	{
            $ses_user = $this->checkses();
            if(isset($_POST['id'],$_POST['status']))
            {
               echo $this->panel->changeuserStatus($this->input->post('id'),  $this->input->post('status'));
            }
            else
                echo 0;                  
            
	}
        
         public function Updatecat( $d_id = 0)
	{
            
            $ses_user = $this->checkses();		
                  
            if($d_id > 0)
            {
                $mydeals = $this->panel->getcategory( 0,0,$d_id);
                if(!empty($mydeals))
                {
                    $data['mydeal']  = $mydeals[0];
                    $data['catlist'] = $this->panel->getcategory(0,1);
                    $data['curpage'] = 'cat';
                    $data['update'] = 1;
                    $this->load->view('updatecat',$data); 
                }
                else
                {
                    $this->session->set_flashdata('error','Invalid category ID');
                    $this->list_category();
                }

            }
            else
                $this->list_category();
           
	}
        
        public function planStatus()
	{
            $ses_user = $this->checkses();
            if(isset($_POST['id'],$_POST['status']))
            {
               echo $this->panel->planStatus($this->input->post('id'),  $this->input->post('status'));
            }
            else
                echo 0;                  
            
	}
        
        public function articleStatus()
	{
            $ses_user = $this->checkses();
            if(isset($_POST['id'],$_POST['status']))
            {
               echo $this->panel->newsStatus($this->input->post('id'),  $this->input->post('status'));
            }
            else
                echo 0;                  
            
	}
        
        public function eventStatus()
	{
            $ses_user = $this->checkses();
            if(isset($_POST['id'],$_POST['status']))
            {
               echo $this->panel->eventStatus($this->input->post('id'),  $this->input->post('status'));
            }
            else
                echo 0;                  
            
	}
        
        public function Updatearticle( $d_id = 0)
	{
            
            $ses_user = $this->checkses();		
                  
            if($d_id > 0)
            {
                $mydeals = $this->panel->getnews($d_id);
                if(!empty($mydeals))
                {
                    $data['mydeal']  = $mydeals[0];                   
                    $data['curpage'] = $mydeals[0]->ns_type=='S'?"surf":'news';
                    $data['update'] = 1;
                    $this->load->view('updatearticle',$data); 
                }
                else
                {
                    $this->session->set_flashdata('error','Invalid article ID');
                    $this->articles();
                }

            }
            else
                $this->articles();
           
	}
        
        public function Updateevent( $d_id = 0)
	{
            
            $ses_user = $this->checkses();
            if($d_id > 0)
            {
                $mydeals = $this->panel->getevents($d_id);
                if(!empty($mydeals))
                {
                    $data['mydeal']  = $mydeals[0];                   
                    $data['curpage'] = 'event';
                    $data['update'] = 1;
                    $this->load->view('updateevent',$data); 
                }
                else
                {
                    $this->session->set_flashdata('error','Invalid event ID');
                    $this->alevents();
                }

            }
            else
                $this->alevents();
           
	}
}