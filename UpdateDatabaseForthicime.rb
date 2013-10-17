
#!/usr/bin/env ruby
require 'logger'
require 'iconv'
require 'fileutils'

# Use to force the encoding to UTF-8
class String
  def force_encoding(enc)
    ::Iconv.conv('UTF-8//IGNORE', 'UTF-8', self + ' ')[0..-2]
  end
end

env = ARGV[0] || 'dev'
time = Time.now.getutc

if env == 'prod'

	csv = "#{ENV['HOME']}/laboratoire-marachlian/Admin/Analyses/FichierCSV"
	orders = "#{ENV['HOME']}/laboratoire-marachlian/Admin/Analyses/FichierCSV/Orders"
	command = "php6 #{ENV['HOME']}/laboratoire-marachlian/forthicime/current/app/console"
	logName = "#{ENV['HOME']}/laboratoire-marachlian/forthicime/current/app/logs/UpdateDatabaseForthicime#{env}_#{time}.log"

else

	csv = File.dirname(__FILE__) + '/Analyses/FichierCSV/csv'
	orders = File.dirname(__FILE__) + '/Analyses/FichierCSV/Orders'
	command = File.dirname(__FILE__) + "/app/console"
	logName = "./app/logs/UpdateDatabaseForthicime#{env}_#{time}.log"

end

log = Logger.new(logName, 'daily' );
log.level = Logger::DEBUG

log.info "================================================================================"
log.info "Starting UpdateDatabaseForthicime..."
log.info "================================================================================"


puts "Running script for env=#{env}"

backup = csv + '/csvBackup'	
processed = orders + '/processed'
errorPath = orders + '/failure'

Dir.mkdir(backup) unless Dir[backup] != nil 
Dir.mkdir(processed) unless Dir[processed] != nil
Dir.mkdir(errorPath) unless Dir[errorPath] != nil

log.info "csv: #{csv}"
log.info "orders: #{orders}"
log.info "command: #{command}"

# Synchronizatin is starting
log.info "Running: #{command} StartSynchronization --env=#{env}"
output = `#{command} StartSynchronization --env=#{env}`

if env == 'prod'
	output = output.lines.first
end

log.info "output: #{output}"
synchronizaitonID = output.split("SynchronizationID:").last.chomp
log.info "synchronizaitonID: #{synchronizaitonID}"
puts "synchId #{synchronizaitonID}"
# Check if an id was returned
if synchronizaitonID.empty?
	log.error "Synchronization id is empty"
	puts "Erreur: synchronizaitonID est vide"
	exit
end

#create one file by order
log.info "================================================================================"
log.info " Generate file orders in progress...						  "
log.info "================================================================================"
log.info "There is currently #{Dir.glob(csv + '/*.csv').count} in #{csv}"
log.info "================================================================================"
i = 0
Dir.glob(csv + '/*.csv') do |file|
	File.open(file).read.each_line do |line|
		begin			
			if !line.strip().empty?			
				i += 1
				order = orders + "/#{File.basename(file, '.csv')}_#{i}.csv"
				File.open(order, 'w'){|f| f.write(line) }
			else
				log.info "The current line is empty"
			end
		rescue Exception => e
			log.error e.message
		end
	end

	FileUtils.move(file, backup)
end

log.info "================================================================================"
log.info "#{i} file(s) where created"
log.info "================================================================================"


#Update the number of transaction that will be done
log.info " Update total number of transaction "
puts "#{command} UpdateTotalTransaction #{ Dir.glob(orders + '/*.csv').count } #{synchronizaitonID}  --env=#{env}"
log.info "Runing: #{command} UpdateTotalTransaction #{ Dir.glob(orders + '/*.csv').count } #{synchronizaitonID}"
`#{command} UpdateTotalTransaction #{ Dir.glob(orders + '/*.csv').count } #{synchronizaitonID}  --env=#{env}`

