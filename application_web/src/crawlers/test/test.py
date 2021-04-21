#!/usr/bin/env python
import sys, json, os
try:
    data = json.loads(sys.argv[1])
except:
    print("ERROR")
    sys.exit(1)

#resultat bidon pour voir si la connection existe
result = {'status' : 'ONLINE'}

#stdin est utilise pour rendre le resultat.
#Penser a utiliser les chemins absolus partout.
#print(json.dumps(result))

dir_path = os.path.dirname(os.path.realpath(__file__))

with open(dir_path+'/Test_data.json') as f:
    data = json.load(f)

print(json.dumps(data))

