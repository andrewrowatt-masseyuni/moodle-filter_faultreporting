<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace filter_faultreporting;

use local_faultreporting\output\popup_link;

/**
 * faultreporting filter
 *
 * Replaces the {faultreport} shortcode with a link that opens the local_faultreporting fault report
 * form in a pop-up. Everything the link needs is provided by local_faultreporting.
 *
 * Documentation: {@link https://moodledev.io/docs/apis/plugintypes/filter}
 *
 * @package    filter_faultreporting
 * @copyright  2026 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class text_filter extends \core_filters\text_filter {
    /**
     * The shortcode this filter replaces
     */
    private const SHORTCODE = '{faultreport}';

    /**
     * Load the pop-up JavaScript
     *
     * This has to happen before the page footer is generated, which is why it cannot wait until a
     * shortcode is actually found.
     *
     * @param \moodle_page $page
     * @param \context $context
     */
    #[\Override]
    public function setup($page, $context) {
        popup_link::require_js($page);
    }

    /**
     * Filter text
     *
     * @param string $text some HTML content to process.
     * @param array $options options passed to the filters
     * @return string the HTML content after the filtering has been applied.
     */
    #[\Override]
    public function filter($text, array $options = []) {
        if (stripos($text, self::SHORTCODE) === false) {
            return $text;
        }

        return str_ireplace(self::SHORTCODE, popup_link::render($this->context), $text);
    }

    /**
     * Leave the shortcode alone in headings, page titles and other plain string contexts
     *
     * format_string() strips tags after filtering, so a link inserted here would be reduced to its
     * text, and the result is used in places where a link is not valid markup anyway.
     *
     * @param string $text
     * @param array $options
     * @return string
     */
    #[\Override]
    public function filter_stage_string(string $text, array $options): string {
        return $text;
    }
}
