<?php 
require_once('../config.php');
require_login();
$params = array();
$context = context_user::instance($USER->id);
$PAGE->set_context($context);
$PAGE->set_url('/my/course_enrol.php', $params);

$header	 = "Courses";
$PAGE->set_heading($header);

echo $OUTPUT->header();
global $DB, $USER;
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<select name="select_course" id="select_course" style="margin-left: 34%; margin-top: 4%;">
<option>--Please Select Module First--</option>
<?php 

$corid=$DB->get_records_sql("SELECT DISTINCT bm_course.id as cours, bm_course.fullname as fullname, bm_course.shortname as shortname FROM bm_course inner join bm_enrol on bm_course.id=bm_enrol.courseid inner join bm_user_enrolments on bm_user_enrolments.enrolid=bm_enrol.id where bm_user_enrolments.userid=$USER->id");
    foreach ($corid as $key)
    {
      echo $courseid=$key->cours;
      ?>
        <option  value="<?php echo $key->cours; ?>"><?php echo $key->fullname.' '.$key->shortname ?></option>
      <?php
    }
?>
<script>
$(function(){
      // bind change event to select
      $('#select_course').on('change', function () {
          var url = $('#select_course').val(); // get selected value
          if (url) { // require a URL
              window.location = '<?php echo $CFG->wwwroot; ?>/enrol/users.php?id=<?php echo $courseid; ?>'; // redirect
          }
          return false;
      });
    });
</script>
</select>
<style type="text/css">
	#page-navbar {
    display: none;
}
</style>
