<?php
require_once('../../includes/config.php');
require_once('../../includes/functions/func.admin.php');
require_once('../../includes/functions/func.sqlquery.php');
require_once('../../includes/functions/func.users.php');
require_once('../../includes/lang/lang_'.$config['lang'].'.php');
$mysqli = db_connect($config);
admin_session_start();
checkloggedadmin($config);

$q = "SELECT * FROM `".$config['db']['pre']."product` WHERE `id` = '".$_GET['id']."' LIMIT 1";
$page_query = mysqli_query($mysqli,$q);
$info = mysqli_fetch_array($page_query);

$item_id = $info['id'];
$status = $info['status'];
$item_title = $info['product_name'];
$item_description = $info['description'];

$item_featured = $info['featured'];
$item_urgent = $info['urgent'];
$item_highlight = $info['highlight'];
$item_city = $info['city'];
$item_state = $info['state'];
$item_country = $info['country'];
$item_contact_phone = $info['contact_phone'];
$item_contact_email = $info['contact_email'];
$item_contact_chat = $info['contact_chat'];

$item_catid = $info['category'];
$item_subcatid = $info['sub_category'];
$get_main = get_maincat_by_id($config,$info['category']);
$get_sub = get_subcat_by_id($config,$info['sub_category']);
$item_category = $get_main['cat_name'];
$item_sub_category = $get_sub['sub_cat_name'];
?>

<header class="slidePanel-header overlay">
    <div class="overlay-panel overlay-background vertical-align">
        <div class="service-heading">
            <h2>Edit Ad - <?php echo $item_title; ?></h2>
        </div>
        <div class="slidePanel-actions">
            <div class="btn-group-flat">
                <button type="button" class="btn btn-floating btn-warning btn-sm waves-effect waves-float waves-light margin-right-10" id="post_sidePanel_data"><i class="icon ion-android-done" aria-hidden="true"></i></button>
                <button type="button" class="btn btn-pure btn-inverse slidePanel-close icon ion-android-close font-size-20" aria-hidden="true"></button>
            </div>
        </div>
    </div>
