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
  assert page.has_content?('Welcome back, Bob Hope | No new notifications | Logout')
end

Then(/^the URL should be clean, with no notification parameters$/) do
  assert_equal '/disputes/2', URL.get_current_uri_path
  assert_nil URL.get_current_uri_params
end

Given(/^I am logged into the '(.+)' account$/) do |email|
  Session.login_with_credentials email, 'test'
end

Then(/^'(.+)' should get a notification that '(.+)' has sent them a message$/) do |email, sender|
  Session.login_with_credentials email, 'test'
  visit '/notifications'
  assert page.has_content? sender + ' has sent you a message'
end

def send_test_message
  fill_in 'message', :with => 'This is a test message'
  click_button 'Send message'
end

When(/^I send a message to the other Agent in the '(.+)' dispute$/) do |dispute_title|
  $dispute_id = DBL.dispute_title_to_id dispute_title
  visit '/disputes/' + $dispute_id + '/chat'
  send_test_message
end

When(/^I send a message to the mediator in the '(.+)' dispute$/) do |dispute_title|
  $dispute_id = DBL.dispute_title_to_id dispute_title
  visit '/disputes/' + $dispute_id + '/mediation'
  send_test_message
end

When(/^I send a message to '(.+)' in the 'Dispute that is in mediation' dispute$/) do |email|
  agent_id = DBL.email_to_id email
  visit '/disputes/' + $dispute_id + '/mediation-chat/' + agent_id
  send_test_message
end