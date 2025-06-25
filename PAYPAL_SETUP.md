# PayPal Gateway Setup

This document explains how to set up and use the PayPal gateway in your Laravel application.

## Environment Variables

Add the following environment variables to your `.env` file:

```env
# PayPal Configuration
PAYPAL_MODE=sandbox
PAYPAL_CLIENT_ID=your_paypal_client_id
PAYPAL_CLIENT_SECRET=your_paypal_client_secret
```

For production, change `PAYPAL_MODE` to `live`.

## PayPal Developer Account Setup

1. Go to [PayPal Developer](https://developer.paypal.com/)
2. Log in with your PayPal account
3. Create a new application
4. Copy the Client ID and Client Secret
5. Add them to your `.env` file

## Database Migration

Run the migration to add the required fields:

```bash
php artisan migrate
```

## Usage

### Creating a PayPal Payment

```php
// Route to PayPal gateway
Route::get('/payment/paypal/{orderId}', \App\Livewire\Gateways\PaypalGateway::class);
```

### PayPal Gateway Features

The PayPal gateway provides:

1. **Payment Creation**: Creates a PayPal order and redirects user to PayPal
2. **Payment Verification**: Checks payment status after user returns
3. **Webhook Handling**: Automatically processes PayPal notifications
4. **Transaction Tracking**: Full transaction history and status updates

### Webhook Setup

1. In your PayPal Developer Dashboard, go to your app settings
2. Add webhook URL: `https://yourdomain.com/paypal/webhook`
3. Subscribe to these events:
   - `CHECKOUT.ORDER.APPROVED`
   - `CHECKOUT.ORDER.COMPLETED`
   - `PAYMENT.CAPTURE.COMPLETED`
   - `PAYMENT.CAPTURE.DENIED`
   - `PAYMENT.CAPTURE.FAILED`

### Transaction Statuses

- `pending`: Transaction created but not yet processed
- `processing`: User has been redirected to PayPal
- `completed`: Payment successfully completed
- `failed`: Payment failed or was denied
- `cancelled`: Payment was cancelled by user

### Error Handling

The gateway handles common errors:
- Invalid PayPal credentials
- Network connectivity issues
- PayPal API errors
- Invalid transaction data

All errors are logged and user-friendly messages are displayed.

### Security Features

- CSRF protection on all forms
- Secure API communication with PayPal
- Transaction validation and verification
- Webhook signature verification (recommended for production)

## Testing

Use PayPal's sandbox environment for testing:
1. Create sandbox accounts at [PayPal Developer](https://developer.paypal.com/developer/accounts/)
2. Use sandbox credentials in your `.env` file
3. Test with sandbox PayPal accounts

## Production Checklist

Before going live:
- [ ] Change `PAYPAL_MODE` to `live`
- [ ] Use production PayPal credentials
- [ ] Set up webhook URLs
- [ ] Test with real PayPal accounts
- [ ] Implement webhook signature verification
- [ ] Monitor transaction logs

## API Endpoints

### Payment Routes
- `GET /payment/paypal/{orderId}` - PayPal gateway page
- `GET /paypal-success/{transactionId}` - Success callback
- `GET /paypal-cancel/{transactionId}` - Cancel callback
- `GET /transaction/{transactionId}` - Transaction details

### Webhook
- `POST /paypal/webhook` - PayPal webhook handler

## Troubleshooting

### Common Issues

1. **Invalid credentials error**
   - Check your PayPal Client ID and Secret
   - Ensure you're using the correct mode (sandbox/live)

2. **Webhook not working**
   - Verify webhook URL is publicly accessible
   - Check webhook event subscriptions
   - Review PayPal webhook logs

3. **Payment not completing**
   - Check transaction status in database
   - Review application logs
   - Verify PayPal order status in PayPal dashboard

### Logs

Check these log files for debugging:
- `storage/logs/laravel.log` - Application logs
- PayPal webhook events are logged with detailed information

## Support

For PayPal-specific issues, refer to:
- [PayPal Developer Documentation](https://developer.paypal.com/docs/)
- [PayPal REST API Reference](https://developer.paypal.com/docs/api/)
