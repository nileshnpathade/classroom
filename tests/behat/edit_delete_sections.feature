@format @format_Classroom
Feature: Sections can be edited and deleted in Classrooms format
  In order to rearrange my course contents
  As a teacher
  I need to edit and Delete Classrooms

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email            |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format | coursedisplay | numsections |
      | Course 1 | C1        | Classrooms | 0             | 5           |
    And the following "activities" exist:
      | activity   | name                   | intro                         | course | idnumber    | section |
      | assign     | Test assignment name   | Test assignment description   | C1     | assign1     | 0       |
      | book       | Test book name         | Test book description         | C1     | book1       | 1       |
      | chat       | Test chat name         | Test chat description         | C1     | chat1       | 4       |
      | choice     | Test choice name       | Test choice description       | C1     | choice1     | 5       |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on

  Scenario: View the default name of the general section in Classrooms format
    When I edit the section "0"
    Then the field "Custom" matches value "0"
    And the field "New value for Section name" matches value "General"

  Scenario: Edit the default name of the general section in Classrooms format
    When I edit the section "0" and I fill the form with:
      | Custom | 1                     |
      | New value for Section name      | This is the general section |
    Then I should see "This is the general section" in the "li#section-0" "css_element"

  Scenario: View the default name of the second section in Classrooms format
    When I edit the section "2"
    Then the field "Custom" matches value "0"
    And the field "New value for Section name" matches value "Classroom 2"

  Scenario: Edit section summary in Classrooms format
    When I edit the section "2" and I fill the form with:
      | Summary | Welcome to section 2 |
    Then I should see "Welcome to section 2" in the "li#section-2" "css_element"

  Scenario: Edit section default name in Classrooms format
    When I edit the section "2" and I fill the form with:
      | Custom | 1                      |
      | New value for Section name      | This is the second Classroom |
    Then I should see "This is the second Classroom" in the "li#section-2" "css_element"
    And I should not see "Classroom 2" in the "li#section-2" "css_element"

  @javascript
  Scenario: Inline edit section name in Classrooms format
    When I click on "Edit Classroom name" "link" in the "li#section-1" "css_element"
    And I set the field "New name for Classroom Classroom 1" to "Midterm evaluation"
    And I press key "13" in the field "New name for Classroom Classroom 1"
    Then I should not see "Classroom 1" in the "region-main" "region"
    And "New name for Classroom" "field" should not exist
    And I should see "Midterm evaluation" in the "li#section-1" "css_element"
    And I am on "Course 1" course homepage
    And I should not see "Classroom 1" in the "region-main" "region"
    And I should see "Midterm evaluation" in the "li#section-1" "css_element"

  Scenario: Deleting the last section in Classrooms format
    When I delete section "5"
    Then I should see "Are you absolutely sure you want to completely delete \"Classroom 5\" and all the activities it contains?"
    And I press "Delete"
    And I should not see "Classroom 5"
    And I should see "Classroom 4"

  Scenario: Deleting the middle section in Classrooms format
    When I delete section "4"
    And I press "Delete"
    Then I should not see "Classroom 5"
    And I should not see "Test chat name"
    And I should see "Test choice name" in the "li#section-4" "css_element"
    And I should see "Classroom 4"

  @javascript
  Scenario: Adding sections in Classrooms format
    When I follow "Add Classrooms"
    Then the field "Number of sections" matches value "1"
    And I press "Add Classrooms"
    And I should see "Classroom 6" in the "li#section-6" "css_element"
    And "li#section-7" "css_element" should not exist
    And I follow "Add Classrooms"
    And I set the field "Number of sections" to "3"
    And I press "Add Classrooms"
    And I should see "Classroom 7" in the "li#section-7" "css_element"
    And I should see "Classroom 8" in the "li#section-8" "css_element"
    And I should see "Classroom 9" in the "li#section-9" "css_element"
    And "li#section-10" "css_element" should not exist
