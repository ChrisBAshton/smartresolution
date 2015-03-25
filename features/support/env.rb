require 'rspec/expectations'
require 'capybara'
require 'capybara/cucumber'
require 'capybara/poltergeist'
require 'test/unit/assertions'
require 'yaml'

World(Test::Unit::Assertions)

Capybara.default_driver = :poltergeist
Capybara.app_host = "http://127.0.0.1:8000"
World(Capybara)

Before do
  use_test_database
end

# We use the @clear tag for all scenarios where we require a clean database (populated through seed.php).
# For example, when registering a new account (which would pass on first run, but fail on second run as
# the email address already exists), we need a clean database. However, this is slow to run, so we only want
# to use it where absolutely necessary.
Before('@clear') do
  clear_database
  use_test_database
end

def clear_database
  # send instruction to clear the test database
  page.driver.headers = { "User-Agent" => "Poltergeist--clear" }
  visit '/'
end

def use_test_database
  # send instructions to use the test database for any subsequent HTTP requests
  page.driver.headers = { "User-Agent" => "Poltergeist" }
end


$data = YAML.load_file(File.expand_path('../../../data/fixtures/fixture_data.yml', __FILE__))
