<?php
namespace CrisaSoft\FabCMS;

class user
{
    public $ID;
    public $username;
    public $articleSignature;
    public $name;
    public $surname;
    public $birthdate;
    public $shortBiography;
    public $biography;
    public $profile_privacy_level;
    public $isGuest;
    public $isAdmin;
    public $isBot   = 0;
    public $email;
    public $group;
    public $sessionID;
    public $logged = false;
    public $groupType;

    /**
     * Permissions matrix
     * @param $action
     */
    public function can($action){

    }

    public function __construct()
    {
        // Check if the visit comes from a bot
        if ( isset($_SERVER['HTTP_USER_AGENT']) && preg_match('#bot|crawl|spider|mediapartner|slurp|teoma|yandex|yeti|mediapartners|insights#i', $_SERVER['HTTP_USER_AGENT'])){
            $this->isBot = true;
        } else {
            $this->isBot = false;
        }
    }

    /*
     * Try to login
     */
    public function login($email, $password)
    {
        global $db;
        global $conf;
        global $log;

        $query = '
        SELECT ID
        FROM ' . $db->prefix . 'users
        WHERE email = \'' . $email . '\'
            AND password = \'' . $this->getPasswordHash($password) . '\'
        LIMIT 1;';

        $db->setQuery($query);
        $result = $db->executeQuery('select');

        if (!$db->numRows) {
            $this->logged = false;
            $log->write('login_failed', 'LOGIN', 'email:' . $email);
            return false;
        }

        $row = mysqli_fetch_assoc($result);
        $this->getUserData($row['ID']);

        $sessionHash = $this->createSessionHash($row['ID']);

        $query = '
        INSERT INTO ' . $db->prefix . 'sessions
        (
            user_ID, 
            hash, 
            start, 
            end
        )
        VALUES
        (
            \'' . $row['ID'] . '\',
            \'' . $sessionHash . '\',
            NOW(),
            DATE_ADD( NOW(), INTERVAL 30 DAY)
        );';

        $db->setQuery($query);
        $db->executeQuery('insert');

        if (!setcookie('ID', $row['ID'], time() + 60 * 60 * 24 * 30, '/')) {
            $this->logged = false;
            echo 'Unable to create the cookie ID. <br/>';
        }

        if (!setcookie('hash', $sessionHash, time() + 60 * 60 * 24 * 30, '/')) {
            $this->logged = false;
            echo 'Unable to create the cookie hash. <br/>';
        }

        if (!setcookie('secureHash', md5($row['ID'] . $conf['security']['siteKey']), time() + 60 * 60 * 24 * 30, '/')) {
            $this->logged = false;
            echo 'Unable to create the cookie secureHash. <br/>';
        }

        $log->write('login', 'LOGIN', 'email:' . $email);
        return true;
    }

    private function createSessionHash($ID)
    {
        global $conf;
        return md5(microtime() . $conf['security']['siteKey']);
    }

    private function getUserData(int $ID)
    {
        global $db;
        global $debug;
        global $conf;
        global $core;

        $query = '
        SELECT
        	U.ID as ID,
            U.username AS username,
            U.article_signature AS article_signature,
            U.name AS name,
            U.surname AS surname,
            U.birthdate AS birthdate,
            U.privacy_profile_level AS profile_privacy_level,
            U.short_biography AS short_biography,
            U.biography AS biography,
            U.email AS email,
            U.enabled AS enabled,
            U.admin AS admin,
            G.group_type
        FROM ' . $db->prefix . 'users as U
        LEFT JOIN ' . $db->prefix . 'users_groups AS G
            ON U.group_ID = G.ID
        WHERE U.ID = \'' . $ID . '\'
        LIMIT 1;';

        $db->setQuery($query);

        if (!$result = $db->executeQuery('select')){
            echo 'Query error while getting user. ';
            return;
        }

        $row = mysqli_fetch_assoc($result);

        if ( (int) $row['group_type'] === 1 ) {
            $debug->write('info', 'Administrator user logged.', 'USER');
            $this->isAdmin = true;
        }

        $this->ID                       =   (int) $row['ID'];
        $this->username                 =   $row['username'];
        $this->articleSignature         =   $row['article_signature'];
        $this->email                    =   $row['email'];
        $this->groupType                =   $row['groupType'];
        $this->name                     =   $row['name'];
        $this->surname                  =   $row['surname'];
        $this->birthdate                =   $row['birthdate'];
        $this->shortBiography           =   $row['short_biography'];
        $this->biography                =   $row['biography'];
        $this->profile_privacy_level    =   (int) $row['profile_privacy_level'];

        $this->logged = true;

        $core->jsVar['fabcms_userUsername'] = '"' . $row['username'] . '"';
        $core->jsVar['fabcms_userLogged'] = 'true';
        $core->jsVar['fabcms_userID'] = (int) $row['ID'];

        return true;
    }

