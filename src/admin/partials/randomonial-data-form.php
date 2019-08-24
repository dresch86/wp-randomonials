<?php
$randomonial_data_form = <<<HTML
<form id="randomonials_data_form" class="randomonials-vbox" method="post">
    <fieldset id="randomonials_core_fields">
        <legend>Core Fields</legend>
        <div class="randomonials-vbox">
            <label for="randmonial_author">Author:</label>
            <input type="text" id="randmonial_author" name="author" minlength="1" maxlength="100" placeholder="Author goes here..." required>
            <label for="randmonial_comment">Comment:</label>
            <textarea id="randmonial_comment" name="comment" rows="10" cols="60" placeholder="Comment goes here..." required></textarea>
        </div>
    </fieldset>
    {$custom_html_fields}
    <div id="randomonials_data_controls" class="randomonials-hbox">
        <button type="submit">Submit</button>
        <button type="reset">Reset</button>
    </div>
    <div id="randomonials_submit_result">
    </div>
    <div id="randomonials_edit_tag_param">
        <input type=text id="randomonial_param_input" placeholder="Please enter value here...">
    </div>
</form>
HTML;
