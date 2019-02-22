<?php 
require_once(__DIR__ . '/../config.php');
require_once($CFG->dirroot . '/my/lib.php');
require_once($CFG->libdir.'/completionlib.php');
require_once($CFG->libdir.'/gradelib.php');
require_once($CFG->dirroot.'/grade/querylib.php');
global $DB, $USER;
?>
<section class="sidesection">
  <div id="calender">
  <a href="<?php echo $CFG->wwwroot; ?>/calendar/view.php"  >
    <div class="calender_1">
    <h4 style="color: #fff;font-size: 17px;">CALENDER</h4>
    <?php    echo $OUTPUT->blocks('side-post', 'span12'); ?>
  </div>
  </a>
   
  <div class=""> 
 <nav id="left-bar">
    <ul class="sideicon">
      <li><i class="fa fa-users" aria-hidden="true" style="color: #fff;font-size: 18px; "></i><a href="<?php echo $CFG->wwwroot.'/cohort/edit.php?contextid=1'; ?>" style="margin-left: 5px;">Create client</a></li>
      <li><i class="fa fa-user" aria-hidden="true" style="color: #fff;font-size: 18px; "></i><a href="<?php echo $CFG->wwwroot.'/user/editadvanced.php?id=-1'; ?>" style="margin-left: 5px; ">Create new user</a></li>
      <li><i class="fa fa-user" aria-hidden="true" style="color: #fff;font-size: 18px; "></i><a href="<?php echo $CFG->wwwroot.'/admin/user.php'; ?>" style="margin-left: 5px; ">Browse users</a></li>
       <li><i class="fa fa-user" aria-hidden="true" style="color: #fff;font-size: 18px; "></i><a href="<?php echo $CFG->wwwroot.'/cohort/index.php?contextid=1&showall=1'; ?>" style="margin-left: 5px;">Add users to client</a></li>
      <li><i class="fa fa-user" aria-hidden="true" style="color: #fff;font-size: 18px; "></i><a href="<?php echo $CFG->wwwroot.'/admin/tool/uploaduser/index.php'; ?>" style="margin-left: 5px;">Create users in bulk </a></li>
      <li><i class="fa fa-user" aria-hidden="true" style="color: #fff;font-size: 18px; "></i><a href="<?php echo $CFG->wwwroot.'/admin/user/user_bulk.php'; ?>" style="margin-left: 5px; ">Message users</a></li>
      <li><i class="fa fa-book" style="color: #fff;font-size: 18px; "></i><a href="<?php echo $CFG->wwwroot.'/my/allcourse_enrol.php'; ?>" style="margin-left: 5px; ">Enrol users to modules</a></li>
       <li><i class="fa fa-book" style="color: #fff;font-size: 18px; "></i><a href="<?php echo $CFG->wwwroot.'/my/unenrol_bulkusers.php'; ?>" style="margin-left: 5px; ">Unenrol bulk users</a></li> 
      <!-- <?php echo $CFG->wwwroot.'/my/allcourse_enrol.php'; ?> -->

    </ul>
  </nav>
  </div>
</section>
<div class="allovertable">
<div id="detaildiv">
<!--   <h2 class="block-heading" style="font-weight: bold!important;background-color: orange; color: #fff;">Site Details</h2> -->

<div class="banner">
  <img src="<?php echo $CFG->wwwroot.'/my/Picture1.jpg'; ?>">
</div>
<div class="col-md-4 col-sm-6 prgress-outer tour" >
  <!-- style="width: 100%; text-align: left!important; margin-left: 8%;" -->
   <?php
  $cohortidd='';
  $querych="SELECT * FROM bm_cohort";
  $datach=$DB->get_records_sql($querych);
  foreach ($datach as $keych) 
  {
    $cohortidd.=$keych->id.',';
  }
  $cohortidd=rtrim($cohortidd,',');
  ?>

 <h4 style="background-color: #9FA0A1;color: #fff;padding: 10px;font-size: 17px;margin-bottom: -55px;">USERS</h4>
 
    <?php
    $cohortuser='';
    $querycm=$DB->get_records_sql("SELECT * FROM bm_cohort_members where cohortid IN($cohortidd)");
    foreach ($querycm as $keycm) {
      $cohortuser.=$keycm->userid.',';
    }
    $cohortuser=rtrim($cohortuser,',');
      $countusers=$DB->get_records_sql("SELECT count(id) as userid from bm_user where id!=2 and deleted=0");
      foreach ($countusers as $cuser) {
        $total_users=$cuser->userid;
      }
      if(!empty($total_users))
      {
          $total_users=$cuser->userid;
      }else
      {
         $total_users='0';
      }
    ?>
