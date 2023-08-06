<?php
/**
 * Copyright (C) Fabrizio Crisafulli 2012
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
namespace Crisasoft\FabCMS;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class email
{
    public $emailSubSystemEnabled = 1;

    protected $to;
    protected $toName;

    protected $from;
    protected $fromName;

    protected $reply;
    protected $replyName;

    protected $subject;
    protected $message;

    public  $method = 2; // 1 = PHP-Mailer, 2 = PHP's mail()

    public $smpt;
    private $port;
    private $username;
    private $password;
    private $auth;

    private $filePath = array();
    private $fileName = array();

    private $PHPMailer;

    public $lastError;

    public function __construct()
    {
        global $debug;
        global $conf;
        global $core;
        global $relog;

        (int) $core->getConfig('email', 'enableEmail') === 1 ? $this->emailSubSystemEnabled = 1
                                                                            : $this->emailSubSystemEnabled = 0;

        $this->method   = $core->getConfig('email', 'method');
        $this->smpt     = $core->getConfig('email', 'smtp');
        $this->port     = $core->getConfig('email', 'port');
        $this->username = $core->getConfig('email', 'username');
        $this->password = $core->getConfig('email', 'password');
        $this->auth     = $core->getConfig('email', 'auth');

        $this->from     = $core->getConfig('email', 'defaultEmail');
        $this->fromName = $core->getConfig('email', 'defaultEmailName');
        $this->reply    = $core->getConfig('email', 'replyEmail');

        if ( null !== !$core->getConfig('email', 'replyEmailName')) {
            $this->replyName = $conf['site']['name'];
        } else {
            $this->replyName = $core->getConfig('email', 'replyEmailName');
        }

        // If method is set to 1 we include PHP-mailer
        if ($this->method = 1) {
            require_once $conf['path']['baseDir'] . '/lib/PHP-Mailer/PHPMailer.php';
            require_once $conf['path']['baseDir'] . '/lib/PHP-Mailer/SMTP.php';
            require_once $conf['path']['baseDir'] . '/lib/PHP-Mailer/Exception.php';

            $this->PHPMailer = new PHPMailer(true);
            $this->PHPMailer->CharSet = 'UTF-8';
            $this->PHPMailer->IsSMTP();
            $this->PHPMailer->Host = $this->smpt;
            $this->PHPMailer->SMTPAuth = true;
            $this->PHPMailer->Username = $this->username;
            $this->PHPMailer->Password = $this->password;
            $this->PHPMailer->SMTPSecure = $this->auth;
            $this->PHPMailer->Port = $this->port;
        }
    }

    public function addTo($value, $name = null)
    {
        $this->to = filter_var($value, FILTER_SANITIZE_EMAIL);
        $this->toName = $name;
    }

    public function addFrom($value, $name = null)
    {
        $this->from = filter_var($value, FILTER_SANITIZE_EMAIL);

        switch ($this->method) {
            default:
            case 0: // mail() function, we have to escape the subject
                $this->fromName = $this->fixHeaders($name);
                break;
            case 1: // PHPMailer(), no need to fix the name
                $this->fromName = $name;
                break;

        }
    }

    /**
     * Replace any risk from string part of the headers.
     * @param $string
     *
     * @return mixed
     */
    public function fixHeaders($string)
    {
        $string = str_replace("\r", '', $string);
        $string = str_replace("\n", '', $string);
        return $string;
    }

    public function addReply($value, $name = null)
    {
        $this->reply = filter_var($value, FILTER_SANITIZE_EMAIL);

        switch ($this->method) {
            case 1: // PHPMailer(), no need to fix the name
                $this->replyName = $name;
                break;
            case 2: // mail() function, we have to escape the subject
                $this->replyName = $this->fixHeaders($name);
                break;
        }
    }

    public function addSubject($subject)
    {
        $this->subject = $subject;
    }

    public function addMessage($message)
    {
        $this->message = $message;
    }

    public function addFile($path, $name)
    {
        //@todo: check if this breach security subsystem.

        $this->filePath = $path;
        $this->fileName = $name;
    }

    public function fixTo($to)
    {
        // Removing anything not allowed
        $regex = '/(\"|\(|\)|,|:|;|<|>|@|\[|\\|\])/m';
        $to = preg_replace($regex, '', $to);

        return $to;
    }

    public function sendEmail()
    {
        global $debug;
        global $log;
        global $core;
        global $relog;

        if ($this->emailSubSystemEnabled !== 1) {
            $this->lastError = 'System disabled by configuration directive.';
            $relog->write(['type'      => '1',
                           'module'    => 'EMAIL',
                           'operation' => 'email_send_email_subsystem_disabled',
                           'details'   => 'Cannot send the email. The subsystem looks like disabled. ',
            ]);

            return false;
        }

        if (empty($this->to) || !filter_var($this->to, FILTER_VALIDATE_EMAIL)) {
            $debug->write('error', 'The email didn\'t pass the filter. Email is ' . htmlentities($this->to));
            return false;
        }


        switch ($this->method) {
            default:
            case 0:
                /*
                 * Using built in PHP mail() function;
                 */
                $relog->write(['type'      => '1',
                               'module'    => 'EMAIL',
                               'operation' => 'email_send_phpmail_init',
                               'details'   => 'Init email send by using PHP Mail() standard function.',
                ]);

                if (empty($this->subject))
                    $this->subject = 'Mail from FabCMS';

                if (empty($this->from)) {
                    $debug->write('error', 'Cannot send the email without subject', 'EMAIL');
                    return false;
                }

                // We have to check the "from" address
                $this->from = $this->fixHeaders($this->from);

                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=utf-8" . "\r\n";
                $headers .= 'From: ' . $this->from . "\r\n" .
                    'X-Mailer: FabCMS mail subsystem';

                // Sends the mail
                if (!mail($this->to, $this->subject, $this->message, $headers)) {

                    $relog->write(['type'      => '4',
                                   'module'    => 'EMAIL',
                                   'operation' => 'email_send_email_phpmail_error',
                                   'details'   => 'Error sending mail via mail(). FROM: ' . $this->from .
                                                  '; REPLY: ' . $this->reply .
                                                  '; TO: ' . $this->to .
                                                  '; SUBJECT: ' . $this->subject .
                                                  '; MESSAGE: ' . $this->message ,
                    ]);

                    $debug->write('error', 'MAIL', 'Unable to send the email');
                    return false;
                } else {

                    $relog->write(['type'      => '1',
                                   'module'    => 'EMAIL',
                                   'operation' => 'email_send_email_phpmail_pk',
                                   'details'   => 'Mail sent using PHP built-in mail(). FROM: ' . $this->from .
                                                  '; REPLY: ' . $this->reply .
                                                  '; TO: ' . $this->to .
                                                  '; SUBJECT: ' . $this->subject .
                                                  '; MESSAGE: ' . $this->message ,
                    ]);


                    $log->write('mail_sent', 'Mail sent via mail(). FROM: ' . $this->from .
                        '; REPLY: ' . $this->reply .
                        '; TO: ' . $this->to .
                        '; SUBJECT: ' . $this->subject .
                        '; MESSAGE: ' . $this->message .
                        '; ERROR: ' . $this->PHPMailer->ErrorInfo, 'EMAIL');

                    $debug->write('error', 'MAIL', 'Mail sent');
                    return true;
                }
                break;
            case 1:
                /*
                 * Using built PHPMailer;
                 */

                $relog->write(['type'      => '1',
                               'module'    => 'EMAIL',
                               'operation' => 'email_send_phpmailer_init',
                               'details'   => 'Init email send by using PHPMailer class. SMTP server is: ' . $this->smpt , ', user: ' . $this->username,
                ]);

                $this->PHPMailer->From = $this->from;
                $this->PHPMailer->FromName = $this->fromName;
                $this->PHPMailer->AddAddress($this->to, $this->toName); // Add a recipient
                $this->PHPMailer->AddReplyTo($this->reply);
                $this->PHPMailer->WordWrap = 100; // Set word wrap to 100 characters
                $this->PHPMailer->IsHTML(true); // Set email format to HTML

                $this->PHPMailer->Subject = $this->subject;
                $this->PHPMailer->Body = $this->message;
                $this->PHPMailer->AltBody = 'Please use an HTML viewer to read the email';

                if ( (int) $core->getConfig('email', 'bypassPeerVerify') === 1 ){

                    $relog->write(['type'      => '0',
                                   'module'    => 'EMAIL',
                                   'operation' => 'email_send_phpmailer_bypass_peer_verify',
                                   'details'   => 'Asking to PHPMailer to bypass peer verify.',
                    ]);

                    $this->PHPMailer->SMTPOptions = array(
                        'ssl' => array(
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        )
                    );
                }

                if (count($this->filePath) > 0) {
                    for ($i = 0; $i < count($this->filePath); $i++) {
                        $this->PHPMailer->AddAttachment($this->filePath[$i], $this->fileName[$i]); // Optional name
                        $i++;
                    }
                }

                try {
                    if ($this->PHPMailer->Send()){

                        $relog->write(['type'      => '1',
                                       'module'    => 'EMAIL',
                                       'operation' => 'email_send_email_phpmailer_ok',
                                       'details'   => 'Mail sent via PHP mailer. ',
                        ]);
                        return true;
                    } else {

                        $relog->write(['type'      => '4',
                                       'module'    => 'EMAIL',
                                       'operation' => 'email_send_email_phpmailer_generic_error',
                                       'details'   => 'Cannot send email. Generic (no exception) error was raised. ' ,
                        ]);
                        return false;
                    }

                } catch (Exception $e){

                    $relog->write(['type'      => '4',
                                   'module'    => 'EMAIL',
                                   'operation' => 'email_send_email_phpmailer_error',
                                   'details'   => 'Unable to send email. Error is: ' . $e->errorMessage(),
                    ]);
                    $this->lastError = $e->errorMessage();
                    return false;
                } catch (\Exception $e){
                    $relog->write(['type'      => '4',
                                   'module'    => 'EMAIL',
                                   'operation' => 'email_send_email_phpmailer_error',
                                   'details'   => 'Unable to send email. Error is: ' . $e->errorMessage(),
                    ]);
                    $this->lastError = $e->errorMessage();
                    return false;
                }
                break;

        }
    }
}