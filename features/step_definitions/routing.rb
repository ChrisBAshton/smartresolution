Given(/^I am not logged into an account$/) do
end

When(/^I try to visit '(.+)'$/) do |url|
  visit url
end

Given(/^I am logged into an Individual account$/) do
  visit '/login'
  login_as_individual
end