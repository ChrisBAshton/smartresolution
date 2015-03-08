Given(/^both Agents have submitted the Dispute$/) do
  login_as_agent
  visit '/disputes/2'
end

Then(/^I should be able to make a lifespan offer$/) do
  visit '/disputes/2/lifespan'
  make_lifespan_offer
  assert page.has_content? 'You have sent a lifespan offer and are waiting for the other Agent to accept.'
end

And(/^regardless of who submitted the Dispute first$/) do
  # login as the other agent, decline offer and create another offer, to prove either agent can propose lifespan
  clear_session_before_login
  login_with_credentials 'agent_b@t.co', 'test'
  visit '/disputes/2/lifespan'
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
  visit '/disputes/2/lifespan'
  click_button accept_or_decline
end

Given(/^the Dispute is fully underway$/) do
  pending # express the regexp above with the code you wish you had
end

Then(/^the Dispute should continue normally despite the renegotiation offer$/) do
  pending # express the regexp above with the code you wish you had
end
