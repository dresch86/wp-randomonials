<?php
$randomonial_data_form = <<<HTML
<form id="randomonials_data_form" class="randomonials-vbox" method="post">
    <fieldset id="randomonials_core_fields">
        <legend>Core Fields</legend>
        <div class="randomonials-vbox">
            <label for="randmonial_author_field">Author:</label>
            <input type="text" id="randmonial_author_field" name="author" minlength="1" maxlength="100" placeholder="Author goes here..." required>
            <label for="randmonial_comment_field">Comment:</label>
            <textarea id="randmonial_comment_field" name="comment" rows="10" cols="60" placeholder="Comment goes here..." required></textarea>
        </div>
    </fieldset>
    {$custom_fields_html}
    <div id="randomonials_submit_result">
    </div>
    <div id="randomonials_edit_tag_param">
        <input type=text id="randomonial_param_input" placeholder="Please enter value here...">
    </div>
</form>
HTML;
