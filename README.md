# WSB
A simple, easy to use scoreboard info plugin for pocketmine 4.0+

![](https://i.imgur.com/Ya5wi4J.png)

Welcome to WSB. 
WSB is an easy to use scoreboard hud plugin for pocketmine 4.0+

## Features
> ### Rotating display lines
> You can set multiple display names for the scoreboard to scroll through

> ### Auto updating lines
> You can change the amount of time that the tags refresh

> ### Easy-reload
> You can reload the scoreboards from config without restarting the server with `/wsb reload` (only accessable to operators)

> ### Command Parameters
> The command has useful parameters for an easy to use command

> ### Customizable lines
> You can set and change all the lines from config

> ### Many useful tag
> Tags are replace values like player position, there are lots

> ### Custom tag creation
> You can make your own tags in YOUR plugins for the scoreboard to use

> ### Per-World-Scoreboards
> You can set different scoreboards for different worlds
## Command
SubCommand | Permission
---|---
`on` | true
`off ` | true
`reload` | op
## Tags
  Tag | Description
  -|-
  `&`|Use for color codes (same as `§`)
  `{NAME}`|Players real name
  `{REAL_NAME}`|Players real name
  `{DISPLAY_NAME}`|Players display name (often nick plugins use display name)
  `{PING}`|Players Current Ping
  `{MAX_PLAYERS}`|Maximum players that can be on the server
  `{ONLINE_PLAYERS}`|Currently online player count
  `{X}`|Players X Position
  `{Y}`|Players Y Position
  `{Z}`|Players Z Position
  `{REAL_TPS}`|Current server tps
  `{TPS}`|Average server tps
  `{REAL_LOAD}`|Current server load
  `{LOAD}`|Average server load
  `{LEVEL_NAME}`|Players current level name
  `{LEVEL_FOLDER}`|Players current level folder name
  `{LEVEL_PLAYERS}`|Players current level player count
  `{CONNECTION_IP}`|The IP address that the player connected from
  `{SERVER_IP}`|The servers IP address
  `{TIME}`|Current server time (Customisable in config)
  `{DATE}`|Current server date (Customisable in config)
