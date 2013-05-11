@webserver
Feature: Guzzle adapter
  In order to get the source from a given URL
  As a user
  I need to be able to fetch the content using Guzzle

  Scenario: Loading static page
    Given I have a "Guzzle" adapter
    And I am requesting "staticPage.html"
    When I transcode the page
    Then "h1" should contain "It has a heading"
    And "p" should contain "And a paragraph of text."

  Scenario: Loading page with content added via javascript
    Given I have a "Guzzle" adapter
    And I am requesting "dynamicContent.html"
    When I transcode the page
    Then "h1" should not exist