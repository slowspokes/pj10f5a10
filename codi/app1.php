<html>
<head>
    <title>G3: Arxiu Generat docker-compose.yml</title>
</head>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recollir dades del formulari
    $app_name = $_POST['app_name'];
    $app_image = $_POST['app_image'];
    $app_hostname = $_POST['app_hostname'];

    $loadbalancer_name = $_POST['loadbalancer_name'];
    $loadbalancer_image = $_POST['loadbalancer_image'];
    $loadbalancer_container = $_POST['loadbalancer_container'];

    $network_name = $_POST['network_name'];
    $network_id = $_POST['network_id'];

    // Comprovar si es vol veure també nginx.conf
    $show_nginx_conf = isset($_POST['show_nginx_conf']);

    // Generar docker-compose.yml
    $dockerComposeContent = <<<YAML
services:
  $app_name: # Aplicació
    image: $app_image
    build: .
    expose:
      - "80"
    environment:
      - HOST_NAME=$app_hostname
    networks:
      - $network_id

  $loadbalancer_name: # Distribuidor de carrega
    image: $loadbalancer_image
    container_name: $loadbalancer_container
    volumes:
      - ./nginx.conf:/etc/nginx/nginx.conf:ro
    depends_on:
      - $app_name
    ports:
      - "80:8000"
    networks:
      - $network_id

networks:
  $network_id:
    name: $network_name
    driver: bridge
YAML;

    file_put_contents('docker-compose.yml', $dockerComposeContent);

    // Generar nginx.conf amb el nom correcte de l'aplicació
    $nginxConfContent = <<<CONF
user nginx;

events {
    worker_connections 1000;
}

http {
    server {
        listen 8000;
        location / {
            proxy_pass http://$app_name/;
        }
    }
}
CONF;

    file_put_contents('nginx.conf', $nginxConfContent);

    // Mostrar resultats
    echo "<h2>Arxiu docker-compose.yml generat amb èxit!</h2>";
    echo "<pre>" . htmlspecialchars($dockerComposeContent) . "</pre>";

    if ($show_nginx_conf) {
        echo "<h2>Contingut de nginx.conf</h2>";
        echo "<pre>" . htmlspecialchars($nginxConfContent) . "</pre>";
    }
} else {
    header('Location: app1.html');
    exit;
}
?>
<body>
    <a href="./app1.html">Generador d'arxiu Docker-compose.yml</a>
</body>
</html>
