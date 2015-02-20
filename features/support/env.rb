require 'rspec/expectations'
require 'capybara'
require 'capybara/cucumber'
require 'capybara/poltergeist'
require 'test/unit/assertions'

World(Test::Unit::Assertions)

Capybara.default_driver = :poltergeist
Capybara.app_host = "http://127.0.0.1:8000"
World(Capybara)

Before do |scenario|
  # send instruction to clear the database
  page.driver.headers = { "User-Agent" => "Poltergeist--clear" }
  visit '/'
end