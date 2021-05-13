#!/usr/bin/env python
try:
    import praw
except ImportError:
    print('Le package praw n\'est pas installe sur cette machine, veuillez l\'installer pour pouvoir utiliser ce crawler')
try:
    import json
except ImportError:
    print('Le package json n\'est pas installe sur cette machine, veuillez l\'installer pour pouvoir utiliser ce crawler')

import sys, os, threading, time

try:
    args = json.loads(sys.argv[1])
except:
    print("ERROR : Aucun argument n'a ete envoye vers ce script, ou les arguments n'ont pas ete interprete correctement.")
    sys.exit(1)

script_dir = os.path.dirname(__file__)
cache_path = "cache/Tache" + args[1]
rel_path = os.path.join(script_dir, cache_path)

#Position des arguments envoyees par la telecommande:
#args[0] = Type de source
#args[1] = id de tache
#args[2] = Point d'entree de la tache (ici : nom de subreddit)
#args[3] = Identifiant de la derniere donnee (ou contenu, si identifiant n'existe pas)
#args[4] = limite de donnees a recuperer (ici : 10000 posts)

#Structure d'une donnee: Text|Path|Index|realID

#TODO: Pour ce test, on se limitera a 20 posts.
reddit = praw.Reddit(client_id='ScN-UpZfhge5Gg', client_secret='qHpUqtbrlboH1iEla69J9PuFGZZZqA', user_agent='ScrapperFR')
subreddit = args[2]
post_list = []
comment_dict_list = []
comment_dict = {}
scrapper = reddit.subreddit(subreddit)

post_index = 0
global_index = 0

def logtoFile(f_stop):
    log_file = open(rel_path + "Log.json", 'w')
    json.dump({'status' : 1, 'post_index' : post_index, 'global_index' : global_index}, log_file)
    log_file.close()
    if not f_stop.is_set():
        # call f() again in 5 seconds
        threading.Timer(5, logtoFile, [f_stop]).start()
f_stop = threading.Event()


#Execution du crawling:
for post in scrapper.new(limit=20):
    post_list.append(post.id)

#Commencer le logging toutes les 5 secondes.
logtoFile(f_stop)

for post_id in post_list:
    #print("Extraction des commentaires du post:", post_id , "...")
    submission = reddit.submission(id=post_id)
    submission.comments.replace_more(limit=None)
    
    #Recherche en largeur des commentaires, on en extrait le corps.
    comment_index = 1
    for comment in submission.comments.list():
        comment_dict = {}    
        comment_dict['text'] = comment.body
        comment_dict['path'] = subreddit + "/" + post_id
        comment_dict['index'] = comment_index
        comment_dict['realID'] = comment.id
        comment_dict['taskID'] = args[1]
        comment_dict_list.append(comment_dict)
        comment_index = comment_index + 1
        global_index = global_index + 1
    post_index = post_index + 1
    
#Arreter le thread du log.
f_stop.set()

#Exporter le resultat final.
with open(rel_path + "Data.json","w") as f:
    json.dump(comment_dict_list,f)

#Garantir un affichage status : 0, lors de la fin d'execution du script.
time.sleep(5)
log_file = open(rel_path + "Log.json", 'w')
json.dump({'status' : 0, 'post_index' : post_index, 'global_index' : global_index}, log_file)
log_file.close()