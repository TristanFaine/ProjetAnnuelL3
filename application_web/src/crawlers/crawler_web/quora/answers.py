import urllib
import requests
from bs4 import BeautifulSoup
import json

from selenium import webdriver
questions= open('question_urls.txt', 'r')
lines = questions.readlines()
data={}
browser=webdriver.Chrome()
for line in lines:
   browser.get(line)
   line.replace("https://fr.quora.com/","")
   data[line]= {}
   html_source = browser.page_source
   soup = BeautifulSoup(html_source,"html.parser")
   answer_text = soup.find("div", {"class": "spacing_log_answer_content"})
   if(answer_text):
  
    data[line]= answer_text.text
with open('data.json', 'w') as outfile:
    json.dump(data, outfile)
