<?php 
require_once('../config.php');
require_login();
$params = array();
$context = context_user::instance($USER->id);
$PAGE->set_context($context);
$PAGE->set_url('/my/unenrol_bulkusers.php', $params);

$header	 = "Courses";
$PAGE->set_heading($header);

echo $OUTPUT->header();
global $DB, $USER;
if(isset($_POST['unenrolusers']))
{
  $cohort=$_POST['select_cohort'];
  $course=$_POST['select_course'];

if(!empty($cohort))
{
  $cohortuser=$DB->get_records_sql("SELECT * FROM bm_cohort_members WHERE cohortid=$cohort");
  foreach ($cohortuser as $removeuser) {
  $user=$removeuser->userid;

    $instances = $DB->get_records('enrol', array('courseid' => $course));
    foreach ($instances as $instance) {
    $plugin = enrol_get_plugin($instance->enrol);
    $plugin->unenrol_user($instance, $removeuser->userid);
    }
  }

  if($instances)
  {
    echo "<script language='javascript'>";
    echo "alert('Users unenrolled from course successfully')";
    echo "</script>";
  }
}
}
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<form method="POST">
  <h2 style="text-align: center;">UNENROL USERS</h2>
  <div class="form-group">
   <label style="display: inline!important; margin-left: 33%; margin-top: 4%;">Select Cohort : </label>
  <select name="select_cohort" class="select_cohort" style=" margin-top: 1%;">
  <option value="">--Please Select Cohort First--</option>
  <?php 

  $corid=$DB->get_records_sql("SELECT c.id as coid, c.name as name FROM {cohort} c inner join {cohort_extra_fields} cef on c.id=cef.cohort_id where cef.created_by=2");
      foreach ($corid as $key)
      {
         $cohortid=$key->coid;
        ?>
          <option value="<?php echo $key->coid; ?>"><?php echo $key->name; ?></option>
        <?php
      }
  ?>
  </select><br>
  </div>
  <script>
          $(document).ready(function(){
            $(".select_cohort").change(function(){
                 var id = $(this).val();
                      $.ajax({
                           method:"POST",
                           data:{cohort:id},
                           url:"get_courses.php",
                           success:function(html){
                        $('.select_course').html(html);
                           }

                      });
                      
                      });
            
            });
  </script>
  <div class="form-group">
     <label style="display: inline!important; margin-left: 33%; margin-top: 4%;">Select Module : </label> 
  <select name="select_course" class="select_course" style=" margin-top: 1%;">
  <option value="">--Select Module--</option>
  </select>
  </div>

  <div class="form-group">
    <input type="submit" name="unenrolusers" value="Unenrol users" style="margin-left: 45%; margin-top: 4%;">
  </div>
</form>
<style type="text/css">
	#page-navbar {
    display: none;
}
select {
    width: 20%;
}
</style>
