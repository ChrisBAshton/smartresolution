def get_id_of_active_dispute
  count = 0
  $data['disputes'].each do |dispute|
    count = count + 1
    return count if dispute['lifespan'] == 'accepted'
  end
end

def assert_can_send_message
  fill_in 'New Message', :with => 'This is a test message'
  click_button 'Send message'
  assert page.has_content? 'This is a test message'
  expect(page).to have_selector('.message', count: 1)
end

def assert_cannot_send_message
  assert page.has_content? 'You do not have permission to view this Dispute!'
end

def id_of_dispute_that_is_fully_underway
  get_id_of_dispute_whose_title_is 'Smith versus Jones'
end

def get_id_of_dispute_whose_title_is (title)
  count = 0
  $data['disputes'].each do |dispute|
    count = count + 1
    return count if dispute['title'] == title
  end
end
