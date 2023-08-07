<?php
class quiz{
    function controllaDomanda($ID, $hash){
        if ($hash === md5($ID . 'roberta')){
            return true;
        }else{
            return false;
        }
    }
    
    function cifraCorretta($ID){
        return md5($ID . 'roberta');
    }

    function cifraNonCorretta($ID){
        return md5($ID . 'NONCORRETTA');
    }

    function showLatestLogs($limit = 5){
        global $core;
        global $db;
        global $user;
        global $URI;
        global $module;
        $limit = (int) $limit;

        $query = '
        SELECT *
        FROM ' . $db->prefix . 'quiz_logs
        WHERE user_ID = \'' . $user->ID .  '\'
        LIMIT ' . $limit . '
        ';

        
        $result = $db->query($query);

        $return = '';
        while ($row = mysqli_fetch_array($result)){
            if ($row['ok'] === ''){
                $percentuale = 0;
            }else if (!substr_count($row['ok'],'|')){
                $percentuale = 10;
            }else{
                $percentuale = (substr_count($row['ok'],'|') + 1) * 10;
            }
            $return .= '&bull; <a href="' . $URI->getBaseUri() . $module->routed . '/mylog/show/' . $row['ID'] . '/">' . $row['date'] . ': ' . $percentuale . '%</a> <br/>';
        }
        return $return;
    }

    function storeSession($type, $subtype = '', $ok, $ko, $blank){
        global $user;
        global $db;
        global $core;

        $subtype = (int) $subtype;

        $query = '
        INSERT INTO ' . $db->prefix . 'quiz_logs
        (user_ID, 
         date, 
         type,
         subtype,
         ok,
         ko, 
         blank)
        VALUES 
        (
        \'' . ($user->logged == true ? $user->ID : '-1') . '\',
        \'' . date('Y-m-d H:i:s') . '\',
        \''. $core->in($type, true). '\',
        \''. $subtype . '\',
        \'';

        foreach ($ok as $single){
            $query .= $single . '|';
        }
        if (count($ok) > 0)
            $query = substr($query, 0, -1);
        $query .= '\', \'';

        foreach ($ko as $single){
            $query .= $single . '|';
        }
        if (count($ko) > 0)
            $query = substr($query, 0, -1);
        $query .= '\', \'';

        foreach ($blank as $single){
            $query .= $single . '|';
        }
        if (count($blank) > 0)
            $query = substr($query, 0, -1);
        $query .= '\');';

        
        if ($db->query($query)){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Gets the most complex question
     *
     * Returns this array:
     * question => the question
     * cat_name => the full category name, ID "My test category"
     * cat_ID => the category's ID
     */
    function getTheMostComplex(){
        global $db;
        $query = '
        SELECT Q.ID, Q.domanda, (Q.ko / Q.views) * 100 AS percent, Q.categorie
        FROM ' . $db->prefix . 'quiz_questions AS Q
        WHERE Q.views > 50
        ORDER BY percent DESC
        LIMIT 1';

        
        if (!$result = $db->query($query)){
            return 'Query error. #1 ';
        }
        $row = mysqli_fetch_array($result);
        $question = $row['domanda'];
        $category = explode('|', $row['categorie']);
        $category = $category[0];

        $query = 'SELECT * FROM ' . $db->prefix . 'quiz_categories WHERE ID = \'' . $category . '\' LIMIT 1;';

        
        $result = $db->query($query);
        $row = mysqli_fetch_array($result);
        $cat_ID = $row['ID'];
        $cat_name = $row['nome'];

        return (array( 'question' => $question, 'cat_ID' => $cat_ID, 'cat_name' => $cat_name  ));
    }
    function getStatsByUser(){
        global $user;
        global $conf;
        global $db;
        global $core;

        if (!$user->logged)
            return false;
    }
}