<div class="progress-cpmt-text enrolled" style="color: #333;"><a href="<?php echo $CFG->wwwroot; ?>/admin/user.php"><span class="dot"><p class="fonts"><?php echo $total_users;?></p></span></a></div>
</div>
<div class="col-md-4 col-sm-6 prgress-outer tour" >
  <h4 style="background-color: #9FA0A1;color: #fff;padding: 10px;font-size: 17px;margin-bottom: -55px;">MODULES</h4>
  <?php
  $tot_users='';
   $totusers=$DB->get_records_sql("SELECT * from bm_user where id NOT IN($cohortuser)");
      foreach ($totusers as $totuser) {
        $tot_users.=$totuser->id.',';
      }
      $tot_users=rtrim($tot_users,',');
  $query11="SELECT count(id) as courseid FROM {course} where category!=0";
  $data11=$DB->get_records_sql($query11);
  foreach ($data11 as $key11) 
  {
     $count_courses=$key11->courseid;
  }
  if(!empty($count_courses))
      {
          $count_courses=$key11->courseid;
      }else
      {
         $count_courses='0';
      }
  ?>
  <div class="progress-cpmt-text enrolled" style="color: #333;"><a href="<?php echo $CFG->wwwroot; ?>/my/adminmodule.php"><span class="dot"><p class="fonts"><?php echo $count_courses;?></p></span></a></div>
</div>

<?php
$courses=0;
$total_comp=0;
// $user_idd='';
$query11="SELECT id FROM `bm_user` where id IN ($tot_users)";
$data11=$DB->get_records_sql($query11);
foreach ($data11 as $key11)
{
   $user_id=$key11->id;
  if($user_id)
  {
    $query="SELECT DISTINCT bm_course.id as courseid FROM bm_course inner join bm_enrol on bm_course.id=bm_enrol.courseid inner join bm_user_enrolments on bm_user_enrolments.enrolid=bm_enrol.id where bm_user_enrolments.userid=$user_id";
    $data=$DB->get_records_sql($query);
    foreach ($data as $key)
    {
      $c_id=$key->courseid;  
      $courses=$courses+1;
      $query_n="SELECT id FROM `bm_course_completions` where course=$c_id and userid=$user_id and timecompleted!=''";
      $data_n=$DB->get_records_sql($query_n);
      foreach ($data_n as $val)
      {
         $total_comp= $total_comp+1;
      }
    }

  }
}
// course id and course_completion information finish
if($total_comp!=0 && $courses!=0)
{
 $comp_pe=($total_comp/$courses)*100;
 $comp_per=round($comp_pe);
}
if($comp_per==0)
{
  $comp_per="0.0";
}
// echo $comp_per."%"."<br>";
?>
<div class="col-md-4 col-sm-6 prgress-outer tour3">
  <h4 style="background-color: #9FA0A1;color: #fff;padding: 10px;font-size: 17px;margin-bottom: 36px;">PERCENTAGE COMPLETED</h4>
  <!-- <div class="progress-cpmt-text course-act"><?php echo intval($comp_per)."%";?></div> -->
   <div class="progress-bar position" data-percent="<?php echo $comp_per;?>" data-duration="1000" data-color="#ccc,#F8BA00"></div> 
</div>

<?php
$overall_av_total=0;
$rank_users=array();
$u_query="SELECT id FROM `bm_user` where id IN ($tot_users)";
$users_data=$DB->get_records_sql($u_query);
$number_user=count($users_data);
foreach ($users_data as $user_data) 
{
  $userId = $user_data->id;
  $user_total_av=0;
  $courseId = null;
  $result = grade_get_course_grade( $userId, $courseId );
  $user_all_g=0;
  $user_course=0;
  if (is_array($result))
  {
    foreach ( $result as $id => $rec )
    {
      $course = $DB->get_record( 'course', array( 'id' => $id ) );
      $user_course_g=(intval($rec->grade)/intval($rec->item->grademax)*100);
      $user_all_g=$user_all_g+$user_course_g;
      $user_course=$user_course+1;
    }
    $user_total_av=$user_all_g/$user_course;
    $rank_users[$userId]=$user_total_av;
  
    arsort($rank_users);
    $overall_av_total=$overall_av_total+$user_total_av;
  }
}
// echo $overall_av_total;
 $overall_av=$overall_av_total/$number_user;
