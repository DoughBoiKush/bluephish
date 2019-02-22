<option value="">--Select Module--</option>
<?php 
require_once('../config.php');
global $DB, $USER;
$cohortid=$_POST['cohort'];
if(!empty($cohortid))
{
	$cc=$DB->get_records_sql("SELECT * FROM bm_cohort_members WHERE cohortid=$cohortid");
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
	else
	{
		?>
	        <option value="" disabled><?php echo "There is no module under this cohort" ?></option>
	    <?php
	}
}
?>