External data import script
====================

Script di utilità per l'import dei dati da vari sorgenti


Install
-------

After cloning the repo, get composer and install the dependencies:

    curl -sS https://getcomposer.org/installer | php
    
    php composer.phar install


Copy the file config.php.dist to config.php and fill it with your data.




Command line
------------

    Flags
      --verbose, -v                 Turn on verbose output
      --version                     Display the version
      --quiet, -q                   Disable all output
      --help, -h                    Show this help screen
      --production-mode, -p         Turn on production mode, default off
      --import-ragazzi, -r          Turn on import ragazzi [API]
      --import-capi, -c             Turn on import capi [API]
      --import-capolaboratorio, -l  Turn on import capi laboratorio [API]
      --import-extra, -x            Turn on import capi extra [API]
      --import-oneteam, -o          Turn on import oneteam [API]
      --import-gruppi, -g           Turn on import gruppi [API]
      --import-external-lab, -e     Turn on import external lab [FILE]
      --import-internal-lab, -i     Turn on import internal lab [FILE]
      --import-subarea, -s          Turn on import sub area [FILE]
      --import-route, -u            Turn on import route definition [FILE]


    Options
      --input-file, -f  Setta il file da caricare [default: /Users/yoghi/Documents/Workspace/external-data-import/resources/${nomefile}.xlxs]

