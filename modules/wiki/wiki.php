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

if (!$core->loaded) 
  die('Direct call detected');

require_once __DIR__ . '/lib/class_wiki.php';

$fabwiki = new wiki();
$fabwiki->loadConfig();

if ($path[2] == 'ajax_post_comment') {
    require_once 'op_ajax_post_comment.php';
    return;
}

if ($path[2] == 'ajax-submit-feedback') {
    require_once 'op_ajax_submit_feedback.php';
    return;
}
if ($path[2] == 'ajax_search') {
    require_once 'op_ajax_search.php';
    return;
}

if (preg_match('#fcms-tags#', $path[2])) {
    require_once 'op_tags.php';
    return;
}

require_once 'op_page_show.php';