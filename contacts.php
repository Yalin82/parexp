<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
session_start();
if (!empty($_REQUEST)) {
	extract($_REQUEST);
	$errors = array();
	$attachment = '';
	if(!trim($name))
	{
		$errors[] = 'name';
	}
	if(!trim($phone))
	{
		$errors[] = 'phone';
	}

	if(!trim($type))
	{
		$errors[] = 'type';
	}
	if(!trim($message))
	{
		$errors[] = 'message';
	}
	if(isset($_FILES['attachment']) && $_FILES['attachment']['size'] > 0)
	{
		$attachment = array('path'=>$_FILES['attachment']['tmp_name'], 'content_type' => $_FILES['attachment']['type'],'filename' => $_FILES['attachment']['name']);
	}
	if (empty($_SESSION['captcha']) || trim(strtolower($_REQUEST['captcha'])) != $_SESSION['captcha']) {
		$errors[] = 'captcha';
	}

	if(!$errors)
	{
		$to_address = 'info@parexp.ru';
		$from_address = 'contact_form@parexp.ru';
		$from_name = 'Форма обратной связи';
		$email_subject = "Сообщение с формы обратной связи";

		$email_body =$type.' от '. $name."($organization, $phone)\n\r".$message;
		SendMail($to_address, $from_name, $from_address, $email_subject, $email_body, false, $attachment);
		echo json_encode(array('status'=>'ok'));
	}
	else
	{
		echo json_encode(array('status'=>'error', 'errors'=>$errors));
	}
}
else
{
	echo json_encode(array('status'=>'error'));
}

function SendMail($emailaddress, $from, $fromaddress, $emailsubject="",	$body="", $html = true,	$attachment="",	$encoding="utf-8") {//{{{

# Is the OS Windows or Mac or Linux
	if (strtoupper(substr(PHP_OS,0,3)=='WIN')) {
		$eol="
";
	} elseif (strtoupper(substr(PHP_OS,0,3)=='MAC')) {
		$eol="\r";
	} else {
		$eol="\n";
	}

//set subject encoding
	$msg = "";

# Common Headers
	$headers = '';
	$headers .= "From: ".$from." <".$fromaddress.">".$eol;
	$headers .= "Reply-To: ".$from." <".$fromaddress.">".$eol;
	$headers .= "Return-Path: ".$from." <".$fromaddress.">".$eol; // these two to set reply address
	$headers .= "Message-ID: <".time()." TheSystem@".$_SERVER['SERVER_NAME'].">".$eol;
	$headers .= "X-Mailer: PHP v".phpversion().$eol; // These two to help avoid spam-filters
	$headers .= 'MIME-Version: 1.0'.$eol;

	if (!empty($attachment)) {
//send multipart message
# Boundry for marking the split & Multitype Headers
		$mime_boundary=md5(time());
		$headers .= "Content-Type: multipart/related; boundary=\"".$mime_boundary."\"".$eol;

# File for Attachment

		$f_name = $attachment['path'];
		$handle=fopen($f_name, 'rb');
		$f_contents=fread($handle, filesize($f_name));
		$f_contents=chunk_split(base64_encode($f_contents));//Encode The Data For Transition using base64_encode();
		fclose($handle);

		$msg .= "--".$mime_boundary.$eol;
		$headers .= "Content-Type: text/plain; charset=\"utf-8\"".$eol;
		$headers .= "Content-Transfer-Encoding: 8bit".$eol.$eol; // !! This line needs TWO end of lines !! IMPORTANT !!
		$msg .= $body.$eol.$eol;

# Attachment
		$msg .= "--".$mime_boundary.$eol;
		$msg .= "Content-Type: ".$attachment["content_type"]."; name=\"".$f_name."\"".$eol;
		$msg .= "Content-Transfer-Encoding: base64".$eol;
		$msg .= "Content-Disposition: attachment; filename=\"".$attachment['filename']."\"".$eol.$eol; // !! This line needs TWO end of lines !! IMPORTANT !!
		$msg .= $f_contents.$eol.$eol;
# Setup for text OR html
		$msg .= "Content-Type: multipart/alternative".$eol;


//		$contentType = "text/plain";
//		if ($html) {
//			$contentType = "text/html";
//		}

# Body
//		$msg .= "--".$mime_boundary.$eol;
//		$msg .= "Content-Type: ".$contentType."; charset=\"utf-8\"".$eol;
//		$msg .= "Content-Transfer-Encoding: 8bit".$eol.$eol; // !! This line needs TWO end of lines !! IMPORTANT !!
//		$msg .= $body.$eol.$eol;

# Finished
		$msg .= "--".$mime_boundary."--".$eol.$eol; // finish with two eol's for better security. see Injection.
	} else {
		$headers .= "Content-Type: text/plain; charset=\"utf-8\"".$eol;
		$headers .= "Content-Transfer-Encoding: 8bit".$eol.$eol; // !! This line needs TWO end of lines !! IMPORTANT !!
		$msg .= $body.$eol.$eol;
	}

// SEND THE EMAIL
//LogMessage("Sending mail to: ".$emailaddress." => ".$emailsubject);

//ini_set(sendmail_from, 'from@me.com'); // the INI lines are to force the From Address to be used !
	ini_set('sendmail_from', $fromaddress); //needed to hopefully get by spam filters.
	$success = mail($emailaddress, $emailsubject, $msg, $headers);
	ini_restore('endmail_from');

	return $success;
}//}}}