# Affilinet

Remote interface for AffiliateWP with integrations for woocommerce and woocommerce subscriptions.

## Features

👍 No fuss installation.

🌐 Use a single instance of AffiliateWP across a network of websites.

♻️ Award recurring referrals on subscription payments.

🛒 Capture referral nominations at the checkout.

🤝 Award referrals after purchases have been completed directly from the order page.

🥳 Clean as a whistle - No ads, banners, upsells or other 💩.

## Configuration

After installion, the following configuration settings can be found in `Settings → Affiliates`. 

| Option | Description |
| - | - |
| Campaign | Campaign name for this service. |
| Referral Rate | The default referral rate. (%) |
| Site URL | The site URL where AffiliateWP is installed. |
| Referral Variable | The referral variable you have set in AffiliateWP at the site URL above. |
| Cookie Expiration | Lifetime of referral tracking cookies. (days) |
| Public Key | Public key for AffilateWP API |
| Token | Token for AffilateWP API |
| Nomination Question | Question for customers to nominate an affiliate during checkout process. (optional) |

## Hooks

| Hook | Args | Info |
| - | - | - |
| `affilinet-referral-rate` | `$rate`, `$referral_obj` | % Rate of the referral. |
| `affilinet-referral-amount` | `$amount`, `$referral_obj` | Final value of the referral. |
| `affilinet-referral-send` | `$referral_obj` | Referral object to be sent to Remote. |
| `affilinet-payment-description` | `$description`, `$order_id` | Description of the referral. |

## Credits

Developed and maintained by Jamie Perrelet.
<br><br>
![Digitalis](https://digitalisweb.ca/wp-content/plugins/digitalisweb/assets/png/logo/digitalis.222.250.png)