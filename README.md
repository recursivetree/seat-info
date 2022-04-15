# SeAT-Info
SeAT-Info is a SeAT module that adds a small article systems for example as a corporation bulletin, or for explanations
on how to use seat.

![screenshot of the seat-info plugin](screenshot.png)

## Usage
### Roles
#### View Role
Allows you to view articles and adds the `Start` and `Articles` page to the sidemenu.
#### Edit Role
This role allows you to create, manage and delete articles aswell as to upload images and other resources usable in the 
articles.

### Editor
The editor supports a markup language that's kinda close to HTML, but not quite. Currently, the parser is relatively 
strict, and for example you can't have spaces in the tags where there doesn't need to be one. E.g. `<a></a>` is valid, 
but `< a ></ a>` isn't.

For all available tags, please see the [documentation](documentation.md)

Currrently there are a lot of features missing that might be useful, and it could use some QOL updates. If you have 
specific needs, open an issue or pull request.

## Installation
I can also recommend reading through the [official seat documentation](https://eveseat.github.io/docs/community_packages/).

### Docker Install

> :warning: The default seat docker container doesn't store images and resources you upload to seat-info permanently due 
> to a misconfiguration of laravel. To fix this, read the section about changing the server settings.

Open your .env file and edit the SEAT_PLUGINS variable to include the package.

```
# SeAT Plugins
SEAT_PLUGINS=recursivetree/seat-info
```

Now run
```
docker-compose up
```
and the plugin should be installed

### Barebone Install

In your seat directory:

```
sudo -H -u www-data bash -c 'php artisan down'
sudo -H -u www-data bash -c 'composer require recursivetree/seat-info'
sudo -H -u www-data bash -c 'php artisan vendor:publish --force --all'
sudo -H -u www-data bash -c 'php artisan migrate'
sudo -H -u www-data bash -c 'php artisan seat:cache:clear'
sudo -H -u www-data bash -c 'php artisan config:cache'
sudo -H -u www-data bash -c 'php artisan route:cache'
sudo -H -u www-data bash -c 'php artisan up'
```

## Changing the server settings
Per default, the configuration for the max allowed file size of php is rather low, meaning you can't upload big files in
the resources tab. Additionally, on docker images and resources aren't stored permanently.

### Barebone
1. Open the `/etc/php/7.3/fpm/php.ini ` file, for example with nano:
    ```
    nano /etc/php/7.3/fpm/php.ini 
    ```
2. Change this line
    ```
    upload_max_filesize = 2M
    ```
    to 
    ```
    upload_max_filesize = [the max size you want in megabytes]M
    ```
3. Do the same for `post_max_size`, and if required for `memory_limit`. The value should be slightly larger than the value of`upload_max_filesize`.
4. Save and exit
5. Reload the config with:
    ```
    service php7.3-fpm reload
    service nginx reload
    ```
6. Reload the management page, and it should state a higher value as the limit.

### Docker
1. Go to the directory with your `docker-compose.yml` file.
2. In this directory, create a new file `seat_info.ini` and a directory `recursive_tree_info_module_resources`
3. Put the following in the `seat_info.ini` file:
   ```
   ; Increase the maximum file upload size for the seat-info plugin
   upload_max_filesize = 40M ; increase this to a value larger than the largest file you intend to upload
   post_max_size = 40M ; must be larger than upload_max_filesize
   ;memory_limit = 512M ;you might need to increase this too if you have huge files
   ```
4. Adjust the values as you like
5. Open the `docker-compose.yml` file and got to the `seat-web` section
6. In there, add the following to the volumes section:
   ```
   - ./seat_info.ini:/usr/local/etc/php/conf.d/seat_info.ini:ro
   - ./recursive_tree_info_module_resources:/var/www/seat/storage/app/recursive_tree_info_module_resources:rw
   ```
   It should look something like this(details might differ):
   ```
   seat-web:
    image: eveseat/seat:4
    restart: "no"
    command: web
    volumes:
      - ./packages:/var/www/seat/packages:ro  # development only
      - ./seat_info.ini:/usr/local/etc/php/conf.d/seat_info.ini:ro
      - ./recursive_tree_info_module_resources:/var/www/seat/storage/app/recursive_tree_info_module_resources:rw
    env_file:
      - .env
    ...
   ```
7. Restart the container and reload the management page.

> For advanced users: Instead of adding the `recursive_tree_info_module_resources` volume, you can also look into 
> configuring the laravel [storage driver](https://laravel.com/docs/6.x/filesystem) properly.

## Donations
Donations are always welcome, although not required. If you end up using this module a lot, I'd appreciate a donation. 
You can give ISK or contract PLEX and Ships to `recursivetree`.

