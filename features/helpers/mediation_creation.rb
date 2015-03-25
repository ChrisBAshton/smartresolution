def dispute_in_mediation
  return page.has_content? 'In Mediation'
end

def get_credentials_for(organisation_name)
  $data['organisations'].each do |organisation|
    if organisation['details']['name'] == organisation_name
      return {
        :email    => organisation['account_details']['email'],
        :password => organisation['account_details']['password']
      }
    end
  end
end
