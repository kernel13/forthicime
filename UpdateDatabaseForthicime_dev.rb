
#!/usr/bin/env ruby

csv = File.dirname(__FILE__) + '/Analyses/FichierCSV/csv'
orders = File.dirname(__FILE__) + '/Analyses/FichierCSV/Orders'
command = File.dirname(__FILE__) + "/app/console"
i = 0

# Synchronizatin is starting
output = `#{command} StartSynchronization`
synchronizaitonID = output.split("SynchronizationID:").last.chomp
puts "synchId #{synchronizaitonID}"
# Check if an id was returned
if synchronizaitonID.empty?
	puts "Erreur: synchronizaitonID est vide"
	exit
end

#create one file by order
Dir.glob(File.dirname(__FILE__) + '/*.csv') do |file|
	File.open(file).read.each_line do |line|
		i += 1;
		order = orders + "/#{File.basename(file, '.csv')}_#{i}.csv"
		File.open(order, 'w'){|f| f.write(line) }
	end

	File.delete(file)
end

#Update the number of transaction that will be done
puts "#{command} UpdateTotalTransaction #{ Dir.glob(orders + '/*.csv').count } #{synchronizaitonID}"
`#{command} UpdateTotalTransaction #{ Dir.glob(orders + '/*.csv').count } #{synchronizaitonID}`

# 
# => Manage Clients
#
Dir.glob(orders + '/*Client*.csv') do |file|
	
	#if file.include?("Client")
		puts file
		action = File.open(file).read

		puts "Gets parameters"
		id, nom, idFth, prenom, nomPrenom = action.force_encoding("iso-8859-1").split(';')		

		a = File.basename(file, '.csv')
		puts "Upate Client: " + a.split('_').first
		puts "#{command} UpdateClients #{a.split('_').first} #{id} '#{nom}' '#{prenom}' '#{nomPrenom.chomp}' #{synchronizaitonID}"
		`#{command} UpdateClients #{a.split('_').first} #{id} "#{nom}" "#{prenom}" "#{nomPrenom.chomp}" #{synchronizaitonID}`
end

#
# => Manage Medecin
#
Dir.glob(orders + '/*Medecin*.csv') do |file|
	#elsif file.include?("Medecin")

		puts file
		action = File.open(file).read

		puts "Get parameters"
		id, nom, identifiant, password, idFth = action.force_encoding("iso-8859-1").split(';')

		a = File.basename(file, '.csv')
		puts "Update Medecin: " + a.split('_').first
		puts "#{command} UpdateMedecins #{a.split('_').first} #{id} '#{nom}' '#{identifiant}' '#{password}' #{synchronizaitonID}"

		`#{command} UpdateMedecins #{a.split('_').first} #{id} "#{nom}" "#{identifiant}" "#{password}" #{synchronizaitonID}` 
end

#
# => Manage Dossier
#
Dir.glob(orders + '/*Dossier*.csv') do |file|
	#elsif file.include?("Dossier")

		puts file
		action = File.open(file).read

		puts "Get parameters"
		id, numeric, medecin, client, libelle, idAutre = action.force_encoding("iso-8859-1").split(';')

		puts "=======Client: " + client

		a = File.basename(file, '.csv')
		puts "Update Dossiers: " + a.split('_').first
		puts "#{command} UpdateDossiers #{a.split('_').first} #{id} '#{numeric}' #{medecin} #{client} '#{libelle}' #{synchronizaitonID}"

		`#{command} UpdateDossiers #{a.split('_').first} #{id} "#{numeric}" #{medecin} #{client} "#{libelle}" #{synchronizaitonID}`
	#end
end

`#{command} EndSynchronization #{synchronizaitonID}`

# Fin de la synchronizaiton le ..\..\.. a ..:..


