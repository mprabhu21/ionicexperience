<?php 
class Lister extends CI_Model
{
	public function general()
	{
		parent::__construct();
		$this->load->helper('url');
		$this->load->database();
	}
	
	public function login ($params) {		 
        $query = $this->db->get_where('admin_login', $params)->row();
        if(!empty($query)){
            $authkey = md5(date('Y-m-d H:i:s'));
            $data['admin_id'] = $query->id;
            $data['auth_key'] = $authkey;
            $data['auth_key_expiry_time'] = date("Y-m-d H:i:s:v", strtotime('+10 minutes'));
            $data['auth_key_generated_time'] = date('Y-m-d H:i:s:v');
            $this->db->insert('login_authentication_log',$data);
            return json_encode(array("return" => true, "auth_key" => $authkey, "message" => 'Logged In'));
        }
        else {
            return json_encode(array("return" => false, "message" => 'Invalid Credentials'));
        }
    }

    public function activateUser($param,$mail)
    {
        if(isset($param,$mail))
        {
            $res = $this->db->update("jax_user",array("usr_status"=>"a"),  array("usr_email"=>$mail,
              "usr_activecode"=>$param,"usr_status"=>"w"))->result();
            return ($result > 0 )? 1 : 0;
        }
        else
            return 0;

    }

    public function validateemail($param) 
    {
        if($param !='')
        {
            $result = $this->db->query("Select * from jax_user where usr_email= '{$param}'")->result();
            return empty($result) ? 0 : 1;
        }
        return 2;
    }

    public function getActiveModules($id=0) {
        $cond = 'where status = 1';
        if($id != 0)
            $cond .= " and id={$id}";
        return $this->db->query("Select * from nomisma_module_management {$cond}")->result();
    }

    public function getModules($id=0) {
        $cond = 'where 1 = 1';
        if($id != 0)
            $cond .= " and id={$id}";
        return $this->db->query("Select * from nomisma_module_management {$cond}")->result();
    }


public function saveModule($obj)
{
    if(isset($obj))
    {
       $this->db->insert('nomisma_module_management',$obj);
       return $this->db->insert_id();
   }
   return 0;
}

public function updateModule($obj = NULL)
{
    if(isset($obj))
    {
        return $this->db->update("nomisma_module_management",$obj,array('id'=>$obj['id']));
    }
    return 0;
}   


    public function getOffers($id=0) {
        $cond = 'where 1 = 1';
        if($id != 0)
            $cond .= " and id={$id}";
        return $this->db->query("Select * from nomisma_offer_management {$cond}")->result();
    }

    public function saveOffer($obj)
    {
        if(isset($obj))
        {
           $this->db->insert('nomisma_offer_management',$obj);
           return $this->db->insert_id();
       }
       return 0;
    }

    public function updateOffer($obj = NULL)
    {
        if(isset($obj))
        {
            return $this->db->update("nomisma_offer_management",$obj,array('id'=>$obj['id']));
        }
        return 0;
    }