$overall_aver =intval($overall_av);
 if($overall_aver==0){
   $overall_aver="0.0"; 
}

?>
<div class="col-md-4 col-sm-6 prgress-outer tour4">
  <h4 style="background-color: #9FA0A1;color: #fff;padding: 10px;font-size: 17px;margin-bottom: 36px;">AVERAGE SCORE</h4>
   <!-- <div class="progress-cpmt-text course-act"><?php echo $overall_aver."%";?></div> -->
  <div class="progress-bar position" data-percent="<?php echo $overall_aver;?>" data-duration="1000" data-color="#ccc,#F8BA00"></div> 
</div>


<link rel="stylesheet" href="<?php echo $CFG->wwwroot; ?>/my/css/progress-bar.css">
<link rel="stylesheet" href="<?php echo $CFG->wwwroot; ?>/my/css/css-circular-prog-bar.css">
<script src="<?php echo $CFG->wwwroot; ?>/my/js/jquery-1.11.1.js"></script>
 <script src="<?php echo $CFG->wwwroot; ?>/my/js/jQuery-plugin-progressbar.js"></script>

<script>
    jQuery(document).ready(function(){
        jQuery(".progress-bar").loading();
        jQuery('input').on('click', function () {
             jQuery(".progress-bar").loading();
        });
    });    
    </script>


</div>

<div class="allcohorts">
<div class="col-md-4 col-sm-6 prgress-outer tour">
   

  <h4 style="background-color: #9FA0A1;color: #fff;padding: 10px;font-size: 17px;margin-bottom: -55px;">COHORTS</h4>
  <div class="progress-cpmt-text enrolled" style="color: #F8BA00;margin-left: 5%;">
    
  <?php
  $cohortid='';
  $number=1;

  $queryc="SELECT c.id as cohortid, c.name as name FROM {cohort} as c inner join {cohort_extra_fields} cef on c.id=cef.cohort_id where cef.created_by=2";
  $datac=$DB->get_records_sql($queryc);
  foreach ($datac as $keyc) 
  {

    $cohortid.=$keyc->cohortid.',';
    $cohort=$keyc->cohortid;
    $cohortname=$keyc->name;
    if(!empty($cohortname))
    {
      $usersid="";
        $queryusr="SELECT bm_user.id as uid FROM bm_user
    INNER JOIN bm_cohort_members ON bm_user.id = bm_cohort_members.userid
    INNER JOIN bm_cohort ON bm_cohort_members.cohortid = bm_cohort.id
    WHERE bm_user.id IN (select userid from bm_role_assignments where roleid='10') and bm_cohort.id=$cohort";
     $datausr=$DB->get_records_sql($queryusr);  
    foreach ($datausr as $keyusr) 
    {

       $usersid=$keyusr->uid;
    }
    if(!empty($usersid))
    {
      $inst_url=$CFG->wwwroot."/course/loginas.php?id=1&user=$usersid&sesskey=$USER->sesskey";
    }else
    {
      $inst_url="#";
    }
    
    ?>
      <li style="text-align: left; margin-left: 8%;"><?php echo $number++.'. '; ?><a href="<?php echo $inst_url; ?>"><?php echo $cohortname;?></a></li>
    <?php
    }
  }
  $cohortid=rtrim($cohortid,',');
  ?>

  </div>
 
