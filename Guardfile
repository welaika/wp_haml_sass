require 'guard'
require 'fileutils'

guard 'compass', :configuration_file => 'compass.rb' do
  watch %r{^src/stylesheets/(.*).s(a|c)ss}
end

guard 'coffeescript', :output => 'public/javascripts' do
  watch %r{^src/coffeescripts/(.*)\.coffee}
end
