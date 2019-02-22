<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * My Moodle -- a user's personal dashboard
 *
 * - each user can currently have their own page (cloned from system and then customised)
 * - only the user can see their own dashboard
 * - users can add any blocks they want
 * - the administrators can define a default site dashboard for users who have
 *   not created their own dashboard
 *
 * This script implements the user's view of the dashboard, and allows editing
 * of the dashboard.
 *
 * @package    moodlecore
 * @subpackage my
 * @copyright  2010 Remote-Learner.net
 * @author     Hubert Chathi <hubert@remote-learner.net>
 * @author     Olav Jordan <olav.jordan@remote-learner.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../config.php');
require_once($CFG->dirroot . '/my/lib.php');
require_once($CFG->libdir.'/completionlib.php');
require_once($CFG->libdir.'/gradelib.php');
require_once($CFG->dirroot.'/grade/querylib.php');
global $DB, $USER;
$roleid='';
if(!is_siteadmin())
{
  $context = context_system::instance();
  $roles = get_user_roles($context, $USER->id, false);
  $role = key($roles);
  $roleid = $roles[$role]->roleid;
}
redirect_if_major_upgrade_required();

// TODO Add sesskey check to edit
$edit   = optional_param('edit', null, PARAM_BOOL);    // Turn editing on and off
$reset  = optional_param('reset', null, PARAM_BOOL);

require_login();

$hassiteconfig = has_capability('moodle/site:config', context_system::instance());
if ($hassiteconfig && moodle_needs_upgrading()) {
    redirect(new moodle_url('/admin/index.php'));
}

$strmymoodle = get_string('myhome');

if (isguestuser()) {  // Force them to see system default, no editing allowed
    // If guests are not allowed my moodle, send them to front page.
    if (empty($CFG->allowguestmymoodle)) {
        redirect(new moodle_url('/', array('redirect' => 0)));
    }

    $userid = null;
    $USER->editing = $edit = 0;  // Just in case
    $context = context_system::instance();
    $PAGE->set_blocks_editing_capability('moodle/my:configsyspages');  // unlikely :)
    $header = "$SITE->shortname: $strmymoodle (GUEST)";
    $pagetitle = $header;

} else {        // We are trying to view or edit our own My Moodle page
    $userid = $USER->id;  // Owner of the page
    $context = context_user::instance($USER->id);
    $PAGE->set_blocks_editing_capability('moodle/my:manageblocks');
    $header = fullname($USER);
    $pagetitle = $strmymoodle;
}

// Get the My Moodle page info.  Should always return something unless the database is broken.
if (!$currentpage = my_get_page($userid, MY_PAGE_PRIVATE)) {
    print_error('mymoodlesetup');
}

// Start setting up the page
$params = array();
$PAGE->set_context($context);
$PAGE->set_url('/my/index.php', $params);
$PAGE->set_pagelayout('mydashboard');
$PAGE->set_pagetype('my-index');
$PAGE->blocks->add_region('content');
$PAGE->set_subpage($currentpage->id);
$PAGE->set_title($pagetitle);
$PAGE->set_heading($header);

if (!isguestuser()) {   // Skip default home page for guests
    if (get_home_page() != HOMEPAGE_MY) {
        if (optional_param('setdefaulthome', false, PARAM_BOOL)) {
            set_user_preference('user_home_page_preference', HOMEPAGE_MY);
        } else if (!empty($CFG->defaulthomepage) && $CFG->defaulthomepage == HOMEPAGE_USER) {
            $frontpagenode = $PAGE->settingsnav->add(get_string('frontpagesettings'), null, navigation_node::TYPE_SETTING, null);
            $frontpagenode->force_open();
            $frontpagenode->add(get_string('makethismyhome'), new moodle_url('/my/', array('setdefaulthome' => true)),
                    navigation_node::TYPE_SETTING);
        }
    }
}