</div>

 <div class="col-md-4 col-sm-6 prgress-outer tour0">
  <h4 style="background-color: #9FA0A1;color: #fff;padding: 10px;font-size: 17px;margin-bottom: -55px;">MODULES</h4>
  <div class="progress-cpmt-text enrolled" style="color: #F8BA00;">
  <?php
   $queryc="SELECT c.id as cohortid, c.name as name FROM bm_cohort as c inner join bm_cohort_extra_fields cef on c.id=cef.cohort_id where cef.created_by=2";
  $datac=$DB->get_records_sql($queryc);
  foreach ($datac as $keyc) 
  {
   $count_courses="0"; 
    $cohortid=$keyc->cohortid;
    if(!empty($cohortid))
    {
    $query1="SELECT bm_user.id as uid FROM bm_user
    INNER JOIN bm_cohort_members ON bm_user.id = bm_cohort_members.userid
    INNER JOIN bm_cohort ON bm_cohort_members.cohortid = bm_cohort.id
    WHERE bm_user.id IN (select userid from bm_role_assignments where roleid='10') and bm_cohort.id=$cohortid";
   
    $data1=$DB->get_records_sql($query1);
     foreach ($data1 as $key1) 
    {
       
        $users=$key1->uid;
        $count_courses=0;
        $query1="SELECT DISTINCT bm_course.id as cours FROM bm_course inner join bm_enrol on bm_course.id=bm_enrol.courseid inner join bm_user_enrolments on bm_user_enrolments.enrolid=bm_enrol.id where bm_user_enrolments.userid=$users";
        //$query1="SELECT count(*) as users FROM `bm_user` where deleted=0 and id NOT IN(1,2)";
        $data1=$DB->get_records_sql($query1);
        foreach ($data1 as $key1) 
        {
           $count_course=$key1->cours;
           $count_courses=$count_courses+1;         
       
        }
        
    }
     if($count_courses!=0)
        {
        ?>
            <li><?php echo $count_courses;?></li>
        <?php
        }else
        {
        ?>
            <li>0</li>
        <?php
        }
  }
}
  ?>
</div>
</div> 

<div class="col-md-4 col-sm-6 prgress-outer tour1">
  <h4 style="background-color: #9FA0A1;color: #fff;padding: 10px;font-size: 17px;margin-bottom: -55px;">USERS</h4>
    <div class="progress-cpmt-text enrolled" style="color: #F8BA00;">
  <?php
  $queryc="SELECT c.id as cohortid, c.name as name FROM bm_cohort as c inner join bm_cohort_extra_fields cef on c.id=cef.cohort_id where cef.created_by=2";
  $datac=$DB->get_records_sql($queryc);
  foreach ($datac as $keyc) 
  {
    $cohortid=$keyc->cohortid;
if(!empty($cohortid))
{
  $queryu="SELECT count(bm_user.id) as users FROM bm_user
  INNER JOIN bm_cohort_members ON bm_user.id = bm_cohort_members.userid
  INNER JOIN bm_cohort ON bm_cohort_members.cohortid = bm_cohort.id
  WHERE bm_cohort.id=$cohortid and bm_user.id!=2";

  //$query1="SELECT count(*) as users FROM `bm_user` where deleted=0 and id NOT IN(1,2)";
  $datau=$DB->get_records_sql($queryu);
  foreach ($datau as $keyu) 
  {
    $count_users=$keyu->users;
    if(!empty($count_users))
    {
    ?>
        <li><?php echo $count_users;?></li>
    <?php
    }else
    {
    ?>
        <li><?php echo '0';?></li>
    <?php
    }
  }
}
}
  ?>
</div>

