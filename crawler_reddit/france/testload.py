import json

with open('Reddit_Post.json') as f:
    data = json.load(f)

for pair in data:
    print(pair["post_id"])
    #for thing in pair.items():
    #    print(thing[1])

#Ok Le JSON semble etre correct vu que cela load sans donner d'erreur..  



#print(data["bdij34"]["ekykuqj"])

