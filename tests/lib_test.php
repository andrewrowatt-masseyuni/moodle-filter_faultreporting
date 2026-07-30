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

namespace mod_edpreset;

use core_course\local\factory\content_item_service_factory;

/**
 * Tests for the chooser provider callbacks and the module's core integration.
 *
 * @package    mod_edpreset
 * @copyright  2026 Andrew Rowatt <A.J.Rowatt@massey.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_edpreset\local\chooser
 */
final class lib_test extends \advanced_testcase {
    /**
     * Load lib.php, which holds the callbacks under test.
     */
    public static function setUpBeforeClass(): void {
        global $CFG;
        require_once($CFG->dirroot . '/mod/edpreset/lib.php');
        parent::setUpBeforeClass();
    }

    /**
     * Set up a course, a teacher, and the plugin pointed at a template course.
     *
     * @param int $presetcount How many live presets to create.
     * @return array [$course, $teacher, $templatecourse, $presets]
     */
    protected function setup_site(int $presetcount = 3): array {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $plugingenerator = $generator->get_plugin_generator('mod_edpreset');

        $templatecourse = $plugingenerator->create_template_course();
        $course = $generator->create_course();
        $teacher = $generator->create_and_enrol($course, 'editingteacher');

        $presets = [];
        for ($i = 0; $i < $presetcount; $i++) {
            $presets[] = $plugingenerator->create_preset(['templatecourseid' => $templatecourse->id]);
        }

        return [$course, $teacher, $templatecourse, $presets];
    }

    /**
     * The real content item service must accept our items.
     *
     * This is the executable form of the constraint the whole design rests on: content_item_service
     * throws a moodle_exception for any item whose component is not an installed mod_* plugin. If
     * the componentname is ever wrong, this test fails rather than the site's activity chooser.
     */
    public function test_service_accepts_our_items(): void {
        [$course, $teacher, , $presets] = $this->setup_site();

        $service = content_item_service_factory::get_content_item_service();
        $items = $service->get_content_items_for_user_in_course($teacher, $course);

        $ours = array_filter($items, fn($item) => $item->componentname === 'mod_edpreset');
        $this->assertCount(count($presets), $ours);
    }

    /**
     * Ids, internal names and links must all be unique per preset.
     *
     * The chooser keys its item map on componentname + link, and resolves favourite toggles by
     * matching on name, so a collision in either would make two presets share a favourite star.
     */
    public function test_item_ids_names_and_links_are_unique(): void {
        [$course, $teacher, , $presets] = $this->setup_site(4);

        $service = content_item_service_factory::get_content_item_service();
        $items = array_values(array_filter(
            $service->get_content_items_for_user_in_course($teacher, $course),
            fn($item) => $item->componentname === 'mod_edpreset'
        ));

        $this->assertCount(count($presets), $items);
        $this->assertSameSize($items, array_unique(array_column($items, 'id')));
        $this->assertSameSize($items, array_unique(array_column($items, 'name')));
        $this->assertSameSize($items, array_unique(array_column($items, 'link')));
    }

    /**
     * Every link must already contain a query string.
     *
     * activitychooser.js string-appends '&section=...&beforemod=...' to the link, so a link with no
     * '?' would produce a malformed URL and the activity would be created in the wrong section.
     */
    public function test_links_contain_a_query_string(): void {
        [$course, $teacher] = $this->setup_site();

        $service = content_item_service_factory::get_content_item_service();
        foreach ($service->get_content_items_for_user_in_course($teacher, $course) as $item) {
            if ($item->componentname !== 'mod_edpreset') {
                continue;
            }
            $this->assertStringContainsString('?', $item->link);
            // Confirm the JS concatenation actually yields a parseable URL.
            $withsection = $item->link . '&section=3&beforemod=0';
            parse_str(parse_url($withsection, PHP_URL_QUERY), $params);
            $this->assertSame('3', $params['section']);
            $this->assertArrayHasKey('preset', $params);
        }
    }

    /**
     * The provider module must never offer itself.
     *
     * Implementing get_course_content_items() makes core discard the module's own default item; if
     * that ever stopped being true, "Activity preset provider" would appear in every chooser.
     */
    public function test_default_provider_item_is_discarded(): void {
        [$course, $teacher] = $this->setup_site();

        $service = content_item_service_factory::get_content_item_service();
        $titles = array_column($service->get_content_items_for_user_in_course($teacher, $course), 'title');

        $this->assertNotContains(get_string('modulename', 'mod_edpreset'), $titles);
    }

    /**
     * get_all_content_items() must return the same ids as the in-course list.
     *
     * content_item_service::add_to_user_favourites() resolves the favourited item by running
     * array_search() over the ids from get_all_content_items(). A preset missing there makes
     * array_search() return false, and $items[false] silently yields a different item.
     */
    public function test_all_content_items_match_course_content_items(): void {
        [$course, $teacher] = $this->setup_site(3);

        $service = content_item_service_factory::get_content_item_service();

        $incourse = array_filter(
            $service->get_content_items_for_user_in_course($teacher, $course),
            fn($item) => $item->componentname === 'mod_edpreset'
        );
        $all = array_filter(
            $service->get_all_content_items($teacher),
            fn($item) => $item->componentname === 'mod_edpreset'
        );

        $incourseids = array_column($incourse, 'id');
        $allids = array_column($all, 'id');
        sort($incourseids);
        sort($allids);
        $this->assertSame($incourseids, $allids);
    }

