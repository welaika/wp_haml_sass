require 'guard'

guard 'compass', :configuration_file => 'compass.rb' do
  watch('^src/stylesheets/(.*).s(a|c)ss')
end

guard 'coffeescript', :output => 'public/javascripts' do
  watch('^src/coffeescripts/(.*)\.coffee')
end