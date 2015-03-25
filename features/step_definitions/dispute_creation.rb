Given(/^I have created at least one Agent account$/) do
end

Given(/^I have created NO Agent accounts$/) do
  clear_session_before_login
  login_with_credentials 'law_firm_with_no_agents@t.co', 'test'
  visit '/disputes/new'
end

Then(/^I should be able to create a new Dispute$/) do
  visit '/disputes/new'
  fill_in('title',       :with => 'Davies versus Jones')
  select('Chris Ashton', :from => 'Agent overseeing Dispute:')
  select('Other',        :from => 'Dispute type:')
  fill_in('summary',     :with => 'Test summary')
  click_button 'Create Dispute'

  assert_equal '/disputes/' + (dispute_count + 1).to_s, get_current_uri_path
  assert page.has_content?('Davies versus Jones')
end

When(/^I try to view a Dispute I've not been allocated to yet$/) do
  visit '/disputes/1'
end

When(/^I try to view a Dispute that does not exist$/) do
  visit '/disputes/1337'
end

Given(/^I have submitted a Dispute$/) do
  login_as_agent
end

Then(/^I should be able to initiate it against another Law Firm$/) do
  open_dispute
end

And(/^I shouldn't be able to reinitiate it against a different Law Firm$/) do
  visit '/disputes/1/open'
  assert page.has_content?('You have already opened this dispute against Maritime Collision Specialists Inc!')
end

Given(/^a Dispute has been initiated against my Law Firm$/) do
  login_as_agent
  open_dispute # against company B. Then login as company B
  clear_session_before_login
  login_with_credentials 'law_firm_b@t.co', 'test'
end

Given(/^I have created an Agent$/) do
  # covered in the YAML fixtures
end

Then(/^I should be able to allocate the Agent to the Dispute$/) do
  visit '/disputes/1/assign'
  select('James Smith', :from => 'Agent overseeing Dispute:')
  fill_in('summary',    :with => 'Another test summary')
  click_button 'Assign Dispute'
  assert page.has_content?('Initiatiated by company: Webdapper Ltd')
  assert page.has_content?('Represented by: Chris Ashton')
  assert page.has_content?('Opened against company: Maritime Collision Specialists Inc')
  assert page.has_content?('Represented by: James Smith')
end

Then(/^I should be able to edit the summary$/) do
  dispute_url = '/disputes/4'
  visit dispute_url + '/summary'
  assert find_field('dispute_summary').has_content? 'Here is a summary for party A.'
  fill_in 'dispute_summary', :with => 'New summary for party A'
  click_button 'Update Summary'
  visit dispute_url
  assert page.has_content? 'New summary for party A'
end