# 
# => Manage Clients
#
i = 0
log.info "================================================================================"
log.info " Updates client...							  "
log.info "================================================================================"
%w{Ajout Modif Supprime}.each do |w|
	log.info "Looking for " + "*#{w}_Client*.csv"
	sorted_files = Dir.glob(orders + "/*#{w}_Client*.csv").sort!{|a,b| File.mtime(a) <=> File.mtime(b) }	
	#Dir.glob(orders + "/*#{w}_Client*.csv") do |file|
	sorted_files.each do |file|
		begin
			i += 1			
			puts file
			log.debug "Read file #{file}"
			action = File.open(file).read
			a = File.basename(file, '.csv')

			if !action.strip().empty?
				
				puts "Gets parameters"
				log.debug "Read parameters from #{action}"
				id, nom, idFth, prenom, nomPrenom = action.force_encoding("iso-8859-1").split(';')		

				if (id && nom && idFth && prenom && nomPrenom)
					
					log.debug "Update client #{a.split('_').first}"
					puts "Upate Client: " + a.split('_').first
					log.debug "Running: #{command} UpdateClients #{a.split('_').first} #{id} '#{nom}' '#{prenom}' '#{nomPrenom.chomp}' #{synchronizaitonID}"
					puts "#{command} UpdateClients #{a.split('_').first} #{id} '#{nom}' '#{prenom}' '#{nomPrenom.chomp}' #{synchronizaitonID} --env=#{env}"
					output = `#{command} UpdateClients #{a.split('_').first} #{id} "#{nom}" "#{prenom}" "#{nomPrenom.chomp}" #{synchronizaitonID} --env=#{env}`
					log.info "output = #{output}"

					log.info "Move file #{file}"
					FileUtils.move(file, processed)	
					
				else
					#not all attribute where provided
					log.error "#{command} AddMessage #{a.split('_').first} #{a.split('_')[1].downcase} #{a.split('_')[1]} #{synchronizaitonID} 'Un ou plusieurs attributs sont manquant. Le fichier csv #{file} contient les valeurs suivantes: #{action}'"
					`#{command} AddMessage #{a.split('_').first} #{a.split('_')[1].downcase} #{synchronizaitonID} "Un ou plusieurs attributs sont manquant. Le fichier csv #{file} contient les valeurs suivantes: #{action}" --env=#{env}`
					FileUtils.move(file, errorPath)
				end
			else				
				#the order file was empty
				log.error "#{command} AddMessage #{a.split('_').first} #{a.split('_')[1].downcase} #{synchronizaitonID} 'Le fichier #{file} est vide'"
				`#{command} AddMessage #{a.split('_').first} #{a.split('_')[1].downcase} #{synchronizaitonID} "Le fichier #{file} est vide" --env=#{env}`
				FileUtils.move(file, errorPath)
			end
			log.info "--------------------------------------------------------------------------------"
		rescue Exception => e
                        log.info "An error occured while computing #{file}"
                        log.info e.message
		end
	end
end
log.info "================================================================================"
log.info "#{i} client(s) where updated													  "
log.info "================================================================================"

#
# => Manage Medecin
#
i = 0
log.info "================================================================================"
log.info " Updates medecin...							  "
log.info "================================================================================"
%w{Ajout Modif Supprime}.each do |w|
	log.info "Looking for " + "*#{w}_Medecin*.csv"
	sorted_files = Dir.glob(orders + "/*#{w}_Medecin*.csv").sort!{|a,b| File.mtime(a) <=> File.mtime(b) }	
	#Dir.glob(orders + "/*#{w}_Medecin*.csv") do |file|
	sorted_files.each do |file|
		begin
			i += 1
			
			puts file
			log.debug "Read file #{file}"
			action = File.open(file).read
			a = File.basename(file, '.csv')

			if !action.strip().empty?
				puts "Get parameters"
				log.debug "Read parameters from #{action}"
				id, nom, identifiant, password, idFth = action.force_encoding("iso-8859-1").split(';')

				if (id && nom && identifiant && password && idFth)
					
					log.debug "Update client #{a.split('_').first}"
					puts "Update Medecin: " + a.split('_').first
					log.debug "Running: #{command} UpdateMedecins #{a.split('_').first} #{id} '#{nom}' '#{identifiant}' '#{password}' #{synchronizaitonID}"
					puts "#{command} UpdateMedecins #{a.split('_').first} #{id} '#{nom}' '#{identifiant}' '#{password}' #{synchronizaitonID} --env=#{env}"

					output = `#{command} UpdateMedecins #{a.split('_').first} #{id} "#{nom}" "#{identifiant}" "#{password}" #{synchronizaitonID} --env=#{env}` 
					log.info "output = #{output}"

					log.info "Move file #{file}"
					FileUtils.move(file, processed)	
				else
					#not all attribute where provided
					log.error "#{command} AddMessage #{a.split('_').first} #{a.split('_')[1].downcase} #{synchronizaitonID} 'Un ou plusieurs attributs sont manquant. Le fichier csv #{file} contient les valeurs suivantes: #{action}'"
					`#{command} AddMessage #{a.split('_').first} #{a.split('_')[1]} #{a.split('_')[1].downcase} #{synchronizaitonID} "Un ou plusieurs attributs sont manquant. Le fichier csv #{file} contient les valeurs suivantes: #{action}" --env=#{env}`
					FileUtils.move(file, errorPath)
				end
			else
				#the order file was empty
				log.error "#{command} AddMessage #{a.split('_').first} #{a.split('_')[1].downcase} #{synchronizaitonID} 'Le fichier #{file} est vide'"
				`#{command} AddMessage #{a.split('_').first} #{a.split('_')[1].downcase} #{synchronizaitonID} "Le fichier #{file} est vide" --env=#{env}`
				FileUtils.move(file, errorPath)
			end
			log.info "--------------------------------------------------------------------------------"
		rescue Exception => e
             log.info "An error occured while computing #{file}"
             log.info e.message
		end
	end
