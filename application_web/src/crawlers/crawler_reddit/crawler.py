#!/usr/bin/env python
try:
    import praw
except ImportError:
    print('Le package praw n\'est pas installe sur cette machine, veuillez l\'installer pour pouvoir utiliser ce crawler')
try:
    import json
except ImportError:
    print('Le package json n\'est pas installe sur cette machine, veuillez l\'installer pour pouvoir utiliser ce crawler')

try:
    from watchdog.observers import Observer
    from watchdog.events import FileSystemEventHandler
except ImportError:
    print('Le package watchdog n\'est pas installe sur cette machine, veuillez l\'installer pour pouvoir utiliser ce crawler')

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
#args[3] = Identifiant de la derniere donnee connue dans la BDD (ou contenu, si identifiant n'existe pas)
#args[4] = limite de donnees a recuperer (ici : X commentaires)
#Structure d'une donnee: Text|Path|Index|realID

reddit = praw.Reddit(client_id='ScN-UpZfhge5Gg', client_secret='qHpUqtbrlboH1iEla69J9PuFGZZZqA', user_agent='ScrapperFR')
subreddit = args[2]
post_list = []
comment_dict_list = []
comment_dict = {}
scrapper = reddit.subreddit(subreddit)

#Regarder le log (status, index local, progressId) et faire en consequence:
time.sleep(2)
log_file = open(rel_path + "Log.json", 'r+')
log_data = json.load(log_file)
log_file.close()

#Evenements externes:
log_stop_event = threading.Event()
kill_event = threading.Event()
pause_event = threading.Event()
incremental_end_event = threading.Event()

#Watchdog sur fichiers pause et kill
class MyHandler(FileSystemEventHandler):
    def on_modified(self, event):
        if event.src_path.endswith("pause_file.json"):
            print("pause signal sent..")
            if pause_event.is_set():
                print("PAUSE OFF")
                pause_event.clear()
            else:
                print("PAUSE ON")
                pause_event.set()
            
        elif event.src_path.endswith("kill_file.json"):
            print("kill signal sent..")
            #Arreter log automatique et forcer fin de l'execution principale
            log_stop_event.set()
            kill_event.set()

event_handler = MyHandler()
observer = Observer()
observer.schedule(event_handler, path=os.path.join(os.path.dirname(__file__) + "/cache"), recursive=False)
observer.start()
#faire observer.stop() et observer.join() en fin de programme.
    

#Action de logging, toutes les 5 secondes.
def logtoFile(log_stop_event):
    #TODO: Trouver un moyen pour ne pas avoir a ouvrir le fichier a chaque demande de log.
    log_file = open(rel_path + "Log.json", 'w')
    json.dump({'status' : 1, 'local_index' : local_index, 'entrypoint' : subreddit, 'progressId' : progressId}, log_file)
    log_file.close()
    if not log_stop_event.is_set():
        # call logtoFile() again in 5 seconds
        threading.Timer(5, logtoFile, [log_stop_event]).start()



if log_data['status'] == 0:
    #Ne rien faire car tache deja finie.
    observer.stop()
    observer.join()
    exit()

elif log_data['status'] == 1:
    #Crawl en cours d'execution, reprendre la recherche a partir du dernier Id recupere.
    with open(rel_path + "Data.json","r") as f:
        comment_dict_list = json.load(f)

    CrawlerUpToDate = False
    local_index = log_data['local_index']
    progressId = log_data['progressId']
    lastId = progressId

    logtoFile(log_stop_event)

    for post in scrapper.new(limit=None):
        submission = reddit.submission(id=post.id)
        submission.comments.replace_more(limit=None)
        
        #Recherche en largeur des commentaires, on en extrait le corps.
        comment_index = 1
        for comment in submission.comments.list():
            if args[3] == comment.id:
                incremental_end_event.set()
                CrawlerUpToDate = True
                break
            if CrawlerUpToDate:
                local_index = local_index + 1
                comment_dict = {}    
                comment_dict['text'] = comment.body
                comment_dict['path'] = subreddit + "/" + post.id
                comment_dict['index'] = comment_index
                comment_dict['realID'] = comment.id
                progressId = comment.id
                comment_dict['taskID'] = args[1]
                comment_dict_list.append(comment_dict)
                comment_index = comment_index + 1
            if lastId == comment.id:
                CrawlerUpToDate = True



        if local_index > args[4]:
            print("LIMIT WAS : ", args[4], "CURRENT AMOUNT IS : ", local_index)
            break
        
        while pause_event.is_set():
            print("SUPPOSED TO BE SLEEPING")
            time.sleep(2)
            if kill_event.is_set():
                break
            elif incremental_end_event.is_set():
                break

        if incremental_end_event.is_set():
            break
        if kill_event.is_set():
            print("RECEPTION DE KILL")
            break

elif log_data['status'] == 2:
    #Premier lancement de crawl
    local_index = 0
    progressId = ''

    logtoFile(log_stop_event)

    for post in scrapper.new(limit=None):
        comment_index = 1
        submission = reddit.submission(id=post.id)
        submission.comments.replace_more(limit=None)
        #Recherche en largeur des commentaires, on en extrait le corps.
        for comment in submission.comments.list():
            #Si detection du meme commentaire qu'indique dans la BDD, alors on arrete le crawling
            if args[3] == comment.id:
                print("RENCONTRE D'UNE DONNEE DEJA CONNUE, ARRET DU CRAWL")
                print("DONNEE EST ", comment.id)
                incremental_end_event.set()
                break
            comment_dict = {}    
            comment_dict['text'] = comment.body
            comment_dict['path'] = subreddit + "/" + post.id
            comment_dict['index'] = comment_index
            comment_dict['realID'] = comment.id
            progressId = comment.id
            comment_dict['taskID'] = args[1]
            comment_dict_list.append(comment_dict)
            comment_index = comment_index + 1
            local_index = local_index + 1

        if local_index > args[4]:
            print("LIMIT WAS : ", args[4], "CURRENT AMOUNT IS : ", local_index)
            break
        
        while pause_event.is_set():
            print("SUPPOSED TO BE SLEEPING")
            time.sleep(2)
            if kill_event.is_set():
                break
            elif incremental_end_event.is_set():
                break

        if incremental_end_event.is_set():
            break
        if kill_event.is_set():
            print("RECEPTION DE KILL")
            break



#Fin d'execution du programme normal:

#Arreter thread de log, et le watchdog
log_stop_event.set()
observer.stop()
observer.join()


#Exporter le resultat final.
with open(rel_path + "Data.json","w") as f:
    json.dump(comment_dict_list,f)

#Garantir un affichage correct lors de la fin d'execution du script.
time.sleep(5)
if kill_event.is_set():
    log_file = open(rel_path + "Log.json", 'w')
    json.dump({'status' : 1, 'local_index' : local_index, 'entrypoint' : subreddit, 'progressId' : progressId}, log_file)
    log_file.close()
else:
    log_file = open(rel_path + "Log.json", 'w')
    json.dump({'status' : 0, 'local_index' : local_index, 'entrypoint' : subreddit, 'progressId' : progressId}, log_file)
    log_file.close()