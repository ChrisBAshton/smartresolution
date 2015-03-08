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
