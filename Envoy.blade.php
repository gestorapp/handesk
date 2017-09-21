@servers(['ks1' => ['ks1.ksirve.com']])

@task('deploy', ['on' => 'ks1'])
    su help
    cd /home/help/handesk
    php artisan down --message="Upgrading system.." --retry=60
    git fetch --all
    git reset --hard origin/master
    git pull origin master
    composer install --no-dev
    php artisan up
@endtask

@task('ssh', ['on' => 'ks1'])
    su help
    cd /home/help/.ssh
    ssh-keygen -t rsa -C "soporte@sunnyface.com" -b 4096  -N "" -f gitkey
    cat  /home/help/.ssh/gitkey.pub
@endtask

@task('setup', ['on' => ['ks1']])
    su help
    cd /home/help
    git clone git@soundhub.sunnyface.com:sunny/handesk.git
    cd /home/help/handesk
    mv .env.example .env
    composer install --no-dev
    php artisan key:generate
    php artisan storage:link
    cd /home/help
    rm -rf public_html
    ln -s /home/help/handesk/public /home/help/public_html
    echo "Now setup your .env"
@endtask

@task('up', ['on' => ['ks1']])
    su help
    cd /home/help/webapp
    php artisan up
@endtask

@task('down', ['on' => ['ks1']])
    su help
    cd /home/help/webapp
    php artisan down
@endtask

@task('migrate', ['on' => 'ks1'])
    su help
    cd /home/help/webapp
    php artisan migrate --force
@endtask

@task('migrate:rollback', ['on' => 'ks1'])
    su help
    cd /home/help/webapp
    php artisan migrate:rollback --force
@endtask

@task('permisos', ['on' => 'ks1'])
    chmod -R 0777 public/upload app/storage
@endtask
