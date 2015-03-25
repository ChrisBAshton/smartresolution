Given(/^I am looking at an Organisation's profile$/) do
  visit '/accounts/' + email_to_id('law_firm_a@t.co').to_s
end

Then(/^I should see the Organisation description$/) do
  assert page.has_content? 'This organisation has not provided a description.'
end

Given(/^I am looking at an Individual's profile$/) do
  visit '/accounts/' + email_to_id('agent_a@t.co').to_s
end

Then(/^I should see the Individual's CV$/) do
  assert page.has_content? 'This individual has not provided a CV.'
end

Then(/^I should see which Organisation the Individual works for$/) do
  assert page.has_content? 'Organisation: Webdapper Ltd'
end

Then(/^I should be able to edit my Organisation's description$/) do
  clear_session_before_login
  login_as_law_firm
  visit '/settings'
  fill_in('Description', :with => 'My company description')
  click_button 'Update Profile'
  assert page.has_content? 'Profile updated.'
end

Then(/^I should be able to edit my CV$/) do
  visit '/settings'
  fill_in('CV', :with => 'My CV')
  click_button 'Update Profile'
  assert page.has_content? 'Profile updated.'
end
