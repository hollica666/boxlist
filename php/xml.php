<?php
require_once('includes/config.php');
require_once('includes/classes/class.template_engine.php');
require_once('includes/functions/func.global.php');
require_once('includes/functions/func.sqlquery.php');
require_once('includes/functions/func.users.php');
require_once('includes/lang/lang_'.$config['lang'].'.php');
require_once('includes/seo-url.php');

$con = db_connect($config);
sec_session_start();
header('Content-type: text/xml');

function text_replace_for_xml($text){
    $text = str_replace("&","&amp;",stripslashes($text));
    $text = str_replace('<','&lt;',$text);
    $text = str_replace('>','&gt;',$text);
    return $text;
}

if(isset($_GET['t']) && $_GET['t'] == 'premiumads'){
    if($config['xml_featured'] == 1){
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:wfw="http://wellformedweb.org/CommentAPI/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://www.w3.org/2005/Atom">';
        echo '<channel>';
        echo '<title>' . text_replace_for_xml($config['site_title']) . '</title>';
        echo '<link>' . text_replace_for_xml($config['site_url']) . '</link>';
        echo '<description>' . text_replace_for_xml($config['site_title']) . '</description>';
        echo '<language>en</language>';

        $query = "SELECT * FROM `".$config['db']['pre']."product` WHERE (featured = '1' or urgent = '1' or highlight = '1') AND  status='active' ORDER BY id DESC";
        $query_result = @mysqli_query ($con,$query) OR error(mysqli_error($con));
        while ($info = @mysqli_fetch_array($query_result))
        {
            $info['product_name'] = text_replace_for_xml($info['product_name']);
            $info['description'] = text_replace_for_xml($info['description']);
            $info['description'] = strip_tags($info['description']);

            $premium = '';
            if ($info['featured'] == "1"){
                $premium = $premium.'Featured ';
            }

            if($info['urgent'] == "1")
            {
                $premium = $premium.'Urgent ';
            }

            if($info['highlight'] == "1")
            {
                $premium = $premium.'Highlight ';
            }

            $pro_url = create_slug($info['product_name']);
            $item_link = $config['site_url'].'ad/' . $info['id'] . '/'.$pro_url;
            $item_created_at = timeAgo($info['created_at']);
            $get_main = get_maincat_by_id($config,$info['category']);
            $get_sub = get_subcat_by_id($config,$info['sub_category']);
            $item_category = $get_main['cat_name'];
            $item_sub_category = $get_sub['sub_cat_name'];

            $item_category = text_replace_for_xml($item_category);
            $item_sub_category = text_replace_for_xml($item_sub_category);

            echo '<item>';
            echo '<title><![CDATA[' . $info['product_name'] . ']]></title>';
            echo '<link>' . $item_link . '</link>';
            echo '<guid>' . $item_link . '</guid>';
            echo '<pubDate>'.$item_created_at.'</pubDate>';
            echo '<featured>' . $premium . '</featured>';
            echo '<category>' . $item_category . '</category>';
            echo '<sub-category>' . $item_sub_category . '</sub-category>';
            echo '<description><![CDATA[' . $info['description'] . ']]></description>';
            echo '</item>';
        }

        echo '</channel>';
        echo '</rss>';
    }

}
else{
    if($config['xml_latest'] == 1){
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:wfw="http://wellformedweb.org/CommentAPI/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://www.w3.org/2005/Atom">';
        echo '<channel>';
        echo '<title>' . text_replace_for_xml($config['site_title']) . '</title>';
        echo '<link>' . text_replace_for_xml($config['site_url']) . '</link>';
        echo '<description>' . text_replace_for_xml($config['site_title']) . '</description>';
        echo '<language>en</language>';
        echo '<atom:link href="'.$config['site_url'].'sitemap.xml" rel="self" type="application/rss+xml" />';

        $query = "SELECT * FROM `".$config['db']['pre']."product` WHERE status='active' ORDER BY id DESC";
        $query_result = @mysqli_query ($con,$query) OR error(mysqli_error($con));
        while ($info = @mysqli_fetch_array($query_result))
        {
            $info['product_name'] = text_replace_for_xml($info['product_name']);
            $info['description'] = text_replace_for_xml($info['description']);
            $info['description'] = strip_tags($info['description']);

            $premium = '';
            if ($info['featured'] == "1"){
                $premium = $premium.'Featured ';
            }

            if($info['urgent'] == "1")
            {
                $premium = $premium.'Urgent ';
            }

            if($info['highlight'] == "1")
            {
                $premium = $premium.'Highlight ';
            }

            $pro_url = create_slug($info['product_name']);
            $item_link = $config['site_url'].'ad/' . $info['id'] . '/'.$pro_url;
            $item_created_at = timeAgo($info['created_at']);
            $get_main = get_maincat_by_id($config,$info['category']);
            $get_sub = get_subcat_by_id($config,$info['sub_category']);
            $item_category = $get_main['cat_name'];
            $item_sub_category = $get_sub['sub_cat_name'];

            $item_category = text_replace_for_xml($item_category);
            $item_sub_category = text_replace_for_xml($item_sub_category);

            echo '<item>';
            echo '<title><![CDATA[' . $info['product_name'] . ']]></title>';
            echo '<link>' . $item_link . '</link>';
            echo '<guid>' . $item_link . '</guid>';
            echo '<pubDate>'.$item_created_at.'</pubDate>';
            echo '<category>' . $item_category . '</category>';
            echo '<sub-category>' . $item_sub_category . '</sub-category>';
            echo '<description><![CDATA[' . $info['description'] . ']]></description>';
            echo '</item>';
        }

        echo '</channel>';
        echo '</rss>';
    }
}
?>