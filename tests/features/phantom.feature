@webserver @phantom
Feature: Phantom adapter
  In order to get the source from a given URL
  As a user
  I need to be able to load the URL in a headless browser

  Scenario: Loading static page
    Given I have a "Phantom" adapter
    And I am requesting "staticPage.html"
    When I transcode the page
    Then "h1" should contain "It has a heading"
    And "p" should contain "And a paragraph of text."

  Scenario: Loading page with content added via javascript
    Given I have a "Phantom" adapter
    And I am requesting "dynamicContent.html"
    When I transcode the page
    Then "h1" should contain "This is a dynamic title"