import praw
import json

#Description : A partir de d'un subreddit pre-defini et de mots-cles, ce crawler donne les identifiants des posts associes.
#Les donnees contenues dans ces posts pourront ensuite etre extraites par un autre crawler a des fins diverses, tel la constitution d'un corpus.


#Authentification sur reddit, veuillez ne pas abuser du scrapper.
reddit = praw.Reddit(client_id='ScN-UpZfhge5Gg', client_secret='qHpUqtbrlboH1iEla69J9PuFGZZZqA', user_agent='ScrapperFR')
post_dict_list = []

#Analyse du subreddit voulu, et des mots voulus
f = open("query.txt", 'r')
lines = f.readlines()
subreddit = lines[0].rstrip('\n')
mots = [x.strip() for x in lines[1].split(",")]

for mot in mots:
    scrapper = reddit.subreddit(subreddit)
    for post in scrapper.search('title:' + mot, limit=10):
        post_dict = {}
        post_dict['post_id'] = post.id
        print("Recherche de Mot-cle:", mot, ":", post.title)
        post_dict['subreddit'] = subreddit
        post_dict['title'] = post.title
        post_dict['reference'] = mot
        post_dict_list.append(post_dict)
    

with open("Reddit_Post.json","w") as f:
    json.dump(post_dict_list,f)
