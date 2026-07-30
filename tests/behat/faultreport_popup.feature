@filter @filter_faultreporting @javascript
Feature: The {faultreport} shortcode opens the fault report form in a pop-up
  In order to report a fault about the page I am on
  As a user
  I need to be able to log a fault without leaving the page

  Background:
    Given the "faultreporting" filter is "on"
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | 98186700 | Andrew    | Barry    | student1@example.com |
    And the following "courses" exist:
      | fullname      | shortname | category |
      | Test course 1 | TC1       | 0        |
    And the following "activities" exist:
      | activity | course | name        | idnumber | content                        |
      | page     | TC1    | Test page 1 | page1    | Something wrong? {faultreport} |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | 98186700 | TC1    | student |

  Scenario: A user can submit a fault report without leaving the page
    Given I am on the "Test page 1" "page activity" page logged in as "98186700"
    Then I should see "Fault report"
    And I should not see "{faultreport}"

    When I click on "Fault report" "link"
    Then I should see "student1@example.com" in the "Create new fault report" "dialogue"

    # The course and activity the report relates to are prefilled from the page the link is on.
    And the field "Course name" matches value "Test course 1"
    And the field "Course code" matches value "TC1"
    And the field "Activity name" matches value "Test page 1"

    When I set the field "Description" to "test98186700 popup"
    And I click on "Submit report" "button" in the "Create new fault report" "dialogue"
    Then "Report successfully queued for sending" "toast_message" should be visible
    And "Create new fault report" "dialogue" should not exist

    # The user stays on the page they reported the fault from.
    And I should see "Test page 1"

    Given I am on the "local_faultreporting > faultreports" page logged in as "admin"
    Then I should see "test98186700 popup"

  Scenario: A description is required
    Given I am on the "Test page 1" "page activity" page logged in as "98186700"
    When I click on "Fault report" "link"
    And I click on "Submit report" "button" in the "Create new fault report" "dialogue"
    Then "Create new fault report" "dialogue" should exist
    And "Report successfully queued for sending" "toast_message" should not exist
