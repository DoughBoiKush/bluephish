<option value="">--Select Module--</option>
<?php 
require_once('../config.php');
global $DB;
$cohortid=$_POST['cohort'];
if(!empty($cohortid))
{
	$cc=$DB->get_records_sql("SELECT * FROM bm_cohort_members WHERE userid IN (select userid from bm_role_assignments where roleid='10') and cohortid=$cohortid");
	foreach ($cc as $keycc) {
		$userid=$keycc->userid;
	}
	if(!empty($userid))
	{
	 $corid=$DB->get_records_sql("SELECT DISTINCT c.id as coid, c.fullname as fullname, c.shortname as shortname FROM {course} c inner join {enrol} e on c.id=e.courseid inner join {user_enrolments} ue on ue.enrolid=e.id where ue.userid=$userid");
	    foreach ($corid as $key)
	    {
	       $courseid=$key->coid;
	      ?>
	        <option value="<?php echo $key->coid; ?>"><?php echo $key->fullname.' '.$key->shortname ?></option>
	      <?php
	    }
	}
}
?>