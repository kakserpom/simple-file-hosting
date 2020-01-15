<?php
$name = 'filehosting';
$sslCrt = $config->ssl_certificate ?? glob('/etc/ssl/' . $name . '/*.crt')[0] ?? null;
$sslKey = $config->ssl_certificate_key ?? glob('/etc/ssl/' . $name . '/*.key')[0] ?? null;

$listen = ['80'];
if ($sslCrt && $sslKey) {
    $listen[] = '443 ssl';
}
$listen = $config->listen ?? $listen;
?>
upstream fastcgi_<?= $name ?> {
    server <?= $config->fastcgi_server ?? 'unix:/run/php/php7.4-fpm.sock' ?>;
}
map $sent_http_content_type $expires {
default                   off;
~image/                   max;
~video/                   max;
~audio/                   max;
text/css                  max;
application/javascript    max;
application/font-woff     max;
}
server {
set $projectDir "<?php echo ZERO_ROOT; ?>";
root $projectDir/public;


<?php if ($sslCrt && $sslKey): ?>
    ssl_certificate      <?= $sslCrt ?>;
    ssl_certificate_key  <?= $sslKey ?>;
<?php endif; ?>

<?php foreach ($listen as $row): ?>
    listen <?= $row ?>;
<?php endforeach; ?>

<?php echo $indentStr(file_get_contents(__DIR__ . '/is-mobile.conf'), 1); ?>

#auth_basic           "restricted area";
#auth_basic_user_file $projectDir/nginx/.htpasswd;

server_name "<?php echo $escapeServerName($config->server_name); ?>";
error_log  /var/log/nginx/error_<?php echo $name; ?>.log notice;
access_log  /var/log/nginx/access_<?php echo $name; ?>.log;

default_type  application/octet-stream;

charset utf-8;
gzip on;
gzip_types text/plain text/css application/json application/javascript \
text/xml application/xml application/xml+rss text/javascript application/x-javascript;
client_max_body_size 200m;

expires $expires;

sendfile on;
tcp_nopush off;

add_header X-Frame-Options SAMEORIGIN;
add_header X-XSS-Protection "1; mode=block";

rewrite ^/(.*)/$ /$1 permanent;

<?php
if ($config->pagespeed ?? false) {
    echo $indentStr(file_get_contents(__DIR__ . '/pagespeed.conf'), 1);
}
?>

<?php echo $indentStr(file_get_contents(__DIR__ . '/realip.conf'), 1); ?>

error_page 403 /error/403;
error_page 404 /error/404;
error_page 405 /error/405;
error_page 500 /error/500;

location = /robots.txt {return 200 "User-agent: *\nDisallow: /\n";}

location ~ /\. {
deny all;
}

location /SOURCES/ {
alias $projectDir/SOURCES/;
index index.html;
}

location /img/ {
alias $projectDir/public/img/;
try_files $uri /error/404;
}

location /css/ {
alias $projectDir/public/css/;
try_files $uri /error/404;
}

location /fonts/ {
alias $projectDir/public/fonts/;
try_files $uri /error/404;
}

location /js/ {
alias $projectDir/public/js/;
try_files $uri /error/404;
}

location /lib/ {
alias $projectDir/public/lib/;
try_files $uri /error/404;
}


location /storage/ {
    alias <?php echo ZERO_ROOT; ?>/storage/;
}

location /dist/node_modules/ {
alias $projectDir/node_modules/;
try_files $uri /error/404;
}

location /dist/ {
alias $projectDir/dist/;
try_files $uri /error/404;
}

location /dist/public/ {
alias $projectDir/public/;
try_files $uri /error/404;
}


<?php echo $indentStr($routesGenerator->generate(
    'fastcgi_param IS_MOBILE $mobile;' . PHP_EOL .
    'fastcgi_pass fastcgi_' . $name . ';'
), 1); ?>
}
