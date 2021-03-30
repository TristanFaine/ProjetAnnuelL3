import praw
import json

#Description : A partir de d'un subreddit pre-defini et de mots-cles, ce crawler donne les identifiants des posts associes.
#Les donnees contenues dans ces posts pourront ensuite etre extraites par un autre crawler a des fins diverses, tel la constitution d'un corpus.

#Authentification sur reddit, veuillez ne pas abuser du scrapper.
reddit = praw.Reddit(client_id='ScN-UpZfhge5Gg', client_secret='qHpUqtbrlboH1iEla69J9PuFGZZZqA', user_agent='ScrapperFR')
post_dict = {}

#Analyse du subreddit voulu, et des mots voulus
f = open("query.txt", 'r')
lines = f.readlines()
subreddit = lines[0].rstrip('\n')
post_dict[subreddit] = {}
mots = [x.strip() for x in lines[1].split(",")]
for mot in mots:
    post_dict[subreddit][mot] = {}
    query_number = 0
    scrapper = reddit.subreddit(subreddit)
    for post in scrapper.search('title:' + mot, limit=10):
        print("Recherche de Mot-cle:", mot, ":", post.title)
        post_dict[subreddit][mot][query_number] = {}
        post_dict[subreddit][mot][query_number]['title'] = post.title 
        post_dict[subreddit][mot][query_number]['id'] = post.id 
        query_number += 1
    

#Essayer de refaire le json mais avec
#post_dict[post_id]
#post_dict[post_id][subreddit]
#post_dict[post_id][titre]
#post_dict[post_id][mot]?
#Le 2eme crawler fait actuellement:
#pour truc dans mot dans sous-reddit:
#mettre les infos des commentaires du post_id

#mettre post_id en 1ere colonne ne change pas grand chose vu que l'on fait
#aucun tri sur les commentaires.
#on pourrait au pire les mettre en:
#comment_id : post_id,body,reference
#et y'aurais pas de soucis.
#hm, ameliorations ce soir, je go vidya.

#On s'en servira pour mettre dans bdd plutot que recherche.
with open("PostId.json","w") as f:
    json.dump(post_dict,f)



with open('PostId.json') as f:
    data = json.load(f)
