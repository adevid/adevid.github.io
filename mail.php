<?php

$mail = new Email();
$mail->process();

class Email
{

	public $to = 'info@poslogic.pro';
	public $subject = 'Сообщение с poslogic.pro';
	public $errors = [];
	public $data = [];

	public function __construct()
	{
		if (isset($_POST) && !empty($_POST)) {
			$this->data = $this->validate($_POST);
		}
	}

	public function process()
	{
		if (count($this->errors) > 0) {
			$this->response($this->errors, 400);
		}
		$this->response($this->send());
	}

	public function response($data, $status = 200)
	{
		http_response_code($status);
		header('Content-Type: application/json');
		die(json_encode($data));
	}

	public function validate(array $data = [])
	{
		foreach ($data as $key => $value) {
			$methodName = 'validate' . ucfirst($key);
			$this->data[$key] = $this->$methodName($value);
		}
	}

	public function validateName($name)
	{
		if (empty($name)) {
			$this->errors["name"] = "Имя должно быть заполнено";
			return false;
		}
		return filter_var($name, FILTER_SANITIZE_SPECIAL_CHARS);
	}

	public function validateEmail($email)
	{
		$mail = filter_var($email, FILTER_VALIDATE_EMAIL);
		if (!$mail) {
			$this->errors["email"] = "E-mail должен быть корректным e-mail адресом";
			return false;
		}
		return $mail;
	}

	public function validateTitle($title)
	{
		if (empty($title)) {
			$this->errors["title"] = "Тема должна быть указана";
			return false;
		}
		return filter_var($title, FILTER_SANITIZE_SPECIAL_CHARS);
	}

	public function validateMessage($content)
	{
		if (empty($content)) {
			$this->errors["message"] = "Сообщение не должно быть пустым";
			return false;
		}
		return filter_var($content, FILTER_SANITIZE_SPECIAL_CHARS);
	}

	public function getMessage()
	{
		return '<html>
					<head>
						<title>' . $this->subject . '</title>
					</head>
					<body>
						<table>
							<tr>
								<td>Тема:</td>
								<td>' . $this->data["title"] . '</td>
							</tr>
							<tr>
								<td>Имя:</td>
								<td>' . $this->data["name"] . '</td>
							</tr>
							<tr>
								<td>Email:</td>
								<td>' . $this->data["email"] . '</td>
							</tr>
							<tr>
								<td colspan="2">
									' . $this->data["content"] . '
								</td>
							</tr>
						</table>
					</body>
				</html>';
	}

	public function getHeaders()
	{
		$headers = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$headers .= 'From: Birthday Reminder <robot@poslogic.pro>' . "\r\n";
		$headers .= 'X-Mailer: PHP/' . phpversion();

		return $headers;
	}

	public function send()
	{
		return mail(
				$this->to, $this->subject, $this->getMessage(), $this->getHeaders()
		);
	}

}
