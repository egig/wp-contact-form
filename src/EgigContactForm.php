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

		try {

			$messageBody = sprintf(
				file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR.'mail_format.php'),
				$name, $email, $subject, $message);

			$smtpUser = $this->getOption('smtp_user');
			$smtpPass = $this->getOption('smtp_pass');
			$smtpHost = $this->getOption('smtp_host');
			$smtpPort = $this->getOption('smtp_port', 465);
			$smtpSsl = $this->getOption('smtp_ssl', 1);

			$mailFrom = $smtpUser;
			$mailFromName =  $this->getOption('mail_from_name');

			$mailTos = $this->getMailTos();

			$message = Swift_Message::newInstance() 
				->setSubject($subject)
				->setFrom($mailFrom, $mailFromName)
				->setTo($mailTos)
				->setBody($messageBody);

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

			var_dump($result);

			if($result) {
				header("Location: ".get_site_url(NULL, 'contact').'?contact-message-sent=1' );
				exit();
			}

		} catch (\Exception $e) {
			throw $e;
		}
	}

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
}