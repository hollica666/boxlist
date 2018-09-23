<?php
/**
* Quickad Rating & Reviews - jQuery & Ajax php
* @author Bylancer
* @version 1.0
*/

include_once('setting.php');
// returns average rating 
function averageRating($productid, $db,$config,$lang)
{
    $productid = mysqli_real_escape_string($db, $productid);
    
    $q_star1_result = mysqli_num_rows(mysqli_query($db, "SELECT rating FROM ".$config['db']['pre']."reviews WHERE rating=1 AND productID='".$productid."' AND publish=1"));
    $q_star2_result = mysqli_num_rows(mysqli_query($db, "SELECT rating FROM ".$config['db']['pre']."reviews WHERE rating=2 AND productID='".$productid."' AND publish=1"));
    $q_star3_result = mysqli_num_rows(mysqli_query($db, "SELECT rating FROM ".$config['db']['pre']."reviews WHERE rating=3 AND productID='".$productid."' AND publish=1"));
    $q_star4_result = mysqli_num_rows(mysqli_query($db, "SELECT rating FROM ".$config['db']['pre']."reviews WHERE rating=4 AND productID='".$productid."' AND publish=1"));
    $q_star5_result = mysqli_num_rows(mysqli_query($db, "SELECT rating FROM ".$config['db']['pre']."reviews WHERE rating=5 AND productID='".$productid."' AND publish=1"));
                            
    $total = $q_star1_result + $q_star2_result + $q_star3_result + $q_star4_result + $q_star5_result;
    
    if ($total != 0) {                      
        $rating = ($q_star1_result*1 + $q_star2_result*2 + $q_star3_result*3 + $q_star4_result*4 + $q_star5_result*5) / $total;
    } else {
        $rating = 0;
    }
    
    $rating = round($rating * 2) / 2;

    echo '<h3>'.$lang['AVRAGE_RATING'].'</h3><p><small><strong>'.$rating.'</strong> '.$lang['AVRAGE_BASED_ON'].' <strong>'.$total.'</strong> '.$lang['REVIEWS'].'.</small></p><div class="rating-passive" data-rating="'.$rating.'"><span class="stars"></span></div>';
}

// show average rating
if (isset($_GET['show'])) {
    if ($_GET['show'] == "average") {
        averageRating($productid, $db,$config,$lang);
    }
} else {
    // show reviews
    $qReviews = mysqli_query($db, "SELECT * FROM ".$config['db']['pre']."reviews WHERE productID='".$productid."' AND publish=1 ORDER BY date DESC") or die(mysqli_error($db));
    $rReviews = mysqli_num_rows($qReviews);

    if ($rReviews == 0) {
        echo '<p>'.$lang['NO_REVIEW'].'</p>';
    } else {
        while ($fReviews = mysqli_fetch_assoc($qReviews)) {
            $query = "SELECT * FROM ".$config['db']['pre']."user WHERE id='".$fReviews['user_id']."' LIMIT 1";
            $query_result = mysqli_query(db_connect($config), $query);

            $info = mysqli_fetch_assoc($query_result);

            $fullname = $info['name'];
            $username = $info['username'];
            $image = $info['image'];
            $star = '';
            for($i=1; $i<=5; $i++){

                if($i <= $fReviews['rating']){
                    $checked = "starchecked";
                }else{
                    $checked = "";
                }
                $star .= '<span class="fa fa-star font-18 '.$checked.'"></span>';
            }



            echo '<div class="review"  id="'.$fReviews['reviewID'].'">
                        <div class="image">
                            <div class="bg-transfer">
                                <img src="'.$config['site_url'].'/storage/profile/'.$image.'" alt="'.$fullname.'">
                            </div>
                        </div>
                        <div class="description">
                            <figure>
                                <div class="rating-passive" data-rating="'.$fReviews['rating'].'">
                                    <span class="stars">

                                    </span>
                                </div>
                                <span class="date">'.date("d F Y", strtotime($fReviews['date'])).'</span>
                                <p class="t-body -size-m h-m0">by <a class="t-link -decoration-reversed" href="'.$config['site_url'].'profile/'.$username.'">'.$fullname.'</a></p>
                            </figure>
                            <p>'.$fReviews['comments'].'</p>
                        </div>
                    </div>
                    <!--end review-->';
        } 
    } 
}
?>