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
