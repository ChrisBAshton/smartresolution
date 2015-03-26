And(/^the Dispute is not in Mediation$/) do
  visit '/disputes/' + get_id_of_active_dispute.to_s
  assert ( ! (page.has_content? 'In Mediation') )
end

Then(/^I should be able to start the Mediation process$/) do
  visit '/disputes/' + get_id_of_active_dispute.to_s
  assert page.has_css?("a[href='/disputes/" + get_id_of_active_dispute.to_s + "/mediation']")
  # sanity check
  assert ! page.has_css?("a[href='/neoiwgniowengo']")
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

Then(/^I should be able to upload evidence to the Dispute$/) do
  visit '/disputes/' + id_of_dispute_that_is_fully_underway.to_s + '/evidence/new'
  attach_file('fileToUpload', File.expand_path('../../../webapp/view/images/logo.png', __FILE__))
  click_button 'Upload'
  assert page.has_content? 'File uploaded.'
end

Given(/^the Dispute is NOT fully underway$/) do
  # @TODO - this is quite a nice model. Set a global variable in one of the "Given" setup steps,
  # refer to that variable throughout the rest of the scenario. Cleaner, more readable tests.
  # Let's apply this everywhere where I haven't already applied it!
  $dispute_id = get_id_of_dispute_whose_title_is 'A fully assigned dispute with no lifespan'
end

When(/^I attempt to view the Evidence page$/) do
  visit '/disputes/' + $dispute_id.to_s + '/evidence'
end
