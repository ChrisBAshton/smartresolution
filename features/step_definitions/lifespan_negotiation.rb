Given(/^both Agents have submitted the Dispute$/) do
  $dispute_id = DBL.dispute_title_to_id 'A fully assigned dispute with no lifespan'
  Session.login_as_agent
  visit '/disputes/' + $dispute_id + '/lifespan/new'
end

Then(/^I should be able to make a lifespan offer$/) do
  visit '/disputes/' + $dispute_id + '/lifespan/new'
  fill_in 'Dispute lifespan start date:',              :with => '2017/01/01 11:00'
  fill_in 'Dispute lifespan end date:',                :with => '2018/01/01 11:00'
  fill_in 'Offer must be accepted by other party by:', :with => '2016/01/01 11:00'
  click_button 'Make lifespan offer'
  assert page.has_content? 'You have sent a lifespan offer and are waiting for the other Agent to accept.'
end

And(/^regardless of who submitted the Dispute first$/) do
  # login as the other agent, decline offer and create another offer, to prove either agent can propose lifespan
  Session.login_with_credentials 'agent_b@t.co', 'test'
  visit '/disputes/' + $dispute_id + '/lifespan'
  click_button 'Decline'
  step "I should be able to make a lifespan offer"
  assert page.has_content? 'You have sent a lifespan offer and are waiting for the other Agent to accept.'
end

Given(/^the other Agent has sent me a Dispute lifespan offer$/) do
  # set up an offer from the other Agent
  step "both Agents have submitted the Dispute"
  step "I should be able to make a lifespan offer"
end

Then(/^I should be able to (Accept|Decline) the offer$/) do |accept_or_decline|
  Session.login_with_credentials 'agent_b@t.co', 'test'
  visit '/disputes/' + $dispute_id + '/lifespan'
  click_button accept_or_decline
end

Then(/^the Dispute should continue normally despite the renegotiation offer$/) do
  visit '/disputes/' + $dispute_id
  assert page.has_content? 'Communicate'
end

When(/^I make a new lifespan offer$/) do
  step "I should be able to make a lifespan offer"
end

And(/^the other Agent accepts the offer$/) do
  Session.login_with_credentials 'agent_b@t.co', 'test'
  visit '/disputes/' + $dispute_id + '/lifespan'
  click_button 'Accept'
end

Then(/^the new lifespan should take immediate effect$/) do
  # @TODO - this will need updating one day! Should make this dynamic
  assert page.has_content? 'Starting: 01/01/2017 11:00:00'
  assert page.has_content? 'Ending: 01/01/2018 11:00:00'
  visit '/disputes/' + $dispute_id
end
