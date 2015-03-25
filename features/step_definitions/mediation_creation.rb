# @TODO - these step definitions are very slow, partly because there call each other a lot for setup.
# It would be far more efficient to have better fixture data which represents disputes at every stage of mediation
# negotiation.

Given(/^the Dispute is underway and a lifespan has been agreed$/) do
  login_as_agent
  visit '/disputes/' + get_id_of_active_dispute.to_s
  assert_dispute_active
  assert ! dispute_in_mediation
end

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
  clear_session_before_login
  login_with_credentials 'agent_b@t.co', 'test'
  visit '/disputes/' + get_id_of_active_dispute.to_s + '/mediation'
  assert page.has_content? 'The other agent has proposed that WeMediate should be the Mediation Centre in this dispute.'
end

Then(/^I should be able to decline the proposal$/) do
  click_button 'Decline'
end

Then(/^I should be able to accept the proposal$/) do
  click_button 'Accept'
end

Given(/^the Agents have agreed on a Mediation Centre$/) do
    step "the other Agent has proposed a Mediation Centre"
    step "I should be able to accept the proposal"
end

Given(/^I am logged in as the Mediation Centre$/) do
  clear_session_before_login
  credentials = get_credentials_for 'WeMediate'
  login_with_credentials credentials[:email], credentials[:password]
end

Then(/^I should be able to provide a list of available Mediators$/) do
  visit '/disputes/' + get_id_of_active_dispute.to_s + '/mediation'
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
  visit '/disputes/' + get_id_of_active_dispute.to_s + '/mediation'
  choose 'John Smith'
  click_button 'Propose Mediator'
end

Given(/^the other Agent has proposed a Mediator$/) do
  step "the Mediation Centre we've agreed upon has provided a list of available Mediators"
  step "I am logged into an Agent account"
  step "I should be able to propose a Mediator to the other Agent"
  clear_session_before_login
  login_with_credentials 'agent_b@t.co', 'test'
  visit '/disputes/' + get_id_of_active_dispute.to_s + '/mediation'
end

Then(/^the Dispute should be in Mediation$/) do
  assert page.has_content? 'Dispute is now in mediation.'
end
