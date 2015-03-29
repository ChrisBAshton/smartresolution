class Session
  extend Capybara::DSL

  def self.clear_session_before_login
    visit '/logout'
    visit '/login'
  end

  def self.login_as (name)
    Session.clear_session_before_login
    login_details = DBL.get_credentials_for name
    Session.login_with_credentials login_details[:email], login_details[:password]
  end

  def self.login_as_law_firm
    Session.login_with_credentials 'law_firm_a@t.co', 'test'
  end

  def self.login_as_mediation_centre
    Session.login_with_credentials 'mediation_centre_email@we-mediate.co.uk', 'test'
  end

  def self.login_as_agent
    Session.login_with_credentials 'agent_a@t.co', 'test'
  end

  def self.login_as_mediator
    Session.login_with_credentials 'john.smith@we-mediate.co.uk', 'test'
  end

  def self.login_as_individual
    Session.login_as_agent
  end

  def self.login_with_credentials (email, password)
    Session.clear_session_before_login
    fill_in 'Email',    :with => email
    fill_in 'Password', :with => password
    click_button 'Login'
  end

end
