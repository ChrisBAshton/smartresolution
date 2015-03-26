class State
  extend Capybara::DSL

  def self.dispute_in_mediation?
    visit '/disputes/' + $dispute_id
    return page.has_content? 'In Mediation'
  end

  def self.dispute_active?
    visit '/disputes/' + $dispute_id
    # either 3 hours 20 or 3 hours 19, depending on how slow the tests run.
    countdown_message = page.has_content? /Dispute has started and ends in 3 hours, ([0-9]+) minutes/
    # agents should be able to communicate if dispute is active
    can_communicate = page.has_content? 'Communicate'
    return countdown_message && can_communicate
  end

end
