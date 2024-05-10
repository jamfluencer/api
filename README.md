# Jamfluencer

**Build the playlist. Grow the jam. Have fun.**

## Authin'

You **must** use Google OAuth to register or authenticate as a user. This is so that we can later verify
identity based on known emails for our selective group.

In order to start, stop, and otherwise host a Jam you must also authorize our application with Spotify. See 
a specific client documentation to determine how to do this.

## Jammin'

### The Audience

_Note: The host may and in many cases should be considered a member of the audience._

All clients, once authenticated, may make GET requests to `https://api.jamfluencer.app/v1/jam/playlist` to retrieve
the current playlist in full. Similarly, clients may make a request to `https://api.jamfluencer.app/v1/jam/queue` to 
retrieve the current queue.

Clients are free to poll these endpoints. However, a websocket server is available to provide updates via a
system that adheres closely to the Pusher protocols. This is available at `wss://ws.jamfluencer.app/app/<app_id>`. 
Request an `app_id` value if you wish to implement a client utilizing the websockets.  
Once a connection is opened a client should a Pusher channel subscription message to the `Jam` channel:  
```javascript
ws.send(JSON.stringify({"event": "pusher:subscribe", "data": {"channe": "jam"}}));
```
You will receive a message in response in the format `{"event": "jam.status", "data": {"active": true}, "channel": "jam"}`  

You will also receive a message when a change to the Jam's queue or playlist is detected. This will be in the format 
`{"event": "jam.update", "channel": "jam"}` and any value of `data` is irrelevant. When this message is received the
client should make a request to fetch the playlist and queue.

_Returning the entire playlist or queue in these events exceeds the normal limits of websocket messages.  
It is intended that these events are expanded to send specific changes in smaller portions._

Two more events may be detected. At the start of a Jam the `jam.start` event is sent to the `Jam` channel. When a
Jam is ended by the host a `jam.end` event is sent. Neither of these contain pertinent data.


### The Host

The host client will mat make a PUT request to `https://api.jamfluencer.app/v1/jam/start/<playlist>` to start the Jam.
The value of `playlist` is any valid Spotify playlist ID to which the host has access. These can be determined by the
"open in Spotify" links. They look like `spotify:playlist:5REttHsnZVG6OewRx6XHfC` where the portion following the last
colon is the ID.

The host can make a PUT request to `https://api.jamfluencer.app/v1/jam/stop` to end the Jam.

## Explorin'

This API supports other endpoints to directly manipulate Spotify for fine-tuning the Jam; and is constantly expanding.

These are not documented here. They are documented within the repository using the Bruno API explorer. To discover
these endpoints on one's own follow these steps:

1. Clone this repository, or a fork, locally.
2. Download [Bruno](https://www.usebruno.com/) for your platform.
3. From the menu bar select "Open Collection" and select the `.bruno` directory of this repository.   
_Note: On Mac OS you can use `Ctrl-Shift-.` to toggle the display of hidden items when using Finder._
