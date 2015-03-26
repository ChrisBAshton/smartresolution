# DBL = Database Layer class.
# Queries the fixture data and returns information useful for step definitions.

require 'yaml'

class DBL

  @data = YAML.load_file(File.expand_path('../../../data/fixtures/fixture_data.yml', __FILE__))

  def self.dispute_title_to_id(title)
    count = 0
    @data['disputes'].each do |dispute|
      count = count + 1
      return count.to_s if dispute['title'] == title
    end
    raise 'Could not find dispute of title: ' + title + ' in the fixture data'
  end

  def self.email_to_id(email)
    count = 0
    @data['organisations'].each do |org|
      count = count + 1
      return count.to_s if org['account_details']['email'] == email
      org['individuals'].each do |individual|
        count = count + 1
        return count.to_s if individual['account_details']['email'] == email
      end
    end
  end

  def self.dispute_count?
    @data['disputes'].size
  end

  def self.get_credentials_for(organisation_name)
    @data['organisations'].each do |organisation|
      if organisation['details']['name'] == organisation_name
        return {
          :email    => organisation['account_details']['email'],
          :password => organisation['account_details']['password']
        }
      end
    end
  end

end
