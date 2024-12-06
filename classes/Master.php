<?php
require_once('../config.php');
Class Master extends DBConnection {
	private $settings;
	public function __construct(){
		global $_settings;
		$this->settings = $_settings;
		parent::__construct();
	}
	public function __destruct(){
		parent::__destruct();
	}
	function capture_err(){
		if(!$this->conn->error)
			return false;
		else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
			return json_encode($resp);
			exit;
		}
	}
	function save_category(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k =>$v){
			if(!in_array($k,array('id','description'))){
				if(!empty($data)) $data .=",";
				$data .= " `{$k}`='{$v}' ";
			}
		}
		if(isset($_POST['description'])){
			if(!empty($data)) $data .=",";
				$data .= " `description`='".addslashes(htmlentities($description))."' ";
		}
		$check = $this->conn->query("SELECT * FROM `category_list` where `name` = '{$name}' and delete_flag = 0 ".(!empty($id) ? " and id != {$id} " : "")." ")->num_rows;
		if($this->capture_err())
			return $this->capture_err();
		if($check > 0){
			$resp['status'] = 'failed';
			$resp['msg'] = " Category already exist.";
			return json_encode($resp);
			exit;
		}
		if(empty($id)){
			$sql = "INSERT INTO `category_list` set {$data} ";
			$save = $this->conn->query($sql);
		}else{
			$sql = "UPDATE `category_list` set {$data} where id = '{$id}' ";
			$save = $this->conn->query($sql);
		}
		if($save){
			$resp['status'] = 'success';
			if(empty($id))
				$this->settings->set_flashdata('success'," New Category successfully saved.");
			else
				$this->settings->set_flashdata('success'," Category successfully updated.");
		}else{
			$resp['status'] = 'failed';
			$resp['err'] = $this->conn->error."[{$sql}]";
		}
		return json_encode($resp);
	}
	function delete_category(){
		extract($_POST);
		$del = $this->conn->query("UPDATE `category_list` set delete_flag = 1 where id = '{$id}'");
		if($del){
			$resp['status'] = 'success';
			$this->settings->set_flashdata('success'," Category successfully deleted.");
		}else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);

	}
	function save_facility(){
		$_POST['description'] = html_entity_decode($_POST['description']);
		if(empty($_POST['id'])){
			$prefix = date('Ym-');
			$code = sprintf("%'.05d",1);
			while(true){
				$check = $this->conn->query("SELECT * FROM `facility_list` where facility_code = '{$prefix}{$code}'")->num_rows;
				if($check > 0){
					$code = sprintf("%'.05d",ceil($code) + 1);
				}else{
					break;
				}
			}
			$_POST['facility_code'] = $prefix.$code;
		}

		extract($_POST);
		$data = "";
		foreach($_POST as $k =>$v){
			if(!in_array($k,array('id'))){
				$v = $this->conn->real_escape_string($v);
				if(!empty($data)) $data .=",";
				$data .= " `{$k}`='{$v}' ";
			}
		}
		if(isset($reg_no)){
			$check = $this->conn->query("SELECT * FROM `facility_list` where `name` = '{$name}' ".(!empty($id) ? " and id != {$id} " : "")." ")->num_rows;
			if($this->capture_err())
				return $this->capture_err();
			if($check > 0){
				$resp['status'] = 'failed';
				$resp['msg'] = " Facility already exist.";
				return json_encode($resp);
				exit;
			}
		}
		
		if(empty($id)){
			$sql = "INSERT INTO `facility_list` set {$data} ";
			$save = $this->conn->query($sql);
		}else{
			$sql = "UPDATE `facility_list` set {$data} where id = '{$id}' ";
			$save = $this->conn->query($sql);
		}
		if($save){
			$resp['status'] = 'success';
			$cid = empty($id) ? $this->conn->insert_id : $id;
			$resp['id'] = $cid ;
			if(empty($id))
				$resp['msg'] = " New facility successfully saved.";
			else
				$resp['msg'] = " Facility successfully updated.";
				if($this->settings->userdata('id')  == $cid && $this->settings->userdata('login_type') == 3){
					foreach($_POST as $k => $v){
						if(!in_array($k,['password']))
						$this->settings->set_userdata($k,$v);
					}
					$resp['msg'] = " Account successfully updated.";
				}
				if(isset($_FILES['img']) && $_FILES['img']['tmp_name'] != ''){
					if(!is_dir(base_app."uploads/facility/"))
						mkdir(base_app."uploads/facility/");
					$fname = 'uploads/facility/'.$cid.'.png';
					$dir_path =base_app. $fname;
					$upload = $_FILES['img']['tmp_name'];
					$type = mime_content_type($upload);
					$allowed = array('image/png','image/jpeg');
					if(!in_array($type,$allowed)){
						$resp['msg'].=" But Image failed to upload due to invalid file type.";
					}else{
						 
						list($width, $height) = getimagesize($upload);
						$t_image = imagecreatetruecolor($width, $height);
						imagealphablending( $t_image, false );
						imagesavealpha( $t_image, true );
						$gdImg = ($type == 'image/png')? imagecreatefrompng($upload) : imagecreatefromjpeg($upload);
						imagecopyresampled($t_image, $gdImg, 0, 0, 0, 0, $width, $height, $width, $height);
						if($gdImg){
								if(is_file($dir_path))
								unlink($dir_path);
								$uploaded_img = imagepng($t_image,$dir_path);
								imagedestroy($gdImg);
								imagedestroy($t_image);
						}else{
						$resp['msg'].=" But Image failed to upload due to unkown reason.";
						}
					}
					if(isset($uploaded_img)){
						$this->conn->query("UPDATE facility_list set `image_path` = CONCAT('{$fname}','?v=',unix_timestamp(CURRENT_TIMESTAMP)) where id = '{$cid}' ");
					}
				}
		}else{
			$resp['status'] = 'failed';
			$resp['err'] = $this->conn->error."[{$sql}]";
		}
		if(isset($resp['msg']) && $resp['status'] == 'success'){
			$this->settings->set_flashdata('success',$resp['msg']);
		}
		return json_encode($resp);
	}
	function delete_facility(){
		extract($_POST);
		$del = $this->conn->query("UPDATE `facility_list` set `delete_flag` = 1  where id = '{$id}'");
		if($del){
			$resp['status'] = 'success';
			$this->settings->set_flashdata('success'," Facility successfully deleted.");
		}else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);

	}
	function save_booking() {
        if (empty($_POST['id'])) {
            $prefix = date('Ym-');
            $code = sprintf("%'.05d", 1);
            while (true) {
                $check = $this->conn->query("SELECT * FROM `booking_list` WHERE ref_code = '{$prefix}{$code}'")->num_rows;
                if ($check > 0) {
                    $code = sprintf("%'.05d", ceil($code) + 1);
                } else {
                    break;
                }
            }
            $_POST['client_id'] = $this->settings->userdata('id');
            $_POST['ref_code'] = $prefix . $code;
        }
        extract($_POST);

        // Building data string for SQL query
        $data = "";
        foreach ($_POST as $k => $v) {
            if (!in_array($k, array('id'))) {
                if (!empty($data)) $data .= ",";
                $data .= " `{$k}`='{$v}' ";
            }
        }

        // Check for conflicts with other bookings on the same facility, date, and time
        $check_sql = "
            SELECT * FROM `booking_list`
            WHERE facility_id = '{$facility_id}'
              AND (
                (
                    '{$date_from}' BETWEEN date(date_from) AND date(date_to) 
                    OR '{$date_to}' BETWEEN date(date_from) AND date(date_to)
                ) AND (
                    '{$time_from}' BETWEEN time(time_from) AND time(time_to)
                    OR '{$time_to}' BETWEEN time(time_from) AND time(time_to)
                )
              )
              AND status = 1
        ";
        $check = $this->conn->query($check_sql)->num_rows;
        
        if ($check > 0) {
            $resp['status'] = 'failed';
            $resp['msg'] = 'Facility is not available on the selected dates and times.';
            return json_encode($resp);
            exit;
        }

        // Insert or Update booking in the database
        if (empty($id)) {
            $sql = "INSERT INTO `booking_list` SET {$data} ";
            $save = $this->conn->query($sql);
        } else {
            $sql = "UPDATE `booking_list` SET {$data} WHERE id = '{$id}' ";
            $save = $this->conn->query($sql);
        }

        // Response based on the success of the query
        if ($save) {
            $resp['status'] = 'success';
            if (empty($id))
                $this->settings->set_flashdata('success', "Facility has been booked successfully.");
            else
                $this->settings->set_flashdata('success', "Booking successfully updated.");
        } else {
            $resp['status'] = 'failed';
            $resp['err'] = $this->conn->error . "[{$sql}]";
        }
        return json_encode($resp);
    }

    // Other functions remain unchanged
    function delete_booking() {
        extract($_POST);
        $del = $this->conn->query("DELETE FROM `booking_list` WHERE id = '{$id}'");
        if ($del) {
            $resp['status'] = 'success';
            $this->settings->set_flashdata('success', "Booking successfully deleted.");
        } else {
            $resp['status'] = 'failed';
            $resp['error'] = $this->conn->error;
        }
        return json_encode($resp);
    }

    function update_booking_status() {
        extract($_POST);
        $update = $this->conn->query("UPDATE `booking_list` SET `status` = '{$status}' WHERE id = '{$id}' ");
        if ($update) {
            $resp['status'] = 'success';
            $this->settings->set_flashdata('success', "Booking status successfully updated.");
        } else {
            $resp['status'] = 'failed';
            $resp['error'] = $this->conn->error;
        }
        return json_encode($resp);
    }

	// In Master.php

	function save_rate() {
		// Sanitize the inputs
		if (empty($_POST['activity_class']) || empty($_POST['rate_per_hour'])) {
			return json_encode(['status' => 'failed', 'msg' => 'Missing required fields.']);
		}
		
		extract($_POST);
		$activity_class = $this->conn->real_escape_string($activity_class);
		$rate_per_hour = $this->conn->real_escape_string($rate_per_hour);

		// Check if the rate already exists (this is optional, depending on your use case)
		$check = $this->conn->query("SELECT * FROM `rates` WHERE `activity_class` = '{$activity_class}'")->num_rows;
		if ($check > 0) {
			return json_encode(['status' => 'failed', 'msg' => 'Rate for this activity class already exists.']);
		}

		// Prepare the SQL query to insert a new rate into the `rates` table
		$sql = "INSERT INTO `rates` (`activity_class`, `rate_per_hour`) VALUES ('{$activity_class}', '{$rate_per_hour}')";
		$save = $this->conn->query($sql);

		if ($save) {
			$resp['status'] = 'success';
			$resp['msg'] = "Rate added successfully.";
			return json_encode($resp);
		} else {
			$resp['status'] = 'failed';
			$resp['msg'] = "Failed to add rate. Error: " . $this->conn->error;
			return json_encode($resp);
		}
	}

	
}

// Instantiate the Master class and route the actions
$Master = new Master();
$action = !isset($_GET['f']) ? 'none' : strtolower($_GET['f']);
$sysset = new SystemSettings();
switch ($action) {
    case 'save_category':
        echo $Master->save_category();
        break;
    case 'delete_category':
        echo $Master->delete_category();
        break;
    case 'save_facility':
        echo $Master->save_facility();
        break;
    case 'delete_facility':
        echo $Master->delete_facility();
        break;
    case 'save_booking':
        echo $Master->save_booking();
        break;
    case 'delete_booking':
        echo $Master->delete_booking();
        break;
    case 'update_booking_status':
        echo $Master->update_booking_status();
        break;
	case 'save_rate':
		echo $Master->save_rate(); // Call the save_rate function
		break;
    default:
        // echo $sysset->index();
        break;
}
?>