<?php 
require_once('../config.php');
global $DB;

require_login();
/*if(!is_siteadmin()){
	$url = $CFG->wwwroot."/my";
	redirect($url);
}*/
$params = array();
$context = context_user::instance($USER->id);
$PAGE->set_context($context);
$PAGE->set_url('/org/report.php', $params);

$header	 = "Report Question";
$PAGE->set_heading($header);
echo $OUTPUT->header();
echo "<a href='$CFG->wwwroot/org/report.php?id=".$_GET['cmid']."&cohort=".$_GET['cohortid']."'><button class='btn btn-primay'> BACK</button></a>";
$context = context_user::instance($USER->id);
$PAGE->set_context($context);
$questionid = $_GET['id'];
 $q_text = $DB->get_record('question',array('id'=>$questionid));
echo "<div class='question".$ques_id." '><h2>".$q_text->name.". ".strip_tags($q_text->questiontext)."?</h2></div><br>";
?>
	<table class="table">
		<thead>
			<th>Sr No.</th>
			<th>FULL NAME</th>
			<th>EMAIL</th>
			<th>RESPONSE SUBMITTED</th>
		</thead>
		<tbody>
			<tr>
<?php
if($_GET['cohortid']){
		 $id = $_GET['cohortid'];
          $cohort_mem = $DB->get_records_sql("SELECT * FROM bm_cohort_members WHERE cohortid = $id");
            $userids = '';
              foreach($cohort_mem as $all_user){
                $userids .= $all_user->userid.",";
              }
              $user_id = rtrim($userids,",");
           // echo "SELECT DISTINCT uniqueid FROM bm_quiz_attempts WHERE userid IN ('$user_id')";
             $unique =$DB->get_records_sql("SELECT DISTINCT uniqueid FROM bm_quiz_attempts WHERE userid IN ($user_id)");
             $unique_ids='';
              foreach($unique as  $uniqueids){
                 $unique_ids .= $uniqueids->uniqueid.",";

              }
                 $uniqueid = rtrim($unique_ids,",");
                if(empty($uniqueid)){
                    echo "<script>alert('No user has attempted this quiz')</script>";
                  exit();
                }
              $user = $DB->get_records_sql("SELECT * FROM bm_question_attempts WHERE questionid ='$questionid' and responsesummary != '' AND questionusageid IN ($uniqueid)");
		$count = 0;
	foreach($user as $users){
		$count = $count + 1;
		  $users->questionusageid;
			$userid = $DB->get_records_sql("SELECT * FROM bm_quiz_attempts WHERE uniqueid = '$users->questionusageid'");
				echo "<tr>";			
			foreach($userid as $key){
				$info = $DB->get_record('user',array('id'=>$key->userid));
				 $name = $info->firstname." ".$info->lastname;
				 $email = $info->email;
			}

		echo "<td>".$count."</td><td>".$name."</td><td>".$email."</td><td><b>".$users->responsesummary."</b></td><div>"; 
		echo "</tr>";
			}
}else{
$user = $DB->get_records_sql("SELECT * FROM bm_question_attempts WHERE questionid ='$questionid' and responsesummary != ''");
		$count = 0;
	foreach($user as $users){
		$count = $count + 1;
		  $users->questionusageid;
			$userid = $DB->get_records_sql("SELECT * FROM bm_quiz_attempts WHERE uniqueid = '$users->questionusageid'");
				echo "<tr>";			
			foreach($userid as $key){
				$info = $DB->get_record('user',array('id'=>$key->userid));
				 $name = $info->firstname." ".$info->lastname;
				 $email = $info->email;
			}

		echo "<td>".$count."</td><td>".$name."</td><td>".$email."</td><td><b>".$users->responsesummary."</b></td><div>"; 
		echo "</tr>";
			}
		}
	?>
		</tbody>
	</table>
	<?php
	echo $OUTPUT->footer();
?>