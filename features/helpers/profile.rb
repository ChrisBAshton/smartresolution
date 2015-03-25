def email_to_id(email)
  count = 0
  $data['organisations'].each do |org|
    count = count + 1
    return count if org['account_details']['email'] == email
    org['individuals'].each do |ind|
      count = count + 1
      return count if ind['account_details']['email'] == email
    end
  end
end
