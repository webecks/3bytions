<?php

// disable direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// The shortcode
function bytion_form_shortcode($bytion_atts) {
	$bytion_atts = shortcode_atts( array( 
		"email_to" => get_bloginfo('admin_email'), // Send the submitted data to admin email
		"label_name" => __('Name', 'bytion-assessment'),
		"label_email" => __('Email', 'bytion-assessment'),
		"label_submit" => __('Submit', 'bytion-assessment'),
		"error_name" => __('Please enter at least 2 characters', 'bytion-assessment'),
		"error_email" => __('Please enter a valid email', 'bytion-assessment'),
		"message_success" => __('Thank you for testing this form! This submission is sent to the admin email set in the backend. It might goes to the spam folder of your mailbox. Looking forward to hearing good news from you :)', 'bytion-assessment'),
		"message_error" => __('Error! Could not send form. This might be a server issue.', 'bytion-assessment'),
		"hide_subject" => ''
	), $bytion_atts);

	// Set variables 
	$form_data = array(
		'form_name' => '',
		'form_email' => ''
	);
	$error = false;
	$sent = false;
	$fail = false;
	$info = '';
	
	if (($_SERVER['REQUEST_METHOD'] == 'POST') && isset($_POST['bytion_send']) ) {

		// Sanitize content
		$post_data = array(
			'form_name' => sanitize_text_field($_POST['bytion_name']),
			'form_email' => sanitize_email($_POST['bytion_email'])
		);

		// Validate name
		$value = $post_data['form_name'];
		if ( strlen($value)<2 ) {
			$error_class['form_name'] = true;
			$error = true;
		}
		$form_data['form_name'] = $value;

		// Validate email
		$value = $post_data['form_email'];
		if ( empty($value) ) {
			$error_class['form_email'] = true;
			$error = true;
		}

		$form_data['form_email'] = $value;
		$form_data['form_subject'] = "Bytion Submission Form";

		// Send the form to admin & save to database
		if ($error == false) {
			// Send the submitted data to admin email
			$to = $bytion_atts['email_to'];
			if ($bytion_atts['hide_subject'] != "true") {
				$subject = "(".get_bloginfo('name').") " . $form_data['form_subject'];
			} else {
				$subject = get_bloginfo('name');
			}
			$message = $form_data['form_name'] . "\r\n\r\n" . $form_data['form_email'] . "\r\n\r\n" . sprintf( esc_attr__( 'IP: %s', 'bytion-assessment' ), bytion_get_the_ip() ); 
			$headers = "Content-Type: text/plain; charset=UTF-8" . "\r\n";
			$headers .= "Content-Transfer-Encoding: 8bit" . "\r\n";
			$headers .= "From: ".$form_data['form_name']." <".$form_data['form_email'].">" . "\r\n";
			$headers .= "Reply-To: <".$form_data['form_email'].">" . "\r\n";
		
			if( wp_mail($to, $subject, $message, $headers) ) { 
				$result = $bytion_atts['message_success'];
				$sent = true;
			} else {
				$result = $bytion_atts['message_error'];
				$fail = true;
			}		
			
			// Save the submitted data to the custom database table
			bytion_save_db($form_data);
		}
	}

	// Display info
	if(!empty($result)) {
		$info = '<p class="bytion-info">'.esc_attr($result).'</p>';
	}

	// Hide or display subject field 
	if ($bytion_atts['hide_subject'] == "true") {
		$hide = true;
	}

	// Contact form
	$email_form = '<form class="bytion" id="bytion" method="post">
		<p><label for="bytion_name">'.esc_attr($bytion_atts['label_name']).': </label></p>
		<p><span class="'.(isset($error_class['form_name']) ? "error" : "hide").'" >'.esc_attr($bytion_atts['error_name']).'</span><p>
		<p><input type="text" name="bytion_name" placeholder="Enter Name" id="bytion_name" '.(isset($error_class['form_name']) ? ' class="error"' : '').' maxlength="50" value="'.esc_attr($form_data['form_name']).'" /></p>		
		<p><label for="bytion_email">'.esc_attr($bytion_atts['label_email']).': </label></p>
		<p><span class="'.(isset($error_class['form_email']) ? "error" : "hide").'" >'.esc_attr($bytion_atts['error_email']).'</span></p>
		<p><input type="text" name="bytion_email" placeholder="Enter Email" id="bytion_email" '.(isset($error_class['form_email']) ? ' class="error"' : '').' maxlength="50" value="'.esc_attr($form_data['form_email']).'" /></p>		
		<p><input type="submit" value="'.esc_attr($bytion_atts['label_submit']).'" name="bytion_send" id="bytion_send" /></p>
	</form>';
	
	// Send form or display error
	if ($sent == true) {
		return $info;
	} elseif ($fail == true) {
		return $info;
	} else {
		return $email_form;
	}
} 
add_shortcode('bytion_form', 'bytion_form_shortcode');

/**
 * Grab the form data and Save it to custom database table
*/
function bytion_save_db($form_data) {
	global $wpdb;

	$form_name		= esc_attr($form_data['form_name']);
	$form_email   = esc_attr($form_data['form_email']);
	$form_time   	= current_time('Y-m-d H:i:s');
	$table_name 	= $wpdb->prefix . 'bytion_form';

	$wpdb->insert( $table_name, array( 
			'form_name'		=> $form_name,
			'form_email'  => $form_email,
			'form_time'		=> $form_time
	) );
}
?>