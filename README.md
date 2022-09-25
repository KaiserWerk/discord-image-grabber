# Discord Image Grabber

## What
This WordPress plugin will automagically fetch images (or rather any file, really) from messages
in a Discord channel.

The plugin folder is supposed to be named "discord-image-grabber".

## Why
This plugin was created then need arose to collect images from discord messages in order
to create a gallery page of those images in WordPress, based on (real life) events.
This way you don't have to download all images by hand.

## How

The plugin offers a small number of configurable settings:

* List of channel IDs (to download messages with attachments from)
* Folder name
* Allowed mime types
* Last message ID (only messages newer than this are downloaded, updated automatically)
* Max. number of files

The plugin installs an hourly cronjob.