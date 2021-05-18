#!/usr/bin/env node
//Necessite d'installer le module discord.js
const Discord = require('discord.js');
const fs =require('fs');
const { prefix, token } = require('./config.json');

//Creation d'une nouvelle session
const client = new Discord.Client();

//Action unique lors du demarrage du bot
client.once('ready', () => {
	console.log('Ready to scrap!');
  
  //Il faut que le bot existe dans un serveur, ou plusieurs.

  //Pour chaque channel dont le bot a acces:
  var bot_channel_array = [];
  function getChannelIDs(fetch) {
    try{
      let channels = client.channels.cache.array();
        for (const channel of channels) 
        {
          if(channel.type === "text") {
            bot_channel_array.push(channel.id);
            console.log(channel.name + " : " + channel.id)
          }
    }}catch(err){
        console.log('Array error : Channels might not exist.')
        message.channel.send('An error occured while getting the channels.')
        console.log(err)
    }

    return bot_channel_array;
  }

  
  async function getMessages(channel, limit = 100) {
    let out = [];
    if (limit <= 100) {
      //Si on se limite a recuperer moins de 100 messages, on ne contourne pas les limitations de l'API.
      let messages = await channel.messages.fetch({ limit: limit })
      out.push(...messages.array())
      console.log("scrapping terminé pour " + channel)
    } else {
      let rounds = (limit / 100) + (limit % 100 ? 1 : 0);
      let last_id = "";
      for (let x = 0; x < rounds; x++) {
        const options = {
          limit: 100
        }
        if (last_id.length > 0) {
          options.before = last_id
        }
        const messages = await channel.messages.fetch(options)
        out.push(...messages.array())
        last_message = messages.array()[(messages.array().length - 1)];
        if (last_message == null || undefined) {
          //Fin de l'execution du scrapper, ajout des infos des messages dans une liste, pour convertir en .json plus tard.
          console.log("scrapping terminé pour " + channel)
          return out;
        }
        last_id = last_message.id
      }
    }
    //Normalement, cette partie du code n'est jamais atteinte.
    console.log("scrapping terminé pour " + channel)
    return out;
  }

  
  //Cette fonction cree une liste contenant les informations necessaires a la creation d'un fichier json, a partir d'un scrap d'un channel.
  let scrapped_data = [];
  function convertJSON(message_array) {
    for (const scrapped_message of message_array) {
      scrapped_data.push({ text: scrapped_message.content})
    }
  }

  //Cette fonction attends que tout les channels sont scrap avant de redonner le tableau.
  async function scrapData() {
    for (const channel of bot_channel_array) {
      console.log("Scrapping du channel: " + channel)
      const query = client.channels.cache.get(channel);
      
      await getMessages(query, 1000).then(out => convertJSON(out));
    }
    var json = JSON.stringify(scrapped_data);
    fs.writeFile('discord_data.json', json, 'utf8', function (err) {
      if (err) return console.log(err);
      console.log("Fin du scrapping, vous pouvez quitter ce programme en faisant CTRL+C")
    });

  };

  console.log("Channels trouvés: ")
  getChannelIDs();

  //a partir des id de channel recuperes, faire le scrapping de chaque message du channel.
  console.log("Recuperation de messages:");  
  console.log(bot_channel_array);
  scrapData();

});

//Amelioration possible: permettre le choix de serveurs.



//Connection discord en utilisant le token d'un bot, de preference le votre.
client.login(token);
