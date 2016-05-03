<?php

use Symfony\Component\HttpFoundation\Request;

class EgigContactForm {

	const OPTION_NAME = 'egig_contact_form';

	private $options;

	/**
	 * Handle the request
	 */
	public function init(Request $request) {

		$this->initSettingForm();

		$this->initOptions();

		$this->handle($request);
	}

	/**
	 * Ini setting form on admin
	 */
	private function initSettingForm() {
		include __DIR__.'/Resources/options.php'; 
	}

	/**
	 * Get options from
	 *
	 * @author 
	 **/
	private function initOptions() {
		$this->options = get_option(static::OPTION_NAME);
	}

	private function getOption($name, $default = NULL) {
		return isset($this->options[$name]) ? $this->options[$name] : $default;
	}

	private function getMailTos()
	{
		$mails = explode(',', $this->getOption('mail_to', ''));
		return array_map('trim', $mails);
	}

	/**
	 * Listen contact message request;
	 */
	private function handle(Request $request) {

		$email = $request->request->get('feedback_user_email');
		$name = $request->request->get('feedback_user_name');
		$email = $request->request->get('feedback_user_email');
		$subject = $request->request->get('feedback_subject');
		$message = $request->request->get('feedback_message');
		$captcha = $request->request->get('g-recaptcha-response');

		if(empty($email) || empty($message)) {
			return;
		}

		if(! $this->captchaIsValid($captcha)) {
			return;
		}

		$uploadedFile = $request->files->get('lampiran');

		$attachedFile = false;
		if($uploadedFile) {

			// NOTE: $attachedFile variable is used below for mail attachment
			$attachedFile = $uploadedFile->getPath()
				.DIRECTORY_SEPARATOR.$uploadedFile->getClientOriginalName();

			$uploadedFileSize = $uploadedFile->getSize();
			$uploadedFileSizeLimit = static::fileUploadMaxSize();

			if($uploadedFileSize > $uploadedFileSizeLimit) {
				// @todo change this to template
				echo '<script>alert("FILE TOO BIG");</script>';
				echo '<script>setTimeout(function(){ window.location = window.location.href }, 2000);</script>';
				exit();
			}

			$uploadedFile->move($uploadedFile->getPath(), $uploadedFile->getClientOriginalName());
		}

		try {

			$messageBody = sprintf(
				file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR.'mail_format.php'),
				$name, $email, $subject, $message);

			$smtpUser = $this->getOption('smtp_user');
			$smtpPass = $this->getOption('smtp_pass');
			$smtpHost = $this->getOption('smtp_host');
			$smtpPort = $this->getOption('smtp_port', 465);
			$smtpSsl = $this->getOption('smtp_ssl', 0);

			$mailFrom = $smtpUser;
			$mailFromName =  $this->getOption('mail_from_name');

			$mailTos = $this->getMailTos();

			$message = Swift_Message::newInstance() 
				->setSubject($subject)
				->setFrom($mailFrom, $mailFromName)
				->setTo($mailTos)
				->setBody($messageBody);

			if($attachedFile) {
				$message->attach(\Swift_Attachment::fromPath($attachedFile));
			}

			if($smtpSsl) {
				$transport = Swift_SmtpTransport::newInstance($smtpHost, $smtpPort, 'ssl');
			} else {
				$transport = Swift_SmtpTransport::newInstance($smtpHost, $smtpPort);
			}
		 	
		 	$transport->setUsername($smtpUser)
		  		->setPassword($smtpPass);

			$mailer = Swift_Mailer::newInstance($transport);

			// Send the message
			$result = $mailer->send($message);

			if($result) {
				header("Location: ".get_site_url(NULL, 'contact').'?contact-message-sent=1' );
				exit();
			}

		} catch (\Exception $e) {
			
			throw $e;
		}
	}

	/**
	 * Check if captcha is valid.
	 *
	 * @return boolean
	 * @author 
	 **/
	private function captchaIsValid($captcha) {
		if(empty($captcha)) {
			return false;
		}

		$secret = $this->getOption('recaptcha_secret');
		$recaptcha = new \ReCaptcha\ReCaptcha($secret);

		$ipAddres = $_SERVER['REMOTE_ADDR'];
		$response = $recaptcha->verify($captcha, $ipAddres);

		if (!$response->isSuccess()) {
			foreach ($response->getErrorCodes() as $code) {
                echo '<tt>' , $code , '</tt> ';
	        }
	        exit();
		}

		return true;
	}

	// Returns a file size limit in bytes based on the PHP upload_max_filesize
	// and post_max_size
	private static function fileUploadMaxSize() {
	  $max_size = -1;

	  if ($max_size < 0) {
	    // Start with post_max_size.
	    $max_size = static::parse_size(ini_get('post_max_size'));

	    // If upload_max_size is less, then reduce. Except if upload_max_size is
	    // zero, which indicates no limit.
	    $upload_max = static::parse_size(ini_get('upload_max_filesize'));
	    if ($upload_max > 0 && $upload_max < $max_size) {
	      $max_size = $upload_max;
	    }
	  }
	  return $max_size;
	}

	private static function parse_size($size) {
	  $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
	  $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
	  if ($unit) {
	    // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
	    return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
	  }
	  else {
	    return round($size);
	  }
	}
}