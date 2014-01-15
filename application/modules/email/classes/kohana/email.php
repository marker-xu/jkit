<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Email message building and sending.
 *
 * @package    Kohana
 * @category   Email
 * @author     Kohana Team
 * @copyright  (c) 2007-2011 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Kohana_Email {

	// Current module version
	const VERSION = '1.0.0';

	/**
	 * @var  object  Swiftmailer instance
	 */
	protected static $_mailer;

	/**
	 * Creates a SwiftMailer instance.
	 *
	 * @return  object  Swift object
	 */
	public static function mailer()
	{
		if ( ! Email::$_mailer)
		{
			// Load email configuration, make sure minimum defaults are set
			$config = JKit::config('email')->as_array() + array(
				'driver'  => 'native',
				'options' => array(),
			);

			// Extract configured options
			extract($config, EXTR_SKIP);
			$transport = new PHPMailer();
			$transport->CharSet = Kohana::$charset;
			$transport->SMTPAuth = true;
			if ($driver === 'smtp')
			{
				// Create SMTP transport
				
				$transport->IsSMTP();
				$transport->Host = $options['hostname'];

				if (isset($options['port']))
				{
					// Set custom port number
					$transport->Port = $options['port'];
				}

				if (isset($options['encryption']))
				{
					// Set encryption
					$transport->setEncryption($options['encryption']);
				}

				if (isset($options['username']))
				{
					// Require authentication, username
					$transport->Username = $options['username'];
				}

				if (isset($options['password']))
				{
					// Require authentication, password
					$transport->Password = $options['password'];
				}

				if (isset($options['timeout']))
				{
					// Use custom timeout setting
					$transport->Timeout = $options['timeout'];
				}
			}
			elseif ($driver === 'sendmail')
			{
				// Create sendmail transport
				$transport->IsSendmail();

				if (isset($options['command']))
				{
					// Use custom sendmail command
					$transport->Sendmail = $options['command'];
				}
			}
			else
			{
				// Create native transport
				$transport = Swift_MailTransport::newInstance();

				if (isset($options['params']))
				{
					// Set extra parameters for mail()
					$transport->setExtraParams($options['params']);
				}
			}

			// Create the SwiftMailer instance
			Email::$_mailer = $transport;
		}

		return Email::$_mailer;
	}

	/**
	 * Create a new email message.
	 *
	 * @param   string  message subject
	 * @param   string  message body
	 * @param   string  body mime type
	 * @return  Email
	 */
	public static function factory($subject = NULL, $message = NULL, $type = NULL)
	{
		return new Email($subject, $message, $type);
	}

	private $subject;
	
	private $message;
	
	private $type;
	
	/**
	 * Initialize a new Swift_Message, set the subject and body.
	 *
	 * @param   string  message subject
	 * @param   string  message body
	 * @param   string  body mime type
	 * @return  void
	 */
	public function __construct($subject = NULL, $message = NULL, $type = NULL)
	{
		// Create a new message, match internal character set
		$this->subject = $subject;
		
		$this->type = $type;
		
		$this->message = $message;
		
		if ($subject)
		{
			// Apply subject
			$this->subject($subject);
		}

		if ($message)
		{
			// Apply message, with type
			$this->message($message, $type);
		}
	}

	/**
	 * Set the message subject.
	 *
	 * @param   string  new subject
	 * @return  Email
	 */
	public function subject($subject)
	{
		// Change the subject
		$this->subject = $subject;
	}

	/**
	 * Set the message body. Multiple bodies with different types can be added
	 * by calling this method multiple times. Every email is required to have
	 * a "text/plain" message body.
	 *
	 * @param   string  new message body
	 * @param   string  mime type: text/html, etc
	 * @return  Email
	 */
	public function message($body, $type = NULL)
	{
		if ( ! $type OR $type === 'text/plain')
		{
			// Set the main text/plain body
			$this->message = $body;
			$this->type = 'text/plain';
		}
		else
		{
			// Add a custom mime type
			$this->type = $type;
			$this->message = $body;
		}

	}

	/**
	 * Add one or more email recipients..
	 *
	 *     // A single recipient
	 *     $email->to('john.doe@domain.com', 'John Doe');
	 *
	 *     // Multiple entries
	 *     $email->to(array(
	 *         'frank.doe@domain.com',
	 *         'jane.doe@domain.com' => 'Jane Doe',
	 *     ));
	 *
	 * @param   mixed    single email address or an array of addresses
	 * @param   string   full name
	 * @param   string   recipient type: to, cc, bcc
	 * @return  Email
	 */
	public function to($email, $name = NULL, $type = 'to')
	{
		if (is_array($email))
		{
			foreach ($email as $key => $value)
			{
				if (ctype_digit((string) $key))
				{
					// Only an email address, no name
					self::mailer()->AddAddress($value);
				}
				else
				{
					// Email address and name
					self::mailer()->AddAddress($key, $value);
				}
			}
		}
		else
		{
			// Call $this->_message->{add$Type}($email, $name)
			self::mailer()->AddAddress($email, $name);
		}
	}

	/**
	 * Add a "carbon copy" email recipient.
	 *
	 * @param   string   email address
	 * @param   string   full name
	 * @return  Email
	 */
	public function cc($email, $name = NULL)
	{
		return self::mailer()->AddCC($email, $name);
	}

	/**
	 * Add a "blind carbon copy" email recipient.
	 *
	 * @param   string   email address
	 * @param   string   full name
	 * @return  Email
	 */
	public function bcc($email, $name = NULL)
	{
		return self::mailer()->AddBCC($email, $name);
	}

	/**
	 * Add email senders.
	 *
	 * @param   string   email address
	 * @param   string   full name
	 * @param   string   sender type: from, replyto
	 * @return  Email
	 */
	public function from($email, $name = NULL, $type = 'from')
	{
		// Call $this->_message->{add$Type}($email, $name)
		return self::mailer()->SetFrom($email, $name);
	}

	/**
	 * Add "reply to" email sender.
	 *
	 * @param   string   email address
	 * @param   string   full name
	 * @return  Email
	 */
	public function reply_to($email, $name = NULL)
	{
		return self::mailer()->AddReplyTo($email, $name);
	}

	/**
	 * Add actual email sender.
	 *
	 * [!!] This must be set when defining multiple "from" addresses!
	 *
	 * @param   string   email address
	 * @param   string   full name
	 * @return  Email
	 */
	public function sender($email, $name = NULL)
	{
		return;
	}

	/**
	 * Set the return path for bounce messages.
	 *
	 * @param   string  email address
	 * @return  Email
	 */
	public function return_path($email)
	{
		return;
	}

	/**
	 * Access the raw [Swiftmailer message](http://swiftmailer.org/docs/messages).
	 *
	 * @return  Swift_Message
	 */
	public function raw_message()
	{
		return ;
	}

	/**
	 * Attach a file.
	 *
	 * @param   string  file path
	 * @return  Email
	 */
	public function attach_file($path)
	{
		return self::mailer()->AddAttachment($path);
	}

	/**
	 * Attach content to be sent as a file.
	 *
	 * @param   binary  file contents
	 * @param   string  file name
	 * @param   string  mime type
	 * @return  Email
	 */
	public function attach_content($data, $file, $mime = NULL)
	{
		if ( ! $mime)
		{
			// Get the mime type from the filename
			$mime = File::mime_by_ext(pathinfo($file, PATHINFO_EXTENSION));
		}
		return  self::mailer()->AddStringAttachment($data, $file, 'base64', $mime);
	}

	/**
	 * Send the email. Failed recipients can be collected by passing an array.
	 *
	 * @param   array   failed recipient list, by reference
	 * @return  boolean
	 */
	public function send(array & $failed = NULL)
	{
		$phpmailerlite = Email::mailer();
		$phpmailerlite->Subject = $this->subject;
		$phpmailerlite->Body = $this->message;
		$phpmailerlite->IsHTML($this->type=="text/html");
		
		$res = $phpmailerlite->Send();
		$failed[] = $phpmailerlite->ErrorInfo;
		return $res;
	}

} // End email
