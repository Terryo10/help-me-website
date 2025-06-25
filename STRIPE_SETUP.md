# Stripe Gateway Setup

This document explains how to set up and use the Stripe gateway in your Laravel application.

## Environment Variables

Add the following environment variables to your `.env` file:

```env
# Stripe Configuration
STRIPE_PUBLISHABLE_KEY=pk_test_your_publishable_key
STRIPE_SECRET_KEY=sk_test_your_secret_key
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret
```

For production, use live keys that start with `pk_live_` and `sk_live_`.

## Stripe Account Setup

1. Go to [Stripe Dashboard](https://dashboard.stripe.com/)
2. Create a new account or log in to existing account
3. Navigate to API Keys section
4. Copy the Publishable Key and Secret Key
5. Add them to your `.env` file

## Database Migration

Run the migration to add the required fields:

```bash
php artisan migrate
```

## Usage

### Creating a Stripe Payment

```php
// Route to Stripe gateway
Route::get('/payment/stripe/{orderId}', \App\Livewire\Gateways\StripeGateway::class);
```

### Stripe Gateway Features

The Stripe gateway provides:

1. **Direct Card Processing**: Processes credit/debit cards directly
2. **Real-time Validation**: Validates card details before submission
3. **3D Secure Support**: Handles Strong Customer Authentication (SCA)
4. **Webhook Handling**: Automatically processes Stripe notifications
5. **Transaction Tracking**: Full transaction history and status updates

### Webhook Setup

1. In your Stripe Dashboard, go to Webhooks
2. Add webhook endpoint: `https://yourdomain.com/stripe/webhook`
3. Subscribe to these events:
   - `payment_intent.succeeded`
   - `payment_intent.payment_failed`
   - `payment_intent.requires_action`
   - `payment_intent.canceled`
   - `charge.succeeded`
   - `charge.failed`
4. Copy the webhook signing secret and add to your `.env` file

### Supported Card Types

- Visa
- Mastercard
- American Express
- Discover
- Diners Club
- JCB
- UnionPay

### Transaction Statuses

- `pending`: Transaction created but not yet processed
- `processing`: Payment requires additional authentication (3D Secure)
- `completed`: Payment successfully completed
- `failed`: Payment failed or was declined
- `cancelled`: Payment was cancelled

### Card Validation

The gateway validates:
- Card number format and length
- Expiry date (month/year)
- CVC code (3-4 digits)
- Cardholder name

### Error Handling

The gateway handles common errors:
- Invalid card details
- Insufficient funds
- Card declined
- Network connectivity issues
- Stripe API errors

All errors are logged and user-friendly messages are displayed.

### Security Features

- PCI DSS compliance through Stripe
- Secure card data handling
- 3D Secure authentication
- Webhook signature verification
- CSRF protection on all forms

## Testing

### Test Card Numbers

Stripe provides test card numbers for different scenarios:

**Successful Payments:**
- `4242424242424242` - Visa
- `5555555555554444` - Mastercard
- `378282246310005` - American Express

**Failed Payments:**
- `4000000000000002` - Card declined
- `4000000000009995` - Insufficient funds
- `4000000000000069` - Card expired

**3D Secure Testing:**
- `4000002500003155` - Requires authentication
- `4000002760003184` - Authentication fails

Use any future expiry date and any 3-digit CVC for testing.

## Production Checklist

Before going live:
- [ ] Use live Stripe keys
- [ ] Set up webhook endpoints
- [ ] Test with real cards
- [ ] Implement webhook signature verification
- [ ] Monitor transaction logs
- [ ] Configure SSL/TLS properly
- [ ] Review PCI compliance requirements

## API Endpoints

### Payment Routes
- `GET /payment/stripe/{orderId}` - Stripe gateway page
- `GET /stripe-return/{transactionId}` - Return callback
- `GET /transaction/{transactionId}` - Transaction details

### Webhook
- `POST /stripe/webhook` - Stripe webhook handler

## Troubleshooting

### Common Issues

1. **Invalid API keys error**
   - Check your Stripe API keys
   - Ensure you're using the correct environment (test/live)

2. **Card declined**
   - Check if card details are correct
   - Verify sufficient funds
   - Review Stripe dashboard for decline reason

3. **Webhook not working**
   - Verify webhook URL is publicly accessible
   - Check webhook signature secret
   - Review Stripe webhook logs

4. **3D Secure authentication issues**
   - Ensure return URL is correct
   - Check browser popup blockers
   - Verify card supports 3D Secure

### Logs

Check these log files for debugging:
- `storage/logs/laravel.log` - Application logs
- Stripe webhook events are logged with detailed information
- Stripe Dashboard - Live transaction logs

## Fees and Pricing

Stripe charges processing fees:
- **US/Canada**: 2.9% + $0.30 per transaction
- **Europe**: 1.4% + €0.25 per transaction
- **International cards**: Additional 1% fee

Review [Stripe Pricing](https://stripe.com/pricing) for current rates.

## Compliance

### PCI DSS
- Stripe handles PCI compliance
- Your application doesn't store card data
- Use Stripe's secure tokenization

### GDPR
- Stripe is GDPR compliant
- Review data processing agreements
- Implement proper data retention policies

## Support

For Stripe-specific issues, refer to:
- [Stripe Documentation](https://stripe.com/docs)
- [Stripe API Reference](https://stripe.com/docs/api)
- [Stripe Support](https://support.stripe.com/)

## Advanced Features

### Subscription Billing
- Set up recurring payments
- Manage subscription lifecycles
- Handle plan changes and cancellations

### Multi-party Payments
- Stripe Connect for marketplace payments
- Split payments between parties
- Manage platform fees

### International Payments
- Accept payments in 135+ currencies
- Automatic currency conversion
- Local payment methods

## Integration Tips

1. **Always validate on server-side** - Never trust client-side validation alone
2. **Handle webhooks properly** - Use webhooks for authoritative payment status
3. **Test thoroughly** - Use Stripe's comprehensive test suite
4. **Monitor actively** - Set up alerts for failed payments
5. **Keep keys secure** - Never expose secret keys in client-side code
