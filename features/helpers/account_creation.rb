def login_as_law_firm
  login_with_credentials 'law_firm_email', 'test'
end

def login_as_mediation_centre
  login_with_credentials 'mediation_centre_email', 'test'
end

def login_with_credentials (email, password)
  fill_in 'Email',    :with => email
  fill_in 'Password', :with => password
  click_button 'Login'
end