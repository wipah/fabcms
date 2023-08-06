<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 10/02/2016
 * Time: 23:37
 */
namespace CrisaSoft\FabCMS;

final class prestashop extends \CrisaSoft\FabCMS\connector{

    var $username;
    var $password;
    var $database;
    var $server;
    var $prefix;
    var $key;

    var $connectorData;
    
    function __construct($additionalData){
        global $log;

        if (!isset($additionalData->key)){
            $log->write('error', 'HANDLER', 'Prestashop handler. Key is not passed. Aborting');
            return;
        }
        $this->key = $additionalData->key;
        
        if (!isset($additionalData->server)){
            $log->write('error', 'HANDLER', 'Prestashop handler. Host is not passed. Aborting');
            return;
        }
        $this->server = $additionalData->server;

        if (!isset($additionalData->username)){
            $log->write('error', 'HANDLER', 'Prestashop handler. Username is not passed. Aborting');
            return;
        }
        $this->username = $additionalData->username;
        
        if (!isset($additionalData->password)){
            $log->write('error', 'HANDLER', 'Prestashop handler. Password is not passed. Aborting');
            return;
        }
        $this->password = $additionalData->password;
        
        if (!isset($additionalData->database)){
            $log->write('error', 'HANDLER', 'Prestashop handler. Database is not passed. Aborting');
            return;
        }
        $this->database = $additionalData->database;
        
        if (!isset($additionalData->prefix)){
            $log->write('error', 'HANDLER', 'Prestashop handler. Prefix is not passed. Aborting');
            return;
        }
        $this->prefix = $additionalData->prefix;
    }

    function refreshUser(){
        global $user;
        global $log;

        $plainPassword = $this->connectorData['plainPassword'];

        // Check if user is stored inside the database

        // If the user is stored take the ID

        // Delete user and user_group

        // Insert new user

    }
    function addUser(){
        global $log;
        global $core;

        if (!$mysqli = new \mysqli($this->server, $this->username, $this->password, $this->database)){
            $log->write('error','HANLDER', 'Unable to connect to database. Username: ' . $this->username .
                ', password: ' . $this->password .
                ', server: ' . $this->server .
                ', database:' . $this->database);
            return;
        }

        $query = 'INSERT INTO ' .($core->in($this->prefix)) . 'customer
          (
          firstname,
          lastname,
          optin,
          active,
          id_lang,
          id_shop,
          id_shop_group,
          id_default_group,
          last_passwd_gen,
          id_risk,
          date_add,
          date_upd,
          secure_key,
          email,
          `passwd`
          )

          VALUES

          (
          \'' . $this->connectorData['name'] . '\',
          \'' . $this->connectorData['surname'] . '\',
          0,
          0,
          1,
          1,
          1,
          3,
          \'' . date('Y-m-d H:i:s') . '\',
          0,
          \'' . date('Y-m-d H:i:s') . '\',
          \'' . date('Y-m-d H:i:s') . '\',
          \'' . md5(uniqid(rand(), true)) . '\',
          \'' . $core->in($this->connectorData['email']) .'\',
          \'' . md5($this->key . $this->connectorData['password']) . '\'
          )

          ';

        if (!$result = $mysqli->query($query)){
            $log->write('error', 'HANDLER', 'Prestashop. Unable to store new customer. ' . $mysqli->error . ' '. $query );
        }else {
            $ID = $mysqli->insert_id;
            $log->write('info', 'HANDLER' . 'Prestashop. User stored. The prestashop ID is .');
        }

        //
        $query = 'INSERT INTO ' .($core->in($this->prefix)) . 'customer_group
        (id_customer, id_group)
        VALUES
        (
        '. $ID .',
        \'3\'
        );
        ';

        if (!$result = $mysqli->query($query)){
            $log->write('error', 'HANDLER', 'Prestashop. Unable to store new customer group. ' . $mysqli->error . ' '. $query );
        }else {
            $ID = $mysqli->insert_id;
            $log->write('info', 'HANDLER' . 'Prestashop. User group stored. The prestashop grupo ID is 3 and the row ID is .' . $mysqli->insert_id);
        }

    }

    function confirmUser(){
        global $log;
        global $core;

        $email = $core->in($this->connectorData['email']);

        if (!$mysqli = new \mysqli($this->server, $this->username, $this->password, $this->database)){
            $log->write('error','HANLDER', 'Unable to connect to database. Username: ' . $this->username .
                ', password: ' . $this->password .
                ', server: ' . $this->server .
                ', database:' . $this->database);
            return;
        }

        $query = 'UPDATE ' . ($core->in($this->prefix))  . 'customer
        SET optin = 1,
            active = 1
        WHERE email = \'' . $core->in($email) . '\'
        LIMIT 1;
        ';

        if (!$result = $mysqli->query($query)){
            $log->write('error', 'HANDLER', 'Prestashop. Unable to store new customer. ' . $mysqli->error . ' '. $query );
        }else {
            $log->write('info', 'HANDLER' , 'Prestashop. Confirmed. ' . $query);
        }
    }

    function updatePassword(){
        global $log;
        global $core;

        $email = $core->in($this->connectorData['email']);

        if (!$mysqli = new \mysqli($this->server, $this->username, $this->password, $this->database)){
            $log->write('error','HANLDER', 'Unable to connect to database. Username: ' . $this->username .
                ', password: ' . $this->password .
                ', server: ' . $this->server .
                ', database:' . $this->database);
            return;
        }

        $query = 'UPDATE ' . $core->in($this->prefix) . 'customer
        SET password = ' . md5($this->key . $this->connectorData['password']) . '
        WHERE email = \'' . $core->in($email) . '\'
        LIMIT 1;
        ';

        if (!$result = $mysqli->query($query)){
            $log->write('error', 'HANDLER', 'Prestashop. Unable to update the password new customer. ' . $mysqli->error . ' '. $query );
        }else {
            $log->write('info', 'HANDLER' , 'Prestashop. Password update. ' . $query);
        }
    }
}