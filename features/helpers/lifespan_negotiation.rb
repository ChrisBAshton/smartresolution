def make_lifespan_offer
  fill_in 'Dispute lifespan start date:',              :with => '2017/01/01 11:00'
  fill_in 'Dispute lifespan end date:',                :with => '2018/01/01 11:00'
  fill_in 'Offer must be accepted by other party by:', :with => '2016/01/01 11:00'
  click_button 'Make lifespan offer'
end

def get_dispute_which_has_no_lifespan
  count = 0
  $data['disputes'].each do |dispute|
    count = count + 1
    return count.to_s if dispute['title'] == 'A fully assigned dispute with no lifespan'
  end
end

def get_dispute_which_has_existing_lifespan
  count = 0
  $data['disputes'].each do |dispute|
    count = count + 1
    return count.to_s if dispute['lifespan'] == 'accepted'
  end
end

def assert_dispute_active
  assert page.has_content? /Dispute has started and ends in 3 hours, ([0-9]+) minutes/
end