</div>
<div class="col-md-4 col-sm-6 prgress-outer tour2">
  <h4 style="background-color: #9FA0A1;color: #fff;padding: 10px;font-size: 17px;margin-bottom: -55px;">COMPLETED</h4>
    <div class="progress-cpmt-text enrolled" style="color: #F8BA00;">
  <?php
  $queryc="SELECT c.id as cohortid, c.name as name FROM bm_cohort as c inner join bm_cohort_extra_fields cef on c.id=cef.cohort_id where cef.created_by=2";
  $datac=$DB->get_records_sql($queryc);
  foreach ($datac as $keyc) 
  {
    $passusers="0";
    $cohortid=$keyc->cohortid;
if(!empty($cohortid))
{
  //$count_users='';
  $queryu="SELECT bm_user.id as users FROM bm_user
  INNER JOIN bm_cohort_members ON bm_user.id = bm_cohort_members.userid
  INNER JOIN bm_cohort ON bm_cohort_members.cohortid = bm_cohort.id
  WHERE bm_user.id IN (select userid from bm_role_assignments where roleid='10') and bm_cohort.id=$cohortid";
   $datau=$DB->get_records_sql($queryu);
  foreach ($datau as $keyu) 
  {
   $count_users=$keyu->users;
  }
   //$count_users=rtrim($count_users,',');
  $usersid='';
    $queryusr="SELECT bm_user.id as uid FROM bm_user
INNER JOIN bm_cohort_members ON bm_user.id = bm_cohort_members.userid
INNER JOIN bm_cohort ON bm_cohort_members.cohortid = bm_cohort.id
WHERE bm_cohort.id=$cohortid and bm_user.id!=2";
  
  //$query1="SELECT count(*) as users FROM `bm_user` where deleted=0 and id NOT IN(1,2)";
  $datausr=$DB->get_records_sql($queryusr);
  foreach ($datausr as $keyusr) 
  {
    $usersid.=$keyusr->uid.',';
  }
 $usersid=rtrim($usersid,',');
 

if(!empty($count_users))
{
    $ccourses='';
    $query1="SELECT DISTINCT bm_course.id as cours FROM bm_course inner join bm_enrol on bm_course.id=bm_enrol.courseid inner join bm_user_enrolments on bm_user_enrolments.enrolid=bm_enrol.id where bm_user_enrolments.userid=$count_users";
      //$query1="SELECT count(*) as users FROM `bm_user` where deleted=0 and id NOT IN(1,2)";
    $data1=$DB->get_records_sql($query1);
    foreach ($data1 as $key1) 
    {
      $ccourses.=$key1->cours.',';  
    }  
    $ccourses=rtrim($ccourses,',');

    if(!empty($ccourses))
    {
      $moduleid='';
      $result1=$DB->get_records_sql("SELECT * from {course_modules} where course IN ($ccourses) and module=18");
      foreach ($result1 as $key1) 
      {    
        $moduleid.=$key1->id.',';
      }
      $moduleid=rtrim($moduleid,',');
    

      if(!empty($moduleid) && !empty($usersid))
      {
          $result4=$DB->get_records_sql("SELECT count(userid) as useridd from {course_modules_completion} where coursemoduleid IN ($moduleid) and userid IN ($usersid) and completionstate=1");
      
          foreach ($result4 as $key4) 
          {
            $passusers=$key4->useridd;
          }
      }
       
    }  

}
    if($passusers!=0)
    {
      ?>
        <li><?php echo $passusers; ?></li>
      <?php
    }else
    {
      ?>
      <li>0</li>

     <?php  
    }
       
  }
}     
   


?>
</div>


</div>
<div class="clear"></div>
</div>
<?php 
$categories=$DB->get_records_sql("SELECT * from {course_categories} order by sortorder ASC");

?>
<div>
<div class="tab">
  <?php foreach($categories as $cat){ ?>
     <button class="tablinks" onclick="openCity(event, '<?php echo "cat".$cat->id; ?>')"><?php echo $cat->name; ?></button>
  <?php } ?>
</div>

