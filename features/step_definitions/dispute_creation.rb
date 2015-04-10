# covered in the YAML fixtures
Given(/^I have created at least one Agent account$/) do
end
Given(/^I have created an Agent$/) do
end

Given(/^I have created NO Agent accounts$/) do
  Session.login_with_credentials 'law_firm_with_no_agents@t.co', 'test'
  visit '/disputes/new'
end

Then(/^I should be able to create a new Dispute$/) do
  visit '/disputes/new'
  fill_in('title',       :with => 'Davies versus Jones')
  select('Chris Ashton', :from => 'Agent overseeing Dispute:')
  select('Other',        :from => 'Dispute type:')
  fill_in('summary',     :with => 'Test summary')
  click_button 'Create Dispute'

  assert_equal '/disputes/' + (DBL.dispute_count? + 1).to_s, URL.get_current_uri_path
  assert page.has_content?('Davies versus Jones')
end

When(/^I try to view a Dispute I've not been allocated to yet$/) do
  visit '/disputes/1'
end

When(/^I try to view a Dispute that does not exist$/) do
  visit '/disputes/1337'
end

Given(/^I have submitted a Dispute$/) do
  Session.login_as_agent
end

Then(/^I should be able to initiate it against another Law Firm$/) do
  visit '/disputes/1/open'
  select('Maritime Collision Specialists Inc', :from => 'Select the opposing company:')
  click_button 'Open Dispute'
  assert page.has_content?('Waiting for Maritime Collision Specialists Inc to assign an agent to the dispute.')
end

And(/^I shouldn't be able to reinitiate it against a different Law Firm$/) do
  visit '/disputes/1/open'
  assert page.has_content?('You have already opened this dispute against Maritime Collision Specialists Inc!')
end

Given(/^a Dispute has been initiated against my Law Firm$/) do
  Session.login_as_agent
  step "I should be able to initiate it against another Law Firm"
  Session.login_with_credentials 'law_firm_b@t.co', 'test'
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
  click_button 'Update Details'
  visit dispute_url
  assert page.has_content? 'New summary for party A'
end
