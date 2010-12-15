ssh_user          = "your@user.com"
remote_root       = "/var/www/your_wp_site/wp-content/themes/your_theme/"
vhost             = "http://your_wp_site.com"

local_db_user     = "your_user"
local_db_password = "your_password"
local_db_name     = "your_db_name"

remote_db_user     = "your_user"
remote_db_password = "your_password"
remote_db_name     = "your_db_name"

# Usage:
# $ rake push

desc "push"
task :push do
  # export all the local Wordpress data into dump.sql
  system "mysqldump --user #{local_db_user} -p#{local_db_password} #{local_db_name} wp_posts wp_postmeta wp_term_relationships wp_term_taxonomy wp_terms wp_options > dump.sql"
  # replace the domain name to the remote one
  system "echo 'UPDATE wp_options SET option_value=\"#{vhost}\" WHERE option_name=\"siteurl\" OR option_name=\"home\";' >> dump.sql"
  # copy remotely all the theme directory
  system "rsync -avz --exclude-from=.rsync_exclude --delete . #{ssh_user}:#{remote_root}"
  # copy remotely all the uploads directory
  system "rsync -avz --exclude-from=.rsync_exclude --delete ../../uploads/ #{ssh_user}:#{remote_root}../../uploads/"
  # import remotely dump.sql
  system "ssh #{ssh_user} 'cd #{remote_root} && mysql --user #{remote_db_user} -p#{remote_db_password} -D #{remote_db_name} < dump.sql && rm dump.sql'"
  # that's it remove dump.sql
  system "rm dump.sql"
end