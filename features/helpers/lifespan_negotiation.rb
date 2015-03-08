def make_lifespan_offer
  fill_in 'Dispute lifespan start date:',              :with => '2017/01/01 11:00'
  fill_in 'Dispute lifespan end date:',                :with => '2018/01/01 11:00'
  fill_in 'Offer must be accepted by other party by:', :with => '2016/01/01 11:00'
  click_button 'Make lifespan offer'
end
