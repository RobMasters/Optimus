Feature: HTML adapter
  In order to transcode raw HTML
  As a user
  I need to be able to load the HTML into an adapter

  Scenario: Loading HTML content
    Given I have a "HTML" adapter
    And The following markup:
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
                  <li><a href="/">Home</a>
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
    When I transcode the page
    Then "h1" should contain "This is the heading"