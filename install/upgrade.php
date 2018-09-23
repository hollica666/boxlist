<?php
$aV = 6.5;
ignore_user_abort(1);
if ($config['version'] < $aV) {
    update_option($config,"max_image_upload",'3');
    update_option($config,"email_sub_signup_details",'{SITE_TITLE} - {LANG_THANKSIGNUP}');
    update_option($config,"email_message_signup_details",'Dear Valued Thanks for creating an account {SITE_TITLE} ,\n\nYour username: {USERNAME}\nYour password: {PASSWORD}\n\n\nHave further questions? You can start chat with live support team.\nSincerely,\n\n{SITE_TITLE} Team!\n{SITE_URL}');

    // Try to connect to the databse
    echo "Connecting to database.... \t";
    $con = @mysqli_connect ($config['db']['host'], $config['db']['user'], $config['db']['pass']);
    $db_select = @mysqli_select_db ($con,$config['db']['name']) OR install_error('ERROR ('.mysqli_error($con).')');
    echo "success<br>";


    echo "Drop subadmin1 Table...  \t\t";
    $q = "DROP TABLE `".$config['db']['pre']."subadmin1`";
    @mysqli_query($con,$q) or install_error('ERROR ('.mysqli_error($con).')');
    echo "success<br>";

    echo "Create subadmin1 Table...  \t\t";
    $q = "CREATE TABLE IF NOT EXISTS `".$config['db']['pre']."subadmin1` (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
      `code` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
      `country_code` varchar(2) COLLATE utf8_unicode_ci DEFAULT NULL,
      `name` varchar(200) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
      `asciiname` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
      `active` tinyint(1) UNSIGNED DEFAULT '1',
      PRIMARY KEY (`id`),
      UNIQUE KEY `code` (`code`),
      KEY `country_code` (`country_code`),
      KEY `name` (`name`),
      KEY `active` (`active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
    @mysqli_query($con,$q) or install_error('ERROR ('.mysqli_error($con).')');
    echo "success<br>";

    echo "Drop subadmin2 Table...  \t\t";
    $q = "DROP TABLE `".$config['db']['pre']."subadmin2`";
    @mysqli_query($con,$q) or install_error('ERROR ('.mysqli_error($con).')');
    echo "success<br>";

    echo "Create subadmin2 Table...  \t\t";
    $q = "CREATE TABLE IF NOT EXISTS `".$config['db']['pre']."subadmin2` (
      `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
      `code` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
      `country_code` varchar(2) COLLATE utf8_unicode_ci DEFAULT NULL,
      `subadmin1_code` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
      `name` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
      `asciiname` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
      `active` tinyint(1) UNSIGNED DEFAULT '1',
      PRIMARY KEY (`id`),
      UNIQUE KEY `code` (`code`),
      KEY `country_code` (`country_code`),
      KEY `subadmin1_code` (`subadmin1_code`),
      KEY `name` (`name`),
      KEY `active` (`active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
    @mysqli_query($con,$q) or install_error('ERROR ('.mysqli_error($con).')');
    echo "success<br>";

    echo "TRUNCATE cities Table...  \t\t";
    $q = "TRUNCATE TABLE `".$config['db']['pre']."cities`";
    @mysqli_query($con,$q) or install_error('ERROR ('.mysqli_error($con).')');
    echo "success<br>";

    echo "Uninstall all countries data...  \t\t";
    $q = "Update `".$config['db']['pre']."countries`  set active='0'";
    @mysqli_query($con,$q) or install_error('ERROR ('.mysqli_error($con).')');
    echo "success<br>";
}

?>