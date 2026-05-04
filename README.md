# TPI

Mettre : 
```
127.0.0.1 tpi.local
127.0.0.1 api.tpi.local
127.0.0.1 db.tpi.local
```
dans le fichier `C:\Windows\System32\drivers\etc\hosts`

Changer aussi les valeurs dans `backend/secrets/PDO-Change.php`, `backend/secret/infomaniak-Change.php` et `db/initChangePass.sql`

Pour lancer le projet merci de taper la commande `docker compose up -d --build` à la racine du projet.