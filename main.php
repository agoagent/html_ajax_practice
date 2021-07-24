<?php 
if(isset ($_GET['inf_action'])){
    $array_GET = ['phone','tester','use_phone','search_option','insert_inf','update_inf_to_cancel'];
    $servername = "localhost";
    $username = "root";
    $password = "root";
    $dbname = "php_website";
    // 创建连接
    $conn = mysqli_connect($servername, $username, $password, $dbname);
    // Check connection
    if (!$conn) {
        die("连接失败: " . mysqli_connect_error());
    }
    
    $sql = "SELECT id, firstname, lastname FROM MyGuests";
    $result = mysqli_query($conn, $sql);

    $inf_result = $_GET['inf_action'];

    
    if(in_array($inf_result, $array_GET) == false){
        print_r("useless information");
        exit();
    }

    if($inf_result == 'phone'){
        $sql = "SELECT id, name FROM phone where state='normal'";
        $result = mysqli_query($conn, $sql);

        if ($result->num_rows > 0) {
            // 输出数据
            while($row = $result->fetch_assoc()) {
              $phone_id = $row['id'];
              $phone =  $row['name'];
              $arr_result[] = array($phone_id,$phone);
            }
            // var_dump($arr_phone);
            // print_r($arr_phone);
            // return($arr_phone);
        } else {
            $arr_result[] = ["phone 0 结果"];
        }
    }

    if($inf_result == 'tester'){
        $sql = "SELECT t.id, t.name, up.phone_id, up.use_state FROM tester as t LEFT JOIN use_phone as up on t.id = up.user_id where work_status = '1' ";
        $result = mysqli_query($conn, $sql);

        if ($result->num_rows > 0) {
            // 输出数据
            while($row = $result->fetch_assoc()) {
              $tester_num = $row['id'];
              $tester =  $row['name'];
              $use_phone_id = $row['phone_id']; 
              $use_state = $row['use_state'];
              $arr_result[] = array($tester_num,$tester,$use_phone_id,$use_state);
            }
            // print_r($arr_tester);
            // return($arr_tester);
        } else {
            $arr_result[] = ["tester 0 结果"];
        }
    }

    if($inf_result == 'use_phone'){
        $sql = "SELECT p.id as p_id, up.id as up_id, up.phone_id, up.user_id, up.use_state FROM phone as p LEFT JOIN use_phone as up on p.id = up.id where p.state = 'normal'";
        $result = mysqli_query($conn, $sql);

        while($row = $result->fetch_assoc()) {
            $phone_id = $row['p_id'];
            $use_list_id = $row['up_id'];
            $use_phone_id =  $row['phone_id'];
            $use_tester_id = $row['user_id'];
            $use_state = $row['use_state'];
            $arr_result[] = array('phone_id' => $phone_id,'use_list_id' => $use_list_id, 'use_phone_id' => $use_phone_id,'use_tester_id' => $use_tester_id,'use_state' => $use_state);
            }
    }

    if($inf_result == 'search_option'){
        $sql = "SELECT id, name FROM tester WHERE work_status = '1'";
        $result = mysqli_query($conn, $sql);
        while($row = $result->fetch_assoc()){
            $tester_id = $row['id'];
            $tester_name = $row['name'];
            $arr_result[] = array('tester_id' => $tester_id, 'tester_name' => $tester_name);
        }
    }

    if($inf_result == 'insert_inf'){
        $now = date('Y-m-d H:i:s');
        $phone_id = $_GET['values']['phone_num'];
        $tester_id = $_GET['values']['tester_num'];

        $sql = "SELECT use_state FROM use_phone where phone_id = '$phone_id' ";
        $result = mysqli_query($conn, $sql);

        if(mysqli_num_rows($result) == 0){
            $sql = "INSERT INTO use_phone (phone_id, user_id, use_state) VALUES('$phone_id','$tester_id','use')";
            $insert_use_phone_result = mysqli_query($conn, $sql);

            $sql = "INSERT INTO use_phone_log (phone_id, user_id, start_time) VALUES('$phone_id','$tester_id','$now')";
            $insert_use_phone_log_result = mysqli_query($conn, $sql);
            $result = true;
        }else{
            while($row = $result->fetch_assoc()) {
                $use_state = $row['use_state'];
            }
            if($use_state == 'use'){
                $result = ("the phone are used");
            }else{
                $sql = "UPDATE use_phone SET use_state = 'use' ,user_id = '$tester_id' WHERE phone_id = '$phone_id'";
                // print_r($sql);
                $insert_use_phone_result = mysqli_query($conn, $sql);
                $sql = "INSERT INTO use_phone_log (phone_id, user_id, start_time) VALUES('$phone_id','$tester_id','$now')";
                $insert_use_phone_log_result = mysqli_query($conn, $sql);
                $result = true;
            }
        }
        $arr_result = $result;
    }

    if($inf_result == 'update_inf_to_cancel'){
        $now = date('Y-m-d H:i:s');
        $phone_id = $_GET['values']['phone_num'];
        $tester_id = $_GET['values']['tester_num'];

        $sql = "SELECT use_state FROM use_phone where phone_id = '$phone_id' ";
        $result = mysqli_query($conn, $sql);

        if(mysqli_num_rows($result) == 0){
            $result = "the phone are cancel";
        }else{
            while($row = $result->fetch_assoc()) {
                $use_state = $row['use_state'];
            }

            if($use_state == 'not_use'){
                $result = "the phone are cancel";
            }else{
                $sql = "UPDATE use_phone SET use_state = 'not_use' WHERE phone_id = '$phone_id'";
                $update_not_use_phone_result = mysqli_query($conn, $sql);
                // $sql = "UPDATE use_phone_log SET stop_time = '$now' WHERE phone_id = '$phone_id' and user_id = '$tester_id'";
                $sql = "INSERT INTO use_phone_log (phone_id, user_id, stop_time) VALUES('$phone_id','$tester_id','$now')";
                $update_not_use_phone_log_result = mysqli_query($conn, $sql);
                $result = true;
            }
        }
        $arr_result = $result;
    }




        $arr = array('result'=>$arr_result);
        $arr = json_encode($arr);
        exit($arr);
}
else{
    print_r($_GET);
    print_r("no any get session");
}

?>