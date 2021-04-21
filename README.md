# ProjetAnnuelL3

## 1.  localiser des sources de dialogues sur Internet
## 2.  les récupérer, par des techniques plus ou moins automatiques (MechanicalSoup, Scrapy, Selenium)
## 3.  les organiser au sein d'une base de données.

## 4. Unifier les resultats des crawlers/scrappers pour mise dans une BDD: Text=text, Source=Discord/Reddit/Autre.., Sub_Path=France/24388ugfj ou autre chose , Sub_Id = incrementation automatique (1..2..3..), Real_Id =Id donne par crawler ou API.
## 5. Mettre en place un crawler incremental (ou manager de crawler) qui peut appeler un certain crawler, en precisant : le crawler a utiliser, la limite de posts/commentaires a crawl, et une liste d'arguments alternatifs (par exemple, Specifier le Subreddit voulu, ou le serveur discord ET une chaine specifique, ou autre chose)
## 6. Mettre en place la BDD.
## 7. Permettre l'utilisation de ce manager via PHP (soit utiliser shell_exec... ne semble pas etre une bonne idee, soit faire un appel js asynchrone?)

## 8. Verifier que tout fonctionne..