// Toggle the editing state and switches
if (empty($CFG->forcedefaultmymoodle) && $PAGE->user_allowed_editing()) {
    if ($reset !== null) {
        if (!is_null($userid)) {
            require_sesskey();
            if (!$currentpage = my_reset_page($userid, MY_PAGE_PRIVATE)) {
                print_error('reseterror', 'my');
            }
            redirect(new moodle_url('/my'));
        }
    } else if ($edit !== null) {             // Editing state was specified
        $USER->editing = $edit;       // Change editing state
    } else {                          // Editing state is in session
        if ($currentpage->userid) {   // It's a page we can edit, so load from session
            if (!empty($USER->editing)) {
                $edit = 1;
            } else {
                $edit = 0;
            }
        } else {
            // For the page to display properly with the user context header the page blocks need to
            // be copied over to the user context.
            if (!$currentpage = my_copy_page($USER->id, MY_PAGE_PRIVATE)) {
                print_error('mymoodlesetup');
            }
            $context = context_user::instance($USER->id);
            $PAGE->set_context($context);
            $PAGE->set_subpage($currentpage->id);
            // It's a system page and they are not allowed to edit system pages
            $USER->editing = $edit = 0;          // Disable editing completely, just to be safe
        }
    }

    // Add button for editing page
    $params = array('edit' => !$edit);

    $resetbutton = '';
    $resetstring = get_string('resetpage', 'my');
    $reseturl = new moodle_url("$CFG->wwwroot/my/index.php", array('edit' => 1, 'reset' => 1));

    if (!$currentpage->userid) {
        // viewing a system page -- let the user customise it
        $editstring = get_string('updatemymoodleon');
        $params['edit'] = 1;
    } else if (empty($edit)) {
        $editstring = get_string('updatemymoodleon');
    } else {
        $editstring = get_string('updatemymoodleoff');
        $resetbutton = $OUTPUT->single_button($reseturl, $resetstring);
    }

    $url = new moodle_url("$CFG->wwwroot/my/index.php", $params);
    $button = $OUTPUT->single_button($url, $editstring);
    $PAGE->set_button($resetbutton . $button);

} else {
    $USER->editing = $edit = 0;
}

echo $OUTPUT->header();

echo $OUTPUT->custom_block_region('content');

