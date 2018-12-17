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
 * Privacy provider tests.
 *
 * @package    tool_certificate
 * @copyright  2018 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_certificate\privacy\provider;
use core_privacy\local\metadata\collection;
use \core_privacy\local\request\approved_userlist;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy provider tests class.
 *
 * @package    tool_certificate
 * @group      tool_certificate
 * @copyright  2018 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_certificate_privacy_provider_testcase extends \core_privacy\tests\provider_testcase {

    /**
     * Test set up.
     */
    public function setUp() {
        $this->resetAfterTest();
    }

    /**
     * Test provider::get_metadata
     */
    public function test_get_metadata() {
        $collection = new collection('tool_certificate');
        $newcollection = provider::get_metadata($collection);
        $itemcollection = $newcollection->get_collection();
        $this->assertCount(1, $itemcollection);

        $table = array_pop($itemcollection);
        $this->assertEquals('tool_certificate_issues', $table->get_name());
        $this->assertEquals('privacy:metadata:tool_certificate:issues', $table->get_summary());

        $privacyfields = $table->get_privacy_fields();
        $this->assertArrayHasKey('userid', $privacyfields);
        $this->assertArrayHasKey('templateid', $privacyfields);
        $this->assertArrayHasKey('code', $privacyfields);
        $this->assertArrayHasKey('expires', $privacyfields);
        $this->assertArrayHasKey('timecreated', $privacyfields);
    }

    /**
     * Test for provider::get_contexts_for_userid().
     */
    public function test_get_contexts_for_userid() {

        // Add a template to the site.
        $template1 = \tool_certificate\template::create((object)['name' => 'Site template']);

        // Another template that has no issued certificates.
        $template2 = \tool_certificate\template::create((object)['name' => 'No issues template']);

        // Create a user who will be issued a certificate.
        $user = $this->getDataGenerator()->create_user();

        // Check there are no contexts with user data before issuing certificates.
        $contextlist = provider::get_contexts_for_userid($user->id);
        $this->assertCount(0, $contextlist);

        // Issue the certificate.
        $template1->issue_certificate($user->id);

        // Check the context supplied is correct.
        $contextlist = provider::get_contexts_for_userid($user->id);
        $this->assertCount(1, $contextlist);

        $contextids = $contextlist->get_contextids();
        $this->assertContains(\context_system::instance()->id, $contextids);
    }

    /**
     * Test that only users within a context are fetched.
     */
    public function test_get_users_in_context() {
        global $DB;

        $component = 'tool_certificate';

        $this->setAdminUser();
        $admin = \core_user::get_user_by_username('admin');
        // Create user1.
        $user1 = $this->getDataGenerator()->create_user();
        $usercontext1 = \context_user::instance($user1->id);

        // Add a template to the site.
        $template1 = \tool_certificate\template::create((object)['name' => 'Site template']);

        // Issue the certificate.
        $template1->issue_certificate($user1->id);

        // The user list for usercontext1 should not return any users.
        $userlist1 = new \core_privacy\local\request\userlist($usercontext1, $component);
        provider::get_users_in_context($userlist1);
        $this->assertCount(0, $userlist1);

        // The user list for systemcontext should have user1.
        $userlist2 = new \core_privacy\local\request\userlist(\context_system::instance(), $component);
        provider::get_users_in_context($userlist2);
        $this->assertCount(1, $userlist2);
    }

    /**
     * Test for provider::export_user_data().
     */
    public function test_export_user_data() {

        // Add a template to the site.
        $template = \tool_certificate\template::create((object)['name' => 'Site template']);

        // Create users who will be issued a certificate.
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

        $template->issue_certificate($user1->id);
        $template->issue_certificate($user2->id);

        // Export all of the data for the context for user 1.
        $context = \context_system::instance();
        $this->export_context_data_for_user($user1->id, $context, 'tool_certificate');
        $writer = \core_privacy\local\request\writer::with_context($context);

        $this->assertTrue($writer->has_any_data());

        $data = $writer->get_data();
        $this->assertCount(1, $data->issues);

        $issues = $data->issues;
        foreach ($issues as $issue) {
            $this->assertArrayHasKey('certificatename', $issue);
            $this->assertArrayHasKey('code', $issue);
            $this->assertArrayHasKey('data', $issue);
            $this->assertArrayHasKey('expires', $issue);
            $this->assertArrayHasKey('timecreated', $issue);
        }
    }

    /**
     * Test for provider::delete_data_for_all_users_in_context().
     */
    public function test_delete_data_for_all_users_in_context() {
        global $DB;

        // Add a template to the site.
        $template1 = \tool_certificate\template::create((object)['name' => 'Site template']);
        $template2 = \tool_certificate\template::create((object)['name' => 'Second template']);

        // Create users who will be issued a certificate.
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

        $template1->issue_certificate($user1->id);
        $template1->issue_certificate($user2->id);

        $template2->issue_certificate($user1->id);
        $template2->issue_certificate($user2->id);

        // Before deletion, we should have 2 issued certificates for the first certificate.
        $count = $DB->count_records('tool_certificate_issues', ['templateid' => $template1->get_id()]);
        $this->assertEquals(2, $count);

        // Delete data on user context will do nothing.
        $usercontext1 = \context_user::instance($user1->id);
        provider::delete_data_for_all_users_in_context($usercontext1);

        $count = $DB->count_records('tool_certificate_issues', ['templateid' => $template1->get_id()]);
        $this->assertEquals(2, $count);

        // Delete data on system context will delete certificate issues.
        $context = \context_system::instance();
        provider::delete_data_for_all_users_in_context($context);

        // After deletion, the issued certificates for all templates should have been deleted.
        $count = $DB->count_records('tool_certificate_issues');
        $this->assertEquals(0, $count);
    }

    /**
     * Test for provider::delete_data_for_user().
     */
    public function test_delete_data_for_user() {
        global $DB;

        $template = \tool_certificate\template::create((object)['name' => 'Site template']);

        // Create users who will be issued a certificate.
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $usercontext1 = \context_user::instance($user1->id);

        $template->issue_certificate($user1->id);
        $template->issue_certificate($user2->id);

        // Before deletion we should have 2 issued certificates.
        $count = $DB->count_records('tool_certificate_issues', ['templateid' => $template->get_id()]);
        $this->assertEquals(2, $count);

        // Delete data without context will do nothing.
        $context = \context_system::instance();
        $contextlist = new \core_privacy\local\request\approved_contextlist($user1, 'tool_certificate', []);
        provider::delete_data_for_user($contextlist);

        $count = $DB->count_records('tool_certificate_issues', ['templateid' => $template->get_id()]);
        $this->assertEquals(2, $count);

        // Delete data on user context will do nothing.
        $context = \context_system::instance();
        $contextlist = new \core_privacy\local\request\approved_contextlist($user1, 'tool_certificate', [$usercontext1->id]);
        provider::delete_data_for_user($contextlist);

        $count = $DB->count_records('tool_certificate_issues', ['templateid' => $template->get_id()]);
        $this->assertEquals(2, $count);

        $context = \context_system::instance();
        $contextlist = new \core_privacy\local\request\approved_contextlist($user1, 'tool_certificate', [$context->id]);
        provider::delete_data_for_user($contextlist);

        // After deletion, the issued certificates for the first user should have been deleted.
        $count = $DB->count_records('tool_certificate_issues', ['templateid' => $template->get_id(), 'userid' => $user1->id]);
        $this->assertEquals(0, $count);

        // Check the issue for the other user is still there.
        $count = $DB->count_records('tool_certificate_issues', ['templateid' => $template->get_id(), 'userid' => $user2->id]);
        $this->assertEquals(1, $count);
    }

    /**
     * Test for provider::delete_data_for_user().
     */
    public function test_delete_data_for_users() {

        $component = 'tool_certificate';

        $template = \tool_certificate\template::create((object)['name' => 'Site template']);

        // Create users who will be issued a certificate.
        $user1 = $this->getDataGenerator()->create_user();

        $template->issue_certificate($user1->id);
        $user2 = $this->getDataGenerator()->create_user();
        $template->issue_certificate($user2->id);

        $systemcontext = \context_system::instance();
        $userlist1 = new \core_privacy\local\request\userlist($systemcontext, $component);
        provider::get_users_in_context($userlist1);
        $this->assertCount(2, $userlist1);

        // Convert $userlist1 into an approved_contextlist.
        $approvedlist1 = new approved_userlist($systemcontext, $component, $userlist1->get_userids());

        // Delete using delete_data_for_user.
        provider::delete_data_for_users($approvedlist1);
        // Re-fetch users in systemcontext.
        $userlist1 = new \core_privacy\local\request\userlist($systemcontext, $component);
        provider::get_users_in_context($userlist1);
        $this->assertCount(0, $userlist1);
    }
}
