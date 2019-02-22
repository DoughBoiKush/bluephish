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
	$PAGE->set_url('/org/upload.php', $params);

	$header	 = "Upload Banner Image/ Video";
	$PAGE->set_heading($header);
	echo $OUTPUT->header();
	$context = context_user::instance($USER->id);
	$PAGE->set_context($context);

	?>
	<?php 
			if(isset($_POST['submit'])){
				 $title = $_POST['text'];
				  $description = $_POST['comment'];
				  $btn_text = $_POST['btn_text'];
				  $btn_url = $_POST['btn_url'];
			 	$place = $_POST['place'];
				$sourcefile= $_FILES['image']['tmp_name'];
	            $image_name = $_FILES['image']['name'];
	             $image_nameRandome = time ().$image_name;
	            $target_dir = 'upload/';
	            $type=$_FILES['image']['type'];
	            $type = substr($type,0,5);        
	            	if(isset($_FILES['image'])){
	            		
	            	move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir.$image_nameRandome);
	            		}
	            	 $exist =$DB->get_record('banner_images',array('id'=>$place));
	            	 if($exist){
	            	 		$query ="UPDATE bm_banner_images	SET id ='$place' ";
	            	 			if(!empty($title)){
	            	 				$query .= ", title = '$title' ";
	            	 			}
	            	 			if(!empty($description)){
	            	 				$query .= ", description = '$description'";
	            	 			}
	            	 			if(!empty($type)){
	            	 				$query .= ",type = '$type'";
	            	 			}	            	 			
	            	 			if(!empty($image_name)){
	            	 				$query .= " , imagename = '$image_nameRandome' ";
	            	 			}
	            	 			if(!empty($btn_text)){
	            	 				$query .= " , button_text = '$btn_text' ";
	            	 			}
	            	 			if(!empty($btn_url)){
	            	 				$query .= " , button_url = '$btn_url' ";
	            	 			}
	            	 			$query .= "WHERE id =$place";
	            	 		$update = $DB->execute($query);
	            	 		if($update){
	            	 			echo "<script>alert(Image Uploaded)</script>";
	            	 		}else{
	            	 			echo "<script>alert(Something Went Wrong...)</script>";
	            	 		}
	            	 }else{
	            	 	$added = $DB->execute("INSERT INTO bm_banner_images (imagename,title,description,place,type,button_text,button_url) VALUES ('$image_nameRandome','$title','$description','$place','$type','$btn_text','$btn_url')");
	            	 	if($added){
	            	 			echo "<script>alert(Image Uploaded)</script>";
	            	 		}else{
	            	 			echo "<script>alert(Something Went Wrong...)</script>";
	            	 		}
	            	 }
	            
	     
			}
			$index =$DB->get_record('banner_images',array('id'=>3));
			$signup =$DB->get_record('banner_images',array('id'=>4));
			$login =$DB->get_record('banner_images',array('id'=>1));
	?>
	 <script type="text/javascript" src="js/nicEdit.js"></script>
	<script type="text/javascript">
		bkLib.onDomLoaded(function() { nicEditors.allTextAreas() });
	</script> 
	<style>
	.form-group>div {
	    margin: 0 auto;
	}
	.nicEdit-panel>div:nth-child(1) {
	    display: none!important;
	}
	.nicEdit-panel>div:nth-child(2) {
	    display: none!important;
	}
	.nicEdit-panel>div:nth-child(3) {
	    display: none!important;
	}
	.nicEdit-panel>div:nth-child(4) {
	    display: none!important;
	}
	.nicEdit-panel>div:nth-child(5) {
	    display: none!important;
	}
	.nicEdit-panel>div:nth-child(6) {
	    display: none!important;
	}
	.nicEdit-panel>div:nth-child(10) {
	    display: none!important;
	}
	.nicEdit-panel>div:nth-child(11) {
	    display: none!important;
	}
	.nicEdit-panel>div:nth-child(12) {
	    display: none!important;
	}
	.nicEdit-panel>div:nth-child(13) {
	    display: none!important;
	}
	.nicEdit-panel>div:nth-child(14) {
	    display: none!important;
	}

	.nicEdit-panel>div:nth-child(16) {
	    display: none!important;
	}
	.nicEdit-panel>div:nth-child(17) {
	    display: none!important;
	}
	.nicEdit-panel>div:nth-child(18) {
	    display: none!important;
	}
	.nicEdit-panel>div:nth-child(20) {
	    display: none!important;
	}

	ul {
	  list-style-type: none;
	}
	.active{
		background-color: #FFA733 !important;
	}
	.align{
		text-align: center;
	}
	.form-group{
		padding:10px;
	}
	</style>
	<div class="main">
		<div class="bread_crum">
				<ul class="nav nav-pills">
					<li class="tab "><button class="btn btn-primary home active">PRE-LOGIN PAGE</button></li>
					<li class="tab"><button class="btn btn-primary index">SIGNUP PAGE</button></li>
					<li class="tab"><button class="btn btn-primary login">LOGIN PAGE</button></li>
				</ul>
		</div>
		<div class="home_tab align">
			<h3>Upload Pre-login Page Image</h3>
				<form class="form-inline" name="home" method="post" enctype="multipart/form-data">
					<div class="form-group">
	    				<label for="Title">Title</label>
	   				    <!-- <input type="text" class="form-control" id="text" name="text" value ="<?php if($index){echo $index->title;} ?>"> -->
	   				     <textarea rows="4" cols="50" name="text" id="text"><?php
							if($index){ echo $index->title; }  ?></textarea>
	 				</div>
	 				<div class="form-group">
	    				<label for="description">Description</label>
	   				    <textarea rows="4" cols="50" name="comment" id="froala-editor"><?php
							if($index){ echo $index->description; }  ?></textarea>
	 				</div>
					<div class="form-group">
	    				<label for="email">Choose File:</label>
	   				    <input type="file" class="form-control" id="file" name="image">
	   				    <input type="hidden" class="form-control" id="place" name="place" value="3"  >
	 				</div>
	 				<div class="form-group">
	    				<label for="text">Button Text</label>
	   				    <input type="text" class="form-control" id="btn_text" name="btn_text">
	 				</div>
	 				<div class="form-group">
	    				<label for="text">Button URL</label>
	   				    <input type="text" class="form-control" id="btn_url" name="btn_url">
	 				</div>
	 				<div class="form-group">
	 						<input type="submit" name="submit" value="Upload" />
	 						 <a href="<?php echo $CFG->wwwroot; ?>/?previewmode=1" class="btn" style="background-color: #51666C;vertical-align: top;" href="#" target="_blank">Preview</a> 
	 				</div>
				</form>
		</div>
		<div class="index_tab align" style="display:none;">
			<h3>Upload Signup Page Image</h3>
				<form class="form-inline" name="index" method="post" enctype="multipart/form-data">
					<div class="form-group">
	    				<label for="Title">Title</label>
	   				   <!--  <input type="text" class="form-control" id="text" name="text" value ="<?php if($signup){echo $signup->title; }?>"> -->
	   				    <textarea rows="4" cols="50" name="text" id="text" style="width: 317px!important;"><?php
							if($signup){ echo $signup->title; }  ?></textarea>
	 				</div>
	 				<div class="form-group">
	    				<label for="description">Description</label>
	   				    <textarea rows="4" cols="50" name="comment"  style="width: 317px!important;"><?php
							if($signup){ echo $signup->description; }  ?></textarea>
					</div>
						<div class="form-group">
	    					<label for="email">Choose File:</label>
	   				    	<input type="file" class="form-control" id="file" name="image" >
	   				    	<input type="hidden" class="form-control" id="place" name="place" value="4"  >
	 				</div>
	 				<div class="form-group">
	 						<input type="submit" name="submit" value="Upload" />
	 				</div>
				</form>
		</div>
		<div class="login_tab align" style="display:none;">
			<h3>Upload Login Page Image</h3>
				<form class="form-inline" name="home" method="post" enctype="multipart/form-data">
					<div class="form-group">
	    				<label for="Title">Title</label>
	   				    <!-- <input type="text" class="form-control" id="text" name="text" value ="<?php if($login){ echo $login->title; }?>"> -->
	   				     <textarea rows="4" cols="50" name="text" id="text" style="width: 317px!important;"><?php
							if($login){echo $login->title;}  ?></textarea>
	 				</div>
	 				<div class="form-group">
	    				<label for="description">Description</label>
	   				    <textarea rows="4" cols="50" name="comment" style="width: 317px!important;"><?php
							if($login){echo $login->description;}  ?></textarea>
	 				</div>
					<div class="form-group">
	    				<label for="email">Choose File:</label>
	   				    <input type="file" class="form-control" id="file" name="image" >
	   				    <input type="hidden" class="form-control" id="place" name="place" value="1"  >
	 				</div>
	 				<div class="form-group">
	 						<input type="submit" name="submit" value="Upload" />
	 				</div>
				</form>
		</div>
	</div>

	<?php
	echo $OUTPUT->footer();
	?>
	<script>
				$(document).ready(function(){
					$('.home').addClass("active");			
				});
			$('.nav li button').click(function(){
				 var text = $(this).text();
				$('.nav li button').each(function(){
						if($(this).text() == text){
							$('.home').removeClass("active");
							$('.index').removeClass("active");
							$('.login').removeClass("active");
							$(this).addClass("active");
						}
				});
			})
			$('.home').click(function(){
				$('.home_tab').css("display","block");
				$('.index_tab').css("display","none");
				$('.login_tab').css("display","none");
			});
			$('.index').click(function(){
				$('.home_tab').css("display","none");
				$('.index_tab').css("display","block");
				$('.login_tab').css("display","none");
			});
			$('.login').click(function(){
				$('.home_tab').css("display","none");
				$('.index_tab').css("display","none");
				$('.login_tab').css("display","block");
			});

	</script>