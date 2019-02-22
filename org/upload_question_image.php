
<style type="text/css">
.upload label {
    font-size: 18px;
    padding-bottom: 1%;
}
.upload select.form-control.quiz {
    margin-bottom: 13px;
}
.upload{
	text-align: center;
	margin-top: 15%;
}
tr:nth-child(even) {background: #CCC}
tr:nth-child(odd) {background: #FFF}
</style>

<?php
require_once('../config.php');
global $DB;

require_login();
if(!is_siteadmin()){
	$url = $CFG->wwwroot."/my";
	redirect($url);
}
$params = array();
$context = context_user::instance($USER->id);
$PAGE->set_context($context);
$PAGE->set_url('/org/report.php', $params);

$header	 = "Question";
$PAGE->set_heading($header);
echo $OUTPUT->header();
$context = context_user::instance($USER->id);
$PAGE->set_context($context);

if(isset($_GET['quiz'])){
		
?>
<table class="table">
		<thead>
			<th>Sr. No</th>
			<th>Question</th>
			<th>SELECT</th>
		</thead>
		<tbody>
	<?php
		 $quizid = $_GET['quiz'];	

	$ques = $DB->get_records_sql("SELECT * FROM bm_quiz_slots WHERE quizid = $quizid ORDER BY questionid ");
	if($ques){
					$count = 0;
			foreach($ques as $ques_id){
				
		$ques_texts =$DB->get_records_sql("SELECT * FROM bm_question WHERE id = $ques_id->questionid");
				foreach($ques_texts as $key){
	?>
		<tr>
			<td><?php echo $count= $count+1; ?></td>
			<td><?php echo $key->questiontext ;?></td>
			<td><?php echo "<a href='".$CFG->wwwroot."/org/question_image.php?question=".$key->id."&quiz=".$_GET['quiz']."' class='btn'>SELECT</a>"; ?></td>
		</tr>
	<?php
			}
		}
		}else{ echo "<h1>No Question Found</h1>" ;
		 	
		 }
	?>
	</tbody>
</table>
	<?php
	}
	else{
?>
<form class="upload" name="question_image" action ='upload_question_image.php'>
	<div class="form-group">
		<label for="quiz">SELECT QUIZ :</label>
		<select class="form-control quiz" name="quiz" required>
			<option value=''>--- SELECT A QUIZ ---</option>
			<?php $quiz = $DB->get_records_sql("SELECT * FROM bm_quiz"); 
				foreach($quiz as $quizs){
					echo "<option value='$quizs->id'> $quizs->name </option>";
				}
			?>
		</select>
	</div>	
	<div class="form-group">
		<input type="submit" class="form-control" value="Submit">
	</div>
</form>	
<?php 


}
