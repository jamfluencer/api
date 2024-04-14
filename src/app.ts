require('dotenv').config();
import express from 'express';
import WebSocket, { WebSocketServer } from 'ws';
import { SpotifyApi } from './classes';

const app = express();
const port = 8888;
const wsPort = 8080;
const wss = new WebSocketServer({ port: wsPort });

const client_id = process.env.SPOTIFY_CLIENT_ID;
const client_secret = process.env.SPOTIFY_CLIENT_SECRET;
const redirect_uri = `http://localhost:${port}/callback`;

const spotifyApi = new SpotifyApi(client_id, client_secret, redirect_uri);

let currentTrack = undefined;

async function pollCurrentTrack() {
  const track = await spotifyApi.getCurrentlyPlaying();
  if (!currentTrack || currentTrack.id !== track.id) {
    currentTrack = track;
    wss.clients.forEach(function each(client) {
      if (client.readyState === WebSocket.OPEN) {
        client.send(JSON.stringify(currentTrack));
      }
    });
  }
}

function startPolling() {
  setInterval(pollCurrentTrack, 3000);
}

wss.on('connection', function connection(ws) {
  if (currentTrack) {
    ws.send(JSON.stringify(currentTrack));
  } else {
    startPolling();
  }
});

app.get('/', function (req, res) {
  res.send('Hello World!');
});

app.get('/login', function (req, res) {
  res.redirect(spotifyApi.redirectUrl());
});

app.get('/callback', async function (req, res) {
  const code = req.query.code;
  if (typeof code !== 'string') {
    res.status(400).send('Invalid code');
    return;
  }

  try {
    await spotifyApi.getAccessToken(code);
    res.status(200).send('Logged in successfully');
  } catch (error) {
    res.status(500).send('Failed to log in');
  }
});

app.use(function (req, res, next) {
  if (!spotifyApi.isAuthorized()) {
    res.status(403).json({ message: 'Not authorized' });
  } else {
    next();
  }
});

app.use(async function (req, res, next) {
  if (spotifyApi.isExpired()) {
    try {
      await spotifyApi.refreshAccessToken();
      next();
    } catch {
      res.status(500).send('Failed to refresh token');
      return;
    }
  } else {
    next();
  }
});

app.get('/current', async function (req, res) {
  try {
    const body = await spotifyApi.getCurrentlyPlaying();
    res.json(body);
  } catch (error) {
    res.status(500).json({ error });
  }
});

app.get('/playlist/:id', async function (req, res) {
  try {
    const body = await spotifyApi.getPlaylist(req.params.id);
    res.status(200).json(body);
  } catch (error) {
    res.status(500).json({ error });
  }
});

app.listen(port, () => {
  return console.log(`Express is listening at http://localhost:${port}`);
});
