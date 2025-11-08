<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lembrete de Renovação</title>
</head>
<body>
    <p>Olá, {{ $subscription->user->name }}!</p>
    
    <p>Este é um lembrete de que sua assinatura do serviço 
       <strong>{{ $subscription->service->name }}</strong> 
       no valor de R$ {{ $subscription->price }} 
       será renovada em {{ \Carbon\Carbon::parse($subscription->renewal_date)->format('d/m/Y') }}.</p>

    <p>Você será cobrado automaticamente nesta data.</p>
    
    <p>Obrigado,<br>
    Equipe SubReminder</p>
</body>
</html>