import urllib
import requests
from bs4 import BeautifulSoup


url='https://fr.quora.com/topic/Art/all_questions'
page = requests.get(url)

soup = BeautifulSoup(page.content, "html.parser")
#on recupere toutes les vingt première questions car le scroll pas encore simulé
questions = soup.find_all('a', attrs={'class': 'question_link'}, href=True)
question_set = set()
for question in questions:
    question_set.add(question)
save_file= ('question_urls.txt')
file_question_urls = open(save_file, mode='w', encoding='utf-8')
for question in question_set:
	link_url =  question.attrs['href']
	file_question_urls.write(link_url+'\n')
file_question_urls.close() 