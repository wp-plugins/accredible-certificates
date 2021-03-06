<script type="text/javascript">
function disableCertificateSubmitButton() {
    document.getElementById("create_certificates").disabled = 'true';

}

function disableUnHide() {
  var elements = document.getElementsByClassName("test");
  var second_elements = document.getElementsByClassName("test1");
  // loop over them all
  for (var i=0; i<elements.length; i++) {
     if(elements[i].checked){ 
    	second_elements[i].disabled = true;
    }
  }
  
}
</script>

<div class="wrap">
<?php

	$api_key = get_option('api_key');

	if(empty($api_key)){

		echo "<p><strong>Please ensure you have entered an API key in the plugin settings page.</strong></p>";
	
	} else {

		$theme = wp_get_theme(); // gets the current theme
		if ('Academy' == $theme->name || 'Academy' == $theme->parent_theme) {
		  academy_theme();   
		} else {
			echo "<p><strong>Please install and activate the Academy Theme to use this plugin.</strong></p>";
		}

	}

	function academy_theme() 
	{
		//initialize the themex code
		//$themexCourse = new ThemexCourse();

		//could be this
			
		//$courses = ThemexCourse::getCourses(wp_get_current_user());
		//$courses = ThemexCourse::getCourses($user);

		$accredible_certificates = new Accredible_Certificate();

		$user = wp_get_current_user();
		$courses = @Accredible_Certificate::get_courses($user);
		    
	    //If there isn't any courses then tell the user
		if(empty($courses)){
			echo "<p><strong>You don't have any courses yet! Please create your courses to issue certificates.</strong></p>";

		} else {

			echo "<h2>Courses:</h2>";
            
            echo "<p>Select one or more students below and click Create Certificates to issue certificiates to students. A link to their certificate is shown for students that have already been issued a certificate.</p>";

		    if(get_option('automatically_issue_certificates')){
			    echo "<p>You are automtically issuing certificates when a student completes a course. To disable this please amend your settings.</p>";
			} else {
			    echo "<p>To automatically issue certificates upon course completition please amend your settings.</p>";
			}

			echo '<form method="POST" action="' . admin_url( 'admin.php' ) . '">';
			echo '<input type="hidden" name="action" value="wpse10500" />';

			//display each course info
			foreach ($courses as $key => $course_id) {
			    $course = ThemexCourse::getCourse($course_id, true);

			    $all_certificates = $accredible_certificates::certificates($course_id);
			    $all_certificates = $all_certificates->credentials;

			    echo "<h1>" . get_the_title($course_id) . "</h1>";

			    if(empty($course['users'])){
			    		
			      echo "<p>This course doesn't have any students.</p>";
			    } else {
			    	//draw table of users for the course
			    	echo "<table class='wp-list-table widefat fixed posts'>";
					echo "<thead>";
					echo "<tr>";
					echo "<th>Student Name</th>";
					echo "<th>Student Email</th>";
					echo "<th>Course Progress</th>";
					echo "<th>Issue certificate?</th>";
					echo "</tr>";
					echo "</thead>";

					foreach ($course['users'] as $user_id) {
                      
					    $user = get_user_by("id", $user_id);
					    
                        
						echo "<tr>";
						echo "<td>";
						if($user->first_name && $user->last_name ){
						     	echo $user->first_name . ' ' . $user->last_name;
						} else {
						    	echo $user->display_name;
						}
						echo "</td>";
						echo "<td>";
						echo $user->user_email;    				
						echo "</td>";
						echo "<td>";
                        
						$completed = ThemexCore::getUserRelations($user_id, $course_id, 'certificate');
						if(empty($completed)){
						    echo "Incomplete";
						} else {
						    echo "Complete";
						}
						echo "</td>";
						echo "<td>";
						    			
						$return_array = $accredible_certificates::find_certificate($all_certificates, $user);

						$no_cert = $return_array[0];
						$cert_id = $return_array[1];
						$approve = $return_array[2];
						//$no_cert = True;
                         
          
					   // if(is_array($all_certificates)){
						 //   foreach ($all_certificates as $key => $cert) {
							//    if($cert->recipient->email == $user->user_email){
							  //  	$no_cert = False;
							    //	$cert_id = $cert->id;
							    //	$approve = $cert->approve;
							    //}
							//}
					//	}
                        
						 
						if($no_cert){
							
							$grade = ThemexCourse::getGrade($course_id, $user_id);
						    echo '<input type="hidden" name="recipient_name[]" value="' . esc_attr($user->display_name) . '" />';
					        echo '<input type="hidden" name="recipient_email[]" value="' . esc_attr($user->user_email) . '" />';
						    echo '<input type="hidden" name="course_name[]" value="' . esc_attr(get_the_title($course_id)) . '" />';
					        echo '<input type="hidden" name="course_link[]" value="' . esc_attr(get_permalink($course_id)) . '" />';
					        echo '<input type="hidden" name="course_id[]" value="' . esc_attr($course_id) . '" />';
                            echo '<input type="hidden" name="grade[]" value="' . esc_attr($grade) . '" />';
					        
					        global $post;
						    $post = get_post($course_id);
						    setup_postdata( $post, $more_link_text, $stripteaser );
						    echo '<input type="hidden" name="course_description[]" value="' . esc_attr(get_the_excerpt()) . '" />';
					        wp_reset_postdata( $post );
				    	    echo '<input class="test" id="test" type="checkbox" name="issue_certificate[]">';
				    	    echo '<input class="test1" id="test1" type="hidden" value="0" name="issue_certificate[]">';
						} else {
						    if($approve){
						    		echo '<a target="_blank" href="https://www.accredible.com/#/' . $cert_id . '">' . $cert_id . '</a>';
						    	} else {
						    		echo $cert_id . ' - Publish on API Dashboard to view';
						    	}
						    				
						}
					    			
						echo "</td>";
						echo "</tr>";
				}

				    	echo "</table>";
			}

	    }
        
	    echo '<br><br><input type="submit" value="Create Certificates" id="create_certificates" class="button button-primary" onclick="setTimeout(disableCertificateSubmitButton, 1);disableUnHide();" />';
		echo '</form>';
	}
  }

?>   
</div>