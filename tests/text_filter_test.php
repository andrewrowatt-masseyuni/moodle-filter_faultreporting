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

/**
 * Tests for the fault reporting filter
 *
 * @package    filter_faultreporting
 * @category   test
 * @copyright  2026 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \filter_faultreporting\text_filter
 */
final class text_filter_test extends \advanced_testcase {
    /**
     * The shortcode becomes a link that the pop-up JavaScript recognises
     */
    public function test_shortcode_is_replaced(): void {
        $this->resetAfterTest();
        filter_set_global_state('faultreporting', TEXTFILTER_ON);

        $course = $this->getDataGenerator()->create_course();
        $context = \context_course::instance($course->id);

        $filtered = format_text('<p>Something wrong? {faultreport}</p>', FORMAT_HTML, ['context' => $context]);

        $this->assertStringContainsString('data-action="local_faultreporting-faultreport"', $filtered);
        $this->assertStringContainsString('/local/faultreporting/faultreport.php', $filtered);
        $this->assertStringNotContainsString('{faultreport}', $filtered);
    }

    /**
     * The link carries the course and activity the report relates to
     */
    public function test_link_carries_course_and_activity(): void {
        $this->resetAfterTest();
        filter_set_global_state('faultreporting', TEXTFILTER_ON);

        $course = $this->getDataGenerator()->create_course();
        $page = $this->getDataGenerator()->create_module('page', ['course' => $course->id]);
        $context = \context_module::instance($page->cmid);

        $filtered = format_text('<p>{faultreport}</p>', FORMAT_HTML, ['context' => $context]);

        $this->assertStringContainsString('data-contextid="' . $context->id . '"', $filtered);
        $this->assertStringContainsString('data-courseid="' . $course->id . '"', $filtered);
        $this->assertStringContainsString('data-coursemoduleid="' . $page->cmid . '"', $filtered);
    }

    /**
     * Text with no shortcode is left alone
     */
    public function test_text_without_shortcode_is_unchanged(): void {
        $this->resetAfterTest();
        filter_set_global_state('faultreporting', TEXTFILTER_ON);

        $context = \context_system::instance();
        $filtered = format_text('<p>Nothing to see here.</p>', FORMAT_HTML, ['context' => $context]);

        $this->assertStringNotContainsString('local_faultreporting-faultreport', $filtered);
    }

    /**
     * The shortcode is left alone while the filter is off
     */
    public function test_shortcode_is_left_alone_when_filter_is_off(): void {
        $this->resetAfterTest();
        filter_set_global_state('faultreporting', TEXTFILTER_OFF);

        $context = \context_system::instance();
        $filtered = format_text('<p>{faultreport}</p>', FORMAT_HTML, ['context' => $context]);

        $this->assertStringContainsString('{faultreport}', $filtered);
        $this->assertStringNotContainsString('local_faultreporting-faultreport', $filtered);
    }

    /**
     * The shortcode is left alone in headings and other plain string contexts
     */
    public function test_shortcode_is_left_alone_in_strings(): void {
        $this->resetAfterTest();
        filter_set_global_state('faultreporting', TEXTFILTER_ON);

        $context = \context_system::instance();
        $filtered = format_string('Test course {faultreport}', true, ['context' => $context]);

        $this->assertSame('Test course {faultreport}', $filtered);
    }
}