<?php 
$categories1=$DB->get_records_sql("SELECT * from {course_categories} order by sortorder ASC");
$cat_first=0;
foreach($categories1 as $cat1){
?>
<!-- <button onclick="exportTableToCSV('Details.csv')" style="border:none; padding:10px;" class="btnn btn-primary">Download CSV</button>  -->

<div id="<?php echo "cat".$cat1->id; ?>" class="tabcontent" <?php if($cat_first==0){ echo "style='display:block;'"; } ?>><!-- <input type="button" id="<?php echo "cat".$cat1->id; ?>" onclick="exportTableToCSV()" style="border:none; padding:10px;" name="" class="btnn btn-primary" <?php  echo $cat1->id; ?> value="Download CSV"> -->
<div class="singlebutton" id="yui_3_17_2_1_1550479854871_119">
 
</div>
<div id="html-table"> 
<table>
  <thead>
    <tr>
      <th>Sr. No.</th> 
      <th style="text-align: left!important;">Module Name</th>
      <th>Completed</th>
      <th>Score < 50%</th>
      <th>Score >= 50%</th>  
      <th>Report</th>   
    </tr>
  </thead>
  <tbody>
    
      <?php
      $cat_first=1;
        $k_count=0;
        $total_ct_per='';
        $result=$DB->get_records_sql("SELECT * from {course} where id!=1 AND category=$cat1->id");
        foreach ($result as $key) 
        {
         $k_count=$k_count+1;
          $courseid=$key->id;
          $course_m=$DB->get_records_sql("SELECT * from {course_modules} where course=$courseid AND deletioninprogress=0 ORDER BY id desc limit 1");
          foreach ($course_m as $cm) {
              $cm_id=$cm->id;
              $cm_module=$cm->module;
           } 

          if($cm_module==18){
          $report_url=$CFG->wwwroot."/mod/scorm/report.php?id=$cm_id";
          } 
          elseif($cm_module==16){
          $report_url=$CFG->wwwroot."/org/report.php?id=$cm_id";
          }
         else{
          $report_url="#";
          }
          ?>
            <tr>
           <td><?php echo $k_count; ?></td>
            <td class="course_name_left"><a href="<?php echo $report_url; ?>"><?php echo $key->fullname; ?></a></td>
          <?php
          $moduleid=0;
          $result1=$DB->get_records_sql("SELECT * from {course_modules} where course=$courseid and (module=18 OR module=16) and visible=1");
         if($result1){ 
          foreach ($result1 as $key1) 
          {
             $moduleid=$key1->id;
             $itemins=$key1->instance;

             $countusersid='';
            $result3=$DB->get_records_sql("SELECT userid from {course_modules_completion} where coursemoduleid=$moduleid and completionstate=1 and userid!=2");
            $countusers=count($result3);
            foreach ($result3 as $key3) 
            {
              $countusersid.=$key3->userid.',';
            }
            $countusersid=rtrim($countusersid,',');
              if($countusers!=0)
              {
              ?>
<td ><span class="myBtn"><?php echo $countusers; ?></span>
<div class="userlist">
  <h2 style="color: #fff;">USERS</h2><hr>
  <div class="userlist_content">
                    
                        <?php              
          if(!empty($countusersid))
          {
            $resultuser=$DB->get_records_sql("SELECT * from {user} where id IN ($countusersid)");
          
            foreach ($resultuser as $keyusers) 
            {
              $fusers=$keyusers->firstname;
              if(!empty($fusers))
              {
                ?>
       
                  <p><?php echo $keyusers->firstname.' '.$keyusers->lastname; ?></p> 
                 
              <?php
              }
            }
          }
            ?> 
 <button  class="close">Close</button> 
 </div>   
</div>
</td>
              <?php
              }else
              { 
              ?>
                <td><?php echo '0'; ?></td>
              <?php   
              } 
            
            $itemid='';
          $result4=$DB->get_records_sql("SELECT * from bm_grade_items where iteminstance=$itemins and courseid=$courseid");
            foreach ($result4 as $key4) 
            {
              $itemid.=$key4->id.',';
            }
            $itemid=rtrim($itemid,',');
            $lessgrade='';
            $greatergrade='';
              $resultm=$DB->get_records_sql("SELECT * from {course_modules_completion} where coursemoduleid=$moduleid");
              foreach ($resultm as $keym) 
              {   
                $totusers=$keym->userid;

                if(!empty($itemid) && !empty($totusers))
                {
                  $resultm=$DB->get_records_sql("SELECT count(userid) as userid from bm_grade_grades where userid=$totusers and itemid IN ($itemid) and finalgrade<50");
                  foreach ($resultm as $keym) 
                  {   
                    $lessgrade=$keym->userid;
                  }
                  $resultgm=$DB->get_records_sql("SELECT count(userid) as useridd from bm_grade_grades where userid=$totusers and itemid IN ($itemid) and finalgrade>=50");
                  foreach ($resultgm as $keygm) 
                  {   
                    $greatergrade=$keygm->useridd;
                  }
                }
              }
                if($lessgrade!=0)
                {
                ?>
                  <td><?php echo $lessgrade; ?></td>
                <?php
                }else
                { 
                ?>
                  <td><?php echo '0'; ?></td>
                <?php   
                }

                if($greatergrade!=0)
                {
                ?>
                  <td><?php echo $greatergrade; ?></td>
                <?php
                }else
                { 
                ?>
                  <td><?php echo '0'; ?></td>
                <?php   
                }

              
              }
          $SESSION->sesskey = !empty($USER->id) ? $USER->sesskey : '';  
          $resultt=$DB->get_records_sql("SELECT * from {course_modules} where course=$courseid and (module=18 OR module=16) and visible=1");
          if($resultt) 
          {
          foreach ($resultt as $keyt) 
          {
             $moduleidd=$keyt->module;
             if($moduleidd==18)
             {
          ?>
            <td><form method="post" action="<?php echo $CFG->wwwroot; ?>/mod/scorm/report.php" id="yui_3_17_2_1_1550479854871_118">
            <div id="yui_3_17_2_1_1550479854871_117">
              <input type="submit" value="Download CSV" id="yui_3_17_2_1_1550479854871_116">
              <input type="hidden" name="id" value="<?php echo $keyt->id; ?>">
              <input type="hidden" name="mode" value="basic">
              <input type="hidden" name="attemptsmode" value="0">
              <input type="hidden" name="download" value="Excel">
              <input type="hidden" name="sesskey" value="<?php echo $SESSION->sesskey; ?>">
            </div>
            </form></td>
          <?php
        }
        if($moduleidd==16)
            {
              ?>
             <td> <form method="get" action="<?php echo $CFG->wwwroot; ?>/mod/quiz/report.php" class="dataformatselector" id="yui_3_17_2_1_1550652630884_525">
            <div class="mdl-align" id="yui_3_17_2_1_1550652630884_524">
                <input type="hidden" name="sesskey" value="<?php echo $SESSION->sesskey; ?>">
              
                <input type="hidden" name="download" value="csv">
                <input type="submit" value="Download CSV">
                <input type="hidden" name="id" value="<?php echo $keyt->id; ?>">
                <input type="hidden" name="mode" value="responses">
                <input type="hidden" name="attempts" value="enrolled_with">
                <input type="hidden" name="onlygraded" value="">
                <input type="hidden" name="group" value="0">
                <input type="hidden" name="qtext" value="1">
                <input type="hidden" name="resp" value="1">
                <input type="hidden" name="right" value="0">
            </div>
            </form></td>
          <?php
            }
       
        }}else{
          ?>
          <td>---</td>
          <?php
        }

        }

          else
          {
          ?>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>---</td>

          <?php
          }

        }


      ?>
   
      </tr>
  </tbody>
</table>
</div>
</div>

<?php } ?>
</div>
<script src="<?php echo $CFG->wwwroot; ?>/my/js/paginathing.js"></script>
  <script type="text/javascript">
    jQuery(document).ready(function(){
        jQuery(".progress-bar").loading();
        jQuery('input').on('click', function () {
             jQuery(".progress-bar").loading();
        });
    });  


   jQuery('table tbody').paginathing({
     perPage: 10,
 });
