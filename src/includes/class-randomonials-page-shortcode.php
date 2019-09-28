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

class Radomonials_Page_Shortcode {
    private $randomonial_template;
    private static $selfClosing = array('area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track', 'wbr');

    public function __construct() {     
    }

    private function build_randomonial_html($idx, $template, $data) {
        $HTML = '    <div id="randomonial-' . $idx . '" class="randomonial">' . "\n";

        foreach ($template->tagStack as $field) {
            $field_id = explode(':', $field);
            $field_template = &$template->fields->{$field_id[0]}->{$field_id[1]};

            if (!isset($data->{$field_id[1]}->value) 
                && (count($data->{$field_id[1]}->attributes) < 1)) {
                // Supplied dataset does not contain data for this field so gracefully skip it...
                continue;
            }

            $evaluated_attrs = [];

            foreach ($field_template->attributes as $attr_idx => $attr) {
                $evaluated_attrs[] = $attr . '="' . $data->{$field_id[1]}->attributes[$attr_idx] . '"';
            }

            if (in_array($field_template->type, self::$selfClosing)) {
                $HTML .= '        <' . $field_template->type . ' class="' . $field_template->class . '" ' . implode(' ', $evaluated_attrs) . '>' . "\n";
            }
            else {
                $HTML .= '        <' . $field_template->type . ' class="' . $field_template->class . '" ' . implode(' ', $evaluated_attrs) . '>' . $data->{$field_id[1]}->value . '</' . $field_template->type . '>' . "\n";
            }
        }

        $HTML .= '    </div>' . "\n";

        return $HTML;
    }

    private function get_page_randomonials($JSON, $display_count, $randomize) {
        $randomonial_record_count = count($JSON->entries);

        if ($randomonial_record_count > 0) {
            $index_map = array_keys($JSON->entries);
            
            if ($overrideSettings['randomize'] === true) {
                shuffle($index_map);
            }
    
            // Sets $randomonial_template using NOWDOC
            require_once RANDOMONIAL_PLUGIN_PATH . 'public/partials/randomonial_page.php';
            $randomonial_template = json_decode($randomonial_template);
            $HTML = '<div id="randomonials-container">' . "\n";
    
            if ($display_count == 0) {
                // Display all the records...
                foreach ($index_map as $index) {
                    $HTML .= $this->build_randomonial_html($index, $randomonial_template, $JSON->entries[$index]);
                }
            }
            else {
                if ($display_count > $randomonial_record_count) {
                    // Make sure we don't look up more testimonials than we actually have...
                    $display_count = $randomonial_record_count;
                }
    
                for ($i=0;$i<$display_count;$i++) {
                    $idx = $index_map[$i];
                    $HTML .= $this->build_randomonial_html($idx, $randomonial_template, $JSON->entries[$idx]);
                }
            }
    
            $HTML .= '</div>' . "\n";
        }
        else {
            $admin_email = get_bloginfo('admin_email');
            
            $HTML = '<div id="randomonials-container">' . "\n";
            $HTML .= '    <div id="randomonials-welcome">Your feedback is very important to us, and our future customers. Please <a href="mailto:{$admin_email}?subject=Testimonial%20Submission%20Request">contact us</a> to be the first to share your thoughts with us about our services!</div>' . "\n";
            $HTML .= '</div>' . "\n"; 
        }

        return $HTML;
    }

    public function display_randomonials($atts = []) {
        $attrSettings = shortcode_atts(array('type'=> 'page', 'count' => '0', 'randomize' => true), $atts);
        $testimonialJSON = RANDOMONIAL_PLUGIN_PATH . 'data/blog_id_' . get_current_blog_id() . '.json';

        if (file_exists($testimonialJSON)) {
            $testimonialJSON = json_decode(file_get_contents($testimonialJSON));

            if ($attrSettings['type'] == 'single') {
                return '';
            }
            elseif ($attrSettings['type'] == 'group') {
                return '';
            }
            elseif ($attrSettings['type'] == 'rotator') {
                return '';
            }
            else {
                return $this->get_page_randomonials($testimonialJSON, $overrideSettings['count'], $overrideSettings['randomize']);
            }
        }
        else {
            return '<div id="randomonials-container">Be the first to share your thoughts with us!</div>' . "\n";
        }
    }
}