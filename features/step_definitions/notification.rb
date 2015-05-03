Given(/^a Dispute has been assigned to me$/) do
end

And(/^I am logged into a one-dispute Agent account$/) do
  Session.login_with_credentials 'one_dispute_agent@company.com', 'test'
end

Then(/^I should get a notification about the Dispute$/) do
  # if this ever breaks in the future, it's because we're now triggering notifications
  # for an additional thing, so this may now be 2 notifications or even more.
  assert page.has_content?('1 notifications')
end

Given(/^I have a new notification$/) do
  visit '/notifications'
  assert !page.has_content?('You have no new notifications.')
  assert page.has_css?('.notification__link')
end

When(/^I click on the associated link$/) do
  first('.notification__link').click
end

Then(/^the notification should be marked as read$/) do
  # Notifications should appear between the welcome and the logout.
  # If not, we have no notifications and the notification has therefore
  # been marked as read.
  assert page.has_content?('Welcome back, Bob Hope | Logout')
end

Then(/^the URL should be clean, with no notification parameters$/) do
  assert_equal '/disputes/2', URL.get_current_uri_path
  assert_nil URL.get_current_uri_params
end

Given(/^my dispute is in mediation$/) do
  $dispute_id = DBL.dispute_title_to_id 'Dispute that is in mediation'
end

When(/^I send a message to the other Agent in a dispute$/) do
  $dispute_id = DBL.dispute_title_to_id 'Smith versus Jones'
  visit '/disputes/' + $dispute_id + '/chat'
  fill_in 'message', :with => 'This is a test message'
  click_button 'Send message'
end

Then(/^they should get a notification$/) do
  Session.login_with_credentials 'agent_b@t.co', 'test'
  visit '/notifications'
  assert page.has_content? 'Chris Ashton has sent you a message'
  puts page.body
end

When(/^I send a message to the mediator of a dispute$/) do
  pending # Write code here that turns the phrase above into concrete actions
end

When(/^I send a message to the agent of a dispute$/) do
  pending # Write code here that turns the phrase above into concrete actions
end
