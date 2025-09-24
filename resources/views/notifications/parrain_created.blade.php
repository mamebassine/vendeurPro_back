<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Bienvenue en tant que Parrain</title>
</head>
<body style="font-family: Arial, sans-serif; color: #000; line-height: 1.6; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px; background-color: #f9f9f9;">
        <h1 style="color: #343F69; font-size: 24px; margin-bottom: 20px;">Bonjour {{ $name }},</h1>

        <p style="font-size: 16px; margin-bottom: 10px;">Vous avez été inscrit en tant que Parrain sur notre site.</p>

        <p style="font-size: 16px; margin-bottom: 10px;">Voici vos informations de connexion :</p>
        <ul style="font-size: 16px; padding-left: 20px; margin-bottom: 20px;">
            <li>Email : {{ $email }}</li>
            <li>Téléphone : {{ $phone }}</li>
            <li>Mot de passe temporaire : {{ $password }}</li>
        </ul>

        <p style="font-size: 16px; margin-bottom: 20px;">
            Pour accéder à votre tableau de bord, cliquez sur le lien ci-dessous :
        </p>
        <p>
            <a href="{{ $lienParrainage }}" style="display: inline-block; background-color: #343F69; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-size: 16px;">
                Accéder au tableau de bord
            </a>
        </p>

        <p style="font-size: 16px; margin-top: 30px;">Merci pour votre engagement !</p>
    </div>
</body>
</html>
