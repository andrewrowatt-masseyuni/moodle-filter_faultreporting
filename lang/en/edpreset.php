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
 * Strings for the activity preset provider.
 *
 * @package    mod_edpreset
 * @copyright  2026 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['cannotcreateinstance'] = 'The activity preset provider cannot be added to a course. It exists only to supply preset activities to the activity chooser.';
$string['category'] = 'Category: {$a}';
$string['modulename'] = 'Activity preset provider';
$string['modulename_help'] = 'This is not an activity you add to a course. It supplies preset activities to the activity chooser, based on the exemplar activities held in a designated template course.';
$string['modulenameplural'] = 'Activity preset providers';
$string['noviewpage'] = 'The activity preset provider has no view page.';
$string['pluginadministration'] = 'Activity preset provider administration';
$string['pluginname'] = 'Activity preset provider';


$string['privacy:metadata'] = 'The activity preset provider plugin does not store any personal data. It stores backups of exemplar activities taken from a template course.';
$string['settings:enabled'] = 'Enable preset activities';
$string['settings:enabled_desc'] = 'Offer the exemplar activities from the template course in every course\'s activity chooser. When disabled, no presets are offered and no backups are taken.';
$string['settings:maxpresets'] = 'Maximum presets';
$string['settings:maxpresets_desc'] = 'The most presets that will be offered at once. Every chooser item is sent to the browser with its full description, so a very large template course makes the chooser slow to open.';
$string['settings:showcategoryinhelp'] = 'Show category in preset description';
$string['settings:showcategoryinhelp_desc'] = 'Include the template course section name in each preset\'s description. This also makes the category searchable, because the activity chooser\'s search matches descriptions as well as titles.';
$string['settings:templatecourseid'] = 'Template course ID';
$string['settings:templatecourseid_desc'] = 'The ID of the course holding the exemplar activities. Each activity in sections 1 and above becomes a preset, and the section name becomes its category. Section 0 is ignored, so it can be used for instructions to whoever curates the course.

Anyone who can edit this course controls what every teacher on the site sees in their activity chooser, so restrict its editing roles accordingly.';
$string['settings:templatecourseid_notfound'] = 'No course with ID {$a} exists.';
$string['settings:templatecourseid_notsandbox'] = 'The restore test course cannot be used as the template course; its contents are deleted before every validation.';
$string['settings:templatecourseid_notsite'] = 'The site home cannot be used as the template course.';
