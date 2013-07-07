
# =============================================================================
# REQUIRED VARIABLES
# =============================================================================
set :application, "Forthicime"
#set :domain,      "#{application}.com"
set :deploy_to,   "/kunden/homepages/32/d299567504/htdocs/laboratoire-marachlian/forthicime"


# =============================================================================
# SCM OPTIONS
# =====================================================================
set :repository,  "git@github.com:kernel13/forthicime.git"
set :scm,         :git
set :scm_user, "kernel13"
set :scm_password, "ciotat13"
set :branch, 'master' 
set :scm_verbose, true

# =============================================================================
# SSH OPTIONS
# =============================================================================
set :user, "u55016233"
set :use_sudo, false


# =============================================================================
# ROLES
# =============================================================================
role :web,        "laboratoire-marachlian.fr" 	                        # Your HTTP server, Apache/etc
role :app,        "laboratoire-marachlian.fr", :primary => true       # This may be the same as your `Web` server

# =============================================================================
# CAPISTRANO OPTIONS
# =============================================================================
set  :keep_releases,  3
set  :deploy_via, :copy

# =============================================================================
# Doctrine
# =============================================================================
set :model_manager, "doctrine"

# =============================================================================
# LOG LEVEL
# =============================================================================
# Be more verbose by uncommenting the following line
logger.level = Logger::MAX_LEVEL

# =============================================================================
# Deployment Using SCP
# =============================================================================
# Deployment uses SFTP by default when you use deploy_via :copy, and there
# doesn't seem to be any way to configure it.  Unfortunately, we don't run
# SFTP on our servers, so it fails.  This forces it to use SCP instead.
# http://www.capify.org/index.php/OverridingTaskCommands
#
module UseScpForDeployment
  def self.included(base)
    base.send(:alias_method, :old_upload, :upload)
    base.send(:alias_method, :upload,     :new_upload)
  end
  
  def new_upload(from, to)
    old_upload(from, to, :via => :scp)
  end
end
 
Capistrano::Configuration.send(:include, UseScpForDeployment)

