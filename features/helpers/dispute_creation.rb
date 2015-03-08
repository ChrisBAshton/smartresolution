def open_dispute
  visit '/disputes/1/open'
  select('Maritime Collision Specialists Inc', :from => 'Select the opposing company:')
  click_button 'Open Dispute'
  assert page.has_content?('Waiting for Maritime Collision Specialists Inc to assign an agent to the dispute.')
end

def dispute_count
  $data['disputes'].size
end
