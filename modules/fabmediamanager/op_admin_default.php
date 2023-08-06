<?php

if (!$core->loaded)
    die("Not loaded");

if (!$user->isAdmin)
    die ("Only admin");

echo '<h1>FabMedia Manager administration</h1>';