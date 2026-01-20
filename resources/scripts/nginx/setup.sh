mkdir -p /etc/nginx/sites-enabled/
rm -r /etc/nginx/sites-enabled/*

cp -r /etc/nginx/sites-available/${DOMAIN} /etc/nginx/sites-enabled/${DOMAIN}