    public function signupform($data)
    {
        $this->db->insert('jax_signup', $data);
        return true;
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
        
    public function getAddons($id=0) {
        $cond = 'where 1 = 1';
        if($id != 0)
            $cond .= " and nomisma_addon_management.id={$id}";
        return $this->db->query("select nomisma_addon_management.*, nomisma_module_management.module_title, nomisma_module_management.id as dependent_module from nomisma_addon_management left join nomisma_module_management on nomisma_addon_management.dependent_module =  nomisma_module_management.id {$cond}")->result();
    }   

    public function saveAddon($obj)
    {
        if(isset($obj))
        {
           $this->db->insert('nomisma_addon_management',$obj);
           return $this->db->insert_id();
       }
       return 0;
    }

    public function updateAddon($obj = NULL)
    {
        if(isset($obj))
        {
            return $this->db->update("nomisma_addon_management",$obj,array('id'=>$obj['id']));
        }
        return 0;
    }
    
    public function getTrialUsers($id=0) {
        $cond = 'where 1 = 1';
        if($id != 0)
            $cond .= " and id={$id}";
        return $this->db->query("select * from try_nomisma_new {$cond}")->result();
    }

    public function saveTrialUsers($obj)
    {
        if(isset($obj))
        {
           $this->db->insert('try_nomisma_new',$obj);
           return $this->db->insert_id();
       }
       return 0;
    }

    public function updateTrialUsers($obj = NULL)
    {
        if(isset($obj))
        {
            return $this->db->update("try_nomisma_new",$obj,array('id'=>$obj['id']));
        }
        return 0;
    }

    public function getModuleAddons($obj = NULL)
    {
        if(isset($obj))
        {
            return $this->db->query("SELECT * FROM `nomisma_addon_management` WHERE status = 1 and dependent_module = ".$obj['moduleid'])->result();
        }
        return 0;
    }    

    public function getPayments($id=0) {
        $cond = ' 1 = 1';
        if($id != 0)
            $cond .= " and nomisma_payments.id={$id}";
        return $this->db->query("select nomisma_payments.*, nomisma_payments.payment_status as pay_status, try_nomisma_new.* from nomisma_payments left join try_nomisma_new on nomisma_payments.reference_no =  try_nomisma_new.customer_reference where nomisma_payments.payment_status != 'Pending' and {$cond} order by nomisma_payments.id desc")->result();    
    }

    public function getEmployees($id=0) {
        $cond = 'where 1 = 1';
        if($id != 0)
            $cond .= " and id={$id}";
        return $this->db->query("select *, nomisma_employee_range.id as emp_range_id from nomisma_employee_range {$cond}  ORDER BY `nomisma_employee_range`.`to_range` ASC")->result();
    }

    public function saveEmployee($obj)
    {
        if(isset($obj))
        {
           $this->db->insert('nomisma_employee_range',$obj);
           return $this->db->insert_id();
       }
       return 0;
    }

    public function updateEmployee($obj = NULL)
    {
        if(isset($obj))
        {
            return $this->db->update("nomisma_employee_range",$obj,array('id'=>$obj['id']));
        }
        return 0;
    }

    public function saveModulePricing($obj)
    {
        if(isset($obj))
        {            
           $this->db->insert('nomisma_module_pricing',$obj);
           return $this->db->insert_id();
       }
       return 0;
    }

    public function deleteModulePricing($id=0)
    {
        if($id != 0)
        {            
           return $this->db->query("DELETE FROM `nomisma_module_pricing` WHERE `nomisma_module_pricing`.`module_id` = ".$id);
       }
       return 0;
    }

    public function getModulePricing($id=0) {
        $cond = 'where 1 = 1';
        if($id != 0)
            $cond .= " and nomisma_module_pricing.module_id={$id}";
        return $this->db->query("Select nomisma_module_pricing.*, nomisma_employee_range.emp_range, nomisma_employee_range.to_range from nomisma_module_pricing left join nomisma_employee_range on nomisma_module_pricing.emp_range_id=nomisma_employee_range.id {$cond}")->result();
    }

    public function deleteAddonPricing($id=0)
    {
        if($id != 0)
        {            
           return $this->db->query("DELETE FROM `nomisma_addon_pricing` WHERE `nomisma_addon_pricing`.`addon_id` = ".$id);
       }
       return 0;
    }

    public function saveAddonPricing($obj)
    {
        if(isset($obj))
        {            
           $this->db->insert('nomisma_addon_pricing',$obj);
           return $this->db->insert_id();
       }
       return 0;
    }

    public function getAddonPricing($id=0, $modid) {
        $cond = 'where 1 = 1';
        if($id != 0)
            $cond .= " and nomisma_addon_pricing.addon_id={$id} and nomisma_addon_pricing.module_id = {$modid}";
        return $this->db->query("select nomisma_addon_pricing.emp_range_id, nomisma_addon_pricing.amount, nomisma_employee_range.emp_range,  nomisma_employee_range.to_range, nomisma_employee_range.id from nomisma_addon_pricing left join nomisma_employee_range on nomisma_addon_pricing.emp_range_id=nomisma_employee_range.id {$cond}")->result();
    }

    public function getModuleWisePricing($id=0) {
        $cond = 'where 1 = 1';
        if($id != 0)
            $cond .= " and nomisma_module_pricing.emp_range_id={$id}";
        return $this->db->query("Select nomisma_module_pricing.*, nomisma_module_management.module_title, nomisma_employee_range.emp_range from nomisma_module_pricing left join nomisma_employee_range on nomisma_module_pricing.emp_range_id=nomisma_employee_range.id 
            left join nomisma_module_management on nomisma_module_pricing.module_id = nomisma_module_management.id {$cond}")->result();
    }

    public function getAddonsList($id=0) { 
        $dispaddons = '';
        if(isset($_POST['moduleid']) && isset($_POST['emp_range_id'])){
            $result = mysql_query("SELECT * FROM `nomisma_addon_management` WHERE status = 1 and dependent_module = ".mysql_real_escape_string($_POST['moduleid']));
            while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                /*$resultk = $this->db->query("SELECT * FROM `nomisma_addon_pricing` WHERE module_id = '".mysql_real_escape_string($_POST["moduleid"])."' and addon_id = '".$row["id"]."' and emp_range_id = ".mysql_real_escape_string($_POST['emp_range_id']))->result();
                print_r($resultk);*/
                $resultk = mysql_query("SELECT * FROM `nomisma_addon_pricing` WHERE module_id = '".mysql_real_escape_string($_POST["moduleid"])."' and addon_id = '".$row["id"]."' and emp_range_id = ".mysql_real_escape_string($_POST['emp_range_id']));
                $num_rowsk = mysql_num_rows($resultk);
                if($num_rowsk > 0){
                    while ($rowk = mysql_fetch_array($resultk, MYSQL_ASSOC)) {
                        $row['addon_price'] = number_format((float)$rowk['amount'], 2, '.', '');
                    }
                }       
                $default = ($row["default_addon"]) ? 'checked="checked"  onClick="return false;"' : '';
                $dispaddons .= '<tr class="addonclass moduleclass'.$_POST['moduleid'].'">
                    <td height="30">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-gift" aria-hidden="true"></i> '.$row['addon_title'].'</td>
                    <td>&pound; '.$row['addon_price'].'</td>
                    <td><input type="checkbox" id="addons'.$row["id"].'" name="addons'.$_POST['moduleid'].'[]" value="'.$row["id"].'" '.$default.' class="module_check addoncheckbox"></td>
                </tr>';
                $default = '';
            }            
        }
        echo $dispaddons;
    }

    public function checkCouponCode()
    {
        $result = mysql_query("SELECT * FROM `nomisma_offer_management` WHERE status = 'published' and offer_validity > '".date("Y-m-d H:i:s")."' and offer_code = '".mysql_real_escape_string($_POST['coupon_code'])."'");
        $num_rows = mysql_num_rows($result);
        if($num_rows > 0){
            echo json_encode(array("return"=>true));
        } else {
        echo json_encode(array("return"=>false));
        }
    }

    public function save_generate_payment_url($obj)
    {
        if(isset($obj))
        {
           $this->db->insert('nomisma_subscription_management',$obj);
           return $this->db->insert_id();
       }
       return 0;
    }
    
    public function getSubscription($id=0) {
        $cond = 'where 1 = 1';
        $select = '';
        $join = '';
        if($id != 0){
            $cond .= " and nomisma_subscription_management.id={$id}";
            $select = 'nomisma_payments.payment_currency, nomisma_payments.payment_status as pstatus, nomisma_payments.payment_amount, nomisma_payments.payment_type, nomisma_payments.createdtime, ';
            $join = 'left join nomisma_payments on nomisma_subscription_management.reference_code = nomisma_payments.reference_no';
        }
        return $this->db->query("select {$select} nomisma_subscription_management.id as subid, nomisma_subscription_management.*, try_nomisma_new.* from nomisma_subscription_management left join try_nomisma_new on nomisma_subscription_management.reference_code =  try_nomisma_new.customer_reference {$join} {$cond} order by nomisma_subscription_management.id desc")->result();    
    }

    public function getDashboardCount() {
        $data['totalusers'] = $this->db->query("SELECT count(*) as totalusers FROM `try_nomisma_new`")->result_array();
        $data['modulecount'] = $this->db->query("SELECT count(*) as modulecount FROM `nomisma_module_management` WHERE  `status` = 1")->result_array();
        $data['addoncount'] = $this->db->query("SELECT count(*) as addoncount FROM `nomisma_addon_management` WHERE  `status` = 1")->result_array();
        $data['subscount'] = $this->db->query("SELECT count(*) as subscount FROM `nomisma_subscription_management`")->result_array();
        $data['paycount'] = $this->db->query("SELECT count(*) as paycount FROM `nomisma_payments`")->result_array();
        return $data;
    }

    public function check_offer_code($email, $id)
    {
        $cond = ($id == -1) ? '1=1' : ' id != '.$this->input->post('id');
        $original_value = $this->db->query("SELECT offer_code FROM nomisma_offer_management WHERE ".$cond);
        $check_unique = false;
        if($original_value->num_rows() > 0){
            foreach($original_value->result_array() as $key => $value){
                if($this->input->post('offer_code') == $value['offer_code']) {
                   $check_unique = true;
                }
            }
        }
        return $check_unique;
    }

    public function saveModulePricingBatch($obj)
    {
        if(isset($obj))
        {            
           $this->db->insert_batch('nomisma_module_pricing', $obj);
           return true;
       }
       return 0;
    }

    public function saveAddonPricingBatch($obj)
    {
        if(isset($obj))
        {            
           $this->db->insert_batch('nomisma_addon_pricing',$obj);
           return true;
       }
       return 0;
    }

    public function getModuleUsed($id)
    {
        $result = mysql_query("SELECT * FROM `nomisma_addon_management` WHERE dependent_module = '".mysql_real_escape_string($id)."'");
        $num_rows = mysql_num_rows($result);
        return $num_rows;
    }

    public function getAddonUsed($id)
    {
        $result = $this->db->query("SELECT * FROM `nomisma_offer_management` WHERE offer_type = 'addon'");
        if($result->num_rows() > 0){
            foreach($result->result_array() as $key => $value){
                $addonlist = explode(',', $value['addon_modules']);
                $find_list = array_search($id, $addonlist);
                if($find_list > 0){
                    return true;
                }
            }            
        } else {
            return false;
        }
    }

    public function getConfiguration($id=0) {
        $cond = 'where 1 = 1';
        if($id != 0)
            $cond .= " and id={$id}";
        return $this->db->query("Select * from nomisma_configuration {$cond}")->result();
    }

    public function updateConfiguration($obj = NULL)
    {
        if(isset($obj))
        {
            return $this->db->update("nomisma_configuration",$obj,array('id'=>$obj['id']));
        }
        return 0;
    }
    
}