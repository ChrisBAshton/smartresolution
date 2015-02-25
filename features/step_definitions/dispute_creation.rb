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

  assert_equal '/disputes/view/2', get_current_uri_path # 2 because we've already defined 1 in the YAML fixture data
  assert page.has_content?('Davies versus Jones')
end