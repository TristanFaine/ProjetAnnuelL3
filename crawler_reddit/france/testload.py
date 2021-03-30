import json

with open('test.json') as f:
    data = json.load(f)

for pair in data.items():
    print(pair)

#Ok Le JSON semble etre correct vu que cela load sans donner d'erreur..  



#print(data["bdij34"]["ekykuqj"])

