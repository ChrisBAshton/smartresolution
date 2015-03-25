Given(/^both Agents have submitted the Dispute$/) do
  login_as_agent
  visit '/disputes/' + get_dispute_which_has_no_lifespan + '/lifespan/new'
end

Then(/^I should be able to make a lifespan offer$/) do
  make_lifespan_offer
  assert page.has_content? 'You have sent a lifespan offer and are waiting for the other Agent to accept.'
end

And(/^regardless of who submitted the Dispute first$/) do
  # login as the other agent, decline offer and create another offer, to prove either agent can propose lifespan
  clear_session_before_login
  login_with_credentials 'agent_b@t.co', 'test'
  visit '/disputes/' + get_dispute_which_has_no_lifespan + '/lifespan'
  click_button 'Decline'
  make_lifespan_offer
  assert page.has_content? 'You have sent a lifespan offer and are waiting for the other Agent to accept.'
end

Given(/^the other Agent has sent me a Dispute lifespan offer$/) do
  # set up an offer from the other Agent
  step "both Agents have submitted the Dispute"
  step "I should be able to make a lifespan offer"
end

Then(/^I should be able to (Accept|Decline) the offer$/) do |accept_or_decline|
  # login as the other agent, accept offer
  clear_session_before_login
  login_with_credentials 'agent_b@t.co', 'test'
  visit '/disputes/' + get_dispute_which_has_no_lifespan + '/lifespan'
  click_button accept_or_decline
end

Given(/^the Dispute is fully underway$/) do
  login_as_agent
  visit '/disputes/' + get_dispute_which_has_existing_lifespan
  # either 3 hours 20 or 3 hours 19, depending on how slow the tests run.
  assert_dispute_active
  assert page.has_content? 'Communicate'
  visit '/disputes/' + get_dispute_which_has_existing_lifespan + '/lifespan/new'
end

Then(/^the Dispute should continue normally despite the renegotiation offer$/) do
  visit '/disputes/' + get_dispute_which_has_existing_lifespan
  assert page.has_content? 'Communicate'
end

When(/^I make a new lifespan offer$/) do
  step "I should be able to make a lifespan offer"
end

And(/^the other Agent accepts the offer$/) do
  clear_session_before_login
  login_with_credentials 'agent_b@t.co', 'test'
  visit '/disputes/' + get_dispute_which_has_existing_lifespan + '/lifespan'
  click_button 'Accept'
end

Then(/^the new lifespan should take immediate effect$/) do
  # @TODO - this will need updating one day!
  assert page.has_content? 'Starting: 01/01/2017 11:00:00'
  assert page.has_content? 'Ending: 01/01/2018 11:00:00'
  visit '/disputes/' + get_dispute_which_has_existing_lifespan
end