</header>
<div class="slidePanel-inner">
    <div class="panel-body">
        <!-- /.row -->
        <div class="row">
            <div class="col-sm-12">

                <div class="white-box">
                    <div id="post_error"></div>
                    <form name="form2"  class="form form-horizontal" method="post" data-ajax-action="postEdit" id="sidePanel_form">
                        <div class="form-body">
                            <input type="hidden" name="id" value="<?php echo $item_id ?>">
                            <div class="form-group">
                                <label class="col-sm-4 control-label">Ad Status</label>
                                <div class="col-sm-6">
                                    <select name="status" class="form-control">
                                        <option value="active" <?php if($status == 'active') echo "selected"; ?>>Active</option>
                                        <option value="pending" <?php if($status == 'pending') echo "selected"; ?>>Pending</option>
                                        <option value="rejected" <?php if($status == 'rejected') echo "selected"; ?>>Rejected</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">Category</label>
                                <div class="col-sm-6">
                                    <select name="category" id="category" class="form-control select2" data-ajax-action="getsubcatbyid">
                                        <option value="">Select a Category...</option>
                                        <?php
                                        $cat =  get_maincategory($config,$item_catid);
                                        foreach($cat as $option){
                                            echo '<option value="'.$option['id'].'" '.$option['selected'].'>'.$option['name'].'</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-4 control-label">SubCategory</label>
                                <div class="col-sm-6">
                                    <select name="sub_category" id="sub_category" class="form-control select2">
                                        <option value="">Select a Subcategory...</option>

                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-4 control-label">Ad Title:</label>
                                <div class="col-sm-6">
                                    <input name="title" type="text" class="form-control" value="<?php echo $item_title ?>">
                                </div>
                            </div>



                            <div class="form-group">
                                <label class="col-sm-4 control-label">Description:</label>
                                <div class="col-sm-6">
                                    <textarea name="content" rows="6" type="text" class="form-control"><?php echo de_sanitize($item_description) ?></textarea>
                                    <p class="help-block">Html tags are allow.</p>
                                </div>
                            </div>

                            <!-- Select2 -->
                            <!-- Select2 (.js-select2 class is initialized in App() -> uiHelperSelect2()) -->
                            <!-- For more info and examples please check https://github.com/select2/select2 -->
                            <div class="form-group">
                                <label class="col-sm-4 control-label">Country</label>
                                <div class="col-sm-6">
                                    <select name="country" class="form-control js-select2" id="country" data-ajax-action="getStateByCountryID"  data-placeholder="Select country..">
                                        <option></option><!-- Required for data-placeholder attribute to work with Chosen plugin -->
                                        <?php $country = get_country_list($config,$item_country);
                                        foreach ($country as $value){
                                            echo '<option value="'.$value['code'].'" '.$value['selected'].'>'.$value['name'].'</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-4 control-label">region</label>
                                <div class="col-sm-6">
                                    <select name="state" id="state" class="form-control js-select2" data-ajax-action="getCityByStateID" data-placeholder="Select region..">
                                        <option value="<?php echo $item_state ?>" checked><?php echo get_stateName_by_id($config,$item_state) ?></option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-4 control-label">City</label>
                                <div class="col-sm-6">
                                    <select name="city" id="city" class="form-control js-select2" data-placeholder="Select city..">
                                        <option value="<?php echo $item_city ?>" checked><?php echo get_cityName_by_id($config,$item_city) ?></option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-4 control-label">Premium:</label>
                                <div class="col-sm-6" style="margin-left: 10px">
                                    <label class="css-input css-checkbox css-checkbox-primary">
                                        <input type="checkbox" name="featured" value="1" <?php if($item_featured == '1') echo "checked"; ?>><span></span> Featured
                                    </label>
                                    <label class="css-input css-checkbox css-checkbox-primary">
                                        <input type="checkbox" name="urgent" value="1" <?php if($item_urgent == '1') echo "checked"; ?>><span></span> Urgent
                                    </label>
                                    <label class="css-input css-checkbox css-checkbox-primary">
                                        <input type="checkbox" name="highlight" value="1" <?php if($item_highlight == '1') echo "checked"; ?>><span></span> Highlight
                                    </label>

                                </div>
                            </div>

                            <div class="form-group hidden">
                                <label class="col-sm-4 control-label">Contact Option:</label>
                                <div class="col-sm-6 checkbox" style="margin-left: 10px">
                                    <input type="checkbox" name="contact_phone" value="1" id="contact_phone"<?php if($item_contact_phone == '1') echo "checked"; ?>>
                                    <label for="contact_phone">By Phone</label><br>
                                    <input type="checkbox" name="contact_email" value="1" id="contact_email"<?php if($item_contact_email == '1') echo "checked"; ?>>
                                    <label for="contact_email">By Email</label><br>
                                    <input type="checkbox" name="contact_chat" value="1" id="contact_chat"<?php if($item_contact_chat == '1') echo "checked"; ?>>
                                    <label for="contact_chat">Instant Chat</label>
                                </div>
                            </div>




                        </div>

                    </form>
                </div>
            </div>
        </div>
        <!-- /.row -->
    </div>
</div>

<script>
    $("#category").change(function(){
        var catid = $(this).val();
        var action = $(this).data('ajax-action');
        var data = { action: action, catid: catid };
        $.ajax({
            type: "POST",
            url: ajaxurl+"?action="+action,
            data: data,
            success: function(result){
                $("#sub_category").html(result);
            }
        });
    });

    $("#country").change(function () {
        var id = $(this).val();
        var action = $(this).data('ajax-action');
        var data = {action: action, id: id};
        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: data,
            success: function (result) {
                $("#state").html(result);
                $("#state").select2();
                $("#city").html('');
                $("#city").select2();
            }
        });
    });

    $("#state").change(function () {
        var id = $(this).val();
        var action = $(this).data('ajax-action');
        var data = {action: action, id: id};
        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: data,
            success: function (result) {
                $("#city").html(result);
                $("#city").select2();
            }
        });
    });

    jQuery(function($) {
        getsubcat("<?php echo $item_catid; ?>","getsubcatbyid","<?php echo $item_subcatid; ?>");
        getcountryToStateSelected("<?php echo $item_country; ?>","getStateByCountryID","<?php echo $item_state; ?>");
        getCitySelected("<?php echo $item_state; ?>","getCityByStateID","<?php echo $item_city; ?>");
    });
    //$(".select2").select2();
</script>

<!-- Page JS Code -->
<script>
    $(function()
    {
        // Init page helpers (BS Datepicker + BS Colorpicker + Select2 + Masked Input + Tags Inputs plugins)
        App.initHelpers('select2');
    });
</script>