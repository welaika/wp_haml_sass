require 'guard'
require 'fileutils'

guard 'compass', :configuration_file => 'compass.rb' do
  watch('^src/stylesheets/(.*).s(a|c)ss')
end

guard 'coffeescript', :output => 'public/javascripts' do
  watch('^src/coffeescripts/(.*)\.coffee')
end

Thread.new do
  sleep(0.5)
  puts "Cold start! Recompiling..."
  Dir.glob('**/*.{sass,coffee}') {|filename|
    puts "* #{filename}"
    FileUtils.touch(filename)
  }
end