    /**
     * Only presets with a validated backup behind them are offered.
     *
     * Visibility is gated on the live archive rather than the status field, so a preset that has
     * never been baked, or whose archive has gone missing or been replaced, is simply not offered.
     */
    public function test_only_live_presets_are_offered(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $plugingenerator = $generator->get_plugin_generator('mod_edpreset');

        $templatecourse = $plugingenerator->create_template_course();
        $course = $generator->create_course();
        $teacher = $generator->create_and_enrol($course, 'editingteacher');

        $live = $plugingenerator->create_preset(['templatecourseid' => $templatecourse->id]);
        $plugingenerator->create_preset(['templatecourseid' => $templatecourse->id, 'live' => false]);

        // A preset whose recorded hash no longer matches its file must not be offered either.
        $stale = $plugingenerator->create_preset(['templatecourseid' => $templatecourse->id]);
        $stale->set('backupcontenthash', sha1('something else entirely'));
        $stale->update();

        $service = content_item_service_factory::get_content_item_service();
        $ours = array_filter(
            $service->get_content_items_for_user_in_course($teacher, $course),
            fn($item) => $item->componentname === 'mod_edpreset'
        );

        $this->assertCount(1, $ours);
        $this->assertSame((int)$live->get('id'), (int)reset($ours)->id);
    }

    /**
     * Nothing is offered when the plugin is off, unconfigured, or inside the template course.
     */
    public function test_nothing_offered_when_not_applicable(): void {
        [$course, $teacher, $templatecourse] = $this->setup_site();
        $service = content_item_service_factory::get_content_item_service();

        // Two things core does that this test has to work around:
        // - the content item cache keys on the global $USER, not the user passed in, so the user
        // must be set before asking;
        // - results are cached per request, so a config change mid-test is invisible without a
        // purge. In production each config change lands in a later request, so this is a test
        // concern rather than a bug.
        $ours = function ($c) use ($service, $teacher) {
            $this->setUser($teacher);
            \cache::make('core', 'user_course_content_items')->purge();
            return array_filter(
                $service->get_content_items_for_user_in_course($teacher, $c),
                fn($item) => $item->componentname === 'mod_edpreset'
            );
        };

        // Inside the template course itself the curator is not offered their own exemplars. Use
        // one user who can manage activities in both courses, so the only difference between the
        // two assertions is which course is being asked about.
        $this->getDataGenerator()->enrol_user($teacher->id, $templatecourse->id, 'editingteacher');

        $this->assertNotEmpty($ours($course), 'sanity: this user sees presets in an ordinary course');
        $this->assertEmpty($ours($templatecourse));

        set_config('enabled', 0, 'mod_edpreset');
        $this->assertEmpty($ours($course));

        set_config('enabled', 1, 'mod_edpreset');
        set_config('templatecourseid', 0, 'mod_edpreset');
        $this->assertEmpty($ours($course));

        // A template course that has since been deleted must not fatal.
        set_config('templatecourseid', $templatecourse->id + 10000, 'mod_edpreset');
        $this->assertEmpty($ours($course));
    }

    /**
     * mod/edpreset:addinstance gates the presets, per course and per role.
     */
    public function test_capability_gates_presets(): void {
        [$course, $teacher] = $this->setup_site(2);
        $service = content_item_service_factory::get_content_item_service();

        $ours = fn() => array_filter(
            $service->get_content_items_for_user_in_course($teacher, $course),
            fn($item) => $item->componentname === 'mod_edpreset'
        );

        $this->assertCount(2, $ours());

        $roleid = $this->getDataGenerator()->create_role();
        role_assign($roleid, $teacher->id, \context_course::instance($course->id));
        assign_capability(
            'mod/edpreset:addinstance',
            CAP_PROHIBIT,
            $roleid,
            \context_course::instance($course->id)->id,
            true
        );
        accesslib_clear_all_caches_for_unit_testing();

        $this->assertEmpty($ours());
    }

    /**
     * The module refuses to be instantiated.
     *
     * Teachers hold mod/edpreset:addinstance - that capability is the per-course gate on whether
     * presets appear - so /course/modedit.php?add=edpreset is reachable by hand.
     */
    public function test_add_instance_throws(): void {
        $this->resetAfterTest();
        $this->expectException(\moodle_exception::class);
        edpreset_add_instance((object)['course' => SITEID]);
    }

    /**
     * The module reports the features it must report.
     */
    public function test_supports(): void {
        $this->assertSame(MOD_ARCHETYPE_SYSTEM, edpreset_supports(FEATURE_MOD_ARCHETYPE));
        $this->assertFalse(edpreset_supports(FEATURE_BACKUP_MOODLE2));
        $this->assertTrue(edpreset_supports(FEATURE_NO_VIEW_LINK));
    }

    /**
     * The course reset page must still work.
     *
     * course/reset_form.php runs an UNCAUGHT count_records($modname, ...) for every installed
     * module. Without the stub instance table this module would raise a dml_exception there and
     * break /course/reset.php for every course on the site.
     */
    public function test_course_reset_page_still_works(): void {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/course/reset_form.php');

        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();

        $failed = [];
        foreach ($DB->get_records('modules') as $mod) {
            if (!file_exists("$CFG->dirroot/mod/{$mod->name}/lib.php")) {
                continue;
            }
            try {
                $DB->count_records($mod->name, ['course' => $course->id]);
            } catch (\Throwable $e) {
                $failed[] = $mod->name;
            }
        }

        $this->assertSame([], $failed, 'These modules have no queryable instance table: '
            . implode(', ', $failed));
    }
}