</script>
<!-- <script src="<?php echo $CFG->wwwroot; ?>/my/js/jquery-1.11.1.js"></script>-->
<script>
$(document).ready(function(){
  $("body").addClass('bgopacity');
});

  $(document).ready(function(){
$(".myBtn").on('click', function () {

$(".bgcontainer").show();
$(this).next().show();

});
});

$(document).ready(function(){
$(".close").on('click', function () {
$(".bgcontainer").hide();
$(this).parent().parent().hide();
});
});


//EXPORT CSV
    $(function(){

  function exportTableToCSV($table, filename) {

    var $rows = $table.find('tr:has(td),tr:has(th)'),
        
        // Temporary delimiter characters unlikely to be typed by keyboard
        // This is to avoid accidentally splitting the actual contents
        tmpColDelim = String.fromCharCode(11), // vertical tab character
        tmpRowDelim = String.fromCharCode(0), // null character

        // actual delimiter characters for CSV format
        colDelim = '","',
        rowDelim = '"\r\n"',

        // Grab text from table into CSV formatted string
        csv = '"' + $rows.map(function (i, row) {
          if(i==0){ 
            var $row = $(row),
                $cols = $row.find('td,th');
          
              return $cols.map(function (j, col) {
          
                  var $col = $(col),
                      text = $col.text();

                  return text.replace(/"/g, '""'); // escape double quotes
                 
              }).get().join(tmpColDelim);
    

            }
            else{
            var $row = $(row),
                $cols = $row.find('td,th');  
              return $cols.map(function (j, col) {
                  var $col = $(col),
                      text = $col.text();

                  return text.replace(/"/g, '""'); // escape double quotes

              }).get().join(tmpColDelim);
           }

        }).get().join(tmpRowDelim)
            .split(tmpRowDelim).join(rowDelim)
            .split(tmpColDelim).join(colDelim) + '"',

        // Data URI
        csvData = 'data:application/csv;charset=utf-8,' + encodeURIComponent(csv);

     var a = document.createElement('a');
      a.href =csvData;
      a.download =filename;
       a.click();
}

    $('.btnn').click(function(){
         exportTableToCSV.apply(this, [$('#html-table'), 'Report.csv']);
    });
 

});
    </script>  

<style>
#region-main {
    padding-bottom: 60px;
    width: 100%;
}
img {
    width: 98%;

}


nav ul {
  list-style-type: none;
  padding: 0;
  margin-left: 10px;
}

.sideicon li a
{
  color: #fff;
  font-size: 14px;
}
.sideicon li i {
    margin-bottom: 18px;
}
.sideicon a:hover {
    color: #333!important;
}
.banner {
    margin-bottom: 13px;
}
p.fonts {
    color: #01A8EC;
    margin-top: 84px;
    font-size: 24px;
    }
.dot {
  height: 200px;
  width: 200px;
  background-color: #F8BA00;
  border-radius: 50%;
  display: inline-block;
}
/*.progress-bar div span {
    color: #333!important;
    }*/
.progress-cpmt-text.course-act {
    color: #333!important;
    }
#frontblockregion
{
  display: none;
}
  tbody
  {
    text-align: center;
  }
  nav.pagination-container 
    {
     position: absolute!important;
     margin-top: -17px;
    }
    .next {
     float: none; 
   }

/*.col-md-4 {
    width: 23%;
    float: left;
    font-size: 18px;
    padding: 1%;
    text-align: center;

}*/
th{
  text-align: center;
  color: black!important;
  border-bottom: 3px solid #4caf50;
  border-top: 3px solid #4caf50;
 padding-top: 2%;
 padding-bottom: 2%;
 font-size: 15px;
}
td{
  color: black!important;
  padding-top: 2%;
  padding-bottom: 2%;
  width: 20%;
  text-align: center;
}

table{
  text-align: center;
  width: 100%;
  max-width: 100%;
  margin-bottom: 20px;
  background: #fff;
}
.pagination>li {
  margin-left: 19px!important; 
      display: table-cell !important;
}
.pagination>li:first-child>a, .pagination>li:first-child>span {
    margin-left: 10px !important;  
}
#course-program .pagination {
    float: none;
    margin: 0 auto;
    text-align: center;
    width: 100%;
    margin-top: 32px;
} 
li
{
  list-style-type:none;
  color: #333;
}
/* Style the tab */
.tab {
  overflow: hidden;
  border: 1px solid #ccc;
  background-color: #f1f1f1;
      margin-top: 50px;
}

