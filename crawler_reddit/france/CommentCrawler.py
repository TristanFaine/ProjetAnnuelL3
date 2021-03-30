import praw
import json

#Description: Ce scrapper permet de recuperer tout les commentaires d'un post reddit, et renvoie un dictionnaire python contenant : l'id du post, le corps du commentaire, l'id du commentaire, et le titre du post
#Celui-ci pourra etre converti en objet json ou autre, a des fins diverses tel une composition de corpus.


#Authentification sur reddit, veuillez ne pas abuser du scrapper.
reddit = praw.Reddit(client_id='ScN-UpZfhge5Gg', client_secret='qHpUqtbrlboH1iEla69J9PuFGZZZqA', user_agent='ScrapperFR')


#Initialisation du dictionnaire
submission_dict = {}

#PostId est a remplir avec l'autre crawler.
with open('PostId.json') as f:
    data = json.load(f)

#Pour chaque post:
for subreddit in data:
    for mot in data[subreddit]:
        for query in data[subreddit][mot]:
            post_id = data[subreddit][mot][query]['id']
            post_title = data[subreddit][mot][query]['title']
            print("Extraction des commentaires de", post_title , "...")
            print("Reference :", mot)

            submission = reddit.submission(id=post_id)
            submission.comments.replace_more(limit=None)
            submission_dict[post_id] = {}

            #Recherche en largeur des commentaires, on en extrait le corps.
            for comment in submission.comments.list():
                
                submission_dict[post_id][comment.id] = {}
                submission_dict[post_id][comment.id]['body'] = comment.body
                submission_dict[post_id][comment.id]['reference'] = mot
                #submission_dict[comment.id]['depth'] = comment.depth
                #submission_dict[comment.id]['time'] = comment.created_utc


#print(submission_dict)

with open("CorpusCommentaireReddit.json","w") as f:
    json.dump(submission_dict,f)

#Moyens d'ameliorations:
# - Mettre les machins en standalone
# - faire un crawler de posts pour recup des commentaires plus interessants (fait)
# - Optimiser
