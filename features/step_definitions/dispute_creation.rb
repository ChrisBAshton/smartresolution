Given(/^I have created at least one Agent account$/) do
end

Given(/^I have created NO Agent accounts$/) do
  visit '/logout' # logout of 'law_firm_email' as it has an Agent account
  visit '/login'
  login_with_credentials 'law_firm_email_with_no_agents', 'test'
  visit '/disputes/new'
end

Then(/^I should be able to create a new Dispute$/) do
  visit '/disputes/new'
  fill_in('title',       :with => 'Davies versus Jones')
  select('Chris Ashton', :from => 'Agent overseeing Dispute:')
  select('Other',        :from => 'Dispute type:')
  click_button 'Create Dispute'

  assert_equal 'http://127.0.0.1:8000/disputes/view/1', current_url
  assert page.has_content?('Davies versus Jones')
end

Then(/^the Agent should be allocated to that Dispute$/) do
  pending # express the regexp above with the code you wish you had
end

Given(/^I am logged into an Agent account$/) do
  pending # express the regexp above with the code you wish you had
end

When(/^a Dispute has been assigned to me$/) do
  pending # express the regexp above with the code you wish you had
end

Then(/^I should get a notification about the Dispute$/) do
  pending # express the regexp above with the code you wish you had
end