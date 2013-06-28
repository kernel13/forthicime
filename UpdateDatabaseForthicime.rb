
#!/usr/bin/env ruby

# Demarage de la synchronizaiton le ..\..\.. a ..:..
csv = File.dirname(__FILE__) + '/Analyses/FichierCSV'
orders = File.dirname(__FILE__) + '/Analyses/FichierCSV/Orders'
command = File.dirname(__FILE__) + "/app/console"
i = 0

#create one file by order
Dir.glob(File.dirname(__FILE__) + '/Analyses/FichierCSV/*.csv') do |file|
	File.open(file).read.each_line do |line|
		i += 1;
		order = orders + "/#{File.basename(file, '.csv')}_#{i}.csv"
		File.open(order, 'w'){|f| f.write(line) }
	end

	File.delete(file)
end

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
		puts "#{command} UpdateClients #{a.split('_').first} #{id} '#{nom}' '#{prenom}' '#{nomPrenom}'"
		`#{command} UpdateClients #{a.split('_').first} #{id} "#{nom}" "#{prenom}" "#{nomPrenom}"`

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
		puts "#{command} UpdateMedecins #{a.split('_').first} #{id} '#{nom}' '#{identifiant}' '#{password}'"

		`#{command} UpdateMedecins #{a.split('_').first} #{id} "#{nom}" "#{identifiant}" "#{password}"` 
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
		puts "#{command} UpdateDossiers #{a.split('_').first} #{id} '#{numeric}' #{medecin} #{client} '#{libelle}'"

		`#{command} UpdateDossiers #{a.split('_').first} #{id} "#{numeric}" #{medecin} #{client} "#{libelle}"`
	#end
end

# Fin de la synchronizaiton le ..\..\.. a ..:..