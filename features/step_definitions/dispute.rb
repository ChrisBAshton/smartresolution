And(/^the Dispute is not in Mediation$/) do
  $dispute_id = DBL.dispute_title_to_id 'Smith versus Jones'
  visit '/disputes/' + $dispute_id
  assert ( ! (page.has_content? 'In Mediation') )
end

Then(/^I should be able to start the Mediation process$/) do
  visit '/disputes/' + $dispute_id
  assert page.has_css?("a[href='/disputes/" + $dispute_id + "/mediation']")
  # sanity check
  assert ! page.has_css?("a[href='/neoiwgniowengo']")
end

Then(/^I (should|should NOT) be able to send a message via the Dispute$/) do |should_or_should_not|
  visit '/disputes/' + $dispute_id + '/chat'
  if should_or_should_not == 'should NOT'
    assert page.has_content? 'You do not have permission to view this Dispute!'
  else
    fill_in 'New Message', :with => 'This is a test message'
    click_button 'Send message'
    assert page.has_content? 'This is a test message'
    expect(page).to have_selector('.message', count: 1)
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
  visit '/disputes/' + $dispute_id + '/evidence/new'
  attach_file('fileToUpload', File.expand_path('../../../webapp/view/images/logo.png', __FILE__))
  click_button 'Upload'
  assert page.has_content? 'File uploaded.'
end

When(/^I attempt to view the '(.+)' page$/) do |page|
  visit '/disputes/' + $dispute_id + '/' + page
end
