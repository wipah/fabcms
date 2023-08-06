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

if (!$core->adminLoaded) {
    die('Direct call detected');
}

echo '
<style type="text/css">
.inputDescription{
    width: 200px;
    float:left;
}
.inputField{
    margin-left: 200px;
}
</style>

<form action="">
    <div class="inputDescription">Name</div>
    <div class="inputField">
        <input id="title" name="title" value="' . $row['title'] . '" />
    </div>
    <div class="cleared"></div>

    <div class="inputDescription">Categories</div>
    <div class="inputField">
        <input id="categories" name="categories" value="' . $row['categories'] . '" />
    </div>
    <div class="cleared"></div>

    <div class="inputDescription">Arguments</div>
    <div class="inputField">
        <input id="arguments" name="arguments" value="' . $row['arguments'] . '" />
    </div>
    <div class="cleared"></div>

    <div class="inputDescription">Questions per page</div>
    <div class="inputField">
        <input id="questionsPerPage" name="questionsPerPage" value="' . $row['question_per_page'] . '" />
    </div>
    <div class="cleared"></div>

    <div class="inputDescription">Max questions</div>
    <div class="inputField">
        <input id="maxQuestions" name="maxQuestions" value="' . $row['max_questions'] . '" />
    </div>
    <div class="cleared"></div>

    <div class="inputDescription">Custom CSS</div>
    <div class="inputField">
        <textarea id="customCSS" name="customCSS">' .  $row['custom_CSS'] . '" </textarea>
    </div>
    <div class="cleared"></div>

    <div class="inputDescription">Pre Questions (HTML)</div>
    <div class="inputField">
        <textarea id="preQuestions" name="preQuestions">' .  $row['pre_questions'] . '" </textarea>
    </div>
    <div class="cleared"></div>

    <div class="inputDescription">Post Questions (HTML)</div>
    <div class="inputField">
        <textarea id="postQuestions" name="postQuestions">' .  $row['post_questions'] . '" </textarea>
    </div>
    <div class="cleared"></div>

    <div class="inputDescription">Post Answers (HTML)</div>
    <div class="inputField">
        <textarea id="postAnswers" name="postAnswers">' .  $row['post_answers'] . '" </textarea>
    </div>
    <div class="cleared"></div>
</form>

';

