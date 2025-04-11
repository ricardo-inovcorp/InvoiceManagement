<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Alerta de Vencimento de Fatura</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 30px;
            border: 1px solid #ddd;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #e74c3c;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #e74c3c;
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 20px 0;
        }
        .invoice-details {
            background-color: #fff;
            border: 1px solid #eee;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .invoice-details p {
            margin: 5px 0;
        }
        .invoice-details .label {
            font-weight: bold;
            width: 150px;
            display: inline-block;
        }
        .button {
            display: inline-block;
            background-color: #e74c3c;
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #777;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Alerta de Vencimento de Fatura</h1>
        </div>
        
        <div class="content">
            <p>Prezado(a) {{ $user->name }},</p>
            
            <p>Esperamos que esteja bem. Gostaríamos de informar que tem uma fatura com vencimento <strong>hoje</strong>. Por favor, verifique os detalhes abaixo:</p>
            
            <div class="invoice-details">
                <p><span class="label">Número da Fatura:</span> {{ $invoice->invoice_number }}</p>
                <p><span class="label">Fornecedor:</span> {{ $invoice->supplier->company_name }}</p>
                <p><span class="label">Data de Emissão:</span> {{ \Carbon\Carbon::parse($invoice->issue_date)->format('d/m/Y') }}</p>
                <p><span class="label">Data de Vencimento:</span> {{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</p>
                <p><span class="label">Valor Total:</span> € {{ number_format($invoice->total_amount, 2, ',', '.') }}</p>
            </div>
            
            <p>É importante proceder ao pagamento desta fatura atempadamente para evitar quaisquer encargos adicionais ou interrupções nos serviços.</p>
            
            <p>Por favor, aceda ao sistema para realizar o pagamento ou atualizar o estado da fatura:</p>
            
            <div style="text-align: center;">
                <a href="{{ url('/invoices/' . $invoice->id) }}" class="button">Ver Fatura</a>
            </div>
            
            <p>Se já efetuou o pagamento, por favor ignore este email ou atualize o estado da fatura no sistema.</p>
            
            <p>Agradecemos a sua atenção a este assunto.</p>
            
            <p>Com os melhores cumprimentos,<br>Equipa de Gestão de Faturas</p>
        </div>
        
        <div class="footer">
            <p>Este é um email automático. Por favor, não responda a esta mensagem.</p>
            <p>© {{ date('Y') }} Sistema de Gestão de Faturas. Todos os direitos reservados.</p>
        </div>
    </div>
</body>
</html> 