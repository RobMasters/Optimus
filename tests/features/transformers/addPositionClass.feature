@transformer
Feature: Add position class transformer
  In order to apply relevant styling to collections of elements
  As a developer
  I need to be able to add position classes automatically based on their order in the DOM

  Background:
    Given The following markup:
    """
        <!DOCTYPE html>
        <html>
          <head>
            <title>Testing the HTML adapter</title>
          </head>
          <body>
            <header>
              <h1>This is the heading</h1>
              <nav>
                <ul id="main-menu">
                  <li id="first-menu-item"><a href="/">Home</a>
                  <li><a href="/about">About</a>
                  <li><a href="/contact">Contact</a>
                </ul>
              </nav>
              <div id="content">
                <p>This is the main page content</p>
              </div>
            </header>
          </body>
        </html>
      """

  Scenario: Adding position classes to main menu
    Given I apply the "add position class" transformer to "li" nodes
    When I transcode the page
    Then "#first-menu-item" should have the class "first"