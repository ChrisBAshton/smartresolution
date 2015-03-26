Then(/^I should be able to propose Mediation$/) do
  find_link('Mediation').trigger('click')
  assert page.has_content? 'Make Mediation Proposal'
end

Then(/^my choice of Mediation Centre should be presented to the other Agent$/) do
  select 'WeMediate', :from => 'Propose a Mediation Centre:'
  click_button 'Make Mediation Proposal'
  assert page.has_content? 'You have proposed WeMediate to mediate your dispute.'
end

Given(/^the other Agent has proposed a Mediation Centre$/) do
  step "I should be able to propose Mediation"
  step "my choice of Mediation Centre should be presented to the other Agent"
  Session.clear_session_before_login
  Session.login_with_credentials 'agent_b@t.co', 'test'
  visit '/disputes/' + $dispute_id + '/mediation'
  assert page.has_content? 'The other agent has proposed that WeMediate should be the Mediation Centre in this dispute.'
end

Then(/^I should be able to decline the proposal$/) do
  click_button 'Decline'
end

Then(/^I should be able to accept the proposal$/) do
  click_button 'Accept'
end

Given(/^the Agents have agreed on a Mediation Centre$/) do
  $dispute_id = DBL.dispute_title_to_id 'Dispute that has agreed on a Mediation Centre'
end

Given(/^I am logged in as the Mediation Centre$/) do
  Session.clear_session_before_login
  credentials = DBL.get_credentials_for 'WeMediate'
  Session.login_with_credentials credentials[:email], credentials[:password]
end

Then(/^I should be able to provide a list of available Mediators$/) do
  visit '/disputes/' + $dispute_id + '/mediation'
  assert page.has_content? 'John Smith Unavailable'
  check 'John Smith'
  click_button 'Submit Available Mediators'
  assert page.has_content? 'John Smith Available'
end

Given(/^the Mediation Centre we've agreed upon has provided a list of available Mediators$/) do
  step "the Agents have agreed on a Mediation Centre"
  step "I am logged in as the Mediation Centre"
  step "I should be able to provide a list of available Mediators"
end

Then(/^I should be able to propose a Mediator to the other Agent$/) do
  visit '/disputes/' + $dispute_id + '/mediation'
  choose 'John Smith'
  click_button 'Propose Mediator'
end

Given(/^the other Agent has proposed a Mediator$/) do
  step "the Mediation Centre we've agreed upon has provided a list of available Mediators"
  step "I am logged into an Agent account"
  step "I should be able to propose a Mediator to the other Agent"
  Session.clear_session_before_login
  Session.login_with_credentials 'agent_b@t.co', 'test'
  visit '/disputes/' + $dispute_id + '/mediation'
end

Then(/^the Dispute should be in Mediation$/) do
  # this is the immediate feedback after accepting Mediator proposal
  assert page.has_content? 'Dispute is now in mediation.'
  # we want to make sure that the mediation state change was persistent
  visit '/disputes/' + $dispute_id
  assert page.has_content? 'Being mediated by John Smith of WeMediate.'
end
