import json

with open('Reddit_Post.json') as f:
    data = json.load(f)

for pair in data:
    print(pair["post_id"])
    #for thing in pair.items():
    #    print(thing[1])

