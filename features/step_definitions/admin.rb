Then(/^I should see admin\-only options on the dashboard$/) do
  assert page.has_content? 'Welcome back, Administrator'
  assert page.has_content? 'Marketplace'
  assert page.has_content? 'Modules'
  assert page.has_content? 'Customise'
end

When(/^I visit the SmartResolution Marketplace$/) do
  # @TODO - delete the test module first, then we can install it as this step definition
  find_link('Marketplace').trigger('click')
  pending # Write code here that turns the phrase above into concrete actions
end

Then(/^I should be able to install new modules$/) do
  pending # Write code here that turns the phrase above into concrete actions
end

Given(/^I have installed a new module$/) do
  pending # Write code here that turns the phrase above into concrete actions
end

Then(/^I should be able to activate the module$/) do
  pending # Write code here that turns the phrase above into concrete actions
end

Given(/^I have activated a module$/) do
  pending # Write code here that turns the phrase above into concrete actions
end

Then(/^I should be able to deactivate the module$/) do
  pending # Write code here that turns the phrase above into concrete actions
end

Then(/^I should be able to uninstall the module$/) do
  pending # Write code here that turns the phrase above into concrete actions
end