$instructor=$_GET['insid'];
if(!empty($instructor))
{
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
      <li><i class="fa fa-user" aria-hidden="true" style="color: #fff;font-size: 18px; "></i><a href="<?php echo $CFG->wwwroot.'/admin/tool/uploaduser/index.php'; ?>" style="margin-left: 5px;">Create users</a></li>
      <li><i class="fa fa-user" aria-hidden="true" style="color: #fff;font-size: 18px; "></i><a href="<?php echo $CFG->wwwroot.'/message/'; ?>" style="margin-left: 5px; ">Message users</a></li>
      <li><i class="fa fa-book" style="color: #fff;font-size: 18px; "></i><a href="<?php echo $CFG->wwwroot.'/my/allcourse_enrol.php'; ?>" style="margin-left: 5px; ">Enrol users to modules</a></li>
      <!-- <?php echo $CFG->wwwroot.'/my/course_enrol.php'; ?> -->

    </ul>
  </nav>
  </div>
</section>

<div class="allovertable">
<div id="detaildiv">
<div class="banner">
  <img src="<?php echo $CFG->wwwroot.'/my/Picture1.jpg'; ?>">
</div>
<?php
 $getcohort=$DB->get_records_sql("SELECT * from bm_cohort_members where userid=$instructor");
  foreach ($getcohort as $getco) {
    $cohortid=$getco->cohortid;
  }
   if(!empty($cohortid))
  {
    $queryc="SELECT c.id as cohortid, c.name as name FROM bm_cohort as c inner join bm_cohort_extra_fields cef on c.id=cef.cohort_id where cef.created_by=2 AND c.id=$cohortid";
    $datac=$DB->get_records_sql($queryc);
    foreach ($datac as $keyc) 
    {
      $cname=$keyc->name;
    }
  }
?>
<h2 class="block-heading" style="font-weight: bold!important; background-color: #F8BA00; color: #fff; text-align: center;width: 98%;"><?php echo $cname; ?></h2> 
  <div>
<div class="col-md-4 col-sm-6 prgress-outer tour1">
  <h4 style="background-color: #9FA0A1;color: #fff;padding: 10px;font-size: 17px;margin-bottom: -55px;">ENROLLED USERS</h4>
  <?php
  $users='';
  $getcohort=$DB->get_records_sql("SELECT * from bm_cohort_members where userid=$instructor");
  foreach ($getcohort as $getco) {
    $cohortid=$getco->cohortid;
  }
  if(!empty($cohortid))
  {
  $query1="SELECT count(*) as users FROM bm_user
INNER JOIN bm_cohort_members ON bm_user.id = bm_cohort_members.userid
INNER JOIN bm_cohort ON bm_cohort_members.cohortid = bm_cohort.id
WHERE bm_cohort.id=$cohortid and bm_user.id!=2";
  
  //$query1="SELECT count(*) as users FROM `bm_user` where deleted=0 and id NOT IN(1,2)";
  $data1=$DB->get_records_sql($query1);
  foreach ($data1 as $key1) 
  {
    $count_users=$key1->users;
  }
$usersid='';
    $queryusr="SELECT bm_user.id as uid FROM bm_user
INNER JOIN bm_cohort_members ON bm_user.id = bm_cohort_members.userid
INNER JOIN bm_cohort ON bm_cohort_members.cohortid = bm_cohort.id
WHERE bm_user.id NOT IN (select userid from bm_role_assignments where roleid='10') and bm_cohort.id=$cohortid and bm_user.id!=2";
  
  //$query1="SELECT count(*) as users FROM `bm_user` where deleted=0 and id NOT IN(1,2)";
  $datausr=$DB->get_records_sql($queryusr);
  foreach ($datausr as $keyusr) 
  {
    $usersid.=$keyusr->uid.',';
  }
  $usersid=rtrim($usersid,',');
 }
  ?>
  <div class="progress-cpmt-text enrolled" style="color: #333;"><span class="dot"><p class="fonts"><?php echo $count_users;?></p></span></div>
</div>

<div class="col-md-4 col-sm-6 prgress-outer tour2">
  <h4 style="background-color: #9FA0A1;color: #fff;padding: 10px;font-size: 17px;margin-bottom: -55px;">MODULES</h4>
  <?php
   $query11="SELECT DISTINCT count(bm_course.id) as cours FROM bm_course inner join bm_enrol on bm_course.id=bm_enrol.courseid inner join bm_user_enrolments on bm_user_enrolments.enrolid=bm_enrol.id where bm_user_enrolments.userid=$instructor";
  //$query11="SELECT count(*) as cours FROM `bm_course` where id!=1";
  $data11=$DB->get_records_sql($query11);
  foreach ($data11 as $key11) 
  {
    $count_courses=$key11->cours;
  }
  ?>
  <div class="progress-cpmt-text enrolled" style="color: #333;"><span class="dot"><p class="fonts"><?php echo $count_courses;?></p></span></div>
</div>

<!-- user information finish -->

<?php
$courses=0;
$total_comp=0;
// $user_idd='';
if(!empty($usersid))
{
  $query11="SELECT id FROM `bm_user` where deleted=0 and id IN($usersid)";
$data11=$DB->get_records_sql($query11);
foreach ($data11 as $key11)
{
  $user_id=$key11->id;
  if(!empty($user_id))
  {
    $query="SELECT DISTINCT bm_course.id as courseid FROM bm_course inner join bm_enrol on bm_course.id=bm_enrol.courseid inner join bm_user_enrolments on bm_user_enrolments.enrolid=bm_enrol.id where bm_user_enrolments.userid=$user_id";
    $data=$DB->get_records_sql($query);
    foreach ($data as $key)
    {
      $c_id=$key->courseid;  
      $courses=$courses+1;
      $query_n="SELECT count(id) as ccid FROM `bm_course_completions` where course=$c_id and userid=$user_id and timecompleted!=''";
      $data_n=$DB->get_records_sql($query_n);
      foreach ($data_n as $val)
      {
         //$total_comp= $total_comp+1;
        $total_comp=$val->ccid;
      }
    }

  }
}
}
$comp_per='';
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
  <h4 style="background-color: #9FA0A1;color: #fff;padding: 10px;font-size: 17px;">PERCENTAGE COMPLETED</h4>
  <!-- <div class="progress-cpmt-text course-act"><?php echo intval($comp_per)."%";?></div>   -->
  <div class="progress-bar position" data-percent="<?php echo $comp_per;?>" data-duration="1000" data-color="#ccc,#F8BA00"></div> 
</div>

<?php
$overall_av_total=0;
$rank_users=array();
if(!empty($usersid))
{
  $u_query="SELECT id FROM `bm_user` where deleted=0 and id IN($usersid)";
$users_data=$DB->get_records_sql($u_query);
$number_user=count($users_data);
foreach ($users_data as $user_data) 
{
  $userId = $user_data->id;
  $user_total_av=0;
  //$courseId = null;
  $queryy="SELECT DISTINCT bm_course.id as courseid FROM bm_course inner join bm_enrol on bm_course.id=bm_enrol.courseid inner join bm_user_enrolments on bm_user_enrolments.enrolid=bm_enrol.id where bm_user_enrolments.userid IN($usersid)";
    $dataa=$DB->get_records_sql($queryy);
    foreach ($dataa as $keyy)
    {
      $courseId=$keyy->courseid;
      $result = grade_get_course_grade( $userId, $courseId );
    }
  $user_all_g=0;
  $user_course=0;
  if (is_array($result))
  {
    foreach ( $result as $id => $rec )
    {       
      //$course = $DB->get_record( 'course', array( 'id' => $id ) );
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
}else{
  $overall_aver="0.0"; 
}
?>
 <div class="col-md-4 col-sm-6 prgress-outer tour4">
  <h4 style="background-color: #9FA0A1;color: #fff;padding: 10px;font-size: 17px;">AVERAGE SCORE</h4>
   <!-- <div class="progress-cpmt-text course-act"><?php echo $overall_aver."%";?></div> -->
   <div class="progress-bar position" data-percent="<?php echo $overall_aver;?>" data-duration="1000" data-color="#ccc,#F8BA00"></div> 
</div>
</div>
<div class="clear"></div>
</div>

<?php 
  $cat_ids='';
  $resultco=$DB->get_records_sql("SELECT bm_course.category as cat, bm_course.fullname as fullname FROM bm_course inner join bm_enrol on bm_course.id=bm_enrol.courseid inner join bm_user_enrolments on bm_user_enrolments.enrolid=bm_enrol.id where bm_user_enrolments.userid=$instructor");
  foreach ($resultco as $keyco) 
  {
    $cat_ids.=$keyco->cat.',';
  }
  $cat_ids=rtrim($cat_ids,',');
  if(!empty($cat_ids))
  {
    $categories=$DB->get_records_sql("SELECT * from {course_categories} where id IN ($cat_ids) order by sortorder ASC");
  }
?>
<div class="tab">
  <?php foreach($categories as $cat){ ?>
     <button class="tablinks" onclick="openCity(event, '<?php echo "cat".$cat->id; ?>')"><?php echo $cat->name; ?></button>
  <?php } ?>
</div>

<?php 
if(!empty($cat_ids))
  {
$categories1=$DB->get_records_sql("SELECT * from {course_categories} where id IN ($cat_ids) order by sortorder ASC");
}
$cat_first=0;
foreach($categories1 as $cat1){
?>
<div id="<?php echo "cat".$cat1->id; ?>" class="tabcontent" <?php if($cat_first==0){ echo "style='display:block;'"; } ?>>
<div id="html-table">
<table>
  <thead>
    <tr>
      <th>Sr. No.</th> 
      <th>Module Name</th>
      <th>Completed</th>
      <th>Score < 50%</th>
      <th>Score >= 50%</th>     
    </tr>
  </thead>
  <tbody>
    
      <?php
      $cat_first=1;
        $total_ct_per='';
        $count=0;
        $result=$DB->get_records_sql("SELECT bm_course.id as cours, bm_course.fullname as fullname FROM bm_course inner join bm_enrol on bm_course.id=bm_enrol.courseid inner join bm_user_enrolments on bm_user_enrolments.enrolid=bm_enrol.id where bm_user_enrolments.userid=$instructor AND bm_course.category=$cat1->id");
        foreach ($result as $key) 
        {
          $courseid=$key->cours;
          $count=$count+1;

           $course_m=$DB->get_records_sql("SELECT * from {course_modules} where course=$courseid limit 1");
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
            <td><?php echo $count; ?></td>
            <td class="course_name_left"><a href="<?php echo $report_url; ?>"><?php echo $key->fullname; ?></a></td>
          <?php
          $moduleid='';
          if(!empty($courseid))
          {
          $result1=$DB->get_records_sql("SELECT * from {course_modules} where course=$courseid and (module=18 OR module=16) and visible=1");
          
          foreach ($result1 as $key1) 
          {
            $moduleid=$key1->id;
            $itemins=$key1->instance;
            if(!empty($moduleid) && !empty($usersid))
            {
             
            $result3=$DB->get_records_sql("SELECT count(userid) as userid from {course_modules_completion} where coursemoduleid=$moduleid and userid IN ($usersid)");
            
            foreach ($result3 as $key3) 
            {
              $countusers=$key3->userid;
              if($countusers!=0)
              {
              ?>
                <td><?php echo $key3->userid; ?></td>
              <?php
              }else
              { 
              ?>
                <td><?php echo '0'; ?></td>
              <?php   
              } 
            }
          }
            if(!empty($moduleid))
            {
              $itemid='';
          $result4=$DB->get_records_sql("SELECT * from bm_grade_items where iteminstance=$itemins and courseid=$courseid");
            foreach ($result4 as $key4) 
            {
              $itemid.=$key4->id.',';
            }
            $itemid=rtrim($itemid,',');
            $lessgrade='';
            $greatergrade='';
              $resultm=$DB->get_records_sql("SELECT * from {course_modules_completion} where coursemoduleid=$moduleid and userid IN ($usersid)");
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
          
             }
            if($moduleid==0)
            {            
            ?>
              <td><?php echo '0'; ?></td>
              <td><?php echo '0'; ?></td>
              <td><?php echo '0'; ?></td>
            <?php
            }
          }
        }



      ?>
      </tr>
  </tbody>
</table>
</div>
</div>
</div>
<?php }
} ?>
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

    $(document).ready(function(){
  $("body").addClass('bgopacity');
});
    </script> 
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
<style>
#html-table th
{
  background-color: #F8BA00;
}
.pagination > .active > a
{
  background-color: #F8BA00;
  border-color: #F8BA00;
}
img {
    width: 98%;
}
.banner {
    margin-bottom: 13px;
}
.sideicon li i {
    margin-bottom: 18px;
}
.sideicon a:hover {
    color: #333!important;
}
.progress-bar div span {
    color: #F8BA00;
}
/*#left-bar
{
  float: left;
  background-color: #eee;
}*/
.dot {
  height: 185px;
  width: 185px;
  background-color: #F8BA00;
  border-radius: 50%;
  display: inline-block;
}
p.fonts {
    color: #01A8EC;
    margin-top: 74px;
    font-size: 24px;
    }
aside#block-region-side-post {
    display: none;
}
aside#block-region-content {
    display: none;
}
#region-main {
    padding-bottom: 60px;
    width: 100%;
}
.allovertable {
    width: 80%;
    float: right;
}
/*nav#left-bar {
    width: 15%;
    float: left;
    height: 358px;
    background: #9FA0A1;
    padding: 20px;
}*/
/*#calender {
    width: 16%;
    float: left;
    height: 582px;
    background: #9FA0A1;
    padding: 20px;
    display: inline-grid;
}*/
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
    padding-left: 13px;
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
#page-footer {
     margin-top: 0px!important; 
}
</style>

<script>
function openCity(evt, cityName) {
  var i, tabcontent, tablinks;
  tabcontent = document.getElementsByClassName("tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }
  document.getElementById(cityName).style.display = "block";
  evt.currentTarget.className += " active";
}
</script>
<?php 
echo $OUTPUT->footer();

// Trigger dashboard has been viewed event.
$eventparams = array('context' => $context);
$event = \core\event\dashboard_viewed::create($eventparams);
$event->trigger();