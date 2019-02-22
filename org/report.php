<?php 
require_once('../config.php');
global $DB,$USER;

require_login();
/*if(!is_siteadmin()){
	$url = $CFG->wwwroot."/my";
	redirect($url);
}*/
$params = array();
$context = context_user::instance($USER->id);
$PAGE->set_context($context);
$PAGE->set_url('/org/report.php', $params);

$header	 = "Report Quiz";
$PAGE->set_heading($header);
echo $OUTPUT->header();
$context = context_user::instance($USER->id);
$PAGE->set_context($context);
$moduleid = $_GET['id'];
$courseid = $DB->get_record('course_modules',array('id'=>$moduleid));
$quizid = $DB->get_record('quiz',array('course'=>$courseid->course));
?>
 <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
<ul class="list-inline">
   <li><a href="<?php echo $CFG->wwwroot; ?>/mod/quiz/view.php?id=<?php echo $_GET['id']; ?>"><button class="btn btn-primary">INFO</button></a></li>
    <li><a href="<?php echo $CFG->wwwroot; ?>/org/report.php?id=<?php echo $moduleid; ?>"><button class="btn btn-primary active">RESULT</button></a></li>
    <li><a href="<?php echo $CFG->wwwroot; ?>/mod/quiz/report.php?id=<?php echo $moduleid; ?>&mode=responses"><button class="btn btn-primary">REPORT</button></a></li>
    
  </ul>
  <?php 
 $cohort = $DB->get_records_sql("SELECT * FROM bm_cohort as ch inner join bm_cohort_extra_fields as che on ch.id=che.cohort_id where che.created_by=$USER->id");
    if($cohort){
  ?>
  <form name ="cohorts" action ='report.php' method ='get'>
    <label for ='cohot'><b>Select Any Cohort :</b></label>
    <select name ="cohort" class="cohort">
        <option value=''>--- Select Cohort --- </option>
  <?php
    if(isset($_GET['cohort'])){
       $coh_id=$_GET['cohort'];
    } 
    else{
       $coh_id="";
    } 
   
    foreach($cohort as $select){
      ?>
     <option value='<?php echo $select->id; ?>' <?php if($coh_id == $select->id){
        echo "selected";
      } ?> > <?php echo $select->name ?></option>;
    <?php
    }
   
  ?>
</select>
    <div class="form-group">
      <input type='hidden' value='<?php echo $_GET['id']; ?>' name='id'>
    </div>
    <div class="form-group">
      <input type="submit" class="btn btn-primary" value='submit' name='cohort_sub'>
    </div>
</form>

<?php
 }
echo "<h1>".$quizid->name."</h1>";
?>
<?php 
if(isset($_GET['cohort'])){
   $coh_id=$_GET['cohort'];
}
else{
   if(!is_siteadmin()){
      $getcohort=$DB->get_records_sql("SELECT * from bm_cohort_members where userid=$USER->id");
         foreach ($getcohort as $getco) {
          $cohortid=$getco->cohortid;
         }
       $coh_id=$cohortid;
   }
   else{
        $coh_id="";
   }
}
$ques = $DB->get_records_sql("SELECT * FROM bm_quiz_slots WHERE quizid = '$quizid->id' ORDER BY questionid");
foreach($ques as $key){
 $ques_id = $key->questionid;
 echo "<div class='question_box'>";
 $q_text = $DB->get_record('question',array('id'=>$ques_id));
 echo "<a href='$CFG->wwwroot/org/question.php?id=".$ques_id."&cohortid=".$coh_id."&cmid=".$_GET['id']."'><div class='question'><b>".$q_text->name.". ".strip_tags($q_text->questiontext)."  ?</b></div></a><br>";
 $q_respose = $DB->get_records_sql("SELECT * FROM bm_question_answers WHERE question = '$ques_id'");
 foreach($q_respose	as $ans){
 		$response = strip_tags($ans->answer);
        $count=0;
        if($coh_id){
          $id = $coh_id;
          $cohort_mem = $DB->get_records_sql("SELECT * FROM bm_cohort_members WHERE cohortid = $id");
            $userids = '';
              foreach($cohort_mem as $all_user){
                $userids .= $all_user->userid.",";
              }
             $user_id = rtrim($userids,",");
           // echo "SELECT DISTINCT uniqueid FROM bm_quiz_attempts WHERE userid IN ('$user_id')";
            if($user_id){ 
             $unique =$DB->get_records_sql("SELECT DISTINCT uniqueid FROM bm_quiz_attempts WHERE userid IN ($user_id)");
             $unique_ids='';
              foreach($unique as  $uniqueids){
                 $unique_ids .= $uniqueids->uniqueid.",";

              }
                $uniqueid = rtrim($unique_ids,",");
             }
             else{
                echo "<script>alert('No user in this group')</script>";
                redirect($CFG->wwwroot."/org/report.php?id=".$_GET['id']);
                  exit();
             }   
                if(empty($uniqueid)){
                    echo "<script>alert('No user has attempted this quiz')</script>";
                  exit();
                }
              $all_response = $DB->get_records_sql("SELECT *  FROM bm_question_attempts WHERE questionid = '$ques_id' AND questionusageid IN ($uniqueid)");
    foreach ($all_response as $value) {
      $user_res=$value->responsesummary;
      $user_res=trim($user_res);
      
            if($user_res==$response){
              $count=$count+1;
            }
        }
        }else{
 		$all_response = $DB->get_records_sql("SELECT *  FROM bm_question_attempts WHERE questionid = '$ques_id'");
 		foreach ($all_response as $value) {
 			$user_res=$value->responsesummary;
 			$user_res=trim($user_res);
 			
            if($user_res==$response){
             $count=$count+1;
            } 
     		}
      }
 		echo "<span class='response_count' style='margin-left:20px;'><b>".$response."</b></span>  &nbsp; <span> = " .$count."</span>";

 }
 echo "</div>";
}

?>

<style>
p{
    margin-left: 20px;
    margin-top: 0px;
    margin-bottom: 0px !important;
}
.question_box{
	margin-bottom: 10px;
}
</style>
<?php
 echo $OUTPUT->footer();
 ?>