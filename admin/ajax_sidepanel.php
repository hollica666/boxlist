<?php
/*
Copyright (c) 2015 Devendra Katariya (bylancer.com)
Version 5.2
*/
require_once('../includes/config.php');
require_once('../includes/classes/class.template_engine.php');
require_once('../includes/classes/class.country.php');
require_once('../includes/functions/func.admin.php');
require_once('../includes/functions/func.sqlquery.php');
require_once('../includes/functions/func.users.php');
require_once('../includes/classes/GoogleTranslate.php');
require_once('../includes/lang/lang_'.$config['lang'].'.php');

$con = db_connect($config);
admin_session_start();
checkloggedadmin($config);

if (!isset($_SESSION['admin']['id'])) {
    exit('Access Denied.');
}

// Check if SSL enabled
$protocol = isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] && $_SERVER["HTTPS"] != "off"    ? "https://" : "http://";

// Define APPURL
$site_url = $protocol
    . $_SERVER["SERVER_NAME"]
    . (dirname($_SERVER["SCRIPT_NAME"]) == DIRECTORY_SEPARATOR ? "" : "/")
    . trim(str_replace("\\", "/", dirname($_SERVER["SCRIPT_NAME"])), "/");

define("SITEURL", $site_url);
$config['site_url'] = dirname($site_url)."/";

require_once('../includes/seo-url.php');




//SidePanel Ajax Function
if(isset($_GET['action'])){
    if(!check_allow()){
        $status = "Sorry:";
        $message = "permission denied for demo.";
        echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
        die();
    }

    if ($_GET['action'] == "addAdmin") { addAdmin(); }
    if ($_GET['action'] == "editAdmin") { editAdmin(); }
    if ($_GET['action'] == "addUser") { addUser(); }
    if ($_GET['action'] == "editUser") { editUser(); }

    if ($_GET['action'] == "addCountry") { addCountry(); }
    if ($_GET['action'] == "editCountry") { editCountry(); }
    if ($_GET['action'] == "addState") { addState(); }
    if ($_GET['action'] == "editState") { editState(); }
    if ($_GET['action'] == "addDistrict") { addDistrict(); }
    if ($_GET['action'] == "editDistrict") { editDistrict(); }
    if ($_GET['action'] == "addCity") { addCity(); }
    if ($_GET['action'] == "editCity") { editCity(); }

    if ($_GET['action'] == "addCurrency") { addCurrency(); }
    if ($_GET['action'] == "editCurrency") { editCurrency(); }
    if ($_GET['action'] == "addTimezone") { addTimezone(); }
    if ($_GET['action'] == "editTimezone") { editTimezone(); }
    if ($_GET['action'] == "addLanguage") { addLanguage(); }
    if ($_GET['action'] == "editLanguage") { editLanguage(); }

    if ($_GET['action'] == "addStaticPage") { addStaticPage(); }
    if ($_GET['action'] == "editStaticPage") { editStaticPage(); }
    if ($_GET['action'] == "addFAQentry") { addFAQentry(); }
    if ($_GET['action'] == "editFAQentry") { editFAQentry(); }

    if ($_GET['action'] == "postEdit") { postEdit(); }
    if ($_GET['action'] == "transactionEdit") { transactionEdit(); }
    if ($_GET['action'] == "editAdvertise") { editAdvertise(); }
    if ($_GET['action'] == "paymentEdit") { paymentEdit(); }

    if ($_GET['action'] == "SaveSettings") { SaveSettings(); }
    if ($_GET['action'] == "saveEmailTemplate") { saveEmailTemplate(); }
    if ($_GET['action'] == "testEmailTemplate") { testEmailTemplate($config,$lang,$link); }

}

function change_config_file_settings($filePath, $newSettings,$lang)
{
    // Update $fileSettings with any new values
    $fileSettings = array_merge($lang, $newSettings);
    // Build the new file as a string
    $newFileStr = "<?php\n";
    foreach ($fileSettings as $name => $val) {
        // Using var_export() allows you to set complex values such as arrays and also
        // ensures types will be correct
        $newFileStr .= "\$lang['$name'] = " . var_export($val, true) . ";\n";
    }
    // Closing tag intentionally omitted, you can add one if you want

    // Write it back to the file
    file_put_contents($filePath, $newFileStr);

}

