import sys
#Utilisation : Ce script est appele depuis une page PHP (normalement c'est possible)
#et appelle donc un crawler selon les informations envoyees depuis la page.
#Sous formes d'arguments en ligne de commande.
#Il faudra faire attention a ne pas avoir de problemes avec les appels de chemin relatif, ou d'injection..
print("coucou")

#Debug temporaire:
print(sys.argv[1]) #Represente le crawler a appeler
print(sys.argv[2]) #Represente la limite, 0 si aucune limite
print(sys.argv[3]) #Represente une liste optionnelle d'args.

#utiliser


#Pour utiliser d'autres scripts depuis un script principal python:
#Option 1 (si python): Utiliser import file pour traiter ce script comme un module (utilisable si le script est aussi en python)
#Puis faire file.fonc() qui contient le script. Ou utiliser runpy..?

#Option 2 (si pas python) : Utiliser la librarie Naked pour permettre d'executer des scripts.


#Option 3: Reecrire tout en python. pour avoir des methodes et des classes.


#Option 4: Gerer le "managing" des crawlers directement depuis le fichier PHP, plutot que de faire un manager intermediaire

#pour appeler ler script un truc du genre
#$cmd = escapeshellcmd('python3 piUno.py + args'); 
#$output = shell_exec($cmd);
#faire des trucs avec $output
