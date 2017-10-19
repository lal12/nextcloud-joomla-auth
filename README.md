# Owncloud / Nextcloud authentication bridge for Joomla

This plugin enables your users to log into joomla with their credentials from the specified Owncloud / Nextcloud Server.

## Features
- Blacklist: Prevent users to log in
- Works with self signed certificate (if you say so in the settings)

## Future Plans
- Automatic updates
- Export Joomla users to Owncloud / Nextcloud

## HOWTO use
### Installation
1. Goto Extensions -> Manage -> Install
2. Click on the "Install from URL" tab
3. Copy and paste the url into the text box [https://codeload.github.com/lal12/nextcloud-joomla-auth/zip/master](https://codeload.github.com/lal12/nextcloud-joomla-auth/zip/master)
4. Click "Check and install"
5. Goto Extensions -> Plugins
6. Click on the red cross next to "Owncloud Nextcloud Authentication" to enable it. If there is a green check, it is already enabled.  In case you cannot find the entry, filter by type = authentication.

### Configuration
1. Goto Extensions -> Plugins
2. Click on "Owncloud Nextcloud Authentication". In case you cannot find the entry, filter by type = authentication.
3. Enter the URL of your nextcloud/owncloud installation
4. If you use a self signed certificate you should set "Verify SSL Certificate" to "no".
5. If you want to prevent specific users from owncloud to log into your Joomla site, enter their name into the "User Blacklist" field and seperate them by comma. E.g. "john,marc,luke".