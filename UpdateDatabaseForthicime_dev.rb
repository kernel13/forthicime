#!/usr/bin/env ruby
require 'logger'
require 'iconv'
require 'fileutils'

time = Time.now.getutc
logName = "./app/logs/UpdateDatabaseForthicimeDev_#{time}.log"
log = Logger.new(logName, 'daily' );
log.level = Logger::DEBUG

log.info "================================================================================"
log.info "Starting UpdateDatabaseForthicime..."
log.info "================================================================================"

csv = File.dirname(__FILE__) + '/Analyses/FichierCSV/csv'
orders = File.dirname(__FILE__) + '/Analyses/FichierCSV/Orders'
command = File.dirname(__FILE__) + "/app/console"
i = 0

backup = File.dirname(__FILE__) + '/Analyses/FichierCSV/cvsBackup'	
processed = orders + '/processed'


Dir.mkdir(backup) unless Dir.exists?(backup)		
Dir.mkdir(processed) unless Dir.exists?(processed)

log.info "csv: #{csv}"
log.info "orders: #{orders}"
log.info "command: #{command}"


# Synchronizatin is starting
log.info "Runnin: #{command} StartSynchronization --env=prod"
output = `#{command} StartSynchronization`
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
log.info "There is currently #{Dir.glob(File.dirname(__FILE__) + '/*.csv').count} in #{File.dirname(__FILE__)}"
log.info "================================================================================"
Dir.glob(csv + '/*.csv') do |file|

	File.open(file).read.each_line do |line|
		i += 1;
		order = orders + "/#{File.basename(file, '.csv')}_#{i}.csv"	
		File.open(order, 'w'){|f| f.write(line) }
	end

	FileUtils.move(file, backup)
end
log.info "================================================================================"
log.info "#{i} file(s) where created"
log.info "================================================================================"

#Update the number of transaction that will be done
log.info " Update total number of transaction "
puts "#{command} UpdateTotalTransaction #{ Dir.glob(orders + '/*.csv').count } #{synchronizaitonID}"
log.info "Runing: #{command} UpdateTotalTransaction #{ Dir.glob(orders + '/*.csv').count } #{synchronizaitonID}"
`#{command} UpdateTotalTransaction #{ Dir.glob(orders + '/*.csv').count } #{synchronizaitonID}`

# 
# => Manage Clients
#
i = 0
log.info "================================================================================"
log.info " Updates client...							  "
log.info "================================================================================"
Dir.glob(orders + '/*Client*.csv') do |file|
	i += 1
	#if file.include?("Client")
		puts file
		log.debug "Read file #{file}"
		action = File.open(file).read

		puts "Gets parameters"
		log.debug "Read parameters from #{action}"
		id, nom, idFth, prenom, nomPrenom = action.force_encoding("iso-8859-1").split(';')		

		a = File.basename(file, '.csv')
		log.debug "Update client #{a.split('_').first}"
		puts "Upate Client: " + a.split('_').first
		log.debug "Running: #{command} UpdateClients #{a.split('_').first} #{id} '#{nom}' '#{prenom}' '#{nomPrenom.chomp}' #{synchronizaitonID}"
		puts "#{command} UpdateClients #{a.split('_').first} #{id} '#{nom}' '#{prenom}' '#{nomPrenom.chomp}' #{synchronizaitonID}"
		`#{command} UpdateClients #{a.split('_').first} #{id} "#{nom}" "#{prenom}" "#{nomPrenom.chomp}" #{synchronizaitonID}`

		log.info "Move file #{file}"
		FileUtils.move(file, processed)	
		log.info "--------------------------------------------------------------------------------"
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
Dir.glob(orders + '/*Medecin*.csv') do |file|
	#elsif file.include?("Medecin")
		i += 1
		puts file
	    log.debug "Read file #{file}"
		action = File.open(file).read

		puts "Get parameters"
		log.debug "Read parameters from #{action}"
		id, nom, identifiant, password, idFth = action.force_encoding("iso-8859-1").split(';')

		a = File.basename(file, '.csv')
		log.debug "Update client #{a.split('_').first}"
		puts "Update Medecin: " + a.split('_').first
		log.debug "Running: #{command} UpdateMedecins #{a.split('_').first} #{id} '#{nom}' '#{identifiant}' '#{password}' #{synchronizaitonID}"
		puts "#{command} UpdateMedecins #{a.split('_').first} #{id} '#{nom}' '#{identifiant}' '#{password}' #{synchronizaitonID}"

		`#{command} UpdateMedecins #{a.split('_').first} #{id} "#{nom}" "#{identifiant}" "#{password}" #{synchronizaitonID}` 

		log.info "Move file #{file}"
		FileUtils.move(file, processed)			
		log.info "--------------------------------------------------------------------------------"
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
Dir.glob(orders + '/*Dossier*.csv') do |file|
	#elsif file.include?("Dossier")
		i += 1
		puts file
		log.debug "Read file #{file}"
		action = File.open(file).read

		puts "Get parameters"
		log.debug "Read parameters from #{action}"
		id, numeric, medecin, client, libelle, idAutre = action.force_encoding("iso-8859-1").split(';')

		puts "=======Client: " + client

		a = File.basename(file, '.csv')
		log.debug "Update dossier #{a.split('_').first}"
		puts "Update Dossiers: " + a.split('_').first
		log.debug "Running: #{command} UpdateDossiers #{a.split('_').first} #{id} '#{numeric}' #{medecin} #{client} '#{libelle}' #{synchronizaitonID}"
		puts "#{command} UpdateDossiers #{a.split('_').first} #{id} '#{numeric}' #{medecin} #{client} '#{libelle}' #{synchronizaitonID}"

		`#{command} UpdateDossiers #{a.split('_').first} #{id} "#{numeric}" #{medecin} #{client} "#{libelle}" #{synchronizaitonID}`

		log.info "Move file #{file}"
		FileUtils.move(file, processed)	
		log.info "--------------------------------------------------------------------------------"
	#end
end
log.info "================================================================================"
log.info "#{i} medecin(s) where updated													  "
log.info "================================================================================"

log.info "Running: #{command} EndSynchronization #{synchronizaitonID}"
`#{command} EndSynchronization #{synchronizaitonID}`


log.info "================================================================================"
log.info " Clear old files "
log.info "================================================================================"
#Delete old csv files
Dir.glob(backup + '/*.csv').
  select{|f| File.mtime(f) < (Time.now - (60*720)) }.
  each do |f| 
  	FileUtils.rm(f) 
  	log.info "Delete file: #{f}"
  end

#Delete old order file
Dir.glob(processed + '/*.csv').
  select{|f| File.mtime(f) < (Time.now - (60*720)) }.
  each do |f| 
  	log.info "Deleting file: #{f}"
  	FileUtils.rm(f) 
  	log.info "File deleted"
  end

log.info "================================================================================"
log.info "End of synchronization"
log.info "================================================================================"
# Fin de la synchronizaiton le ..\..\.. a ..:..


