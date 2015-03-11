And(/^the Dispute is not in Mediation$/) do
end

Then(/^I (should|should NOT) be able to send a message via the Dispute$/) do |should_or_should_not|
  visit '/disputes/' + get_id_of_active_dispute.to_s + '/chat'
  if should_or_should_not == 'should NOT'
    assert_cannot_send_message
  else
    assert_can_send_message
  end
end

Then(/^I should be able to mark the Dispute as resolved$/) do
  visit '/disputes/1/close'
  choose 'Dispute was resolved successfully'
  click_button 'Close Dispute'
end

Then(/^I should be able to take the Dispute to court$/) do
  visit '/disputes/1/close'
  choose 'Unable to resolve dispute with Online Dispute Resolution'
  click_button 'Close Dispute'
end

Then(/^the Dispute should close (successfully|unsuccessfully)$/) do |successful|
  assert page.has_content? 'You have successfully closed the dispute.'
end