    function checkLogin()
    {
        global $debug;
        global $core;
        global $conf;
        global $db;
        global $log;
        global $template;

        // Reads cookie to start session.
        if (!isset($_COOKIE['ID']) || !isset($_COOKIE['hash']) || !isset($_COOKIE['secureHash'])) {
            // No cookie. First visit? First login?
            $debug->write('info', 'No cookie, destroying $user', 'USER');
            unset ($user); // Security check
            $this->logged = false;
        } else {
            $cookie_ID = (int)$_COOKIE['ID'];
            $cookie_hash = $core->in($_COOKIE['hash'], false);
            $cookie_secureHash = $core->in($_COOKIE['secureHash']);

            $debug->write('info', 'Reading login cookie. ID:' . $cookie_ID . ', hash: ' . $cookie_hash, 'USER');
            $query = '
                SELECT
                    u.ID,
                    u.enabled,
                    s.user_ID
                FROM ' . $conf['db']['prefix'] . 'users AS u
                LEFT JOIN ' . $conf['db']['prefix'] . 'sessions AS s
                        ON u.ID = s.user_ID
                WHERE
                        u.ID = \'' . $cookie_ID . '\'
                        AND s.hash = \'' . $cookie_hash . '\'
                LIMIT 1
                ';

            $db->setQuery($query);
            $result = $db->executeQuery();

            if (!$db->numRows) {
                $this->logged = false;
                $log->write('login_failed_with_hack', 'LOGIN', 'cookieID: ' . $cookie_ID . ', cookieHash: ' . $cookie_hash . ', cookieSecureHash: ' . $cookie_secureHash);

                /* Not now, John */
                $debug->write('warn', 'No match between session ID and hash. Hacking?', 'USER');
                $debug->write('info', $query, 'USER');
                setcookie("ID", "", time() - 3600);
                setcookie("hash", "", time() - 3600);
                setcookie("secureHash", "", time() - 3600);
                unset ($user);
            } else {
                $debug->write('Info', 'User, cookie and session pairing went ok. Message from user class: the property logged felt alone until now', 'USER');
                $this->getUserData($cookie_ID);

                // Extra security check. We store, inside the cookie named secureHash, a MD5 value based on the ID + the site's secret key
                if ($cookie_secureHash !== md5($this->ID . $conf['security']['siteKey'])) {
                    $this->logged = false;
                    $debug->write('warn', 'No match between ID and secureHash. Hacking?', 'USER');
                    unset ($user);
                    setcookie("ID", "", time() - 3600);
                    setcookie("hash", "", time() - 3600);
                    setcookie("secureHash", "", time() - 3600);
                }
            }
        }
    }

