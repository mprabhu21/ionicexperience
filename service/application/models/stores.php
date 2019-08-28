<?php 
class Stores extends CI_Model
{
        public function general()
        {
                parent::__construct();
                $this->load->helper('url');
                $this->load->database();
        }

    //Create new store
        public function newStore( $strArr  = array() ) {
            $return_val = 0;    
            if( !empty( $strArr ) )
                {
                     //insert into database  
                    if( $this->db->insert('go_store' , $strArr ) )
                    { $return_val = 1; } 
                } 
             return $return_val;
    }
    //Update store
    public function updateStore( $strArr = array() ) {
        if( !empty( $strArr ) )
        {
            $where = array("str_id"=> $strArr['str_id'] , "str_userid" => $strArr['str_userid'] );
          // $where = "str_id=".$strArr['str_id']." AND str_userid =".$strArr['str_userid'];
           return $this->db->update( 'go_store' , $strArr ,$where );
        }
    }
    public function getStoreById(  $owner_id = -1 , $str_id = -1 , $pgcond = array(), $count = -1 , $start = 0 , $end = 10 ,$cities = -1 )
    {
        if( $owner_id != -1 || $str_id != -1 )
        {
            
            $limit = " LIMIT {$start},{$end}";
            $select = "str_id,str_name,str_address,cty_title,str_phone,str_status";
            $cond = "WHERE str_city = cty_id";
            $group = '';
            if( !empty( $pgcond ) )
            {
                $cond .= ( $pgcond['strn'] != '' ) ? " AND str_name LIKE'%{$pgcond['strn']}%'" : "";
                $cond .= ( $pgcond['strc'] != 0 ) ? " AND str_city = {$pgcond['strc']}" : "";
            }
            if( $owner_id != -1 )
            {
                $cond .= " AND  str_userid = ".$owner_id;
                if( $cities != -1 )
                {
                    $limit = '';
                    $select = 'cty_title,cty_id';
                    $group .= "   group by str_city  ";
                }
            }
           if( $str_id != -1 )
           {
                $cond .= " AND str_id = ".$str_id;
                $select .= ",str_country,str_city,str_zipcode,str_phone,str_website,str_latitude,str_longtitude,str_metakey,str_metadescription,str_logo";
           }
           if( $count == 1 )
           {
               $myStores = $this->db->query("SELECT count(*) as total FROM go_store,go_city ". $cond )->result();
           }
           else
           {
               $myStores = $this->db->query("SELECT ".$select." FROM go_store,go_city ".$cond.$group.$limit)->result();
           }
            return $myStores;
        }
    }
    
    //Update Store Status
    public function upStatus( $sid =-1,$status = -1 ) {
       
        if( $sid != -1 && $status != -1 )
        {
             //echo "UPDATE go_store SET str_status = ".$status." WHERE str_id = ".$sid."";
             $upstat = $this->db->query("UPDATE go_store SET str_status = '".$status."' WHERE str_id = ".$sid."");
             return $upstat;
        }
        
    }
}

