import praw
import json

#Description: Ce scrapper permet de recuperer tout les commentaires d'un post reddit, et renvoie un dictionnaire python contenant : l'id du post, le corps du commentaire, l'id du commentaire, et le titre du post
#Celui-ci pourra etre converti en objet json ou autre, a des fins diverses tel une composition de corpus.



#Notes personelles: faire une meilleure structure de donneees.

#Authentification sur reddit, veuillez ne pas abuser du scrapper.
reddit = praw.Reddit(client_id='ScN-UpZfhge5Gg', client_secret='qHpUqtbrlboH1iEla69J9PuFGZZZqA', user_agent='ScrapperFR')


submission_dict_list = []
submission_dict = {}

#Ce fichier json est obtenu grace a QueryCrawler.py
with open('Reddit_Post.json') as f:
    data = json.load(f)

#Pour chaque post:
for post in data:
    print("Extraction des commentaires du post:", post['post_id'] , "...")
    print("Reference :", post['reference'])
    submission = reddit.submission(id=post['post_id'])
    submission.comments.replace_more(limit=None)
    

    #Recherche en largeur des commentaires, on en extrait le corps.
    for comment in submission.comments.list():
        submission_dict = {}
        submission_dict['comment_id'] = comment.id
        submission_dict['body'] = comment.body
        submission_dict['reference'] = post['reference']
        
        submission_dict['post_id'] = post['post_id']
        submission_dict['subreddit'] = post['subreddit']
        #submission_dict[comment.id]['depth'] = comment.depth
        #submission_dict[comment.id]['time'] = comment.created_utc
        submission_dict_list.append(submission_dict)


#print(submission_dict)

with open("Reddit_Comment.json","w") as f:
    json.dump(submission_dict_list,f)