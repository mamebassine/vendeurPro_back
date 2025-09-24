<!DOCTYPE html>
<html>
<head>
    <title>Bienvenue en tant que Parrain0</title>
</head>
<body>
    <h1>Bonjour {{ $name }},</h1>
    <p>Vous avez été inscrit en tant que Parrain sur notre site.</p>
    <p>Voici vos informations de connexion :</p>
    <ul>
        <li>Email : {{ $email }}</li>
        <li>Téléphone : {{ $phone }}</li>
    </ul>
    <p>Pour accéder à votre tableau de bord, utilisez le formulaire de connexion via ce lien :
        <a href="{{ $lienParrainage }}">{{ $lienParrainage }}</a>
    </p>
    <p>Merci pour votre engagement !</p>
</body>
</html>