function addAdmin(){
    global $config,$lang,$con;

    if (isset($_POST['submit'])) {

        $valid_formats = array("jpg","jpeg","png"); // Valid image formats

        if ($_FILES['file']['name'] != "") {

            $filename = stripslashes($_FILES['file']['name']);
            $ext = getExtension($filename);
            $ext = strtolower($ext);
            //File extension check
            if (in_array($ext, $valid_formats)) {
                $uploaddir = '../storage/profile/';
                $original_filename = $_FILES['file']['name'];
                $random1 = rand(9999, 100000);
                $random2 = rand(9999, 200000);
                $random3 = $random1 . $random2;
                $extensions = explode(".", $original_filename);
                $extension = $extensions[count($extensions) - 1];
                $uniqueName = $random3 . "." . $extension;
                $uploadfile = $uploaddir . $uniqueName;

                $file_type = "file";

                if ($extension == "jpg" || $extension == "jpeg" || $extension == "gif" || $extension == "png") {
                    $file_type = "image";

                    $size = filesize($_FILES['file']['tmp_name']);

                    $image = $_FILES["file"]["name"];
                    $uploadedfile = $_FILES['file']['tmp_name'];

                    if ($image) {
                        if ($extension == "jpg" || $extension == "jpeg") {
                            $uploadedfile = $_FILES['file']['tmp_name'];
                            $src = imagecreatefromjpeg($uploadedfile);
                        } else if ($extension == "png") {
                            $uploadedfile = $_FILES['file']['tmp_name'];
                            $src = imagecreatefrompng($uploadedfile);
                        } else {
                            $src = imagecreatefromgif($uploadedfile);
                        }

                        list($width, $height) = getimagesize($uploadedfile);

                        $newwidth = 225;
                        $newheight = 225;
                        //$newheight = ($height / $width) * $newwidth;
                        $tmp = imagecreatetruecolor($newwidth, $newheight);

                        imagecopyresampled($tmp, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

                        $filename = $uploaddir . "small" . $uniqueName;

                        imagejpeg($tmp, $filename, 100);

                        imagedestroy($src);
                        imagedestroy($tmp);
                    }


                }
                //else if it's not bigger then 0, then it's available '
                //and we send 1 to the ajax request
                if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
                    //$time = date('Y-m-d H:i:s', time());
                    $password = $_POST["password"];
                    $pass_hash = password_hash($password, PASSWORD_DEFAULT, ['cost' => 13]);

                    $sql = "Insert into `" . $config['db']['pre'] . "admins` set
                username='" . validate_input($_POST['username']) . "',
                password_hash='" . $pass_hash . "',
                name='" . validate_input($_POST['name']) . "',
                email='" . validate_input($_POST['email']) . "',
                image='$uniqueName' ";

                    if (!mysqli_query($con,$sql)) {
                        $status = "error";
                        $message = "Error : " . mysqli_error($con);
                    } else{
                        $status = "success";
                        $message = $lang['SAVED_SUCCESS'];
                    }

                }
            }
            else {
                $error = "Only allowed jpg, jpeg png";
                $status = "error";
                $message = $error;
            }

        } else {
            $error = "Profile Picture Required";
            $status = "error";
            $message = $error;
        }

    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}

function editAdmin(){
    global $config,$lang,$con;

    if (isset($_POST['id'])) {
        $password = $_POST["newPassword"];

        if(isset($_FILES['file']['name']) && $_FILES['file']['name'] != "")
        {
            $valid_formats = array("jpg","jpeg","png"); // Valid image formats
            $filename = stripslashes($_FILES['file']['name']);
            $ext = getExtension($filename);
            $ext = strtolower($ext);
            //File extension check
            if (in_array($ext, $valid_formats)) {
                $uploaddir = '../storage/profile/';
                $original_filename = $_FILES['file']['name'];
                $random1 = rand(9999,100000);
                $random2 = rand(9999,200000);
                $random3 = $random1.$random2;
                $extensions = explode(".", $original_filename);
                $extension = $extensions[count($extensions) - 1];
                $uniqueName =  $random3 . "." . $extension;
                $uploadfile = $uploaddir . $uniqueName;

                $file_type = "file";

                if ($extension == "jpg" || $extension == "jpeg" || $extension == "gif" || $extension == "png") {
                    $file_type = "image";

                    $size = filesize($_FILES['file']['tmp_name']);

                    $image = $_FILES["file"]["name"];
                    $uploadedfile = $_FILES['file']['tmp_name'];

                    if ($image) {
                        if ($extension == "jpg" || $extension == "jpeg") {
                            $uploadedfile = $_FILES['file']['tmp_name'];
                            $src = imagecreatefromjpeg($uploadedfile);
                        } else if ($extension == "png") {
                            $uploadedfile = $_FILES['file']['tmp_name'];
                            $src = imagecreatefrompng($uploadedfile);
                        } else {
                            $src = imagecreatefromgif($uploadedfile);
                        }

                        list($width, $height) = getimagesize($uploadedfile);

                        $newwidth = 225;
                        $newheight = 225;
                        //$newheight = ($height / $width) * $newwidth;
                        $tmp = imagecreatetruecolor($newwidth, $newheight);

                        imagecopyresampled($tmp, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

                        $filename = $uploaddir . "small" . $uniqueName;

                        imagejpeg($tmp, $filename, 100);

                        imagedestroy($src);
                        imagedestroy($tmp);
                    }


                }
                //else if it's not bigger then 0, then it's available '
                //and we send 1 to the ajax request
                if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {

                    $sql = "select image from `".$config['db']['pre']."admins` WHERE id = '" . validate_input($_POST['id']) . "'";
                    $result = mysqli_query($con,$sql) OR error(mysqli_error($con));
                    $info = mysqli_fetch_assoc($result);
                    if($info['image'] != "default_user.png"){
                        if(file_exists($uploaddir.$info['image'])){
                            unlink($uploaddir.$info['image']);
                            unlink($uploaddir."small".$info['image']);
                        }
                    }
                    if(!empty($password)){
                        $pass_hash = password_hash($password, PASSWORD_DEFAULT, ['cost' => 13]);
                        $sql = "Update `".$config['db']['pre']."admins` set
                name='" . validate_input($_POST['name']) . "',
                password_hash ='".$pass_hash."',
                image='$uniqueName'
                WHERE id = '" . validate_input($_POST['id']) . "'";
                    }else{
                        $sql = "Update `".$config['db']['pre']."admins` set
                name='" . validate_input($_POST['name']) . "',
                image='$uniqueName'
                WHERE id = '" . validate_input($_POST['id']) . "'";
                    }

                    if (!mysqli_query($con,$sql)) {
                        $status = "error";
                        $message = "Error : " . mysqli_error($con);
                    } else{
                        $status = "success";
                        $message = $lang['SAVED_SUCCESS'];
                    }
                }
            }
            else {
                $error = "Only allowed jpg, jpeg png";
                $status = "error";
                $message = $error;
            }

        }
        else{
            if(!empty($password)){
                $pass_hash = password_hash($password, PASSWORD_DEFAULT, ['cost' => 13]);
                $sql = "Update `".$config['db']['pre']."admins` set
                name='" . validate_input($_POST['name']) . "',
                username='".$_POST["username"]."',
                password_hash ='".$pass_hash."'
                WHERE id = '" . validate_input($_POST['id']) . "'";
            }else{
                $sql = "Update `".$config['db']['pre']."admins` set
            name='" . validate_input($_POST['name']) . "',
            username='".$_POST["username"]."'
            WHERE id = '".validate_input($_POST['id'])."'";
            }


            if (!mysqli_query($con,$sql)) {
                $status = "error";
                $message = "Error : " . mysqli_error($con);
            } else{
                $status = "success";
                $message = $lang['SAVED_SUCCESS'];
            }
        }


    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}

function addUser(){
    global $config,$lang,$con;

    if (isset($_POST['submit'])) {

        $valid_formats = array("jpg","jpeg","png"); // Valid image formats

        if($_FILES['file']['name'] != "")
        {
            $valid_formats = array("jpg","jpeg","png"); // Valid image formats
            $filename = stripslashes($_FILES['file']['name']);
            $ext = getExtension($filename);
            $ext = strtolower($ext);
            //File extension check
            if (in_array($ext, $valid_formats)) {
                $uploaddir = '../storage/profile/';
                $original_filename = $_FILES['file']['name'];
                $random1 = rand(9999,100000);
                $random2 = rand(9999,200000);
                $random3 = $random1.$random2;
                $username = $_POST['username'];
                $image_name = $username.'_'.$random1.$random2.'.'.$ext;
                $image_name1 = 'small_'.$username.'_'.$random1.$random2.'.'.$ext;

                $filename = $uploaddir . $image_name;
                $filename1 = $uploaddir . $image_name1;

                $uploadedfile = $_FILES['file']['tmp_name'];

                //else if it's not bigger then 0, then it's available '
                //and we send 1 to the ajax request
                if (resizeImage(500, $filename, $uploadedfile)) {
                    resize_crop_image(200, 200, $filename1, $uploadedfile);
                    //$time = date('Y-m-d H:i:s', time());
                    $password = $_POST["password"];
                    $pass_hash = password_hash($password, PASSWORD_DEFAULT, ['cost' => 13]);

                    $sql = "insert into `".$config['db']['pre']."user` set
            username='" . validate_input($_POST['username']) . "',
            password_hash='" . $pass_hash . "',
            name = '" . validate_input($_POST['name']) . "',
            email='" . validate_input($_POST['email']) . "',
            description='" . validate_input($_POST['about']) . "',
            sex='" . validate_input($_POST['sex']) . "',
            country='" . validate_input($_POST['country']) . "',
            created_at = NOW(),
            image='$image_name' ";

                    if (!mysqli_query($con,$sql)) {
                        $status = "error";
                        $message = "Error : " . mysqli_error($con);
                    } else{
                        $status = "success";
                        $message = $lang['SAVED_SUCCESS'];
                    }
                }
            }
            else {
                $error = "Only allowed jpg, jpeg png";
                $status = "error";
                $message = $error;
            }

        } else {
            $error = "Profile Picture Required";
            $status = "error";
            $message = $error;
        }

    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}

function editUser(){
    global $config,$lang,$con;

    if (isset($_POST['id'])) {
        $password = $_POST["password"];

        if(isset($_FILES['file']['name']) && $_FILES['file']['name'] != "")
        {
            $valid_formats = array("jpg","jpeg","png"); // Valid image formats
            $filename = stripslashes($_FILES['file']['name']);
            $ext = getExtension($filename);
            $ext = strtolower($ext);
            //File extension check
            if (in_array($ext, $valid_formats)) {
                $uploaddir = '../storage/profile/';
                $original_filename = $_FILES['file']['name'];
                $random1 = rand(9999,100000);
                $random2 = rand(9999,200000);

                $image_name = $random1.$random2.'.'.$ext;
                $image_name1 = 'small_'.$random1.$random2.'.'.$ext;

                $filename = $uploaddir . $image_name;
                $filename1 = $uploaddir . $image_name1;

                $uploadedfile = $_FILES['file']['tmp_name'];

                //else if it's not bigger then 0, then it's available '
                //and we send 1 to the ajax request
                if (resizeImage(500, $filename, $uploadedfile)) {
                    resize_crop_image(200, 200, $filename1, $uploadedfile);

                    $sql = "select image from `".$config['db']['pre']."user` WHERE id = '" . validate_input($_POST['id']) . "'";
                    $result = mysqli_query($con,$sql) OR error(mysqli_error($con));
                    $info = mysqli_fetch_assoc($result);
                    if($info['image'] != "default_user.png"){
                        if(file_exists($uploaddir.$info['image'])){
                            unlink($uploaddir.$info['image']);
                            unlink($uploaddir."small_".$info['image']);
                        }
                    }

                    if(!empty($password)){
                        $pass_hash = password_hash($password, PASSWORD_DEFAULT, ['cost' => 13]);
                        $sql = "Update `".$config['db']['pre']."user` set
                            name='" . validate_input($_POST['name']) . "',
                            status='" . validate_input($_POST['status']) . "',
                            description='" . validate_input($_POST['about']) . "',
                            sex='" . validate_input($_POST['sex']) . "',
                            country='" . validate_input($_POST['country']) . "',
                            username='" . validate_input($_POST['username']) . "',
                            email='" . validate_input($_POST['email']) . "',
                            password_hash ='".$pass_hash."',
                            image='$image_name'
                            WHERE id = '" . validate_input($_POST['id']) . "'";
                    }else{
                        $sql = "Update `" . $config['db']['pre'] . "user` set
                            name='" . validate_input($_POST['name']) . "',
                            status='" . validate_input($_POST['status']) . "',
                            description='" . validate_input($_POST['about']) . "',
                            sex='" . validate_input($_POST['sex']) . "',
                            country='" . validate_input($_POST['country']) . "',
                            username='" . validate_input($_POST['username']) . "',
                            email='" . validate_input($_POST['email']) . "',
                            image='$image_name'
                            WHERE id = '".validate_input($_POST['id'])."' LIMIT 1";
                    }

                    if (!mysqli_query($con,$sql)) {
                        $status = "error";
                        $message = "Error : " . mysqli_error($con);
                    } else{
                        $status = "success";
                        $message = $lang['SAVED_SUCCESS'];
                    }
                }
            }
            else {
                $error = "Only allowed jpg, jpeg png";
                $status = "error";
                $message = $error;
            }

        }
        else{
            if(!empty($password)){
                $pass_hash = password_hash($password, PASSWORD_DEFAULT, ['cost' => 13]);
                $sql = "Update `".$config['db']['pre']."user` set
                            name='" . validate_input($_POST['name']) . "',
                            status='" . validate_input($_POST['status']) . "',
                            description='" . validate_input($_POST['about']) . "',
                            sex='" . validate_input($_POST['sex']) . "',
                            country='" . validate_input($_POST['country']) . "',
                            username='" . validate_input($_POST['username']) . "',
                            email='" . validate_input($_POST['email']) . "',
                            password_hash ='".$pass_hash."'
                            WHERE id = '" . validate_input($_POST['id']) . "'";
            }else{
                $sql = "Update `" . $config['db']['pre'] . "user` set
                            name='" . validate_input($_POST['name']) . "',
                            status='" . validate_input($_POST['status']) . "',
                            description='" . validate_input($_POST['about']) . "',
                            sex='" . validate_input($_POST['sex']) . "',
                            country='" . validate_input($_POST['country']) . "',
                            username='" . validate_input($_POST['username']) . "',
                            email='" . validate_input($_POST['email']) . "'
                            WHERE id = '".validate_input($_POST['id'])."' LIMIT 1";
            }


            if (!mysqli_query($con,$sql)) {
                $status = "error";
                $message = "Error : " . mysqli_error($con);
            } else{
                $status = "success";
                $message = $lang['SAVED_SUCCESS'];
            }
        }
    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}

function addCountry(){
    global $config,$lang,$con;

    if (isset($_POST['submit'])) {

        $sql = "INSERT INTO `".$config['db']['pre']."countries` ( `code` , `name` , `asciiname` , `currency_code`, `phone`, `languages` ) VALUES ('" . validate_input($_POST['code']) . "', '" . validate_input($_POST['name']) . "', '" . validate_input($_POST['asciiname']) . "', '" . validate_input($_POST['currency_code']) . "', '" . validate_input($_POST['phone']) . "', '" . validate_input($_POST['languages']) . "')";
        if (!mysqli_query($con,$sql)) {
            $status = "error";
            $message = "Error : " . mysqli_error($con);
        } else{
            $status = "success";
            $message = $lang['SAVED_SUCCESS'];
        }

    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}
function editCountry(){
    global $config,$lang,$con;

    if (isset($_POST['code'])) {
        $sql = "Update `".$config['db']['pre']."countries` set
            code='" . validate_input($_POST['code']) . "',
            name='" . validate_input($_POST['name']) . "',
            asciiname='" . validate_input($_POST['asciiname']) . "',
            currency_code='" . validate_input($_POST['currency_code']) . "',
            phone='" . validate_input($_POST['phone']) . "',
            languages='" . validate_input($_POST['languages']) . "'
            WHERE code = '".validate_input($_POST['code'])."'";
        if (!mysqli_query($con,$sql)) {
            $status = "error";
            $message = "Error : " . mysqli_error($con);
        } else{
            $status = "success";
            $message = $lang['SAVED_SUCCESS'];
        }

    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}

function addState(){
    global $config,$lang,$con;

    if (isset($_POST['code'])) {
        $check = mysqli_fetch_array(mysqli_query($con,"SELECT code from `".$config['db']['pre']."subadmin1` Where code like '%".$_POST['code']."%' order by code desc  limit 1"));
        $check = substr($check['code'],3);
        $code = $_POST['code'].".".($check+1);

        $sql = "INSERT INTO `".$config['db']['pre']."subadmin1` set
            code='" . validate_input($code) . "',
            name='" . $_POST['name'] . "',
            asciiname='" . $_POST['asciiname'] . "',
            active='" . $_POST['active'] . "' ";
        if (!mysqli_query($con,$sql)) {
            $status = "error";
            $message = "Error : " . mysqli_error($con);
        } else{
            $status = "success";
            $message = $lang['SAVED_SUCCESS'];
        }
    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}

function editState(){
    global $config,$lang,$con;

    if (isset($_POST['code'])) {
        $sql = "UPDATE `".$config['db']['pre']."subadmin1` set
            name='" . $_POST['name'] . "',
            asciiname='" . $_POST['asciiname'] . "',
            active='" . $_POST['active'] . "' where code = '".$_POST['code']."'";
        if (!mysqli_query($con,$sql)) {
            $status = "error";
            $message = "Error : " . mysqli_error($con);
        } else{
            $status = "success";
            $message = $lang['SAVED_SUCCESS'];
        }

    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}

function addDistrict(){
    global $config,$lang,$con;

    if (isset($_POST['code'])) {
        $sql = "SELECT code from `".$config['db']['pre']."subadmin2` Where code like '%".$_POST['code']."%' order by code desc  limit 1";
        $query = mysqli_query($con,$sql) OR error(mysqli_error($con));
        $check = mysqli_fetch_array($query);
        $code = $check['code'];
        $pieces = explode(".", $code);
        $code_count = count($pieces);
        if($code_count == 3){
            $country = $pieces[0];
            $subadmin1 = $pieces[1];
            $subadmin2 = $pieces[2]+1;
        }else{
            $code = $_POST['code'];
            $pieces = explode(".", $code);
            $code_count = count($pieces);
            $country = $pieces[0];
            $subadmin1 = $pieces[1];
            $subadmin2 = 1;
        }

        $code = $country.".".$subadmin1.".".$subadmin2;

        $sql = "INSERT INTO `".$config['db']['pre']."subadmin2` set
            code='" . validate_input($code) . "',
            name='" . validate_input($_POST['name']) . "',
            asciiname='" . validate_input($_POST['asciiname']) . "',
            active='" . validate_input($_POST['active']) . "' ";

        if (!mysqli_query($con,$sql)) {
            $status = "error";
            $message = "Error : " . mysqli_error($con);
        } else{
            $status = "success";
            $message = $lang['SAVED_SUCCESS'];
        }
    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}

function editDistrict(){
    global $config,$lang,$con;

    if (isset($_POST['code'])) {

        $sql = "UPDATE `".$config['db']['pre']."subadmin2` set
            name='" . $_POST['name'] . "',
            asciiname='" . $_POST['asciiname'] . "',
            active='" . $_POST['active'] . "' where code = '".$_POST['code']."'";
        if (!mysqli_query($con,$sql)) {
            $status = "error";
            $message = "Error : " . mysqli_error($con);
        } else{
            $status = "success";
            $message = $lang['SAVED_SUCCESS'];
        }


    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}

function addCity(){
    global $config,$lang,$con;

    if (isset($_POST['submit'])) {

        $sql = "INSERT INTO `".$config['db']['pre']."cities` set
            name='" . validate_input($_POST['name']) . "',
            asciiname='" . validate_input($_POST['asciiname']) . "',
            country_code='" . validate_input($_POST['country_code']) . "',
            subadmin1_code='" . validate_input($_POST['subadmin1_code']) . "',
            subadmin2_code='" . validate_input($_POST['subadmin2_code']) . "',
            longitude='" . validate_input($_POST['longitude']) . "',
            latitude='" . validate_input($_POST['latitude']) . "',
            population='" . validate_input($_POST['population']) . "',
            time_zone='" . validate_input($_POST['time_zone']) . "',
            active='" . validate_input($_POST['active']) . "' ";

        if (!mysqli_query($con,$sql)) {
            $status = "error";
            $message = "Error : " . mysqli_error($con);
        } else{
            $status = "success";
            $message = $lang['SAVED_SUCCESS'];
        }

    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}
function editCity(){
    global $config,$lang,$con;

    if (isset($_POST['id'])) {
        $sql = "Update `".$config['db']['pre']."cities` set
            name='" . validate_input($_POST['name']) . "',
            asciiname='" . validate_input($_POST['asciiname']) . "',
            country_code='" . validate_input($_POST['country_code']) . "',
            subadmin1_code='" . validate_input($_POST['subadmin1_code']) . "',
            subadmin2_code='" . validate_input($_POST['subadmin2_code']) . "',
            longitude='" . validate_input($_POST['longitude']) . "',
            latitude='" . validate_input($_POST['latitude']) . "',
            population='" . validate_input($_POST['population']) . "',
            time_zone='" . validate_input($_POST['time_zone']) . "',
            active='" . validate_input($_POST['active']) . "'
            WHERE id = '".validate_input($_POST['id'])."'";
        if (!mysqli_query($con,$sql)) {
            $status = "error";
            $message = "Error : " . mysqli_error($con);
        } else{
            $status = "success";
            $message = $lang['SAVED_SUCCESS'];
        }
    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}

function addCurrency()
{
    global $config,$lang,$con;

    if (isset($_POST['submit'])) {

        $in_left = isset($_POST['in_left']) ? 1 : 0;
        $sql = "INSERT into `".$config['db']['pre']."currencies` SET
        `code` = '" . validate_input($_POST['code']) . "',
        `name` = '" . validate_input($_POST['name']) . "',
        `html_entity` = '" . validate_input($_POST['html_entity']) . "',
        `font_arial` = '" . validate_input($_POST['font_arial']) . "',
        `font_code2000` = '" . validate_input($_POST['font_code2000']) . "',
        `unicode_decimal` = '" . validate_input($_POST['unicode_decimal']) . "',
        `unicode_hex` = '" . validate_input($_POST['unicode_hex']) . "',
        `in_left` = '" . $in_left . "',
        `decimal_places` = '" . validate_input($_POST['decimal_places']) . "',
        `decimal_separator` = '" . validate_input($_POST['decimal_separator']) . "',
        `thousand_separator` = '" . validate_input($_POST['thousand_separator']) . "' ";

        if (!mysqli_query($con,$sql)) {
            $status = "error";
            $message = "Error : " . mysqli_error($con);
        } else{
            $status = "success";
            $message = $lang['SAVED_SUCCESS'];
        }

    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}

function editCurrency()
{
    global $config,$lang,$con;

    if (isset($_POST['id'])) {

        $in_left = isset($_POST['in_left']) ? 1 : 0;
        $sql = "UPDATE `".$config['db']['pre']."currencies` SET
        `code` = '" . validate_input($_POST['code']) . "',
        `name` = '" . validate_input($_POST['name']) . "',
        `html_entity` = '" . validate_input($_POST['html_entity']) . "',
        `font_arial` = '" . validate_input($_POST['font_arial']) . "',
        `font_code2000` = '" . validate_input($_POST['font_code2000']) . "',
        `unicode_decimal` = '" . validate_input($_POST['unicode_decimal']) . "',
        `unicode_hex` = '" . validate_input($_POST['unicode_hex']) . "',
        `in_left` = '" . $in_left . "',
        `decimal_places` = '" . validate_input($_POST['decimal_places']) . "',
        `decimal_separator` = '" . validate_input($_POST['decimal_separator']) . "',
        `thousand_separator` = '" . validate_input($_POST['thousand_separator']) . "'
        WHERE `id` = '".$_POST['id']."' LIMIT 1 ;";

        if (!mysqli_query($con,$sql)) {
            $status = "error";
            $message = "Error : " . mysqli_error($con);
        } else{
            $status = "success";
            $message = $lang['SAVED_SUCCESS'];
        }

    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}

function addTimezone()
{
    global $config,$lang,$con;

    if (isset($_POST['submit'])) {

        $sql = "INSERT into `".$config['db']['pre']."time_zones` SET
        `country_code` = '" . validate_input($_POST['country_code']) . "',
        `time_zone_id` = '" . validate_input($_POST['time_zone_id']) . "',
        `gmt` = '" . validate_input($_POST['gmt']) . "',
        `dst` = '" . validate_input($_POST['dst']) . "',
        `raw` = '" . validate_input($_POST['raw']) . "' ";

        if (!mysqli_query($con,$sql)) {
            $status = "error";
            $message = "Error : " . mysqli_error($con);
        } else{
            $status = "success";
            $message = $lang['SAVED_SUCCESS'];
        }

    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}

function editTimezone()
{
    global $config,$lang,$con;

    if (isset($_POST['id'])) {

        $sql = "UPDATE `".$config['db']['pre']."time_zones` SET
        `country_code` = '" . validate_input($_POST['country_code']) . "',
        `time_zone_id` = '" . validate_input($_POST['time_zone_id']) . "',
        `gmt` = '" . validate_input($_POST['gmt']) . "',
        `dst` = '" . validate_input($_POST['dst']) . "',
        `raw` = '" . validate_input($_POST['raw']) . "'
        WHERE `id` = '".$_POST['id']."' LIMIT 1 ";

        if (!mysqli_query($con,$sql)) {
            $status = "error";
            $message = "Error : " . mysqli_error($con);
        } else{
            $status = "success";
            $message = $lang['SAVED_SUCCESS'];
        }

    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}

function addLanguage()
{
    global $config,$lang,$con;
    if (isset($_POST['submit'])) {

        $filePath = '../includes/lang/lang_'.strtolower($_POST['name']).'.php';
        if (!file_exists($filePath)) {
            $source = 'en';
            $target = $_POST['code'];

            $trans = new GoogleTranslate();
            $newLangArray = array();
            foreach ($lang as $key => $value)
            {
                $result = $trans->translate($source, $target, $value);
                $newLangArray[$key] = $result;
            }
            fopen($filePath, "w");
            change_config_file_settings($filePath, $newLangArray,$lang);
        } else {
            $message = "Same language file is exist. Change language name.";
            echo $json = '{"status" : "error","message" : "' . $message . '"}';
            die();
        }

        $sql = "INSERT into `".$config['db']['pre']."languages` SET
        `code` = '" . validate_input($_POST['code']) . "',
        `name` = '" . validate_input($_POST['name']) . "',
        `direction` = '" . validate_input($_POST['direction']) . "',
        `file_name` = '" . validate_input(strtolower($_POST['name'])) . "',
        `active` = '" . validate_input($_POST['active']) . "' ";

        if (!mysqli_query($con = db_connect($config),$sql)) {
            $status = "error";
            $message = "Error : " . mysqli_error($con);
        } else{
            $status = "success";
            $message = $lang['SAVED_SUCCESS'];
        }

    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}

function editLanguage()
{
    global $config,$lang,$con;

    if (isset($_POST['id'])) {

        $active = isset($_POST['active']) ? 1 : 0;
        $sql = "UPDATE `".$config['db']['pre']."languages` SET
        `code` = '" . validate_input($_POST['code']) . "',
        `name` = '" . validate_input($_POST['name']) . "',
        `direction` = '" . validate_input($_POST['direction']) . "',
        `file_name` = '" . validate_input(strtolower($_POST['name'])) . "',
        `active` = '" . $active . "'
        WHERE `id` = '".$_POST['id']."' LIMIT 1 ";

        if (!mysqli_query($con,$sql)) {
            $status = "error";
            $message = "Error : " . mysqli_error($con);
        } else{
            $status = "success";
            $message = $lang['SAVED_SUCCESS'];
        }

    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}

function addStaticPage()
{
    global $config,$lang,$con;
    $errors = array();
    $response = array();

    if (isset($_POST['submit'])) {

        if (empty($_POST['name'])) {
            $errors[]['message'] = $lang['PAGENAME_REQ'];
        }
        if (empty($_POST['title'])) {
            $errors[]['message'] = $lang['PAGETITLE_REQ'];
        }
        if (empty($_POST['content'])) {
            $errors[]['message'] = $lang['PAGECONTENT_REQ'];
        }
        if (!count($errors) > 0) {
            if (empty($_POST['slug']))
                $slug = create_slug($_POST['name']);
            else
                $slug = create_slug($_POST['slug']);
                $active = isset($_POST['active']) ? 1 : 0;
            $sql = "INSERT INTO `".$config['db']['pre']."pages` ( `translation_lang` ,`name` , `title` , `content`, `slug` , `type`, `active` ) VALUES ( 'en','" . validate_input($_POST['name']) . "', '" . validate_input($_POST['title']) . "', '" . addslashes($_POST['content']) . "', '" . validate_input($slug) . "', '" . validate_input($_POST['type']) . "', '" . validate_input($active) . "')";
            $con->query($sql) OR error(mysqli_error($con));
            $id = $con->insert_id;

            $sql = "UPDATE `".$config['db']['pre']."pages` SET
            `translation_of` = '".validate_input($id)."',
            `parent_id` = '".validate_input($id)."'
           WHERE `id` = '".validate_input($id)."' LIMIT 1 ";
            $con->query($sql) OR error(mysqli_error($con));

            $sql = "SELECT code,name FROM `".$config['db']['pre']."languages` where active = '1' and code != 'en'";
            $query = mysqli_query($con,$sql) OR error(mysqli_error($con));
            while($fetch = mysqli_fetch_array($query)){
                $sql2 = "INSERT INTO `".$config['db']['pre']."pages` SET
                `translation_lang` = '" . validate_input($fetch['code']) . "',
                `translation_of` = '" . validate_input($id) . "',
                `parent_id` = '".validate_input($id)."',
               `name` = '" . validate_input($_POST['name']) . "',
               `title` = '" . validate_input($_POST['title']) . "',
               `content` = '" . addslashes($_POST['content']) . "',
               `slug` = '" . validate_input($slug) . "',
               `type` = '" . validate_input($_POST['type']) . "',
               `active` = '" . validate_input($active) . "' ";
                $con->query($sql2) OR error(mysqli_error($con));
            }

            $status = "success";
            $message = $lang['SP_PAGE_ADDED'];

            echo $json = '{"id" : "' . $id . '","status" : "' . $status . '","message" : "' . $message . '"}';
            die();
        }else {
            $status = "error";
            $message = $lang['ERROR'];
        }
    } else {
        $status = "error";
        $message = $lang['UNKNOWN_ERROR'];
    }

    $json = '{"status" : "' . $status . '","message" : "' . $message . '","errors" : ' . json_encode($errors, JSON_UNESCAPED_SLASHES) . '}';
    echo $json;
    die();
}

function editStaticPage()
{
    global $config,$lang,$con;
    $errors = array();
    $response = array();

    if (isset($_POST['id'])) {

        if (empty($_POST['name'])) {
            $errors[]['message'] = $lang['PAGENAME_REQ'];
        }
        if (empty($_POST['title'])) {
            $errors[]['message'] = $lang['PAGETITLE_REQ'];
        }
        if (empty($_POST['content'])) {
            $errors[]['message'] = $lang['PAGECONTENT_REQ'];
        }
        if (!count($errors) > 0) {
            if (empty($_POST['slug']))
                $slug = create_slug($_POST['name']);
            else
                $slug = create_slug($_POST['slug']);
            $active = isset($_POST['active']) ? 1 : 0;

           $sql = "UPDATE `".$config['db']['pre']."pages` SET
           `name` = '" . validate_input($_POST['name']) . "',
           `title` = '" . validate_input($_POST['title']) . "',
           `content` = '" . addslashes($_POST['content']) . "',
           `slug` = '" . validate_input($slug) . "',
           `type` = '" . validate_input($_POST['type']) . "',
           `active` = '" . validate_input($active) . "'
           WHERE `id` = '".validate_input($_POST['id'])."' LIMIT 1 ";
            $con->query($sql) OR error(mysqli_error($con));
            $status = "success";
            $message = $lang['SP_PAGE_EDITED'];

            echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
            die();
        }else {
            $status = "error";
            $message = $lang['ERROR'];
        }
    } else {
        $status = "error";
        $message = $lang['UNKNOWN_ERROR'];
    }

    $json = '{"status" : "' . $status . '","message" : "' . $message . '","errors" : ' . json_encode($errors, JSON_UNESCAPED_SLASHES) . '}';
    echo $json;
    die();
}

function addFAQentry()
{
    global $config,$lang,$con;
    $errors = array();

    if (isset($_POST['submit'])) {

        if (empty($_POST['title'])) {
            $errors[]['message'] = $lang['FAQTITLE_REQ'];
        }
        if (empty($_POST['content'])) {
            $errors[]['message'] = $lang['FAQCONTENT_REQ'];
        }
        if (!count($errors) > 0) {
            $active = isset($_POST['active']) ? 1 : 0;
            $sql = "INSERT INTO `".$config['db']['pre']."faq_entries` ( `translation_lang` ,`faq_title` , `faq_content`, `active`) VALUES ('en','" . validate_input($_POST['title']) . "', '" . addslashes($_POST['content']) . "','" . validate_input($active) . "')";
            $con->query($sql);
            $id = $con->insert_id;

            $sql = "UPDATE `".$config['db']['pre']."faq_entries` SET
            `translation_of` = '".validate_input($id)."',
            `parent_id` = '".validate_input($id)."'
           WHERE `faq_id` = '".validate_input($id)."' LIMIT 1 ";
            $con->query($sql);

            $sql = "SELECT code,name FROM `".$config['db']['pre']."languages` where active = '1' and code != 'en'";
            $query = mysqli_query($con,$sql) OR error(mysqli_error($con));
            while($fetch = mysqli_fetch_array($query)){
                $sql2 = "INSERT INTO `".$config['db']['pre']."faq_entries` SET
                `translation_lang` = '" . validate_input($fetch['code']) . "',
                `translation_of` = '" . validate_input($id) . "',
                `parent_id` = '".validate_input($id)."',
               `faq_title` = '" . validate_input($_POST['title']) . "',
               `faq_content` = '" . addslashes($_POST['content']) . "',
               `active` = '" . validate_input($active) . "' ";
                $con->query($sql2) OR error(mysqli_error($con));
            }

            $status = "success";
            $message = $lang['SAVED_SUCCESS'];

            echo $json = '{"id" : "' . $id . '","status" : "' . $status . '","message" : "' . $message . '"}';
            die();
        }else {
            $status = "error";
            $message = $lang['ERROR_TRY_AGAIN'];
        }
    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    $json = '{"status" : "' . $status . '","message" : "' . $message . '","errors" : ' . json_encode($errors, JSON_UNESCAPED_SLASHES) . '}';
    echo $json;
    die();
}

function editFAQentry()
{
    global $config,$lang,$con;
    $errors = array();
    $response = array();

    if (isset($_POST['id'])) {

        if (empty($_POST['title'])) {
            $errors[]['message'] = $lang['FAQTITLE_REQ'];
        }
        if (empty($_POST['content'])) {
            $errors[]['message'] = $lang['FAQCONTENT_REQ'];
        }
        if (!count($errors) > 0) {
            $active = isset($_POST['active']) ? 1 : 0;
            $sql = "UPDATE `".$config['db']['pre']."faq_entries` SET
            `faq_title` = '" . validate_input($_POST['title']) . "',
            `faq_content` = '" . addslashes($_POST['content']) . "',
             `active` = '" . validate_input($active) . "'
             WHERE `faq_id` = '".$_POST['id']."' LIMIT 1 ";
            $con->query($sql);
            $status = "success";
            $message = $lang['SP_PAGE_EDITED'];

            echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
            die();
        }else {
            $status = "error";
            $message = $lang['ERROR'];
        }
    } else {
        $status = "error";
        $message = $lang['UNKNOWN_ERROR'];
    }

    $json = '{"status" : "' . $status . '","message" : "' . $message . '","errors" : ' . json_encode($errors, JSON_UNESCAPED_SLASHES) . '}';
    echo $json;
    die();
}

function postEdit()
{
    global $config,$lang,$con;
    $errors = array();
    $response = array();

    if (isset($_POST['id'])) {

        if (empty($_POST['category']) or empty($_POST['sub_category'])) {
            $errors[]['message'] = $lang['CAT_REQ'];
        }
        if (empty($_POST['title'])) {
            $errors[]['message'] = $lang['ADTITLE_REQ'];
        }
        if (empty($_POST['content'])) {
            $errors[]['message'] = $lang['DESC_REQ'];
        }
        if (empty($_POST['city'])) {
            $errors[]['message'] = $lang['CITY_REQ'];
        }
        if (!empty($_POST['price'])) {
            if (!is_numeric($_POST['price'])) {
                $errors[]['message'] = $lang['PRICE_MUST_NO'];
            }
        }

        if (!count($errors) > 0) {

            $urgent = isset($_POST['urgent']) ? 1 : 0;
            $featured = isset($_POST['featured']) ? 1 : 0;
            $highlight = isset($_POST['highlight']) ? 1 : 0;

            $contact_phone = isset($_POST['contact_phone']) ? 1 : 0;
            $contact_email = isset($_POST['contact_email']) ? 1 : 0;
            $contact_chat = isset($_POST['contact_chat']) ? 1 : 0;

            $description = validate_input($_POST['content'],true);
            $sql = "UPDATE `".$config['db']['pre']."product` SET
            `product_name` = '" . validate_input($_POST['title']) . "',
            `status` = '" . validate_input($_POST['status']) . "',
            `category` = '" . validate_input($_POST['category']) . "',
            `sub_category` = '" . validate_input($_POST['sub_category']) . "',
            `featured` = '" . $featured . "',
            `urgent` = '" . $urgent . "',
            `highlight` = '" . $highlight . "',
            `city` = '" . validate_input($_POST['city']) . "',
            `state` = '" . validate_input($_POST['state']) . "',
            `country` = '" . validate_input($_POST['country']) . "',
            `description` = '" . $description . "',
             contact_phone = '" . $contact_phone . "',
             contact_email = '" . $contact_email . "',
             contact_chat = '" . $contact_chat . "'
             WHERE `id` = '".$_POST['id']."' LIMIT 1";
            mysqli_query($con,$sql) OR error(mysqli_error($con));

            $status = "success";
            $message = $lang['SAVED_SUCCESS'];

            echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
            die();
        }else {
            $status = "error";
            $message = $lang['ERROR_TRY_AGAIN'];
        }
    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    $json = '{"status" : "' . $status . '","message" : "' . $message . '","errors" : ' . json_encode($errors, JSON_UNESCAPED_SLASHES) . '}';
    echo $json;
    die();
}

function transactionEdit()
{
    global $config,$lang,$con;
    $errors = array();
    $response = array();

    if (isset($_POST['id'])) {

        if (isset($_POST['status'])) {

            if($_POST['status'] == "success"){
                $transaction_id = $_POST['id'];
                transaction_success($transaction_id);
            }else{
                mysqli_query($con,"UPDATE `".$config['db']['pre']."transaction` SET
        `status` = '" . validate_input($_POST['status']) . "'
            WHERE `id` = '".$_POST['id']."' LIMIT 1 ;") OR error(mysqli_error($con));
            }
            $status = "success";
            $message = $lang['SAVED_SUCCESS'];


        }else {
            $status = "error";
            $message = $lang['ERROR_TRY_AGAIN'];
        }
    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}

function editAdvertise()
{
    global $config,$lang,$con;

    if (isset($_POST['id'])) {

        $status = isset($_POST['status']) ? 1 : 0;
        $query = "Update `".$config['db']['pre']."adsense` set
            provider_name='" . validate_input($_POST['provider_name']) . "',
            status='" . $status . "',
            large_track_code='" . $_POST['large_track_code'] . "',
            tablet_track_code='" . $_POST['tablet_track_code'] . "',
            phone_track_code='" . $_POST['phone_track_code'] . "'
            WHERE id = '".$_POST['id']."'";
        $con->query($query) OR error(mysqli_error($con));

        $status = "success";
        $message = $lang['SAVED_SUCCESS'];

    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}

function paymentEdit()
{
    global $config,$lang,$con;

    if (isset($_POST['id'])) {

        mysqli_query($con,"UPDATE `".$config['db']['pre']."payments` SET `payment_title` = '" . validate_input($_POST['title']) . "',`payment_install` = '" . validate_input($_POST['install']) . "' WHERE `payment_id` = '".$_POST['id']."' LIMIT 1 ") OR error(mysqli_error($con));

        if(isset($_POST['paypal_sandbox_mode'])){
            update_option($config,"paypal_sandbox_mode",$_POST['paypal_sandbox_mode']);
            update_option($config,"paypal_api_username",$_POST['paypal_api_username']);
            update_option($config,"paypal_api_password",$_POST['paypal_api_password']);
            update_option($config,"paypal_api_signature",$_POST['paypal_api_signature']);
        }

        if(isset($_POST['skrill_merchant_id'])){
            update_option($config,"skrill_merchant_id",$_POST['skrill_merchant_id']);
        }

        if(isset($_POST['nochex_merchant_id'])){
            update_option($config,"nochex_merchant_id",$_POST['nochex_merchant_id']);
        }

        if(isset($_POST['company_bank_info'])){
            update_option($config,"company_bank_info",$_POST['company_bank_info']);
        }

        if(isset($_POST['company_cheque_info'])){
            update_option($config,"company_cheque_info",$_POST['company_cheque_info']);
            update_option($config,"cheque_payable_to",$_POST['cheque_payable_to']);
        }

        if(isset($_POST['paystack_public_key'])){
            update_option($config,"paystack_public_key",$_POST['paystack_public_key']);
            update_option($config,"paystack_secret_key",$_POST['paystack_secret_key']);
        }

        if(isset($_POST['paystack_public_key'])){
            update_option($config,"paystack_public_key",$_POST['paystack_public_key']);
            update_option($config,"paystack_secret_key",$_POST['paystack_secret_key']);
        }

        if(isset($_POST['PAYTM_ENVIRONMENT'])){
            update_option($config,"PAYTM_ENVIRONMENT",$_POST['PAYTM_ENVIRONMENT']);
            update_option($config,"PAYTM_MERCHANT_KEY",$_POST['PAYTM_MERCHANT_KEY']);
            update_option($config,"PAYTM_MERCHANT_MID",$_POST['PAYTM_MERCHANT_MID']);
            update_option($config,"PAYTM_MERCHANT_WEBSITE",$_POST['PAYTM_MERCHANT_WEBSITE']);
        }

        if(isset($_POST['stripe_secret_key'])){
            update_option($config,"stripe_secret_key",$_POST['stripe_secret_key']);
        }

        $status = "success";
        $message = $lang['SAVED_SUCCESS'];



    } else {
        $status = "error";
        $message = $lang['ERROR_TRY_AGAIN'];
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}

function SaveSettings(){
    global $config,$lang,$con;
    $status = "";
    if (isset($_POST['logo_watermark'])) {
        $valid_formats = array("jpg","jpeg","png"); // Valid image formats
        if (isset($_FILES['banner']) && $_FILES['banner']['tmp_name'] != "") {
            $filename = stripslashes($_FILES['banner']['name']);
            $ext = getExtension($filename);
            $ext = strtolower($ext);
            //File extension check
            if (in_array($ext, $valid_formats)) {
                $uploaddir = "../storage/banner/"; //Image upload directory
                $bannername = stripslashes($_FILES['banner']['name']);
                $size = filesize($_FILES['banner']['tmp_name']);
                //Convert extension into a lower case format

                $ext = getExtension($bannername);
                $ext = strtolower($ext);
                $banner_name = "bg" . '.' . $ext;
                $newBgname = $uploaddir . $banner_name;
                //Moving file to uploads folder
                if(file_exists($newBgname)){
                    unlink($newBgname);
                }
                if (move_uploaded_file($_FILES['banner']['tmp_name'], $newBgname)) {

                    update_option($config,"home_banner",$banner_name);
                    $status = "success";
                    $message = ' Banner updated Successfully ';

                } else {
                    $status = "error";
                    $message = 'Error in uploading Banner';
                }
            }
            else {
                $status = "error";
                $message = 'Only allowed jpg, jpeg png';
            }

        }

        if (isset($_FILES['favicon']) && $_FILES['favicon']['tmp_name'] != "") {
            $filename = stripslashes($_FILES['favicon']['name']);
            $ext = getExtension($filename);
            $ext = strtolower($ext);
            //File extension check
            if (in_array($ext, $valid_formats)) {
                $uploaddir = "../storage/logo/"; //Image upload directory
                $filename = stripslashes($_FILES['favicon']['name']);
                $size = filesize($_FILES['favicon']['tmp_name']);
                //Convert extension into a lower case format

                $ext = getExtension($filename);
                $ext = strtolower($ext);
                $image_name = "favicon" . '.' . $ext;
                $newLogo = $uploaddir . $image_name;
                if(file_exists($newLogo)){
                    unlink($newLogo);
                }
                //Moving file to uploads folder
                if (move_uploaded_file($_FILES['favicon']['tmp_name'], $newLogo)) {

                    update_option($config,"site_favicon",$image_name);
                    $status = "success";
                    $message = ' Site Favicon icon updated Successfully ';
                } else {
                    $status = "error";
                    $message = 'Error in uploading Favicon';
                }
            }
            else {
                $status = "error";
                $message = 'Only allowed jpg, jpeg png';
            }

        }

        if (isset($_FILES['file']) && $_FILES['file']['tmp_name'] != "") {
            $filename = stripslashes($_FILES['file']['name']);
            $ext = getExtension($filename);
            $ext = strtolower($ext);
            //File extension check
            if (in_array($ext, $valid_formats)) {
                $uploaddir = "../storage/logo/"; //Image upload directory
                $filename = stripslashes($_FILES['file']['name']);
                $size = filesize($_FILES['file']['tmp_name']);
                //Convert extension into a lower case format

                $ext = getExtension($filename);
                $ext = strtolower($ext);
                $image_name = $config['tpl_name']."_logo" . '.' . $ext;
                $newLogo = $uploaddir . $image_name;
                if(file_exists($newLogo)){
                    unlink($newLogo);
                }
                //Moving file to uploads folder
                if (move_uploaded_file($_FILES['file']['tmp_name'], $newLogo)) {

                    update_option($config,"site_logo",$image_name);
                    $status = "success";
                    $message = ' Site Logo updated Successfully ';
                } else {
                    $status = "error";
                    $message = 'Error in uploading Logo';
                }
            }
            else {
                $status = "error";
                $message = 'Only allowed jpg, jpeg png';
            }

        }

        if (isset($_FILES['watermark']) && $_FILES['watermark']['tmp_name'] != "") {
            $filename = stripslashes($_FILES['watermark']['name']);
            $ext = getExtension($filename);
            $ext = strtolower($ext);
            //File extension check
            if (in_array($ext, $valid_formats)) {
                $uploaddir = "../storage/logo/"; //Image upload directory
                $filename = stripslashes($_FILES['watermark']['name']);
                $size = filesize($_FILES['watermark']['tmp_name']);
                //Convert extension into a lower case format

                $ext = getExtension($filename);
                $ext = strtolower($ext);
                $mark_name = "watermark" . '.' . $ext;
                $watermark = $uploaddir . $mark_name;
                if(file_exists($watermark)){
                    unlink($watermark);
                }
                //Moving file to uploads folder
                if (move_uploaded_file($_FILES['watermark']['tmp_name'], $watermark)) {
                    $status = "success";
                    $message = ' Watermark Logo updated Successfully ';
                } else {
                    $status = "error";
                    $message = 'Error in uploading Watermark';
                }
            }
            else {
                $status = "error";
                $message = 'Only allowed jpg, jpeg png';
            }

        }

        if (isset($_FILES['adminlogo']) && $_FILES['adminlogo']['tmp_name'] != "") {
            $filename = stripslashes($_FILES['adminlogo']['name']);
            $ext = getExtension($filename);
            $ext = strtolower($ext);
            //File extension check
            if (in_array($ext, $valid_formats)) {
                $uploaddir = "../storage/logo/"; //Image upload directory
                $filename = stripslashes($_FILES['adminlogo']['name']);
                $size = filesize($_FILES['adminlogo']['tmp_name']);
                //Convert extension into a lower case format

                $ext = getExtension($filename);
                $ext = strtolower($ext);
                $adminlogo_name = "adminlogo" . '.' . $ext;
                $adminlogo = $uploaddir . $adminlogo_name;
                if(file_exists($adminlogo)){
                    unlink($adminlogo);
                }
                //Moving file to uploads folder
                if (move_uploaded_file($_FILES['adminlogo']['tmp_name'], $adminlogo)) {
                    update_option($config,"site_admin_logo",$adminlogo_name);
                    $status = "success";
                    $message = ' Adminlogo Logo updated Successfully ';
                } else {
                    $status = "error";
                    $message = 'Error in uploading adminlogo';
                }
            }
            else {
                $status = "error";
                $message = 'Only allowed jpg, jpeg png';
            }

        }

        if($status == ""){
            $status = "success";
            $message = $lang['SAVED_SUCCESS'];
        }
    }

    if (isset($_POST['general_setting'])) {

        update_option($config,"site_title",$_POST['site_title']);
        update_option($config,"home_page",$_POST['home_page']);
        update_option($config,"gmap_api_key",$_POST['gmap_api_key']);
        update_option($config,"featured_fee",$_POST['featured_fee']);
        update_option($config,"urgent_fee",$_POST['urgent_fee']);
        update_option($config,"highlight_fee",$_POST['highlight_fee']);
        update_option($config,"userlangsel",$_POST['userlangsel']);
        update_option($config,"userthemesel",$_POST['userthemesel']);
        update_option($config,"color_switcher",$_POST['color_switcher']);
        update_option($config,"transfer_filter",$_POST['transfer_filter']);
        update_option($config,"temp_php",$_POST['temp_php']);
        update_option($config,"quickad_debug",$_POST['quickad_debug']);
        $status = "success";
        $message = 'General setting updated Successfully';
    }

    if (isset($_POST['international'])) {

        if(isset($_POST['currency']))
        {
            $query = "SELECT * FROM ".$config['db']['pre']."currencies WHERE `id` = '".$_POST['currency']."' LIMIT 1";
            $query_result = mysqli_query($con,$query) OR error(mysqli_error($con));
            $info = mysqli_fetch_array($query_result);
            $currency_sign = $info['html_entity'];
            $currency_code = $info['code'];
            $currency_pos = $info['in_left'];
        }
        update_option($config,"country_type",$_POST['country_type']);
        update_option($config,"specific_country",$_POST['specific_country']);
        update_option($config,"lang",$_POST['lang']);
        update_option($config,"timezone",$_POST['timezone']);
        update_option($config,"currency_sign",$currency_sign);
        update_option($config,"currency_code",$currency_code);
        update_option($config,"currency_pos",$currency_pos);
        $status = "success";
        $message = 'International setting updated Successfully';
    }

    if (isset($_POST['email_setting'])) {

        update_option($config,"admin_email",$_POST['admin_email']);
        update_option($config,"email_template",$_POST['email_template']);
        update_option($config,"email_engine",$_POST['email_engine']);
        update_option($config,"email_type",$_POST['email_type']);

        update_option($config,"smtp_host",$_POST['smtp_host']);
        update_option($config,"smtp_port",$_POST['smtp_port']);
        update_option($config,"smtp_username",$_POST['smtp_username']);
        update_option($config,"smtp_password",$_POST['smtp_password']);
        update_option($config,"smtp_secure",$_POST['smtp_secure']);
        update_option($config,"smtp_auth",$_POST['smtp_auth']);

        update_option($config,"aws_host",$_POST['aws_host']);
        update_option($config,"aws_access_key",$_POST['aws_access_key']);
        update_option($config,"aws_secret_key",$_POST['aws_secret_key']);

        update_option($config,"mandrill_user",$_POST['mandrill_user']);
        update_option($config,"mandrill_key",$_POST['mandrill_key']);

        update_option($config,"sendgrid_user",$_POST['sendgrid_user']);
        update_option($config,"sendgrid_pass",$_POST['sendgrid_pass']);



        $status = "success";
        $message = 'Email setting updated Successfully';
    }

    if (isset($_POST['theme_setting'])) {
        update_option($config,"home_map_latitude",$_POST['home_map_latitude']);
        update_option($config,"home_map_longitude",$_POST['home_map_longitude']);
        update_option($config,"home_map_zoom",$_POST['home_map_zoom']);

        update_option($config,"theme_color",$_POST['theme_color']);
        update_option($config,"map_color",$_POST['map_color']);

        update_option($config,"meta_keywords",$_POST['meta_keywords']);
        update_option($config,"meta_description",$_POST['meta_description']);

        update_option($config,"contact_address",$_POST['contact_address']);
        update_option($config,"contact_phone",$_POST['contact_phone']);
        update_option($config,"contact_email",$_POST['contact_email']);
        update_option($config,"contact_latitude",$_POST['contact_latitude']);
        update_option($config,"contact_longitude",$_POST['contact_longitude']);

        update_option($config,"footer_text",$_POST['footer_text']);
        update_option($config,"copyright_text",$_POST['copyright_text']);

        update_option($config,"facebook_link",$_POST['facebook_link']);
        update_option($config,"twitter_link",$_POST['twitter_link']);
        update_option($config,"googleplus_link",$_POST['googleplus_link']);
        update_option($config,"youtube_link",$_POST['youtube_link']);
        $status = "success";
        $message = ' Theme Setting updated Successfully';
    }

    if (isset($_POST['frontend_submission'])) {
        update_option($config,"post_desc_editor",$_POST['post_desc_editor']);
        update_option($config,"post_address_mode",$_POST['post_address_mode']);
        update_option($config,"post_tags_mode",$_POST['post_tags_mode']);
        update_option($config,"post_auto_approve",$_POST['post_auto_approve']);
        update_option($config,"post_watermark",$_POST['post_watermark']);
        update_option($config,"max_image_upload",$_POST['max_image_upload']);
        update_option($config,"post_premium_listing",$_POST['post_premium_listing']);
        $status = "success";
        $message = 'Frontend submission form setting updated Successfully';
    }

    if (isset($_POST['social_login_setting'])) {
        update_option($config,"facebook_app_id",$_POST['facebook_app_id']);
        update_option($config,"facebook_app_secret",$_POST['facebook_app_secret']);
        update_option($config,"google_app_id",$_POST['google_app_id']);
        update_option($config,"google_app_secret",$_POST['google_app_secret']);
        $status = "success";
        $message = ' Social Login setting updated Successfully';
    }

    if (isset($_POST['recaptcha_setting'])) {

        update_option($config,"recaptcha_mode",$_POST['recaptcha_mode']);
        update_option($config,"recaptcha_public_key",$_POST['recaptcha_public_key']);
        update_option($config,"recaptcha_private_key",$_POST['recaptcha_private_key']);
        $status = "success";
        $message = 'reCAPTCHA setting updated Successfully';
    }

    if (isset($_POST['valid_purchase_setting'])) {

        // Set API Key
        $code = $_POST['purchase_key'];
        $buyer_email = (isset($_POST['buyer_email']))? $_POST['buyer_email'] : "";
        $installing_version = $config['version'];

        $url = "https://bylancer.com/api/api.php?verify-purchase=" . $code . "&version=" . $installing_version . "&site_url=". $config['site_url']."&email=" . $buyer_email;
        // Open cURL channel
        $ch = curl_init();

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        //Set the user agent
        $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)';
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
        // Decode returned JSON
        $output = json_decode(curl_exec($ch), true);
        // Close Channel
        curl_close($ch);

        if (!$output['success']) {
            if(isset($config['quickad_secret_file']) && $config['quickad_secret_file'] != ""){
                $fileName = $config['quickad_secret_file'];
            }else{
                $fileName = get_random_string();
            }
            file_put_contents( $fileName . '.php', $output['data']);
            $success = true;
            update_option($config,"quickad_secret_file",$fileName);
            update_option($config,"purchase_key",$_POST['purchase_key']);
            $status = "success";
            $message = 'Purchase code verified successfully';
        } else {
            $status = "error";
            $message = $output['error'];
        }

    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}

function saveEmailTemplate(){
    global $config;

    if (isset($_POST['email_setting'])) {
        $email_template = $_POST['email_template'];
        update_option($config,"email_template",$email_template);
        if($email_template == 0){
            update_option($config,"email_message_signup_details",$_POST['email_message_editor_signup_details']);
            update_option($config,"email_message_signup_confirm",$_POST['email_message_editor_signup_confirm']);
            update_option($config,"email_message_forgot_pass",$_POST['email_message_editor_forgot_pass']);
            update_option($config,"email_message_contact",$_POST['email_message_editor_contact']);
            update_option($config,"email_message_feedback",$_POST['email_message_editor_feedback']);
            update_option($config,"email_message_report",$_POST['email_message_editor_report']);

            update_option($config,"email_message_ad_approve",$_POST['email_message_editor_ad_approve']);
            update_option($config,"email_message_re_ad_approve",$_POST['email_message_editor_re_ad_approve']);
            update_option($config,"email_message_contact_seller",$_POST['email_message_editor_contact_seller']);
        }else{
            update_option($config,"email_message_signup_details",$_POST['email_message_textarea_signup_details']);
            update_option($config,"email_message_signup_confirm",$_POST['email_message_textarea_signup_confirm']);
            update_option($config,"email_message_forgot_pass",$_POST['email_message_textarea_forgot_pass']);
            update_option($config,"email_message_contact",$_POST['email_message_textarea_contact']);
            update_option($config,"email_message_feedback",$_POST['email_message_textarea_feedback']);
            update_option($config,"email_message_report",$_POST['email_message_textarea_report']);

            update_option($config,"email_message_ad_approve",$_POST['email_message_textarea_ad_approve']);
            update_option($config,"email_message_re_ad_approve",$_POST['email_message_textarea_re_ad_approve']);
            update_option($config,"email_message_contact_seller",$_POST['email_message_textarea_contact_seller']);
        }
        update_option($config,"email_sub_signup_details",$_POST['email_sub_signup_details']);
        update_option($config,"email_sub_signup_confirm",$_POST['email_sub_signup_confirm']);
        update_option($config,"email_sub_forgot_pass",$_POST['email_sub_forgot_pass']);
        update_option($config,"email_sub_contact",$_POST['email_sub_contact']);
        update_option($config,"email_sub_feedback",$_POST['email_sub_feedback']);
        update_option($config,"email_sub_report",$_POST['email_sub_report']);

        update_option($config,"email_sub_ad_approve",$_POST['email_sub_ad_approve']);
        update_option($config,"email_sub_re_ad_approve",$_POST['email_sub_re_ad_approve']);
        update_option($config,"email_sub_contact_seller",$_POST['email_sub_contact_seller']);



        $status = "success";
        $message = 'Email setting updated Successfully';
    }else{
        $status = "Error";
        $message = 'Problem in save setting.';
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}

function testEmailTemplate($config,$lang,$link){

    if (isset($_POST['test-email-notification'])) {
        $test_to_email =  $_POST['test_to_email'];
        $test_to_name = $_POST['test_to_name'];

        if (isset($_POST['signup-details'])) {

            $page = new HtmlTemplate();
            $page->html = $config['email_sub_signup_details'];
            $page->SetParameter ('EMAIL', $test_to_email);
            $page->SetParameter ('USER_FULLNAME', $test_to_name);
            $email_subject = $page->CreatePageReturn($lang,$config,$link);

            $page = new HtmlTemplate();
            $page->html = $config['email_message_signup_details'];
            $page->SetParameter ('USERNAME', "demo");
            $page->SetParameter ('PASSWORD', "demo");
            $page->SetParameter ('USER_ID', "1");
            $page->SetParameter ('EMAIL', $test_to_email);
            $page->SetParameter ('USER_FULLNAME', $test_to_name);
            $email_body = $page->CreatePageReturn($lang,$config,$link);

            email($test_to_email,$test_to_name,$email_subject,$email_body,$config);
        }

        if (isset($_POST['create-account'])) {

            $page = new HtmlTemplate();
            $page->html = $config['email_sub_signup_confirm'];
            $page->SetParameter ('EMAIL', $test_to_email);
            $page->SetParameter ('USER_FULLNAME', $test_to_name);
            $email_subject = $page->CreatePageReturn($lang,$config,$link);

            $confirmation_link = $link['SIGNUP']."?confirm=123456&user=1";
            $page = new HtmlTemplate();
            $page->html = $config['email_message_signup_confirm'];
            $page->SetParameter ('CONFIRMATION_LINK', $confirmation_link);
            $page->SetParameter ('USERNAME', "demo");
            $page->SetParameter ('USER_ID', "1");
            $page->SetParameter ('EMAIL', $test_to_email);
            $page->SetParameter ('USER_FULLNAME', $test_to_name);
            $email_body = $page->CreatePageReturn($lang,$config,$link);

            email($test_to_email,$test_to_name,$email_subject,$email_body,$config);
        }

        if (isset($_POST['forgot-pass'])) {
            $page = new HtmlTemplate();
            $page->html = $config['email_sub_forgot_pass'];
            $page->SetParameter ('EMAIL', $test_to_email);
            $page->SetParameter ('USER_FULLNAME', $test_to_name);
            $email_subject = $page->CreatePageReturn($lang,$config,$link);

            $forget_password_link = $config['site_url']."login?forgot=sd1213f1x1&r=21d1d2d12&e=12&t=1213231";
            $page = new HtmlTemplate();
            $page->html = $config['email_message_forgot_pass'];
            $page->SetParameter ('FORGET_PASSWORD_LINK', $forget_password_link);
            $page->SetParameter ('EMAIL', $test_to_email);
            $page->SetParameter ('USER_FULLNAME', $test_to_name);
            $email_body = $page->CreatePageReturn($lang,$config,$link);

            email($test_to_email,$test_to_name,$email_subject,$email_body,$config);
        }

        if (isset($_POST['contact_us'])) {
            $page = new HtmlTemplate();
            $page->html = $config['email_sub_contact'];
            $page->SetParameter ('CONTACT_SUBJECT', "Contact Email");
            $page->SetParameter ('EMAIL', $test_to_email);
            $page->SetParameter ('NAME', $test_to_name);
            $email_subject = $page->CreatePageReturn($lang,$config,$link);

            $page = new HtmlTemplate();
            $page->html = $config['email_message_contact'];
            $page->SetParameter ('EMAIL', $test_to_email);
            $page->SetParameter ('NAME', $test_to_name);
            $page->SetParameter ('CONTACT_SUBJECT', "Contact Email");
            $page->SetParameter ('MESSAGE', "Test Message");
            $email_body = $page->CreatePageReturn($lang,$config,$link);

            email($test_to_email,$test_to_name,$email_subject,$email_body,$config);
        }

        if (isset($_POST['feedback'])) {
            $page = new HtmlTemplate();
            $page->html = $config['email_sub_feedback'];
            $page->SetParameter ('FEEDBACK_SUBJECT', "Feedback Email");
            $page->SetParameter ('EMAIL', $test_to_email);
            $page->SetParameter ('NAME', $test_to_name);
            $email_subject = $page->CreatePageReturn($lang,$config,$link);

            $page = new HtmlTemplate();
            $page->html = $config['email_message_feedback'];
            $page->SetParameter ('EMAIL', $test_to_email);
            $page->SetParameter ('NAME', $test_to_name);
            $page->SetParameter ('PHONE', "1234567890");
            $page->SetParameter ('FEEDBACK_SUBJECT', "Feedback Email");
            $page->SetParameter ('MESSAGE', "Test Message");
            $email_body = $page->CreatePageReturn($lang,$config,$link);

            email($test_to_email,$test_to_name,$email_subject,$email_body,$config);
        }

        if (isset($_POST['report'])) {
            $page = new HtmlTemplate();
            $page->html = $config['email_sub_report'];
            $page->SetParameter ('EMAIL', $test_to_email);
            $page->SetParameter ('NAME', $test_to_name);
            $page->SetParameter ('USERNAME', $test_to_name);
            $page->SetParameter ('VIOLATION', $lang['ADVWEBSITE']);
            $email_subject = $page->CreatePageReturn($lang,$config,$link);

            $page = new HtmlTemplate();
            $page->html = $config['email_message_report'];
            $page->SetParameter ('EMAIL', $test_to_email);
            $page->SetParameter ('NAME', $test_to_name);
            $page->SetParameter ('USERNAME', $test_to_name);
            $page->SetParameter ('USERNAME2', "Violator Username");
            $page->SetParameter ('VIOLATION', $lang['ADVWEBSITE']);
            $page->SetParameter ('URL', $config['site_url']."ad/1");
            $page->SetParameter ('DETAILS', "Violator Message details here");
            $email_body = $page->CreatePageReturn($lang,$config,$link);

            email($test_to_email,$test_to_name,$email_subject,$email_body,$config);
        }

        if (isset($_POST['ad_approve'])) {
            $item_title = "Advertise Title";
            $ad_link = $config['site_url']."ad/1";

            $page = new HtmlTemplate();
            $page->html = $config['email_sub_ad_approve'];
            $page->SetParameter ('ADTITLE', $item_title);
            $page->SetParameter ('ADLINK', $ad_link);
            $page->SetParameter ('SELLER_NAME', $test_to_name);
            $page->SetParameter ('SELLER_EMAIL', $test_to_email);
            $email_subject = $page->CreatePageReturn($lang,$config,$link);

            $page = new HtmlTemplate();
            $page->html = $config['email_message_ad_approve'];;
            $page->SetParameter ('ADTITLE', $item_title);
            $page->SetParameter ('ADLINK', $ad_link);
            $page->SetParameter ('SELLER_NAME', $test_to_name);
            $page->SetParameter ('SELLER_EMAIL', $test_to_email);
            $email_body = $page->CreatePageReturn($lang,$config,$link);

            email($test_to_email,$test_to_name,$email_subject,$email_body,$config);
        }

        if (isset($_POST['re_ad_approve'])) {
            $item_title = "Advertise Title";
            $ad_link = $config['site_url']."ad/1";

            $page = new HtmlTemplate();
            $page->html = $config['email_sub_re_ad_approve'];
            $page->SetParameter ('ADTITLE', $item_title);
            $page->SetParameter ('ADLINK', $ad_link);
            $page->SetParameter ('SELLER_NAME', $test_to_name);
            $page->SetParameter ('SELLER_EMAIL', $test_to_email);
            $email_subject = $page->CreatePageReturn($lang,$config,$link);

            $page = new HtmlTemplate();
            $page->html = $config['email_message_re_ad_approve'];;
            $page->SetParameter ('ADTITLE', $item_title);
            $page->SetParameter ('ADLINK', $ad_link);
            $page->SetParameter ('SELLER_NAME', $test_to_name);
            $page->SetParameter ('SELLER_EMAIL', $test_to_email);
            $email_body = $page->CreatePageReturn($lang,$config,$link);
            email($test_to_email,$test_to_name,$email_subject,$email_body,$config);
        }

        if (isset($_POST['contact_to_seller'])) {
            $item_title = "Advertise Title";
            $ad_link = $config['site_url']."ad/1";

            $page = new HtmlTemplate();
            $page->html = $config['email_sub_contact_seller'];
            $page->SetParameter ('ADTITLE', $item_title);
            $page->SetParameter ('ADLINK', $ad_link);
            $page->SetParameter ('SELLER_NAME', $test_to_name);
            $page->SetParameter ('SELLER_EMAIL', $test_to_email);
            $page->SetParameter('SENDER_NAME', "Sender Name");
            $page->SetParameter('SENDER_EMAIL', "sender@gmail.com");
            $page->SetParameter('SENDER_PHONE', "1234567890");
            $email_subject = $page->CreatePageReturn($lang,$config,$link);

            $page = new HtmlTemplate();
            $page->html = $config['email_message_contact_seller'];;
            $page->SetParameter ('ADTITLE', $item_title);
            $page->SetParameter ('ADLINK', $ad_link);
            $page->SetParameter ('SELLER_NAME', $test_to_name);
            $page->SetParameter ('SELLER_EMAIL', $test_to_email);
            $page->SetParameter('SENDER_NAME', "Sender Name");
            $page->SetParameter('SENDER_EMAIL', "sender@gmail.com");
            $page->SetParameter('SENDER_PHONE', "1234567890");
            $page->SetParameter('MESSAGE', "Test Message : I want to inquiry about your classified.");
            $email_body = $page->CreatePageReturn($lang,$config,$link);
            email($test_to_email,$test_to_name,$email_subject,$email_body,$config);
        }

        $status = "success";
        $message = 'Email Sent Successfully';
    }else{
        $status = "Error";
        $message = 'Problem in sent e-mail.';
    }

    echo $json = '{"status" : "' . $status . '","message" : "' . $message . '"}';
    die();
}
?>