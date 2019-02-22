<?php
require_once('../config.php');
global $DB;
global $CFG;	
require_login();
if(!is_siteadmin()){
	$url = $CFG->wwwroot."/my";
	redirect($url);
}
$params = array();
$context = context_user::instance($USER->id);
$PAGE->set_context($context);
$PAGE->set_url('/org/report.php', $params);

$header	 = "Question_image";
$PAGE->set_heading($header);
echo $OUTPUT->header();
$context = context_user::instance($USER->id);
$PAGE->set_context($context);
if(isset($_GET['question'])){
$q_id = $_GET['question'];
if(isset($_POST['submit'])){
			 $questionid = $_POST['questionid'];
			  
			$sourcefile= $_FILES['image']['tmp_name'];
            $image_name = $_FILES['image']['name'];
             $image_nameRandome = time ().$image_name;
            $target_dir = 'questionimages/';
            $type=$_FILES['image']['type'];
            $type = substr($type,0,5);        
            if(move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir.$image_nameRandome)){
            	 $exist =$DB->get_record('question_image',array('questionid'=>$questionid));
            	 if($exist){
            	 		$update = $DB->execute("UPDATE bm_question_image	SET text ='$image_nameRandome'WHERE  questionid = $questionid");
            	 		if($update){
            	 			echo "<script>alert(Image Uploaded)</script>";
            	 		}else{
            	 			echo "<script>alert(Something Went Wrong...)</script>";
            	 		}
            	 }else{
            	 	$added = $DB->execute("INSERT INTO bm_question_image (text,questionid) VALUES ('$image_nameRandome','$questionid')");
            	 	if($added){
            	 			?>
            	 			<script>
            	 			alert(Image Uploaded);
            	 			</script>
            	 			<?php
            	 		}else{
            	 			?>
            	 			<script>
            	 			alert(Something Went Wrong...);
            	 			</script>
            	 			<?php
            	 		}
            	 }
            }else{
            	 "<script>alert('failed')</script>";

            }
     
		}

?>
	<div class="main">
		<a href="<?php echo $CFG->wwwroot;?>/org/upload_question_image.php?quiz=<?php echo $_GET['quiz'];?>"><button class="btn">BACK</button></a>
		<form class="form-inline" name="home" method="post" enctype="multipart/form-data">
				
 				<label> <h3>Selected Question : <?php $name = $DB->get_record('question',array('id'=>$q_id));
 						echo $name->questiontext;
 				 ?></h3></label>
				<div class="form-group">
    				<label for="File">Choose File : </label>
   				    <input type="file" class="form-control" id="file" name="image" required >
   				    <input type="hidden" class="form-control" id="question" name="questionid" value="<?php echo $_GET['question']; ?>"  >
 				</div>

 				<div class="form-group">
 						<input type="submit" name="submit" value="Upload" />
 				</div>
			</form>

	</div>
<?php
}
?>
