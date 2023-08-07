<?php
/**
 * Copyright (C) Fabrizio Crisafulli 2012

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

if (!isset($path[3])){
    echo $language->get('user','userManagementNoUserPassed');
    return;
}

$userID = (int) $path[3];

$template->navBarAddItem($language->get('user', 'userManagement'),$URI->getBaseUri() . $this->routed . '/' );
$template->navBarAddItem($language->get('user', 'userShowInfoUser'));

$query = 'SELECT * 
          FROM ' . $db->prefix . 'users 
          WHERE ID = \'' . $userID . '\' LIMIT 1';

$this->addMeta('og:type', 'profile');

if (!$result = $db->query($query))
{
    echo $template->getCustomBox(['title'   => 'Query error',
                                  'message' => 'Query error in user selection.',
                                  'class' => 'danger']);

    if ($user->isAdmin)
        echo $query;
    return;
}

if (!$db->affected_rows)
{
    echo $language->get('user','userCannotFindUser');
    return;
}

$row = mysqli_fetch_assoc($result);

$this->addTitleTag(  sprintf($language->get('user', 'showUserProfileTitle'), $row['username']) );

/*
 *  Privacy options
 * ================================================
 *  1 = visible
 *  2 = visible but no search engines
 *  3 = Visible, no search engines and also crypted
 *  4 = Hidden
 */

if ( (int) $row['privacy_profile_level'] === 4){
    echo $language->get('user', 'showUserCannotDisplayUserDuePrivacyRestrictions');
    return;
}

if ( (int) $core->getConfig( 'user', 'allowPublicProfiles' ) !== 1 && (int) $row['admin'] !== 1) {

    echo $language->get('user', 'showUserCannotDisplayUserDuePrivacyRestrictions');
    return;
}

// If the policy is set to hidden to search engines (2) or crypted (3) set the appropriate meta
if ($row['privacy_profile_level'] ===  2 || $row['privacy_profile_level'] === 3 ){
    $this->head .= '<meta name="robots" content="noindex,nofollow">';
}

$theBuffer = '<h1>' . $row['name'] . ' ' . $row['surname'];

if (!empty ( $row['name']) || !empty($row['surname'])){
    $theBuffer .= ' (' . $row['username'] . ')';
}else{
    $theBuffer .= ' ' . $row['username'] . ' ';
}
$theBuffer .= '</h1>';

$template->pageTitle = $row['username'];

if ( (int)  $row['privacy_profile_level'] === 3){
    echo '
    <script type="text/javascript">
        document.write("' . str_replace('"','&quot;',  $theBuffer ). '");
    </script>
    ';
} else{
    echo $theBuffer;
}

echo '
<!--FabCMS-hook:beforeShowUser-->
<h2>' . $language->get('user', 'biography') . '</h2>
<p>
    <div id="theBio"></div>
</p>';

if ( (int)  $row['privacy_profile_level'] === 3){
    $module->head .= '<meta name="robots" content="noindex, nofollow, noarchive, nosnippet, noydir, noodp, nocache" />';

    $theScript = '
    $(document).ready(function(){
        $("#theBio").html("' . str_replace(['"', "\n"],['&quot;',''], $row['biography']) . '");
});';

    $this->addScript($theScript);

} else{
    echo $row['biography'];
}

echo '<!--FabCMS-hook:afterShowUser-->';