    /**
     * @param $user user be parsed with $core->in($usern)
     *
     * @return bool
     */
    function checkIfUserExists($user)
    {
        global $db;

        $query = '
        SELECT ID
        FROM ' . $db->prefix . 'users
        WHERE username = \'' . $user . '\'
        ';

        $db->setQuery($query);
        $db->executeQuery();
        if (!$db->numRows) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Check if email is present in database.
     *
     * @param $email
     *
     * @return bool
     */
    function checkIfEmailExists($email)
    {
        global $db;

        $query = '
        SELECT ID
        FROM ' . $db->prefix . 'users
        WHERE email = \'' . $email . '\'
        ';

        $db->setQuery($query);
        $db->executeQuery();
        if (!$db->numRows) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Add a user to database. Please note that no check will be made, just for example a check for another
     * existing username.
     * Password will be secured with hash creation
     *
     * @param     $username
     * @param     $password
     * @param     $email
     * @param int $userGroup
     * @param int $enabled
     *
     * @return bool
     */
    function addUserToDb($username, $password, $email, $newsletter, $name, $surname, $userGroup = 2, $enabled = 0)
    {
        global $db;
        global $debug;
        global $core;
        global $log;

        $optinHash = md5(microtime(true));

        $query = '
        INSERT INTO ' . $db->prefix . 'users
        (enabled, username, password, email, `name`, surname, group_ID, registration_IP, registration_date, newsletter, optin_hash)
        VALUES(
        \'' . $enabled . '\',
        \'' . $username . '\',
        \'' . $this->getPasswordHash($password) . '\',
        \'' . $email . '\',
        \'' . $name . '\',
        \'' . $surname . '\',
        \'' . $userGroup . '\',
        \'' . $core->in($_SERVER['REMOTE_ADDR']) . '\',
        NOW(),
        \'' . $newsletter . '\',
        \'' . $optinHash . '\'
        );
        ';

        $db->setQuery($query);

        if ($db->executeQuery('insert')) {
            $returnArray['ID'] = $db->lastInsertID;
            $returnArray['hash'] = $optinHash;

            $log->write('add_user', 'LOGIN', 'username:' . $username, ', email: ' . $email . ', ID: ' . $db->lastInsertID);
            $debug->write('info', 'User was pushed to DB', 'USER');

            return $returnArray;
        } else {
            $debug->write('warning', 'Unable to insert user: ' . $query, 'USER');
            return false;
        }
    }

    function sendResetPasswordEmail($email, $username, $hash)
    {
        global $conf;
        global $URI;
        global $language;
        global $debug;
        global $fabmail;
        global $log;
        global $core;
        global $relog;


        $message = $language->get('user', 'resetPasswordBody');

        // Process the message
        $confirmLink = $URI->getBaseUri() . '/user/reset_password/verify/' . $hash . '/';

        $message = str_replace('--THELINK--', $confirmLink, $message);
        $message = str_replace('--SITENAME--', $conf['site']['name'], $message);

        // Mail it
        $fabmail->addFrom($core->getConfig('email', 'replyEmail'), $conf['site']['name'] . ', ' .$language->get('user','userEmailFrom')); // We use the global reply name, such as noreply@domain.tld
        $fabmail->addSubject($language->get('user', 'userResetPasswordEmailSubject') . ' ' .  $conf['site']['name']);
        $fabmail->addTo($email, $username);
        $fabmail->addMessage($message);

        if (!$fabmail->sendEmail()) {

            $relog->write(['type' => '3', 'module' => 'USER', 'operation' => 'sendResetPasswordEmail', 'details' => $fabmail->lastError]);

            $debug->write('error','Unable to send reset email to ' . $email . ' with username ' . $username);
            return false;
        } else {

            $relog->write(['type' => '1', 'module' => 'USER', 'operation' => 'sendResetPasswordEmail', 'details' => 'Email: ' . $email . ', Username: ' . $username]);


            $debug->write('info','Sent reset email to ' . $email . ' with username ' . $username);
            return true;
        }
    }

    /*
     * This method is used to (re)send the confirmation email by fetching both username and email from the passed ID
     */
    function sendConfirmationEmail($userID, $hashOptin)
    {
        global $db;
        global $core;
        global $debug;
        global $language;
        global $URI;
        global $conf;
        global $fabmail;
        global $relog;

        $userID = (int) $userID;

        $relog->write(['type'      => '1',
            'module'    => 'user',
            'operation' => 'user_register_send_email_entry',
            'details'   => 'Attempting to send email. Using method' . $fabmail->method . ' and using server ' . $this->smpt ,
        ]);


        $query = '
        SELECT username, email, ID
          FROM ' . $db->prefix . 'users
        WHERE ID = \'' . $userID . '\'
        LIMIT 1;
        ';
        $db->setQuery($query);

        if (!$db->executeQuery('select')) {

            $relog->write(['type' => '4', 'module' => 'USER', 'operation' => 'send_confermation_email_query_error', 'details' => 'Query error in select phase. ' . $query]);

            $debug->write('warning', 'Unable to query user (' . $userID . ')', 'USER');
            return false;
        }

        if (!$db->numRows) {
            echo 'Internal error. No match with database. Please contact our staff';

            $relog->write(['type' => '4', 'module' => 'USER', 'operation' => 'send_confermation_email_no_rows', 'details' => 'SELECT error. ' . $query]);

            return false;
        }

        $row = $db->getResultAsArray();

        $ID = $row['ID'];
        $emailAddress = $row['email'];
        $username = $row['username'];

        $relog->write(['type' => '0', 'module' => 'USER', 'operation' => 'sendConfirmationEmail', 'details' => 'Checking for custom optin. ' . $conf['user']['optinEmail']]);

        // Email creation
        if ( strlen( $core->getConfig('user','optinEmail', 'extended_value') ) > 0 ) {
            $message = '<html><body>' . $core->getConfig('user','optinEmail', 'extended_value') . '</body></html>';

            $relog->write(['type' => '0', 'module' => 'USER', 'operation' => 'sendConfirmationEmail', 'details' => 'Checking for custom optin. ' . 'Using optin from body. Lenght is:' . strlen($message)]);

        } else {
            if ($language->stringExists('user', 'optinEmail')) {
                $message = $language->get('user', 'optinEmail');
                $relog->write(['type' => '1', 'module' => 'USER', 'operation' => 'sendConfirmationEmail', 'details' => 'Using optin from language. Lenght is:' . strlen($message)]);
            } else {
                $debug->write('warning', 'Using FabCMS default email body.', ' USER');
                $message = '<html>
                                <body>Welcome.
                                We are sending this message because you have requested a registration.
                                To finalize this process please click <a href="--THELINK--">this link</a>
                                </body>
                            </html>';
                $relog->write(['type' => '1', 'module' => 'USER', 'operation' => 'sendConfirmationEmail', 'details' => 'Using default optin. This is not optimal. Lenght is:' . strlen($message)]);

            }
        }

        // Process the message
        $confirmLink = $URI->getBaseUri() . 'user/confirm/' . $ID . '/' . $hashOptin . '/';

        $message = str_replace('--THELINK--', $confirmLink, $message);
        $message = str_replace('--USERNAME--', $username, $message);
        $message = str_replace('--SITENAME--', $conf['site']['name'], $message);

        $relog->write(  array('type' => '1', 'module' => 'USER', 'operation' => 'sendConfirmationEmail', 'details' => 'Message lenght after update is:' . strlen($message)));

        // Mail it
        $fabmail->addFrom( $core->getConfig('email', 'replyEmail'), $conf['site']['name'] . ', ' . $language->get('user', 'userEmailFrom'));  // We use the global reply name, such as noreply@domain.tld
        $fabmail->addSubject($language->get('user', 'userEmailSubjectRegistrationTo') . ' ' . $conf['site']['name']);
        $fabmail->addTo($emailAddress, $username);
        $fabmail->addMessage($message);

        if (!$fabmail->sendEmail()) {
            $relog->write(  array('type' => '4', 'module' => 'USER', 'operation' => 'user_sendmail_error', 'details' => 'Error reported is: ' . $fabmail->lastError));

            return false;
        } else {
            return true;
        }
    }

    function updateTags(int $user_ID, array $tags) :int
    {
        global $db;
        global $relog;
        global $core;

        // Delete already set tags
        $query = 'DELETE FROM ' . $db->prefix . 'users_tags 
                  WHERE user_ID = ' . $user_ID;

        $db->setQuery($query);

        if (!$db->executeQuery('delete')) {
            echo $query;
            return -1;
        }

        $query = 'INSERT INTO ' . $db->prefix . 'users_tags 
        (
        user_ID, 
        tag
        )
        VALUES
        
        ';

        foreach ($tags as $singleTag){
            $singleTag = $core->in($singleTag, true);
            $query .= "($user_ID, '$singleTag'), ";
        }

        $query =substr ($query, 0, -2);

        $db->setQuery($query);

        if (!$db->executeQuery('insert')){
            echo $query;
            return -2;
        }

        return true;

    }

    function getPasswordHash($string)
    {
        global $conf;
        return md5($string . $conf['security']['siteKey']);
    }
}