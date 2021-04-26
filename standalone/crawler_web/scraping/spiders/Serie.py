import string
import scrapy
from scrapy import Request

class SerieSpider(scrapy.Spider):
    name = "Serie"
    start_urls = ['https://www.forumfr.com/sujet846837-votre-s%C3%A9rie-du-moment.html']
  
    def parse(self, response):
        lines=set()  #utilisation d'un set pour eviter les doublons
        dialog=response.css('article').xpath('//div/div/div[2]/div[1]').css('p::text').extract() #on accede à un post
        id=0
        for line in dialog:
            if line not in lines:
                id=id+1
                lines.add(line)
                yield {"id":id,
                        "text":line.strip()}

        #on recupere l'url de la page suivante           
        next_url =response.css('.ipsPagination_next a::attr(href)').get()
        if next_url:#si elle existe, appel de parse
            yield Request(response.urljoin(next_url), callback=self.parse)