eval "$(ssh-agent)" && ssh-agent -s

chmod 0600 /root/.ssh/id_rsa
ssh-add /root/.ssh/id_rsa

ssh -T git@github.com-banana

git clone --single-branch --branch master git@github.com-banana:banana-mama/golovin-app.git /var/www/app/