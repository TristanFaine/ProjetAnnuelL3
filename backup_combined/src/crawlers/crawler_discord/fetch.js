#!/usr/bin/env node
//Necessite d'installer le module discord.js

const Discord = require('discord.js');
const fs =require('fs');
const { resolve } = require('path');
const { prefix, token } = require('./config.json');

/* Importation d'arguments:
On envoie tout les arguments en une seule liste => process.argv[2] = args
Position des arguments envoyees par la telecommande:
process.argv[2] = Type de source
process.argv[3] = id de tache
process.argv[4] = Point d'entree de la tache (ici : identifiant de serveur)
process.argv[5] = Identifiant de la derniere donnee connue dans la BDD (ou contenu, si identifiant n'existe pas)
process.argv[6] = limite de donnees a recuperer (ici : X messages maximum par canal/salon textuel)

Structure d'une donnee: Text|Path|Index|realID
*/

script_dir = (__dirname);
cache_path = "cache/Tache" + process.argv[3];
rel_path = script_dir + "/" + cache_path;


taskId = process.argv[3];
serverName = process.argv[4]; //Par exemple : 392263975355809804
lastKnownId = process.argv[5];
limit = process.argv[6];


//Prise en compte d'evenements externes:
//Solution pour eviter que fs.watch s'active plusieurs fois : implementation de debouncing simple.
//Source : https://stackoverflow.com/questions/10468504/why-fs-watchfile-called-twice-in-node

//La meilleure solution est de regarder au niveaurepertoire et de faire une certaine action selon le nom de fichier,
//mais vu que j'ai mis le fichier error_log.txt au meme niveau que ces fichiers... cela causerait une boucle infinie, et un plantage du programme.
pauseStatus = false;
killStatus = false;
var previousLogStatus = -1;
var local_index = 1;
var progressId = ''; 

var actionDone = {};
fs.watch(script_dir + '/cache/pause_file.json', function(eventType,filename) {
  var stats = fs.statSync(script_dir + '/cache/pause_file.json');
  let seconds = +stats.mtime;
  if(actionDone[filename] == seconds) return;
  actionDone[filename] = seconds
  pauseStatus = pauseStatus == true ? false : true;
});
fs.watch(script_dir + '/cache/kill_file.json', function(eventType,filename) {
  var stats = fs.statSync(script_dir + '/cache/kill_file.json');
  let seconds = +stats.mtime;
  if(actionDone[filename] == seconds) return;
  actionDone[filename] = seconds
  killStatus = true;
});


//Creation d'une nouvelle session
const client = new Discord.Client();
let serverId = process.argv[4];
serverId = 817363953679728660;


//Programme principal
client.once('ready', () => {
	console.log('Ready to scrap!');
  console.log(previousLogStatus);

  if (previousLogStatus === 0) {
    console.log("INFO: Arret du programme car tache deja effectuee avec succes")
    process.exit();
  }

  //TODO: Prendre en compte la situation avec status === 1 (tache en cours d'execution)
  //Si c'est le cas : fonction conver
  let CrawlerUpToDate = previousLogStatus === 1 ? false : true;

  //Pour chaque channel dont le bot a acces:
  var bot_channel_array = [];
  //INFO: Plutot que de faire une recherche de toutes les chaines du client, il peut etre possible d'affiner la recherche en precisant
  //un certain serveur comme point d'entree.
  //Il faudrait selectionner un serveur dans client.guilds.cache qui est egal a un id fourni, puis regarder les chaines contenues dans celui-ci
  //J'implementerais cette fonctionabilite si j'ai le temps.
  //Documentation https://discord.js.org/#/docs/main/stable/class/Client?scrollTo=channels

  function getChannelIDs() {
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
      //console.log("scrapping terminé pour " + channel)
    } else {
      let rounds = (limit / 100) + (limit % 100 ? 1 : 0);
      let last_id = "";
      for (let x = 0; x < rounds; x++) {
        //Boucle principale de crawling.
        //Arret si reception d'evenement exterieur:
        if (killStatus) {
          return out;
        }
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
          //console.log("scrapping terminé pour " + channel)
          return out;
        }
        last_id = last_message.id
      }
    }
    //Normalement, cette partie du code n'est jamais atteinte.
    //console.log("scrapping terminé pour " + channel)
    return out;
  }

  
  //Cette fonction cree une liste contenant les informations necessaires a la creation d'un fichier json, a partir d'un scrap d'un channel.
  let scrapped_data = [];
  function convertJSON(message_array, channel) {
    let message_index = 1;
    for (const scrapped_message of message_array) {
      //TODO: Replace path with an actual path.

      if (CrawlerUpToDate) {
        scrapped_data.push({
          text: scrapped_message.content,
          path: serverId + '/' + channel,
          index: message_index,
          realid: scrapped_message.id,
          taskid: taskId})
          progressId = progressId;
          
        message_index = message_index + 1;
        local_index = local_index + 1;
      }
      if (scrapped_message.id === process.argv[5]) {
        CrawlerUpToDate = true;
      }
    } 
  }

  //Cette fonction attends que tout les channels sont scrap avant de redonner le tableau.
  async function scrapData() {
    for (const channel of bot_channel_array) {
      //On regarde avant de commencer a crawl un canal la reception d'evenement pause ou kill ou autre:
      while (pauseStatus) {
        console.log("SLEEP MODE : ON");
        await new Promise(r => setTimeout(r, 2000));
      }
      //La prise en compte de l'interruption du programme se fait dans la boucle principale de getMessages().


      //console.log("Scrapping du canal: " + channel)
      const query = client.channels.cache.get(channel);
      
      //Mettre la limite de messages ici:
      
      await getMessages(query, process.argv[6]).then(out => convertJSON(out, channel));

      //Action de log apres avoir crawl chaque chaine
      fs.writeFile(rel_path + 'Log.json', JSON.stringify({status: 1, local_index: local_index, entrypoint: serverId}), 'utf8', function (err) {
        if (err) return console.log(err);
      });

    }
    
    //Procedures en fin d'execution du programme avec succes:

    var json = JSON.stringify(scrapped_data);
    //Ecrire dans le fichier Data
    fs.writeFile(rel_path + 'Data.json', json, 'utf8', function (err) {
      if (err) return console.log(err);
      let logValue = killStatus === true ? 1 : 0;
      fs.writeFile(rel_path + 'Log.json', JSON.stringify({status: logValue, local_index: local_index, entrypoint: serverId}), 'utf8', function (err) {
        if (err) return console.log(err);
        console.log("Fin du scrapping, vous pouvez quitter ce programme en faisant CTRL+C");  
        process.exit();
      });
    });
  };

  console.log("Salons trouvés: ")
  getChannelIDs();
  //a partir des id de channel recuperes, faire le scrapping de chaque message du channel.
  console.log("Recuperation de messages:");  
  scrapData();
});



//Connection a discord via le token d'un bot, apres avoir recupere le log pre-existant.
async function loadPreviousLog() {
  previousLog = fs.readFileSync(rel_path + 'Log.json', 'utf8');
  previousLogStatus = JSON.parse(previousLog).status;

  return previousLogStatus;
}

loadPreviousLog().then(client.login(token));