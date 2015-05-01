def button_text(button, text)
  text == button.native.visible_text
end

$module_dir = File.expand_path('../../../webapp/modules', __FILE__)

Then(/^I should see admin\-only options on the dashboard$/) do
  assert page.has_content? 'Marketplace'
  assert page.has_content? 'Modules'
  assert page.has_content? 'Customise'
end

When(/^I visit the SmartResolution Marketplace$/) do
  # before we visit the marketplace, make sure we've deleted the test module so we can reinstall it
  FileUtils.rm_rf($module_dir + '/test')
  FileUtils.rm($module_dir + '/config.json')
  visit '/admin-modules-new'
end

Then(/^I should be able to install new modules$/) do
  # sanity checks
  install_button = page.find('#module--test')
  assert button_text install_button, "Install Module"
  install_button.click

  # assertions
  assert URL.get_current_uri_path === '/admin-modules'
  assert page.has_content? 'Test Module'
  assert page.has_content? 'This module is used only in testing the SmartResolution module functionality and should not be included in the production environment.'

  # more sanity checks
  visit '/admin-modules-new'
  install_button = page.find('#module--test')
  assert button_text install_button, "Module already installed."
  assert install_button[:disabled] == "disabled"
end

Given(/^I have installed a new module$/) do
  # this was done in step "I should be able to install new modules"
end

Then(/^I should be able to activate the module$/) do
  visit '/admin-modules'
  test_module_action = page.find('#module--test')
  assert button_text test_module_action, "Activate"
  test_module_action.click
  test_module_action = page.find('#module--test')
  assert button_text test_module_action, "Deactivate"
end

Given(/^I have activated a module$/) do
  # this was done in step "I should be able to activate the module"
end

Then(/^I should be able to deactivate the module$/) do
  visit '/admin-modules'
  test_module_action = page.find('#module--test')
  assert button_text test_module_action, "Deactivate"
  test_module_action.click
  test_module_action = page.find('#module--test')
  assert button_text test_module_action, "Activate"
end

Then(/^I should be able to uninstall the module$/) do
  visit '/admin-modules'
  delete_link = page.find('a[href="/admin-modules-delete/?id=test"]')
  delete_link.click
  assert ! (page.has_content? 'Test Module')
  # should be able to install the module again now if we want to
  visit '/admin-modules-new'
  install_button = page.find('#module--test')
  assert button_text install_button, "Install Module"

  # and indeed, we will install it again, to tidy up the test suite and prepare it
  # for the module.feature tests.
  install_button.click
  step "I visit the SmartResolution Marketplace"
  step "I should be able to install new modules"
  step "I should be able to activate the module"
end