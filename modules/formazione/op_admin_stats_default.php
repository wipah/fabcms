<?php
if (!$core->loaded)
    die ("Not loaded");

if (!$user->isAdmin)
    die ("Only admin");

$query = '
SELECT COUNT(STATS.hits) total

';