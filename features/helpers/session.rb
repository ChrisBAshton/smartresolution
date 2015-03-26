class Session
  extend Capybara::DSL

  def self.clear_session_before_login
    visit '/logout'
    visit '/login'
  end

  def self.login_as_law_firm
    Session.clear_session_before_login
    Session.login_with_credentials 'law_firm_a@t.co', 'test'
  end

  def self.login_as_mediation_centre
    Session.clear_session_before_login
    Session.login_with_credentials 'mediation_centre_email@we-mediate.co.uk', 'test'
  end

  def self.login_as_agent
    Session.clear_session_before_login
    Session.login_with_credentials 'agent_a@t.co', 'test'
  end

  def self.login_as_mediator
    Session.clear_session_before_login
    Session.login_with_credentials 'john.smith@we-mediate.co.uk', 'test'
  end

  def self.login_as_individual
    Session.login_as_agent
  end

  def self.login_with_credentials (email, password)
    fill_in 'Email',    :with => email
    fill_in 'Password', :with => password
    click_button 'Login'
  end

end
