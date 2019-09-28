<?php
/**********************************************************************
Randomonials is a plugin for WordPress that manages and displays
testimonials in a randomized order.
Copyright (C) 2019 by Daniel Resch

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, version 3 of the License.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
***********************************************************************/

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
