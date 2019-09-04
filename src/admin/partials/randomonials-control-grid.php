<?php
$randomonials_control_grid = <<<HTML
    <form id="randomonials_bulk_action_top" class="randomonials-hbox">
        <select id="randomonials_bulk_action_select_top">
            <option value="bulk" selected>Bulk Actions</option>
            <option value="delete">Delete</option>
        </select>
        <button class="randomonials_bulk_action_apply" type="button">Apply</button>
    </form>
    <table id="randomonials_control_grid">
        <thead>
            <tr>
                <th><input id="randomonials_select_all" type="checkbox"></th>
                <th>Author</th>
                <th>Comment</th>
                <th>Edit</th>
                <th colspan="2">Move</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tbody>
            {$randomonial_controls}
        </tbody>
    </table>
    <form id="randomonials_bulk_action_bottom" class="randomonials-hbox">
        <select id="randomonials_bulk_action_select_bottom">
            <option value="bulk" selected>Bulk Actions</option>
            <option value="delete">Delete</option>
        </select>
        <button class="randomonials_bulk_action_apply" type="button">Apply</button>
    </form>
    <div id="randomonials_action_result">
    </div>
HTML;
