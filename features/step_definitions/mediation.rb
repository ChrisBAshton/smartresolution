Given(/^we have not activated round\-table communication$/) do
end

Then(/^I should not be able to communicate with the other Agent$/) do
  visit '/disputes/' + $dispute_id + '/chat'
  assert page.has_content? 'You cannot communicate with the other party at this time. This may be because the dispute is in mediation, has finished, or has not started yet.'
end

Then(/^I should be able to send a message to (.+)$/) do |agent_name|
  # send the message
  visit '/disputes/' + $dispute_id
  find_link('Communicate with ' + agent_name).trigger('click')
  fill_in 'message', :with => 'Test message for ' + agent_name
  click_button 'Send message'

  # login as the agent
  Session.login_as agent_name
  visit '/disputes/' + $dispute_id + '/mediation'

  # make sure the message is there
  assert page.has_content? 'Test message for ' + agent_name
  assert page.has_content? /John Smith wrote this on [0-9]{2}\/[0-9]{2}\/[0-9]{4} [0-9]{2}:[0-9]{2}:[0-9]{2}/
end

Then(/^I should be able to communicate with the Agents in individual threads$/) do
  step "I should be able to send a message to Chris Ashton"
  step "I am logged into a Mediator account"
  step "I should be able to send a message to James Smith"
end

Then(/^there should be no way for either Agent to see the messages of the other$/) do
  Session.login_as 'Chris Ashton'
  visit '/disputes/' + $dispute_id + '/mediation'

  assert page.has_content? 'Test message for Chris Ashton'
  assert !(page.has_content? 'Test message for James Smith')

  Session.login_as 'James Smith'
  visit '/disputes/' + $dispute_id + '/mediation'

  assert page.has_content? 'Test message for James Smith'
  assert !(page.has_content? 'Test message for Chris Ashton')
end

Then(/^I should be able to (enable|disable) round\-table communication$/) do |enable_or_disable|
  visit '/disputes/' + $dispute_id + '/mediation'
  enable_or_disable = enable_or_disable.capitalize
  click_button enable_or_disable + ' Round-Table Communication'
end

Given(/^the Dispute is in round\-table communication mode$/) do
  step "I should be able to enable round-table communication"
end

Then(/^all parties should be able to communicate freely$/) do
  visit '/disputes/' + $dispute_id + '/chat'
  assert page.has_content? 'Send message'

  fill_in 'message', :with => 'Message from mediator'
  click_button 'Send message'

  Session.login_as 'Chris Ashton'
  visit '/disputes/' + $dispute_id + '/chat'
  fill_in 'message', :with => 'Message from Agent A'
  click_button 'Send message'

  Session.login_as 'James Smith'
  visit '/disputes/' + $dispute_id + '/chat'
  fill_in 'message', :with => 'Message from Agent B'
  click_button 'Send message'

  assert page.has_content? 'Message from mediator'
  assert page.has_content? 'Message from Agent A'
  assert page.has_content? 'Message from Agent B'
end

Then(/^free communication should no longer be allowed between all parties$/) do
  assert page.has_content? 'Round-Table Communication is currently disabled.'
end

Given(/^I try to send a message to someone not involved in the dispute$/) do
  visit '/disputes/' + $dispute_id + '/mediation-chat/' + DBL.email_to_id('one_dispute_agent@company.com')
end