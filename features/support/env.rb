require 'rspec/expectations'
require 'capybara'
require 'capybara/cucumber'
require 'capybara/poltergeist'
require 'test/unit/assertions'

World(Test::Unit::Assertions)

Capybara.default_driver = :poltergeist
Capybara.app_host = "http://localhost:8000"
World(Capybara)