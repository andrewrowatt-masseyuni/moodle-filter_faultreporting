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

/**
 * Admin settings for the activity preset provider.
 *
 * @package    mod_edpreset
 * @copyright  2026 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configcheckbox(
        'mod_edpreset/enabled',
        get_string('settings:enabled', 'mod_edpreset'),
        get_string('settings:enabled_desc', 'mod_edpreset'),
        0
    ));

    $settings->add(new \mod_edpreset\admin\setting_templatecourse(
        'mod_edpreset/templatecourseid',
        get_string('settings:templatecourseid', 'mod_edpreset'),
        get_string('settings:templatecourseid_desc', 'mod_edpreset'),
        '',
        PARAM_INT
    ));

    $settings->add(new admin_setting_configcheckbox(
        'mod_edpreset/showcategoryinhelp',
        get_string('settings:showcategoryinhelp', 'mod_edpreset'),
        get_string('settings:showcategoryinhelp_desc', 'mod_edpreset'),
        1
    ));

    $settings->add(new admin_setting_configtext(
        'mod_edpreset/maxpresets',
        get_string('settings:maxpresets', 'mod_edpreset'),
        get_string('settings:maxpresets_desc', 'mod_edpreset'),
        100,
        PARAM_INT
    ));
}