end
log.info "================================================================================"
log.info "#{i} medecin(s) where updated													  "
log.info "================================================================================"


#
# => Manage Dossier
#
i = 0
log.info "================================================================================"
log.info " Updates dossier...							  "
log.info "================================================================================"
%w{Ajout Modif Supprime}.each do |w|
	log.info "Looking for " + "*#{w}_Dossier*.csv"
	sorted_files = Dir.glob(orders + "/*#{w}_Dossier*.csv").sort!{|a,b| File.mtime(a) <=> File.mtime(b) }	
	#Dir.glob(orders + "/*#{w}_Dossier*.csv") do |file|
	sorted_files.each do |file|
		
		begin
			i += 1
			puts file
			log.debug "Read file #{file}"
			action = File.open(file).read
			a = File.basename(file, '.csv')

			if !action.strip().empty?
				puts "Get parameters"
				log.debug "Read parameters from #{action}"
				id, numeric, medecin, client, libelle, idAutre = action.force_encoding("iso-8859-1").split(';')

				if (id && numeric && medecin && client && libelle && idAutre)
					puts "=======Client: " + client
					
					log.debug "Update dossier #{a.split('_').first}"
					puts "Update Dossiers: " + a.split('_').first
					puts "#{command} UpdateDossiers #{a.split('_').first} #{id} '#{numeric}' #{medecin} #{client} '#{libelle}' #{synchronizaitonID} --env=#{env}"

					log.debug "Running: #{command} UpdateDossiers #{a.split('_').first} #{id} '#{numeric}' #{medecin} #{client} '#{libelle}' #{synchronizaitonID}"
					output = `#{command} UpdateDossiers #{a.split('_').first} #{id} "#{numeric}" #{medecin} #{client} "#{libelle}" #{synchronizaitonID} --env=#{env}`
					log.info "output = #{output}"

					log.info "Move file #{file}"
					FileUtils.move(file, processed)	
				else
					#not all attribute where provided
					log.error "#{command} AddMessage #{a.split('_').first} #{a.split('_')[1].downcase} #{synchronizaitonID} 'Un ou plusieurs attributs sont manquant. Le fichier csv #{file} contient les valeurs suivantes: #{action}'"
					`#{command} AddMessage #{a.split('_').first} #{a.split('_')[1].downcase} #{synchronizaitonID} "Un ou plusieurs attributs sont manquant. Le fichier csv #{file} contient les valeurs suivantes: #{action}" --env=#{env}`
					FileUtils.move(file, errorPath)
				end
			else
				#the order file was empty
				log.error "#{command} AddMessage #{a.split('_').first} #{a.split('_')[1].downcase} #{synchronizaitonID} 'Le fichier #{file} est vide'"
				`#{command} AddMessage #{a.split('_').first} #{a.split('_')[1].downcase} #{synchronizaitonID} "Le fichier #{file} est vide" --env=#{env}`
				FileUtils.move(file, errorPath)
			end

			log.info "--------------------------------------------------------------------------------"
		rescue Exception => e
           log.info "An error occured while computing #{file}"
           log.info e.message
		end

	end
end 

log.info "================================================================================"
log.info "#{i} dossier(s) where updated													  "
log.info "================================================================================"

log.info "Running: #{command} EndSynchronization #{synchronizaitonID}"
output = `#{command} EndSynchronization #{synchronizaitonID} --env=#{env}`
log.info "output = #{output}"


log.info "================================================================================"
log.info " Clear old files "
log.info "================================================================================"
#Delete old csv files
# Dir.glob(backup + '/*.csv').
#   select{|f| File.mtime(f) < (Time.now - (60*720)) }.
#   each do |f| 
#   	FileUtils.rm(f) 
#   	log.info "Delete file: #{f}"
#   end

# #Delete old order file
# Dir.glob(processed + '/*.csv').
#   select{|f| File.mtime(f) < (Time.now - (60*720)) }.
#   each do |f| 
#   	log.info "Deleting file: #{f}"
#   	FileUtils.rm(f) 
#   	log.info "File deleted"
#   end

log.info "================================================================================"
log.info "End of synchronization"
log.info "================================================================================"
# Fin de la synchronizaiton le ..\..\.. a ..:..


