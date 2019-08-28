<?php 
class Panel extends CI_Model
{
	public function general()
	{
		parent::__construct();
		$this->load->helper('url');
		$this->load->database();
	}
	
	public function login () {
		$user = $this->input->post('username');
		$pass = md5($this->input->post("password"));
		return $this->db->query("Select * from nomisma_user_details where user_email_address = '".$user."' and user_password ='".$pass."'")->result();
	}
	
	public function checkpassword($pass = NULL , $userid = NULL)
	{
		if(isset($pass , $userid))
		{
			$result = $this->db->query("select * from nomisma_user_details where id = '".$userid."' and
												user_password = '".$pass."'")->result();			
			return !empty($result) ? 1 : 0;
		}
		return 0;
	}
	
	public function updatepassword($pass = NULL , $userid = NULL)
	{
		if(isset($pass , $userid))
		{
			$result = $this->db->update('nomisma_user_details',array('user_password' => $pass),array('id' => $userid));
			return ($result > 0 )? 1 : 0;
		}
		return 0;
	}
	
	public function getUser( $userid = -1 )
	{
		if($userid != -1)
		{
           return $this->db->query("Select * from nomisma_user_details where usr_id = '".$userid."' and usr_type = 'L'")->result();
		}
        else
        {
          return $this->db->query("Select * from nomisma_user_details where usr_type = 'L'")->result();
  
        }
		return -2;
	}
	
	//Get Active countries
	public function getActCountry()
	{
		return $this->db->query("SELECT * FROM go_country WHERE ctry_status = '1'")->result();
	}
	
	public function getCity( $cid )
	{
		if( $cid != "" && $cid >= 1 )
		{
			return $this->db->query("SELECT * FROM go_city WHERE cty_country = '".$cid."' AND cty_status = '1'")->result();		
		}
	}
        
        public function countAds($param) {
            return $this->db->query("Select count(*) as total from jax_listing where ad_plan={$param}")->result();
        }
        
        public function getCategory($pare=0,$all=0,$id = -1)
        {
            $cond='Where cat_type="d"';
            if($all != 0)
                $cond .= " and cat_parentid = {$pare}";
            elseif($id != -1)
                $cond .= " and cat_id = {$id}";
            return $this->db->query("Select * from jax_category {$cond} order by cat_title")->result();
        }
        
        public function getallads($type=0,$start=0,$end=20,$count = -1)
        {
            $cond = '';
            if($type !=0)
                $cond = " Where ad_plan={$type}";
            if($count == -1)
          return $this->db->query("Select * from jax_listing {$cond} LIMIT {$start},{$end}")->result();  
          else
             return $this->db->query("Select count(*) as total from jax_listing {$cond} ")->result();  
        }
        
        public function editad($param) {
            return $this->db->query("Select * from jax_listing where ad_id={$param}")->result();
        }
        
        public function getPlans($id=-1) {
            $cond = '';
            if($id != -1)
                $cond = "where pl_id={$id}";
                
            return $this->db->query("Select * from jax_plans {$cond}")->result();
        }
        
        public function getcmspage($id=-1) {
            $cond = '';
            if($id != -1)
                $cond = "where pag_id={$id}";
                
            return $this->db->query("Select * from jax_page {$cond}")->result();
        }
        
        function planStatus( $id = -1 , $status = -1)
        {
            if($id != -1 && $status != -1)
            {
                $result = $this->db->update('jax_plans',array("pl_status"=> $status),array('pl_id'=> $id));
                return ($result > 0 )? 1 : 0;
            }
            return 0;
        }
        
        public function savePlan($obj)
        {
            if(isset($obj))
            {
                 $this->db->insert('jax_plans',$obj);
                 return $this->db->insert_id();
            }
            return 0;
        }
        
        public function savePage($obj)
        {
            if(isset($obj))
            {
                 $this->db->insert('jax_page',$obj);
                 return $this->db->insert_id();
            }
            return 0;
        }
        
         public function updatePage($obj = NULL)
        {
            if(isset($obj))
            {
                $result = $this->db->update("jax_page",$obj,array('pag_id'=>$obj['pag_id']));
                return ($result > 0 )? 1 : 0;
            }
            return 0;
        }
        
        public function updatePlan($obj = NULL)
        {
            if(isset($obj))
            {
                $result = $this->db->update("jax_plans",$obj,array('pl_id'=>$obj['pl_id']));
                return ($result > 0 )? 1 : 0;
            }
            return 0;
        }
        
        public function getnews($id=-1,$type = 'N') {
            $cond = "Where ns_type='{$type}'";
            if($id != -1)
                $cond .= " and ns_id= {$id}";
                
            return $this->db->query("Select * from jax_news {$cond}")->result();
        }
        
        
         public function savenews($obj)
        {
            if(isset($obj))
            {
                 $this->db->insert('jax_news',$obj);
                 return $this->db->insert_id();
            }
            return 0;
        }
        
        public function updatenews($obj = NULL)
        {
            if(isset($obj))
            {
                $result = $this->db->update("jax_news",$obj,array('ns_id'=>$obj['ns_id']));
                return ($result > 0 )? 1 : 0;
            }
            return 0;
        }
        
        function newsStatus( $id = -1 , $status = -1)
        {
            if($id != -1 && $status != -1)
            {
                $result = $this->db->update('jax_news',array("ns_status"=> $status),array('ns_id'=> $id));
                return ($result > 0 )? 1 : 0;
            }
            return 0;
        }
        
        public function saveCategory($obj)
        {
            if(isset($obj))
            {
                 $this->db->insert('jax_category',$obj);
                 return $this->db->insert_id();
            }
            return 0;
        }
        
        function catStatus( $id = -1 , $status = -1)
        {
            if($id != -1 && $status != -1)
            {
                $result = $this->db->update('jax_category',array("cat_status"=> $status),array('cat_id'=> $id));
                return ($result > 0 )? 1 : 0;
            }
            return 0;
        }
         public function updatecategory($obj = NULL)
        {
            if(isset($obj))
            {
                $result = $this->db->update("jax_category",$obj,array('cat_id'=>$obj['cat_id']));
                return ($result > 0 )? 1 : 0;
            }
            return 0;
        }
        
        public function updateUser($obj = NULL)
        {
            if(isset($obj))
            {
                return $this->db->update("nomisma_user_details",$obj);
            }
            return 0;
        }
        
        function changeuserStatus( $id = -1 , $status = -1)
        {
            if($id != -1 && $status != -1)
            {
                $result = $this->db->update('nomisma_user_details',array("usr_status"=> $status),array('usr_id'=> $id));
                return ($result > 0 )? 1 : 0;
            }
            return 0;
        }
        
        public function getevents($id=-1) {
            $cond = '';
            if($id != -1)
                $cond = "where evt_id={$id}";
                
            return $this->db->query("Select * from jax_event {$cond}")->result();
        }
        
       public function saveevent($obj)
        {
            if(isset($obj))
            {
                 $this->db->insert('jax_event',$obj);
                 return $this->db->insert_id();
            }
            return 0;
        }
        
        public function updateevent($obj = NULL)
        {
            if(isset($obj))
            {
                $result = $this->db->update("jax_event",$obj,array('evt_id'=>$obj['evt_id']));
                return ($result > 0 )? 1 : 0;
            }
            return 0;
        }
        
        function eventStatus( $id = -1 , $status = -1)
        {
            if($id != -1 && $status != -1)
            {
                $result = $this->db->update('jax_event',array("evt_status"=> $status),array('evt_id'=> $id));
                return ($result > 0 )? 1 : 0;
            }
            return 0;
        }
        
        function founded(){
            $res = $this->db->query("select cat_id,ad_category,ad_id from jax_category , dummy where cat_title = ad_category")->result();
           // print_r($res);
            foreach ($res as $key => $value)
            {
                $this->db->update('dummy',array('ad_category'=>$value->cat_id),array('ad_id'=>$value->ad_id));
            }
            echo $this->db->affected_rows();
        }
}