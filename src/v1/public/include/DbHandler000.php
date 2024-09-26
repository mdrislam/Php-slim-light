<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

/*
A domain Class to demonstrate RESTful web services
*/

class DbHandler
{
    const NORMAL_USER = 0;

    const JOB_CARD_CREATED = 152;
	const QA_TESTING = 113;
	const QC_COMPLETED = 155;
	const JOB_CARD_CLOSED = 156;
	
	const HOME = 145;
    const OFFICE = 146;
	
	
    private $conn;

    function __construct()
    {
        require_once dirname(__FILE__) . '/DbConnect.php';
        // opening db connection
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    /**
     * Validating user
     *
     * @param id
     * @param pass
     *
     * @return user
     * @author risad <risadmit05@email.com>
     */
    function getVersionInformation()
    {
        $stmt = $this->conn->prepare("SELECT * FROM mobile_app_version WHERE is_active = 1 ORDER BY ID DESC LIMIT 1");
        if ($stmt->execute()) {
            $task = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $task;
        } else {
            return NULL;
        }
    }

    
    /**
     * Validating user
     *
     * @param id
     * @param pass
     *
     * @return user
     * 
     */
     /**@author risad <risad@email.com>*/
    function getAppPermission($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM app_permission WHERE user_id = '$id' ");
        if ($stmt->execute()) {
            $task = $stmt->get_result();
            $stmt->close();
            return $task;
        } else {
            return NULL;
        }

        
    }


    /**
     * Validating user api key
     * If the api key is there in db, it is a valid key
     * @param String $api_key user api key
     * @return boolean
     * @author risad <email@email.com>
     */
    public function isValidApiKey($id, $api_key)
    {
        $sql = "SELECT id from users WHERE apiKey= ? and id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $api_key, $id);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    /**@author risad <risad@email.com>*/
    public function isPasswordValid($id, $old_password)
    {
        $md5_password = md5($old_password);

        $sql = "SELECT id from users WHERE password = ? and id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $md5_password, $id);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }



    /**
     * Validating user
     *
     * @param id
     * @param pass
     *
     * @return user
     * @author  risad <risadmit05@email.com>
     */
    function getUserByCredentitial($id, $pass)
    {

        $credential = md5($pass);

        $stmt = $this->conn->prepare("SELECT * from users WHERE username = ? and password = ? and status = 1");
        $stmt->bind_param("ss", $id, $credential);
        if ($stmt->execute()) {
            $task = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $task;
        } else {
            return NULL;
        }
    }

     /**@author risad <risad@email.com>*/
    function getUserByName($id)
    {
        $stmt = $this->conn->prepare("SELECT * from users WHERE username = ?");
        $stmt->bind_param("s", $id);
        if ($stmt->execute()) {
            $task = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $task;
        } else {
            return NULL;
        }
    }


    /**
     * get user info
     *
     * @param id
     * @param pass
     *
     * @return user
     * @author risad <risadmit05@email.com>
     */
    function getUserInfo($id)
    {

        $stmt = $this->conn->prepare("SELECT users.*,employees.department_id from users LEFT JOIN employees ON employees.id = users.employee_id WHERE users.id = ?");
        // SELECT users.id,employees.department_id  FROM `users` users INNER JOIN employees ON employees.id = users.employee_id WHERE users.id = 171 ORDER BY `id` DESC
        $stmt->bind_param("s", $id);
        if ($stmt->execute()) {
            $task = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $task;
        } else {
            return NULL;
        }
    }
    /**
     * get user info
     *
     * @param id
     * @param pass
     *
     * @return user
     * @author risad <risadmit05@email.com>
     */
    function getDepartmentByEmpId($id)
    {

        $stmt = $this->conn->prepare("SELECT department_id from employees WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $task = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $task;
        } else {
            return NULL;
        }
    }


     /**
     * get Assigned Vehicle info
     *
     * @param id
     * @param pass
     *
     * @return vehicle_info
     *
     */
    function getAssignedVehicleInfo($id)
    {
        
        $stmt = $this->conn->prepare("SELECT * from vehicle_info WHERE assign_driver_id = ?");
        $stmt->bind_param("s", $id);
        if ($stmt->execute()) {
            $task = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $task;
        } else {
            return NULL;
        }
    }
    /**
     * get user info
     *
     * @param id
     * @param pass
     *
     * @return user
     *
     */
    function getSendNotification($id,$type,$title,$body,$user_id,$click,$department)
    {

        $url = 'https://fcm.googleapis.com/fcm/send';

        // Server API key obtained from the Firebase project settings
        $serverKey = 'AAAARv_mX2M:APA91bHe5O1XRZ4AgKkGa_IdOTLU2_iuiHWv5IekzFSlOCx_Ocf6qWPMb9ZIOkSTu6Aw7gDWkajKHAFuaGthkIEE266Lp-rkziuVo_iLBQv6-Xcp79CJnSBvvDCOPd9EXEEHzLHxseyi';

        // Notification payload
        $notification = [
            'title' => $title,
            'body' => $body,
             "sound"=>"jetsons_doorbell.mp3"
        ];
        $data = [
            'type' => $click,
            'id' => $id
        ];
        $android = [
            'notification_count' => ['notification_count'=>"2"],
            'channel_id' => 1,
        ];

        // Device token received by the Flutter app
        //$requisition_id,$topic,$sendBy,$title,$body,$click
        
        if($type=='admin'){

            $deviceToken = '/topics/admin';
             $this->savePushNotification($id,'admin',$user_id,$title,$body,$click);
        }else if($type=='basic') {
            $deviceToken = "/topics"."/".$department;
             $this->savePushNotification($id,$department,$user_id,$title,$body,$click);
        }
        else{
            $db=new DbHandler();
        $result = $db->getUserInfo($user_id);
         $this->savePushNotification($id,null,$user_id,$title,$body,$click);
        if($result){

            $deviceToken = isset( $result['device_token'])? $result['device_token']:'';
        }else{

            $deviceToken = '';
        }

        }
        // Request payload
        $data = [
            'to' =>$deviceToken ,
            'notification' => $notification,
            'android' => $android,
            'data' => $data,
        ];

        // HTTP headers
        $headers = [
            'Authorization: key=' . $serverKey,
            'Content-Type: application/json',
        ];

        // Create the CURL request
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        // Send the request
        $response = curl_exec($ch);

        // Check for errors
        if ($response === false) {
            echo 'Error: ' . curl_error($ch);
        }

        // Close CURL
        curl_close($ch);

        // Print the response
       // echo $response;
    }

    	
	public function savePushNotification($requisition_id,$topic,$sendBy,$title,$body,$click)
    {
        $sql = "INSERT INTO `push_notifications` (`requisition_id`, `topic`, `send_by`, `title`, `body`, `click`) VALUES ('$requisition_id', '$topic', '$sendBy', '$title', '$body', '$click')";
		if ($stmt = $this->conn->prepare($sql)) {
				$result = $stmt->execute();
				$stmt->close();
				$lastInsertedId = $result ? $this->conn->insert_id : 0;
				return $lastInsertedId;
		} else {
				$error = $this->conn->errno . ' ' . $this->conn->error;
				echo $error;
				return null;
		}
    }
	
    // /**@author risad <risad@email.com>*/
	public function getVehicleInfoAll($pageno,$sql_search){
		
		$no_of_records_per_page = 25;
        $offset = ($pageno  * $no_of_records_per_page) - $no_of_records_per_page;
        $sql ='';
				
		$sql="SELECT * FROM vehicle_info $sql_search ORDER BY unit_name_id DESC LIMIT $no_of_records_per_page OFFSET $offset";
      
        if($stmt=$this->conn->prepare($sql)){
			$stmt->execute();
			$task=$stmt->get_result();
			$stmt->close();
			return $task;
		}else{
			$error=$this->conn->errno.' '.$this->conn->error;
			return null;
		}		
			
		
	}
	
    // /**@author risad <risad@email.com>*/
	public function getCustomModel($sql,$type)
    {
			
            if ($stmt = $this->conn->prepare($sql)) {
                $stmt->execute();
				$tasks;
				if($type==1){
					$tasks = $stmt->get_result()->fetch_assoc(); //ONE ROW
				}else{
					$tasks = $stmt->get_result();   //MULTIPLE ROW
				}
                $stmt->close();
                return $tasks;
            } else {
                $error = $this->conn->errno . ' ' . $this->conn->error;
               
                return NULL;
            }	
        
    }
	
	public function maxValEntryNo()
    {
        $sql = "SELECT MAX(entry_no) as entry_no FROM issue_fuel";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $task = $stmt->get_result()->fetch_assoc();
        $entry_no = $task['entry_no'];
        if ($entry_no != NULL) {
            $entry_no = $entry_no + 1;
            $stmt->close();
            return $entry_no;
        } else {
            $stmt->close();
            return 1;
        }
    }

	public function saveCustomModel($param,$tableName)
    {
        $keys = implode(', ', array_keys($param));
        $values = "'" . implode("','", array_values($param)) . "'";
	    $sql = "INSERT INTO $tableName ($keys) VALUES ($values)";
	

		if ($stmt = $this->conn->prepare($sql)) {
				$result = $stmt->execute();
				$stmt->close();
				$lastInsertedId = $result ? $this->conn->insert_id : 0;
				return $lastInsertedId;
		} else {
				$error = $this->conn->errno . ' ' . $this->conn->error;
				echo $error;
				return null;
		}
    }

	public function saveUsageRequisitionModel($param,$tableName)
    {
		
        
        $keys = implode(', ', array_keys($param));
        $values = "'" . implode("','", array_values($param)) . "'";
	    $sql = "INSERT INTO $tableName ($keys) VALUES ($values)";
	

		if ($stmt = $this->conn->prepare($sql)) {
				$result = $stmt->execute();
				$stmt->close();
				$lastInsertedId = $result ? $this->conn->insert_id : 0;
				return $lastInsertedId;
		} else {
				$error = $this->conn->errno . ' ' . $this->conn->error;
				echo $error;
				return null;
		}
    }
	
	public function saveVisitLocationModel($param,$tableName)
    {
		
        
        $keys = implode(', ', array_keys($param));
        $values = "'" . implode("','", array_values($param)) . "'";
	    $sql = "INSERT INTO $tableName ($keys) VALUES ($values)";
	

		if ($stmt = $this->conn->prepare($sql)) {
				$result = $stmt->execute();
				$stmt->close();
				$lastInsertedId = $result ? $this->conn->insert_id : 0;
				return $lastInsertedId;
		} else {
				$error = $this->conn->errno . ' ' . $this->conn->error;
				echo $error;
				return null;
		}
    }
	
     /**@author risad <risad@email.com>*/
	public function updateCustomModel($sql){


        if ($stmt1 = $this->conn->prepare($sql)) {
            $result = $stmt1->execute();
         
            $stmt1->close();
            return $result;
        } else {
            $error = $this->conn->errno . ' ' . $this->conn->error;
         
         return $error;
        }


    }
    
	public function deleteItem($sql){


        if ($stmt1 = $this->conn->prepare($sql)) {
            $result = $stmt1->execute();
         
            $stmt1->close();
            return $result;
        } else {
            $error = $this->conn->errno . ' ' . $this->conn->error;
         
         return $error;
        }


    }

 
	
    /** 
     * @requires Get Emlpoyees By Emp ID Or Name
     * @return employee result
     * @author Risad <risadmit05@email.com>
     */
	public function getEmployeeByIdOrName($sqlSearch){

		$sql='';
		$fields = 'id,full_name,designation_id,department_id,contact_no_office as contact_no,email,emp_id_no';
         $sql = "SELECT $fields FROM employees WHERE (LOWER(emp_id_no) LIKE '%$sqlSearch%') OR (LOWER(full_name) LIKE '%$sqlSearch%')";

        $params = array($sqlSearch,$sqlSearch);
          if($stmt=$this->conn->prepare($sql)){
            $stmt->execute();
            $tasks = $stmt->get_result();
            $stmt->close();
            return $tasks;
          }else {
              $error =$this->conn->errno . ' ' . $this->conn->error;
              return null;
          }
    }
     /** 
     * @requires Nothing
     * @return VahicleType All result
     * @author Risad <risadmit05@email.com>
     */
	public function getAllVehicleType(){

        $fields = 'id,title as name ';
        $sql ='';
				
		$sql="SELECT $fields FROM vc_types ORDER BY id DESC ";
      
        if($stmt=$this->conn->prepare($sql)){
			$stmt->execute();
			$task=$stmt->get_result();
			$stmt->close();
			return $task;
		}else{
			$error=$this->conn->errno.' '.$this->conn->error;
			return null;
		}	

    }
     /** 
     * @requires Nothing
     * @return VahicleType All result
     * @author Risad <risadmit05@email.com>
     */
	public function getCustomers(){

        $fields = 'id,customer_name,address';
        $sql ='';
				
		$sql="SELECT $fields FROM customers ORDER BY id DESC ";
      
        if($stmt=$this->conn->prepare($sql)){
			$stmt->execute();
			$task=$stmt->get_result();
			$stmt->close();
			return $task;
		}else{
			$error=$this->conn->errno.' '.$this->conn->error;
			return null;
		}	

    }
     /** 
     * @requires Nothing
     * @return GetAsinged Drivers By id
     * @author Risad <risadmit05@email.com>
     */
	public function getAssignedDrivers($id){

        $fields = 'uvd.id,uvd.vehicle_id,uvd.driver_id,uvd.usage_req_id,vi.reg_no,vi.current_unit_id,di.name,di.driver_id_no';
        $sql ='';
        //driver_info
        // $sql = "SELECT id,reg_no,current_unit_id FROM vehicle_info
        // $sql = "SELECT id,name,driver_id_no FROM driver_info
        // WHERE id NOT IN ($valuesString1)";

		$sql="SELECT $fields FROM usage_req_vehicle_details AS uvd INNER JOIN vehicle_info AS vi ON uvd.vehicle_id=vi.id INNER JOIN  driver_info AS di ON uvd.driver_id=di.id WHERE uvd.usage_req_id=$id";
      
        if($stmt=$this->conn->prepare($sql)){
			$stmt->execute();
			$task=$stmt->get_result();
			$stmt->close();
			return $task;
		}else{
			$error=$this->conn->errno.' '.$this->conn->error;
			return null;
		}	

    }
     /** 
     * @requires Nothing
     * @return GET ENGAGED VEHICLE All result
     * @author Risad <risadmit05@email.com>
     */
	public function getEngagedVehicleRe($statTime){

        $sql =  "SELECT ud.vehicle_id FROM usage_requisition AS ur
        JOIN usage_req_vehicle_details AS ud ON ur.id = ud.usage_req_id
        WHERE ur.requisition_approve != 10 and (('$statTime' BETWEEN ur.trip_start_time AND ur.trip_return_time) OR ('$statTime' BETWEEN ur.extended_trip_start_time AND ur.extended_trip_return_time) OR ('$statTime' >= ur.trip_start_time AND ur.trip_return_time IS NULL)) AND (ur.status != 'END' OR ur.status IS NULL)";

        if($stmt=$this->conn->prepare($sql)){
			$stmt->execute();
			$task=$stmt->get_result();
			$stmt->close();
			return $task;
		}else{
			$error=$this->conn->errno.' '.$this->conn->error;
			return null;
		}	

    }
     /** 
     * @requires Nothing
     * @return GET ENGAGED VEHICLE All result
     * @author Risad <risadmit05@email.com>
     */
	public function getEngagedVehicle($statTime){

        $fields = 'id,customer_name';
        $sql ='';
       
        // $sql = "SELECT ud.vehicle_id FROM usage_requisition AS ur
        // JOIN usage_req_vehicle_details AS ud ON ur.id = ud.usage_req_id
        // WHERE ('$statTime' BETWEEN ur.trip_start_time AND ur.trip_return_time) OR ('$statTime' BETWEEN ur.extended_trip_start_time AND ur.extended_trip_return_time)";
        $sql = "SELECT d.vehicle_id as vehicle_id FROM `usage_requisition` t inner join usage_req_vehicle_details d on d.usage_req_id = t.id where t.status != 'END' and t.status != '' group by d.vehicle_id";

        // $sql = "SELECT * FROM usage_requisition WHERE ('$statTime' BETWEEN trip_start_time AND trip_return_time) OR ('$statTime' BETWEEN extended_trip_start_time AND extended_trip_return_time)";

        if($stmt=$this->conn->prepare($sql)){
			$stmt->execute();
			$task=$stmt->get_result();
			$stmt->close();
			return $task;
		}else{
			$error=$this->conn->errno.' '.$this->conn->error;
			return null;
		}	

    }
     /** 
     * @requires Nothing
     * @return GET ENGAGED VEHICLE All result
     * @author Risad <risadmit05@email.com>
     */
	public function getEngagedRoutePlanVehicle(){

        date_default_timezone_set("Asia/Dhaka");

        $currentDate = date('y-m-d');
        $currentTime = date('H:i:s');

        $sql = "SELECT vehicle_id FROM route_planing WHERE morning_shift_out_time <= '$currentTime' AND evening_shift_in_time >= '$currentTime' AND date_from >= '$currentDate' AND date_to <= '$currentDate' and vehicle_id > 0 group by vehicle_id";

        if($stmt=$this->conn->prepare($sql)){
			$stmt->execute();
			$task=$stmt->get_result();
			$stmt->close();
			return $task;
		}else{
			$error=$this->conn->errno.' '.$this->conn->error;
			return null;
		}	

    }
     /** 
     * @requires Nothing
     * @return GET ENGAGED VEHICLE All result
     * @author Risad <risadmit05@email.com>
     */
	public function getGarageEngagedVehicle(){

        date_default_timezone_set("Asia/Dhaka");

        $currentDate = date('y-m-d');
        $currentTime = date('H:i:s');

        $sql = "SELECT vehicle_id FROM complain_sheet WHERE flag =1 Group by vehicle_reg_no";

        if($stmt=$this->conn->prepare($sql)){
			$stmt->execute();
			$task=$stmt->get_result();
			$stmt->close();
			return $task;
		}else{
			$error=$this->conn->errno.' '.$this->conn->error;
			return null;
		}	

    }
     /** 
     * @requires Nothing
     * @return GET Available Vehicle All result
     * @author Risad <risadmit05@email.com>
     */
	public function getAvaiableVehicle($vehicles){

        $sql ='';

    // Prepare the excluded values as a comma-separated string
    $valuesString1 = implode(',', $vehicles);

    if (!empty($vehicles)){
        // list is empty.
        $sql = "SELECT id,reg_no,current_unit_id,assign_driver_id FROM vehicle_info
        WHERE id NOT IN ($valuesString1) AND (flag <>1 OR flag is null) AND isActive='1'";
       // $sql = "SELECT id,reg_no,current_unit_id FROM vehicle_info";

   }else{
        $sql = "SELECT id,reg_no,current_unit_id,assign_driver_id FROM vehicle_info where (flag <>1 OR flag is null) AND isActive=1";
   }

        if($stmt=$this->conn->prepare($sql)){
			$stmt->execute();
			$task=$stmt->get_result();
			$stmt->close();
			return $task;
		}else{
			$error=$this->conn->errno.' '.$this->conn->error;
			return null;
		}	

    }
     /** 
     * @requires Nothing
     * @return GET Available Vehicle All result
     * @author Risad <risadmit05@email.com>
     */
	public function getVehicles(){

        $sql ='';
        // list is empty.
        $sql = "SELECT id,reg_no,current_unit_id FROM vehicle_info WHERE isActive=1";
     
        if($stmt=$this->conn->prepare($sql)){
			$stmt->execute();
			$task=$stmt->get_result();
			$stmt->close();
			return $task;
		}else{
			$error=$this->conn->errno.' '.$this->conn->error;
			return null;
		}	

    }

      /** 
     * @requires Nothing
     * @return GET ENGAGED VEHICLE All result
     * @author Risad <risadmit05@email.com>
     */
	public function getEngagedDriver($statTime){

        $sql ='';
       
        // $sql = "SELECT ud.driver_id FROM usage_requisition AS ur
        // JOIN usage_req_vehicle_details AS ud ON ur.id = ud.usage_req_id
        // WHERE ('$statTime' BETWEEN ur.trip_start_time AND ur.trip_return_time) OR ('$statTime' BETWEEN ur.extended_trip_start_time AND ur.extended_trip_return_time) AND ur.status != 'END'";

 $sql = "SELECT ud.driver_id FROM usage_requisition AS ur
        JOIN usage_req_vehicle_details AS ud ON ur.id = ud.usage_req_id
        WHERE ur.requisition_approve != 10 and (('$statTime' BETWEEN ur.trip_start_time AND ur.trip_return_time) OR ('$statTime' BETWEEN ur.extended_trip_start_time AND ur.extended_trip_return_time) OR ('$statTime' >= ur.trip_start_time AND ur.trip_return_time IS NULL)) AND (ur.status != 'END' OR ur.status IS NULL)";

        // $sql = "SELECT * FROM usage_requisition WHERE ('$statTime' BETWEEN trip_start_time AND trip_return_time) OR ('$statTime' BETWEEN extended_trip_start_time AND extended_trip_return_time)";

        if($stmt=$this->conn->prepare($sql)){
			$stmt->execute();
			$task=$stmt->get_result();
			$stmt->close();
			return $task;
		}else{
			$error=$this->conn->errno.' '.$this->conn->error;
			return null;
		}	

    }

     /** 
     * @requires Nothing
     * @return GET Available Vehicle All result
     * @author Risad <risadmit05@email.com>
     */
	public function getAvaiableDriver($drivers){

        $sql ='';

    // Prepare the excluded values as a comma-separated string
    $valuesString1 = implode(',', $drivers);

    if (!empty($drivers)){
        $sql = "SELECT id,name,driver_id_no FROM driver_info
              WHERE id NOT IN ($valuesString1)";

    }else{
         $sql = "SELECT id,name,driver_id_no FROM driver_info ";


    }

    
            //   AND column2 NOT IN ($valuesString2)";
  

        if($stmt=$this->conn->prepare($sql)){
			$stmt->execute();
			$task=$stmt->get_result();
			$stmt->close();
			return $task;
		}else{
			$error=$this->conn->errno.' '.$this->conn->error;
			return null;
		}	

    }

     /** 
     * @requires Nothing
     * @return Requistions All result
     * @author Risad <risadmit05@email.com>
     */
	public function getRequisitions($pageno,$sch,$sch_type,$user_id,$type=0){

        $no_of_records_per_page = 15;
        $offset = ($pageno-1) * $no_of_records_per_page;
		$sql='';

        $fields = 'ur.id,ur.requisition_date,ur.user_id,emp.full_name,ur.extended_requisition_date,emp.emp_id_no,ur.user_number as contact_no,vt.title as name,ur.no_of_passenger,ur.status,ur.trip_start_time,ur.extended_trip_start_time,ur.requisition_approve,ur.trip_return_time,ur.extended_trip_return_time,ur.purpose,ur.remarks,ur.is_extended ';
        $sql ='';

            $user=$this->getUserInfo($user_id);
            $dept= isset($user['department_id'])?$user['department_id']:'-1';
            $emp_id= isset($user['employee_id'])?$user['employee_id']:'-1';
            if($user['user_type']==0){
                /**Admin User */
                if($type==1){
                    //Actions Requistions
                    if($sch_type==0){
                        //Req Seaching
                        $sql="SELECT $fields FROM usage_requisition AS ur LEFT JOIN vc_types AS vt ON ur.vehicle_type = vt.id INNER JOIN employees AS emp ON ur.user_id = emp.id WHERE ur.requisition_approve = '4' AND ur.id LIKE '%$sch%' AND ( ur.status != 'END' OR ur.status is null) ORDER BY ur.id DESC LIMIT $offset, $no_of_records_per_page ";
                    }else{
                        //Reg_no Search

                        $sql="SELECT $fields FROM usage_requisition AS ur LEFT JOIN vc_types AS vt ON ur.vehicle_type = vt.id LEFT JOIN employees AS emp ON ur.user_id = emp.id LEFT JOIN usage_req_vehicle_details AS urvd ON urvd.usage_req_id = ur.id LEFT JOIN vehicle_info AS vi ON vi.id = urvd.vehicle_id WHERE ur.requisition_approve = '4' AND ( ur.status != 'END' OR ur.status is null) AND vi.reg_no LIKE '%$sch%' ORDER BY ur.id DESC LIMIT $offset, $no_of_records_per_page ";
                    }

                }else{
                    //Requisitions

                    if($sch_type==0){
                        $sql="SELECT $fields FROM usage_requisition AS ur LEFT JOIN vc_types AS vt ON ur.vehicle_type = vt.id LEFT JOIN employees AS emp ON ur.user_id = emp.id WHERE ur.id LIKE '%$sch%' AND ( ur.status != 'END' OR ur.status is null ) AND  ur.requisition_approve != '10' ORDER BY ur.id DESC LIMIT $offset, $no_of_records_per_page ";
                    }else{
                        $sql="SELECT $fields FROM usage_requisition AS ur LEFT JOIN vc_types AS vt ON ur.vehicle_type = vt.id LEFT JOIN employees AS emp ON ur.user_id = emp.id LEFT JOIN usage_req_vehicle_details AS urvd ON urvd.usage_req_id = ur.id LEFT JOIN vehicle_info AS vi ON vi.id = urvd.vehicle_id WHERE vi.reg_no LIKE '%$sch%' AND ( ur.status != 'END' OR ur.status is null ) AND  ur.requisition_approve != '10' ORDER BY ur.id DESC LIMIT $offset, $no_of_records_per_page ";

                    }

                }
           
            }else if($user['user_type']==3){

                /** Basic User***/ 
                if($type==1){
                    //Actions Requistions
                    $statTime = date('y-m-d H:i:s');
                    $id=$user['id'];
                    if($sch_type==0){

                    //REq ID
                    $sql= "SELECT $fields FROM usage_requisition AS ur
                    LEFT JOIN visit_location AS vl ON ur.id = vl.requisition_id 
                    LEFT JOIN vc_types AS vt ON ur.vehicle_type = vt.id 
                    INNER JOIN employees AS emp ON ur.user_id = emp.id 
                    WHERE ur.id LIKE '%$sch%' AND ur.requisition_approve = '4' AND ( ur.status != 'END' OR ur.status is null) and (ur.department_id = '$dept' OR vl.passenger_id = '$emp_id' OR ur.created_by = '$id') GROUP BY ur.id ORDER BY ur.id DESC LIMIT $offset, $no_of_records_per_page ";

                    }else{
                        //REG NO
                        $sql= "SELECT $fields FROM usage_requisition AS ur
                        LEFT JOIN visit_location AS vl ON ur.id = vl.requisition_id 
                        LEFT JOIN vc_types AS vt ON ur.vehicle_type = vt.id 
                        INNER JOIN employees AS emp ON ur.user_id = emp.id 
                        INNER JOIN usage_req_vehicle_details AS urvd ON urvd.usage_req_id = ur.id 
                        LEFT JOIN vehicle_info AS vi ON vi.id = urvd.vehicle_id 
                        WHERE vi.reg_no LIKE '%$sch%' AND ur.requisition_approve = '4' AND ( ur.status != 'END' OR ur.status is null) and (vl.passenger_id = '$emp_id' OR ur.department_id = '$dept' OR ur.created_by = '$id') GROUP BY ur.id
                        ORDER BY ur.id DESC LIMIT $offset, $no_of_records_per_page";
                    }

                }else{
                    $id=$user['id'];
                    
                    if($sch_type==0){

                        //REq ID
                        $sql="SELECT $fields FROM usage_requisition AS ur 
                        LEFT JOIN visit_location AS vl ON ur.id = vl.requisition_id 
                        LEFT JOIN vc_types AS vt ON ur.vehicle_type = vt.id 
                        INNER JOIN employees AS emp ON ur.user_id = emp.id 
                        WHERE  (ur.created_by = '$id' OR ur.department_id = '$dept' OR vl.passenger_id = '$emp_id') AND ur.id LIKE '%$sch%' AND ( ur.status != 'END' OR ur.status is null ) AND  ur.requisition_approve != '10' GROUP BY ur.id ORDER BY ur.id DESC LIMIT $offset, $no_of_records_per_page ";
                        
                    }else{
                    
                         //REG NO
                         $sql= "SELECT $fields FROM usage_requisition AS ur
                         LEFT JOIN visit_location AS vl ON ur.id = vl.requisition_id 
                         LEFT JOIN vc_types AS vt ON ur.vehicle_type = vt.id 
                         INNER JOIN employees AS emp ON ur.user_id = emp.id 
                         INNER JOIN usage_req_vehicle_details AS urvd ON urvd.usage_req_id = ur.id 
                         LEFT JOIN vehicle_info AS vi ON vi.id = urvd.vehicle_id 
                         WHERE vi.reg_no LIKE '%$sch%' and (vl.passenger_id = '$emp_id' OR ur.department_id = '$dept' OR ur.created_by = '$id') AND ( ur.status != 'END' OR ur.status is null ) AND  ur.requisition_approve != '10' GROUP BY ur.id
                         ORDER BY ur.id DESC LIMIT $offset, $no_of_records_per_page";
                        
                    }
                }
         
           }else if($user['user_type']==1){

                 // Driver
                 if($type==1){
                    //Actions Requistions
                    $statTime = date('y-m-d H:i:s');
                     $driver=$user['driver_id'];

                    $sql= "SELECT $fields FROM usage_requisition AS ur
                    JOIN usage_req_vehicle_details AS ud ON ur.id = ud.usage_req_id LEFT JOIN vc_types AS vt ON ur.vehicle_type = vt.id INNER JOIN employees AS emp ON ur.user_id = emp.id
                    WHERE ur.requisition_approve = '4' AND ( ur.status != 'END' OR ur.status is null) and ud.driver_id ='$driver'  ORDER BY ur.id DESC LIMIT $offset, $no_of_records_per_page ";

                }else{
                   //Requisitions  
                    $sql="SELECT $fields FROM usage_requisition AS ur LEFT JOIN vc_types AS vt ON ur.vehicle_type = vt.id INNER JOIN employees AS emp ON ur.user_id = emp.id WHERE ur.created_by = '$user_id' AND ( ur.status != 'END' OR ur.status is null ) AND  ur.requisition_approve != '10' ORDER BY ur.id DESC LIMIT $offset, $no_of_records_per_page ";

                }
          
            }else{

                /**********Only Passenger*****/

                if($type==1){
                    //Actions Requistions
                    $statTime = date('y-m-d H:i:s');
                    $id=$user['id'];
                    if($sch_type==0){

                    //REq ID
                    $sql= "SELECT $fields FROM usage_requisition AS ur
                    LEFT JOIN visit_location AS vl ON ur.id = vl.requisition_id 
                    LEFT JOIN vc_types AS vt ON ur.vehicle_type = vt.id 
                    INNER JOIN employees AS emp ON ur.user_id = emp.id 
                    WHERE ur.id LIKE '%$sch%' AND ur.requisition_approve = '4' AND ( ur.status != 'END' OR ur.status is null) and (ur.department_id = '$dept' OR vl.passenger_id = '$emp_id' OR ur.created_by = '$id') GROUP BY ur.id ORDER BY ur.id DESC LIMIT $offset, $no_of_records_per_page ";

                    }else{
                        //REG NO
                        $sql= "SELECT $fields FROM usage_requisition AS ur
                        LEFT JOIN visit_location AS vl ON ur.id = vl.requisition_id 
                        LEFT JOIN vc_types AS vt ON ur.vehicle_type = vt.id 
                        INNER JOIN employees AS emp ON ur.user_id = emp.id 
                        INNER JOIN usage_req_vehicle_details AS urvd ON urvd.usage_req_id = ur.id 
                        LEFT JOIN vehicle_info AS vi ON vi.id = urvd.vehicle_id 
                        WHERE vi.reg_no LIKE '%$sch%' AND ur.requisition_approve = '4' AND ( ur.status != 'END' OR ur.status is null) and (vl.passenger_id = '$emp_id' OR ur.department_id = '$dept' OR ur.created_by = '$id') GROUP BY ur.id
                        ORDER BY ur.id DESC LIMIT $offset, $no_of_records_per_page";
                    }

                }else{
                    $id=$user['id'];
                    
                    if($sch_type==0){

                        //REq ID
                        $sql="SELECT $fields FROM usage_requisition AS ur 
                        LEFT JOIN visit_location AS vl ON ur.id = vl.requisition_id 
                        LEFT JOIN vc_types AS vt ON ur.vehicle_type = vt.id 
                        INNER JOIN employees AS emp ON ur.user_id = emp.id 
                        WHERE  (ur.created_by = '$id' OR ur.department_id = '$dept' OR vl.passenger_id = '$emp_id') AND ur.id LIKE '%$sch%' GROUP BY ur.id ORDER BY ur.id DESC LIMIT $offset, $no_of_records_per_page ";
                        
                    }else{
                    
                         //REG NO
                         $sql= "SELECT $fields FROM usage_requisition AS ur
                         LEFT JOIN visit_location AS vl ON ur.id = vl.requisition_id 
                         LEFT JOIN vc_types AS vt ON ur.vehicle_type = vt.id 
                         INNER JOIN employees AS emp ON ur.user_id = emp.id 
                         INNER JOIN usage_req_vehicle_details AS urvd ON urvd.usage_req_id = ur.id 
                         LEFT JOIN vehicle_info AS vi ON vi.id = urvd.vehicle_id 
                         WHERE vi.reg_no LIKE '%$sch%' and (vl.passenger_id = '$emp_id' OR ur.department_id = '$dept' OR ur.created_by = '$id') GROUP BY ur.id
                         ORDER BY ur.id DESC LIMIT $offset, $no_of_records_per_page";
                        
                    }
                }

            }
       
		
      
        if($stmt=$this->conn->prepare($sql)){
			$stmt->execute();
			$task=$stmt->get_result();
			$stmt->close();
			return $task;
		}else{
			$error=$this->conn->errno.' '.$this->conn->error;
			return null;
		}	

    }

     /** 
     * @requires Nothing
     * @return Requistions Details For Admin Home 
     * @author Risad <risadmit05@email.com>
     */
	public function getRequisitionDetails($id){

    
        $sql ='';
        $fields = 'ur.id,ur.requisition_date,ur.user_id,emp.full_name,emp.emp_id_no,ur.user_number as contact_no,vt.title as name,ur.no_of_passenger,ur.trip_start_time,ur.requisition_approve,ur.from_location,ur.to_location,ur.trip_return_time,ur.department_id,ur.purpose,ur.remarks ';
        
		$sql="SELECT $fields FROM usage_requisition AS ur LEFT JOIN vc_types AS vt ON ur.vehicle_type = vt.id INNER JOIN employees AS emp ON ur.user_id = emp.id  WHERE ur.id = $id ";
      
        if($stmt=$this->conn->prepare($sql)){
			$stmt->execute();
			$task=$stmt->get_result();
			$stmt->close();
			return $task;
		}else{
			$error=$this->conn->errno.' '.$this->conn->error;
			return null;
		}	

    }

      /** 
     * @requires Nothing
     * @return Requistions Details For Action
     * @author Risad <risadmit05@email.com>
     */
	public function getRequisitionActionDetails($id){

    
        $sql ='';
        $fields = 'ur.id,ur.requisition_date,ur.user_id,ur.status,emp.full_name,emp.emp_id_no,ur.user_number as contact_no,vt.title as name,ur.no_of_passenger,ur.trip_start_time,ur.action_datetime,ur.requisition_approve,ur.trip_return_time,ur.department_id,ur.purpose,ur.remarks,ur.extended_trip_start_time,ur.extended_trip_return_time,ur.extended_from_location,ur.extended_to_location';
        
		$sql="SELECT $fields FROM usage_requisition AS ur LEFT JOIN vc_types AS vt ON ur.vehicle_type = vt.id INNER JOIN employees AS emp ON ur.user_id = emp.id WHERE ur.id = $id ";
      
        if($stmt=$this->conn->prepare($sql)){
			$stmt->execute();
			$task=$stmt->get_result();
			$stmt->close();
			return $task;
		}else{
			$error=$this->conn->errno.' '.$this->conn->error;
			return null;
		}	

    }
      /** 
     * @requires Nothing
     * @return Requistions Details For Action
     * @author Risad <risadmit05@email.com>
     */
	public function getReqDetails($id){

        $sql ='';
        $fields = 'ur.id,ur.requisition_date,ur.user_id,ur.status,emp.full_name,emp.emp_id_no,ur.user_number as contact_no,vt.title as name,ur.no_of_passenger,ur.trip_start_time,ur.action_datetime,ur.requisition_approve,ur.trip_return_time,ur.department_id,ur.purpose,ur.remarks ';
        
		$sql="SELECT $fields FROM usage_requisition AS ur LEFT JOIN vc_types AS vt ON ur.vehicle_type = vt.id INNER JOIN employees AS emp ON ur.user_id = emp.id WHERE ur.id = $id ";
      
        if($stmt=$this->conn->prepare($sql)){
			$stmt->execute();
			$task=$stmt->get_result();
			$stmt->close();
			return $task;
		}else{
			$error=$this->conn->errno.' '.$this->conn->error;
			return null;
		}	

    }
      /** 
     * @requires Nothing
     * @return Requistions Details For Action
     * @author Risad <risadmit05@email.com>
     */
	public function getDriverInfo($id){

    
        $sql ='';
        $fields = 'u.device_token,u.id as user_id,di.name,di.driver_id_no';
        
		$sql="SELECT $fields FROM users AS u  INNER JOIN  driver_info AS di ON di.id = u.employee_id WHERE u.employee_id = $id AND u.user_type='1' ";
      
        if($stmt=$this->conn->prepare($sql)){
			$stmt->execute();
			$task=$stmt->get_result();
			$stmt->close();
			return $task;
		}else{
			$error=$this->conn->errno.' '.$this->conn->error;
			return null;
		}	

    }
      /** 
     * @requires Nothing
     * @return Requistions Details For Action
     * @author Risad <risadmit05@email.com>
     */
	public function getRequisity($id){

    
        $sql ='';
        $fields = 'id as user_id';
        
		$sql="SELECT $fields FROM users WHERE employee_id = '$id'";
      
        if($stmt=$this->conn->prepare($sql)){
			$stmt->execute();
			$task=$stmt->get_result();
			$stmt->close();
			return $task;
		}else{
			$error=$this->conn->errno.' '.$this->conn->error;
			return null;
		}	

    }
       /** 
     * @requires Nothing
     * @return Requistions Details For Action
     * @author Risad <risadmit05@email.com>
     */
	public function getSuperviser($id){

    
        $sql ='';
        $fields = 'supervisor  as user_id';
        
		$sql="SELECT $fields FROM employees WHERE id = '$id'";
      
        if($stmt=$this->conn->prepare($sql)){
			$stmt->execute();
			$task=$stmt->get_result();
			$stmt->close();
			return $task;
		}else{
			$error=$this->conn->errno.' '.$this->conn->error;
			return null;
		}	

    }
      /** 
     * @requires Nothing
     * @return Requistions Details For Action
     * @author Risad <risadmit05@email.com>
     */
	public function getDriverUser($id){

    
        $sql ='';
        $fields = 'id as user_id';
        
		$sql="SELECT $fields FROM users WHERE driver_id = '$id'";
      
        if($stmt=$this->conn->prepare($sql)){
			$stmt->execute();
			$task=$stmt->get_result();
			$stmt->close();
			return $task;
		}else{
			$error=$this->conn->errno.' '.$this->conn->error;
			return null;
		}	

    }
      /** 
     * @requires Nothing
     * @return Requistions Details For Action
     * @author Risad <risadmit05@email.com>
     */
	public function getDriverInfoByReq($id){

    
        $sql ='';
        $fields = 'u.id as user_id,di.name,vi.reg_no,di.contact_no';
        
		$sql="SELECT $fields FROM usage_req_vehicle_details urvd INNER JOIN driver_info AS di ON di.id = urvd.driver_id INNER JOIN vehicle_info vi ON vi.id = urvd.vehicle_id LEFT JOIN users u ON u.driver_id = di.id WHERE urvd.usage_req_id = '$id'";
      
        if($stmt=$this->conn->prepare($sql)){
			$stmt->execute();
			$task=$stmt->get_result();
			$stmt->close();
			return $task;
		}else{
			$error=$this->conn->errno.' '.$this->conn->error;
			return null;
		}	

    }
      /** 
     * @requires Nothing
     * @return Requistions Details For Action
     * @author Risad <risadmit05@email.com>
     */
	public function getPassengerInfoByReq($id){

    
        $sql ='';
        $fields = 'u.id as user_id';
        
		$sql="SELECT $fields FROM visit_location vl INNER JOIN users u ON u.employee_id = vl.passenger_id WHERE vl.requisition_id = '$id' ";
      
        if($stmt=$this->conn->prepare($sql)){
			$stmt->execute();
			$task=$stmt->get_result();
			$stmt->close();
			return $task;
		}else{
			$error=$this->conn->errno.' '.$this->conn->error;
			return null;
		}	

    }
      /** 
     * @requires Nothing
     * @return Requistions Details For Action
     * @author Risad <risadmit05@email.com>
     */
	public function getVehicleInfo($id){

    
        $sql ='';
        $fields = 'reg_no';
        
		$sql="SELECT $fields FROM vehicle_info WHERE id = $id ";
      
      
        if($stmt=$this->conn->prepare($sql)){
			$stmt->execute();
			$task=$stmt->get_result();
			$stmt->close();
			return $task;
		}else{
			$error=$this->conn->errno.' '.$this->conn->error;
			return null;
		}	

    }
      /** 
     * @requires Nothing
     * @return Requistions Details For Action
     * @author Risad <risadmit05@email.com>
     */
	public function getVehicleInfoById($id){

    
        $sql ='';
        $fields = 'reg_no';
        
		$sql="SELECT * FROM vehicle_info WHERE id = $id ";
      
      
        if($stmt=$this->conn->prepare($sql)){
			$stmt->execute();
			$task=$stmt->get_result()->fetch_assoc();
			$stmt->close();
			return $task;
		}else{
			$error=$this->conn->errno.' '.$this->conn->error;
			return null;
		}	

    }


        /** 
     * @requires Nothing
     * @return Requistions Details For Action
     * @author Risad <risadmit05@email.com>
     */
	public function getAssignDriverInfo($id){

    
        $sql ='';
        $fields = 'reg_no';
        
		$sql="SELECT * FROM usage_req_vehicle_details WHERE id = $id ";
      
      
        if($stmt=$this->conn->prepare($sql)){
			$stmt->execute();
			$task=$stmt->get_result();
			$stmt->close();
			return $task;
		}else{
			$error=$this->conn->errno.' '.$this->conn->error;
			return null;
		}	

    }
      /** 
     * @requires Nothing
     * @return Requistions Details For Action
     * @author Risad <risadmit05@email.com>
     */
	public function getRequisitionActionVehicleInfo($id){

    
        $sql ='';
        $fields = 'ur.id,ur.usage_req_id,ur.vehicle_id,ur.driver_id,vi.workshop_status,vi.reg_no,di.name,di.driver_id_no';
        
		$sql="SELECT $fields FROM usage_req_vehicle_details AS ur INNER JOIN vehicle_info AS vi ON vi.id = ur.vehicle_id INNER JOIN driver_info AS di ON di.id = ur.driver_id WHERE ur.usage_req_id = $id ";
      
        if($stmt=$this->conn->prepare($sql)){
			$stmt->execute();
			$task=$stmt->get_result();
			$stmt->close();
			return $task;
		}else{
			$error=$this->conn->errno.' '.$this->conn->error;
			return null;
		}	

    }
     /** 
     * @requires Nothing
     * @return VisitLocation All result
     * @author Risad <risadmit05@email.com>
     */
	public function getVisitLocationBYID($id){

    
        $sql ='';

        
        $fields = 'vl.trip_start_from,vl.destination,vl.pickup_time,vl.contact_no,emp.full_name,emp.emp_id_no';

		$sql="SELECT $fields FROM visit_location AS vl INNER JOIN employees AS emp ON vl.passenger_id = emp.id WHERE vl.requisition_id = $id";
      
        if($stmt=$this->conn->prepare($sql)){
			$stmt->execute();
			$task=$stmt->get_result();
			$stmt->close();
			return $task;
		}else{
			$error=$this->conn->errno.' '.$this->conn->error;
			return null;
		}	

    }
     /** 
     * @requires Nothing
     * @return VisitLocation All result
     * @author Risad <risadmit05@email.com>
     */
	public function getCustomerInfoBYID($id){

    
        $sql ='';

        
        $fields = 'cms.customer_name,urcd.address';

		$sql="SELECT $fields FROM usage_req_customer_details AS urcd INNER JOIN customers AS cms ON urcd.customer_id = cms.id WHERE urcd.usage_req_id = $id";
      
        if($stmt=$this->conn->prepare($sql)){
			$stmt->execute();
			$task=$stmt->get_result();
			$stmt->close();
			return $task;
		}else{
			$error=$this->conn->errno.' '.$this->conn->error;
			return null;
		}	

    }
	public function getFuelManage($pageno,$sqlSearch,$user_id){

        $user = $this->getUserInfo($user_id);
        

        $no_of_records_per_page = 15;
        $offset = ($pageno-1) * $no_of_records_per_page;
        
		if($user['user_type']==0){
            $sql = "SELECT issue_fuel.fuel_approved_status,
            issue_fuel.id,issue_fuel.entry_no,issue_fuel.refill_amount,
            issue_fuel.refill_ltr,issue_fuel.refill_date,
            vehicle_info.reg_no FROM issue_fuel issue_fuel INNER JOIN vehicle_info 
            ON issue_fuel.vehicle_info_id=vehicle_info.id WHERE LOWER(vehicle_info.reg_no) LIKE LOWER('%$sqlSearch%') AND NOT issue_fuel.fuel_approved_status ='2'  ORDER BY issue_fuel.id DESC LIMIT $offset, $no_of_records_per_page";
   
        }else{
            $sql = "SELECT issue_fuel.fuel_approved_status,
            issue_fuel.id,issue_fuel.entry_no,issue_fuel.refill_amount,
            issue_fuel.refill_ltr,issue_fuel.refill_date,
            vehicle_info.reg_no FROM issue_fuel issue_fuel INNER JOIN vehicle_info 
            ON issue_fuel.vehicle_info_id=vehicle_info.id WHERE LOWER(vehicle_info.reg_no) LIKE LOWER('%$sqlSearch%') AND NOT issue_fuel.fuel_approved_status ='2' AND issue_fuel.created_by='$user_id' ORDER BY issue_fuel.id DESC LIMIT $offset, $no_of_records_per_page";
   
        }
        
      
          if($stmt=$this->conn->prepare($sql)){
            $stmt->execute();
            $tasks = $stmt->get_result();
            $stmt->close();
            return $tasks;
          }else {
              $error =$this->conn->errno . ' ' . $this->conn->error;
              return null;
          }
    }
    public function getFuelManageData($pageno,$sqlSearch,$user_id){


        $no_of_records_per_page = 15;
        $offset = ($pageno-1) * $no_of_records_per_page;
		$sql='';
		$user = $this->getUserInfo($user_id);

        if($user['user_type']==0||$user['user_type']==3){
            $sql = "SELECT issue_fuel.fuel_approved_status,
            issue_fuel.id,issue_fuel.entry_no,issue_fuel.refill_amount,
            issue_fuel.refill_ltr,issue_fuel.refill_date,
            vehicle_info.reg_no FROM issue_fuel issue_fuel INNER JOIN vehicle_info 
            ON issue_fuel.vehicle_info_id=vehicle_info.id WHERE NOT issue_fuel.fuel_approved_status ='2' ORDER BY issue_fuel.id DESC LIMIT $offset, $no_of_records_per_page";
   
        }else{
            $sql = "SELECT issue_fuel.fuel_approved_status,
            issue_fuel.id,issue_fuel.entry_no,issue_fuel.refill_amount,
            issue_fuel.refill_ltr,issue_fuel.refill_date,
            vehicle_info.reg_no FROM issue_fuel issue_fuel INNER JOIN vehicle_info 
            ON issue_fuel.vehicle_info_id=vehicle_info.id WHERE NOT issue_fuel.fuel_approved_status ='2' AND issue_fuel.created_by='$user_id' ORDER BY issue_fuel.id DESC LIMIT $offset, $no_of_records_per_page";
   
        }

        
      
          if($stmt=$this->conn->prepare($sql)){
            $stmt->execute();
            $tasks = $stmt->get_result();
            $stmt->close();
            return $tasks;
          }else {
              $error =$this->conn->errno . ' ' . $this->conn->error;
              return null;
          }
    }
	
    public function getFuelManageDetails($id){

           $sql= "SELECT issue_fuel.fuel_approved_status,
            issue_fuel.id,issue_fuel.entry_no,issue_fuel.refill_date,issue_fuel.total_run,issue_fuel.current_millage,issue_fuel.refill_millage,issue_fuel.refill_ltr,issue_fuel.unit_price,issue_fuel.fuel_type_id,issue_fuel.refill_station_id,issue_fuel.refill_amount,issue_fuel.route_location_id,issue_fuel.bill_type,issue_fuel.bill_no,
            issue_fuel.refill_ltr,issue_fuel.refill_date,issue_fuel.date,driver_info.name,driver_info.driver_id_no,driver_info.id as driver_id,
            vehicle_info.reg_no,issue_fuel.vehicle_info_id FROM issue_fuel LEFT JOIN driver_info 
            ON issue_fuel.driver_id=driver_info.id  LEFT JOIN vehicle_info 
            ON issue_fuel.vehicle_info_id=vehicle_info.id  WHERE issue_fuel.id ='$id'";
      
          if($stmt=$this->conn->prepare($sql)){
            $stmt->execute();
            $tasks = $stmt->get_result();
            $stmt->close();
            return $tasks;
          }else {
              $error =$this->conn->errno . ' ' . $this->conn->error;
              return null;
          }
    }
	
	public function checkLastOdoMeter($odometer,$vehicleId){
      // this function will only work if there is general service(3) data in notification table. First GS Data will come from bill.
 
     $sql="SELECT * FROM vehicle_notification_log WHERE vehicle_id=$vehicleId AND is_active=1 AND notification_type=3";
	 $notificationData= self::getCustomModel($sql,1);
	 
           
		   if(!empty($notificationData))
            {
                $previous_odo_meter = $notificationData["odo_meter"];
                $next_renewal_date = date('Y-m-d', strtotime(date('Y-m-d'). "+15 days"));
                $difference = $odometer - $previous_odo_meter ;
                if($difference >= 3000)
                {
                    $id= $notificationData["odo_meter"];
					$sql ="UPDATE vehicle_notification_log SET is_active = '0' WHERE id ='$id'";				
	                $res=$this->updateCustomModel($sql);
                    
                    $paramLogModel =array();
                    $paramLogModel['vehicle_id'] = $vehicleId;
                    $paramLogModel['odo_meter']= $odometer;
                    $paramLogModel['is_active'] = 1;
                    $paramLogModel['vehicle_expire_date'] = $next_renewal_date;
                    $paramLogModel['notification_type'] = 3;
                    $logModelData= self::saveCustomModel($paramLogModel,'vehicle_notification_log');						
                  
                }             
            }  
		
	}


   public function getDeleteAssignedDriver($id) {

            $result = $this->getAssignedDrivers($id);
            if ($result) {
                while ($data = $result->fetch_assoc()) {
                    $tmp = array();
                    
                    $tmp["index"] = $id=$data["id"] == "" || null ? ''  : utf8_encode($data["id"]);
                    
                    //Send Push Notification
                    $assign = $this-> getAssignDriverInfo($id)->fetch_assoc();
                    
                    $driver = $this-> getDriverInfo($assign['driver_id'])->fetch_assoc();
                        
                    $vehicle = $this-> getVehicleInfo($assign['vehicle_id'])->fetch_assoc();
                    $details = $this-> getRequisitionActionDetails($id)->fetch_assoc();
                    
                    if(!empty($assign)&&!empty($driver)&&!empty($vehicle)&&!empty($details)){
                        
                        $driverdata= $driver['name'].'-'.$driver['driver_id_no'];
                        $vehicledata= $vehicle['reg_no'];
                        //Send 
                        $body= 'Dear '.$driverdata.'you have removed at'.$details['trip_start_time']. ' a trip & Vehicle '.$vehicledata;
                        $this->getSendNotification($id,'user',' You have removed this trip ',$body,$driver['user_id'],'driver','');  
                            
                    }

        
                        $date = date('Y-m-d H:i:s');
                        $query = "DELETE FROM usage_req_vehicle_details WHERE id = $id";
                        $res =$this->deleteItem($query);


                }
            }

       
      }
       /**@author risad <risad@email.com>*/
   public function generateUniqueKey($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $key = '';
      
        for ($i = 0; $i < $length; $i++) {
          $key .= $characters[rand(0, $charactersLength - 1)];
        }
      
        return $key;
      }

	
     /** 
     * @requires Nothing
     * @return GET ENGAGED VEHICLE All result
     * @author Risad <risadmit05@email.com>
     */
	public function getEngagedPoolVehicleCount(){

        $currentDateTime = new DateTime();
        $currentDate = date('y-m-d');
        $currentTime = date('H:i:s');
        
    
        $sql ='';
       
       
        // $sql = "SELECT * FROM route_planing
        // WHERE ('$currentDate' BETWEEN date_from AND date_to) AND ('$currentTime' BETWEEN morning_shift_out_time AND evening_ shift_in_time)";
        
        // $sql = "SELECT count(*) as pool_engaged FROM route_planing WHERE morning_shift_out_time <= '$currentTime' AND evening_shift_in_time >= '$currentTime' AND date_from >= '$currentDate' AND date_to <= '$currentDate'";
        
        $sql = "SELECT vehicle_id FROM route_planing WHERE morning_shift_out_time <= '$currentTime' AND evening_shift_in_time >= '$currentTime' AND date_from >= '$currentDate' AND date_to <= '$currentDate' and vehicle_id > 0 group by vehicle_id";

        if($stmt=$this->conn->prepare($sql)){
			$stmt->execute();
			$task=$stmt->get_result();
			$stmt->close();
			return $task;
		}else{
			$error=$this->conn->errno.' '.$this->conn->error;
			return null;
		}	
    }
     /** 
     * @requires Nothing
     * @return GET ENGAGED VEHICLE All result
     * @author Risad <risadmit05@email.com>
     */
	public function getEngagedRequisitionVehicleCount(){

        
        $sql = "SELECT d.vehicle_id as vehicle_id FROM `usage_requisition` t inner join usage_req_vehicle_details d on d.usage_req_id = t.id where t.status != 'END' and t.status != '' group by d.vehicle_id";

        if($stmt=$this->conn->prepare($sql)){
			$stmt->execute();
			$task=$stmt->get_result();
			$stmt->close();
			return $task;
		}else{
			$error=$this->conn->errno.' '.$this->conn->error;
			return null;
		}	
    }
     /** 
     * @requires Nothing
     * @return GET ENGAGED VEHICLE All result
     * @author Risad <risadmit05@email.com>
     */
	public function getEngagedPoolVehicleRe($tripStartTime){
        $tripStartDate = date("Y-m-d", strtotime($tripStartTime));
        $tripStartTime = date("H:i:s", strtotime($tripStartTime));
        $sql = "SELECT * FROM route_planing WHERE morning_shift_out_time <= '$tripStartTime' AND evening_shift_in_time >= '$tripStartTime' AND date(date_from) >= '$tripStartDate' AND date(date_to) <= '$tripStartDate'";

        if($stmt=$this->conn->prepare($sql)){
			$stmt->execute();
			$task=$stmt->get_result();
			$stmt->close();
			return $task;
		}else{
			$error=$this->conn->errno.' '.$this->conn->error;
			return null;
		}	
    }
     /** 
     * @requires Nothing
     * @return GET ENGAGED Driver Assign Vehicle ID
     * @author Risad <risadmit05@email.com>
     */
	public function getEngagedPoolDriverVehicleId($driver_id){

        $currentDateTime = new DateTime();
        $currentDate = date('y-m-d');
        $currentTime = date('H:i:s');
        
    
        $sql ='';
       
       
        // $sql = "SELECT * FROM route_planing
        // WHERE ('$currentDate' BETWEEN date_from AND date_to) AND ('$currentTime' BETWEEN morning_shift_out_time AND evening_ shift_in_time)";
        
        $sql = "SELECT vehicle_id FROM route_planing WHERE morning_shift_out_time <= '$currentTime' AND evening_shift_in_time >= '$currentTime' AND date_from >= '$currentDate' AND date_to <= '$currentDate' AND driver_id='$driver_id'";
        
        // $sql = "SELECT * FROM usage_requisition WHERE ('$statTime' BETWEEN trip_start_time AND trip_return_time) OR ('$statTime' BETWEEN extended_trip_start_time AND extended_trip_return_time)";

        if($stmt=$this->conn->prepare($sql)){
			$stmt->execute();
			$task=$stmt->get_result();
			$stmt->close();
			return $task;
		}else{
			$error=$this->conn->errno.' '.$this->conn->error;
			return null;
		}	
    }
        /** 
     * @requires Nothing
     * @return GET ENGAGED VEHICLE All result
     * @author Risad <risadmit05@email.com>
     */
	public function getEngagedDriverVehicleID($driver_id){
        $statTime = date('y-m-d H:i:s');
        // $statTime = '2023-05-31 16:00:21';
        
        $sql ='';
       
        $sql = "SELECT ud.vehicle_id FROM usage_requisition AS ur
        JOIN usage_req_vehicle_details AS ud ON ur.id = ud.usage_req_id
        WHERE ('$statTime' BETWEEN ur.trip_start_time AND ur.trip_return_time) OR ('$statTime' BETWEEN ur.extended_trip_start_time AND ur.extended_trip_return_time)";

        // $sql = "SELECT * FROM usage_requisition WHERE ('$statTime' BETWEEN trip_start_time AND trip_return_time) OR ('$statTime' BETWEEN extended_trip_start_time AND extended_trip_return_time)";

        if($stmt=$this->conn->prepare($sql)){
			$stmt->execute();
			$task=$stmt->get_result();
			$stmt->close();
			return $task;
		}else{
			$error=$this->conn->errno.' '.$this->conn->error;
			return null;
		}	

    }
      /** 
     * @requires Nothing
     * @return GET ENGAGED VEHICLE All result
     * @author Risad <risadmit05@email.com>
     */
	public function getEngagedPoolVehicleID(){

        $currentDate = date('y-m-d');
        $currentTime = date('H:i:s');
        
        $sql = "SELECT vehicle_id FROM route_planing WHERE morning_shift_out_time <= '$currentTime' AND evening_shift_in_time >= '$currentTime' AND date_from >= '$currentDate' AND date_to <= '$currentDate'";
        
        // $sql = "SELECT * FROM usage_requisition WHERE ('$statTime' BETWEEN trip_start_time AND trip_return_time) OR ('$statTime' BETWEEN extended_trip_start_time AND extended_trip_return_time)";

        if($stmt=$this->conn->prepare($sql)){
			$stmt->execute();
			$task=$stmt->get_result();
			$stmt->close();
			return $task;
		}else{
			$error=$this->conn->errno.' '.$this->conn->error;
			return null;
		}	
    }
     /** 
     * @requires Nothing
     * @return GET Available Pool Vehicle
     * @author Risad <risadmit05@email.com>
     */
	public function getAvaiablePoolVehicle($vehicles){

        $sql ='';

    // Prepare the excluded values as a comma-separated string
    $valuesString1 = implode(',', $vehicles);

    
        $sql = "SELECT id,reg_no FROM vehicle_info
        WHERE id IN ($valuesString1) AND (flag <>1 OR flag is null) AND isActive='1'";
       // $sql = "SELECT id,reg_no,current_unit_id FROM vehicle_info";


        if($stmt=$this->conn->prepare($sql)){
			$stmt->execute();
			$task=$stmt->get_result();
			$stmt->close();
			return $task;
		}else{
			$error=$this->conn->errno.' '.$this->conn->error;
			return null;
		}	

    }
     /** 
     * @requires Nothing
     * @return GET Available Pool Vehicle
     * @author Risad <risadmit05@email.com>
     */
	public function getINVehicle($vehicles,$type=1,$user_id){

        $sql ='';

    // Prepare the excluded values as a comma-separated string
    if($type==1){
		  $vehicles = [];
        $user=$this->getUserInfo($user_id);
        $vehicleInfo=$this->getAssignedVehicleInfo($user['driver_id']);
        if($vehicleInfo!=null){
            array_push($vehicles,$vehicleInfo['id']);
        }

        $valuesString1 = implode(',', $vehicles);

        $sql = "SELECT id,reg_no,garage_in_out FROM vehicle_info
        WHERE id IN ($valuesString1) AND isActive='1' AND (garage_in_out ='1' OR garage_in_out IS null) ";

    }else{

        $sql = "SELECT id,reg_no,garage_in_out FROM vehicle_info WHERE isActive='1' AND garage_in_out ='1' ";
    }

    if($stmt=$this->conn->prepare($sql)){
        $stmt->execute();
        $task=$stmt->get_result();
        $stmt->close();
        return $task;
    }else{
        $error=$this->conn->errno.' '.$this->conn->error;
        return null;
    }	

    }
     /** 
     * @requires Nothing
     * @return GET Available Pool Vehicle
     * @author Risad <risadmit05@email.com>
     */
	public function getOUTVehicle($user_id,$type=1){

        if($type==1){

            $sql = "SELECT vih.id,vi.reg_no,vi.garage_in_out FROM vehicle_inout_history AS vih LEFT JOIN vehicle_info AS vi ON  vih.vehicle_id=vi.id
            WHERE vih.driver_id='$user_id' AND vi.garage_in_out ='2' AND vih.in_out_status='0' ";

        }else if($type==2){

            $sql = "SELECT vih.id,vi.reg_no,vi.garage_in_out FROM vehicle_inout_history AS vih LEFT JOIN vehicle_info AS vi ON  vih.vehicle_id=vi.id
            WHERE vih.created_by='$user_id' AND vi.garage_in_out ='2' AND vih.in_out_status='0' ";

        }
        else{
            $sql = "SELECT vih.id,vi.reg_no,vi.garage_in_out FROM vehicle_inout_history AS vih LEFT JOIN vehicle_info AS vi ON  vih.vehicle_id=vi.id
            WHERE vi.garage_in_out ='2' AND vih.in_out_status='0'";
        }
      
       // $sql = "SELECT id,reg_no,current_unit_id FROM vehicle_info";


        if($stmt=$this->conn->prepare($sql)){
			$stmt->execute();
			$task=$stmt->get_result();
			$stmt->close();
			return $task;
		}else{
			$error=$this->conn->errno.' '.$this->conn->error;
			return null;
		}	

    }
    /** 
     * @requires Nothing
     * @return GET Available Pool Vehicle
     * @author Risad <risadmit05@email.com>
     */
	public function getvehicle_Id($id){

        $sql = "SELECT vehicle_id FROM vehicle_inout_history WHERE id='$id'";
        if($stmt=$this->conn->prepare($sql)){
			$stmt->execute();
			$task=$stmt->get_result();
			$stmt->close();
			return $task;
		}else{
			$error=$this->conn->errno.' '.$this->conn->error;
			return null;
		}	

    }

     /** 
     * @requires Nothing
     * @return GET ENGAGED VEHICLE All result
     * @author Risad <risadmit05@email.com>
     */
	public function getWorkshopEngagedVehicle(){

        $sql ='';
        $sql = "SELECT count(*) as workshop_engaged FROM complain_sheet WHERE flag =1 Group by vehicle_reg_no ";

        if($stmt=$this->conn->prepare($sql)){
			$stmt->execute();
			$task=$stmt->get_result();
			$stmt->close();
			return $task;
		}else{
			$error=$this->conn->errno.' '.$this->conn->error;
			return null;
		}	
    }
     /** 
     * @requires Nothing
     * @return GET WorkshopEngage Vehicle
     * @author Risad <risadmit05@email.com>
     */
	public function getWorkshopEngagedVehicleList(){

        $sql ='';
        $sql = "SELECT id,reg_no FROM vehicle_info WHERE workshop_status =1 AND isActive=1";

        if($stmt=$this->conn->prepare($sql)){
			$stmt->execute();
			$task=$stmt->get_result();
			$stmt->close();
			return $task;
		}else{
			$error=$this->conn->errno.' '.$this->conn->error;
			return null;
		}	
    }
    /** 
     * @requires Nothing
     * @return GET Available Vehicle Count
     * @author Risad <risadmit05@email.com>
     */
	public function getAvaiableVehicleCount($vehicles){

        $sql ='';

    // Prepare the excluded values as a comma-separated string
    $valuesString1 = implode(',', $vehicles);

    if (!empty($vehicles)){
        // list is empty.
        $sql = "SELECT count(*) as free_vehicle FROM vehicle_info
        WHERE id NOT IN ($valuesString1)  AND isActive='1'";
       // $sql = "SELECT id,reg_no,current_unit_id FROM vehicle_info";

   }else{
        $sql = "SELECT count(*) as free_vehicle FROM vehicle_info where isActive=1";
   }

        if($stmt=$this->conn->prepare($sql)){
			$stmt->execute();
			$task=$stmt->get_result();
			$stmt->close();
			return $task;
		}else{
			$error=$this->conn->errno.' '.$this->conn->error;
			return null;
		}	

    }

  /** 
     * @requires Nothing
     * @return Requistions All result
     * @author Risad <risadmit05@email.com>
     */
public function getIssueFuelHistory($date,$id){
    $sql = "SELECT issue_fuel.refill_date AS refill_date, issue_fuel.refill_millage AS refill_odo FROM vehicle_info INNER JOIN issue_fuel ON issue_fuel.vehicle_info_id = vehicle_info.id WHERE vehicle_info.id = '$id' AND issue_fuel.refill_date <= '$date' ORDER BY issue_fuel.refill_date DESC LIMIT 1";

    $stmt = $this->conn->prepare($sql);
    if ($stmt->execute()) {
        $task = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $task;
    } else {
        return NULL;
    }
}
  /** 
     * @requires Nothing
     * @return Requistions All result
     * @author Risad <risadmit05@email.com>
     */
	public function getNotifications($pageno,$user_id){

        $no_of_records_per_page = 15;
        $offset = ($pageno - 1) * 15;
		$sql='';
        $user = $this->getUserInfo($user_id);
        if($user){
            $userType = $user['user_type'];
            $department_id = $user['department_id'];
            if($userType==0){
                $sql= "SELECT *  FROM `push_notifications` WHERE `topic` = 'admin' ORDER BY `id` DESC LIMIT $no_of_records_per_page offset $offset";

            }else if($userType == 3 ){
                $sql= "SELECT *  FROM `push_notifications` WHERE `topic` = '$department_id' ORDER BY `id` DESC LIMIT $no_of_records_per_page offset $offset";
            }else{
                $sql= "SELECT *  FROM `push_notifications` WHERE `send_by` = '$user_id' ORDER BY `id` DESC LIMIT $no_of_records_per_page offset $offset";
            }

        }else{
            $sql ="";
        }
        
        if($stmt=$this->conn->prepare($sql)){
			$stmt->execute();
			$task=$stmt->get_result();
			$stmt->close();
			return $task;
		}else{
			$error=$this->conn->errno.' '.$this->conn->error;
			return null;
		}	

    }	


}