/* Style the buttons inside the tab */
.tab button {
  background-color: inherit;
  float: left;
  border: none;
  outline: none;
  cursor: pointer;
  padding: 14px 16px;
  transition: 0.3s;
  font-size: 17px;
}

/* Change background color of buttons on hover */
.tab button:hover {
  background-color: #ddd;
}

/* Create an active/current tablink class */
.tab button.active {
  background-color: #ccc;
}

/* Style the tab content */
.tabcontent {
  display: none;
  padding: 6px 12px;
  border: 1px solid #ccc;
  border-top: none;
}
/*.classusers {
    width: 100%!important;
    height: 200px;
}*/
.progress-bar div span {
    color: #F8BA00!important;
}
#html-table th {
    background-color: #F8BA00;
}
.pagination > .active > a
{
  background-color: #F8BA00!important;
  border-color: #F8BA00!important;
}


/* The Close Button */
.close {
  color: #aaaaaa;
  float: right;
  font-size: 28px;
  font-weight: bold;
}
.close:hover,
.close:focus {
  color: #000;
  text-decoration: none;
  cursor: pointer;
}

/*#calender {
    width: 16%;
    float: left;
    height: 582px;
    background: #9FA0A1;
    padding: 20px;
    display: inline-grid;
}*/
div#inst9 {
    display: none;
}
div#inst10 {
    display: none;
}
div#inst11 {
    display: none;
}
div#inst13 {
    display: none;
}
div#inst23 {
    display: none;
}
div#inst24 {
    display: none;
}
</style>
<div class="bgcontainer">
</div> 