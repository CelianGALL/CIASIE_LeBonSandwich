# lbs auth service

## JWT

### Calculer le header 'Authorization'

Pour calculer le header 'authorization' il faut :
1. Prendre le mail de l'utilisateur et son mdp non hashé (son nom de famille) dans la base et séparer ces deux éléments par' ":"
2. [Encoder le résultat en base64](https://www.base64encode.org/)
3. Passer le résultat dans le header en ajoutant "Basic" :
```json
"Authorization":
"Basic TWljaGVsbGUuQm91Y2hlckBsaXZlLmNvbTokMnkkMTAkOFRuNk52dlEyWlVGTHhFNHh1NlFDT2JSRmxXeXFlTS5Rckp5OVZseEZZNEpWRTRrZHZreTY="
```