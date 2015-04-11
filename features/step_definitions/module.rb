Given(/^the Dispute type has been set to 'Test Module'$/) do
  find_link('Edit').trigger('click')
  select 'Test Module', :from => 'Dispute type:'
  click_button 'Update Details'
  click_link 'Return to dispute dashboard'
end

Then(/^I should see a custom dashboard item$/) do
  assert page.has_content? 'Test Dashboard Item'
end

When(/^I click on the custom dashboard item$/) do
  find_link('Test Dashboard Item').trigger('click')
end

Then(/^this message should have been passed as a parameter rather than hardcoded$/) do
  filepath = File.expand_path('../../../webapp/modules/test/hello.html', __FILE__)
  file_contents = File.read(filepath)
  # sanity check - do we have the right file?
  assert_match /Entries in database/, file_contents
  # sanity check - does our assertion code work?
  assert_no_match /Entries in datssssabase/, file_contents
  # now, the thing we actually want to test:
  assert_no_match /Hello world/, file_contents
end

Then(/^the page should have this selector: '(.+)'$/) do |selector|
  page.has_css? selector
end

When(/^I click on the '(.+)' button$/) do |button_text|
  click_button button_text
end