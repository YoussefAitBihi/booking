<header>
  <h1 style="margin-bottom: 10px">Booking web application</h1>
  <p>Ce projet se base sur la réservation de logement, le propriétaire peut mettre en place son logement sous forme d'une annonce, et donc le voyageur peut le louer.</p>
</header>

<hr style="margin: 20px 0">

<main>
  <h3 style="margin-bottom: 10px">Installation</h3>
  <ol>
    <li>Clonez le dépôt chez vous</li>
    <li>Taper la commande suivante <code>php bin/console doctrine:database:create</code> afin que vous puissiez créer la base de données</li>
    <li>Taper la commande suivante <code>php bin/console make:migration</code> afin que vous puissiez créer la migration</li>
    <li>Taper la commande suivante <code>php bin/console doctrine:migrations:migrate</code> afin que vous puissiez créer toutes les tables</li>
    <li>Taper la commande suivante <code>php bin/console doctrine:fixtures:load --no-interaction</code> pour avoir des fausses données</li>
    <li>Lancer le serveur interne de php en tapant <code>php -S localhost:8000 -t public</code></li>
    <li>Profitez-en</li>
  </ol>
</main>


