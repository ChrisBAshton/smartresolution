class State
  extend Capybara::DSL

  def self.dispute_in_mediation?
    visit '/disputes/' + $dispute_id
    return page.has_content? 'In Mediation'
  end

  def self.dispute_active?
    visit '/disputes/' + $dispute_id
    countdown_message = page.has_content? /Dispute has started and ends in [0-9] hours, ([0-9]+) minutes/
    # agents should be able to communicate if dispute is active
    can_communicate = page.has_content? 'Communicate'
    return countdown_message && can_communicate
  end

end
