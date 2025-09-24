<img src="./zaincashmodule/logo-white.svg" alt="Zain Cash Logo" style="width:200px;"/>

# Zain Cash Payment Gateway for WHMCS

## Overview

The **Zain Cash Payment Gateway Module** seamlessly integrates Zain Cash with WHMCS, allowing customers to make payments directly from their Zain Cash wallets.

---

## Installation Guide

1. Navigate to your WHMCS directory.
2. Move the following files and directories:
   - `includes/`
   - `zaincashmodule/`
   - `zaincashmodule.php`
3. Move `callback/zaincashmodule.php` to the `callback` directory in WHMCS.
4. Open the WHMCS Marketplace, search for "Zain Cash," and install the module.
5. Go to WHMCS settings under "Payment Gateways" and enter your Zain Cash credentials.

---

## Project Structure

```
├── callback
│   └── zaincashmodule.php
├── includes
│   ├── autoload.php
│   ├── composer/
│   └── firebase/php-jwt/
├── zaincashmodule
│   ├── logo-white.svg
│   ├── logo.png
│   └── whmcs.json
├── zaincashmodule.php
└── README.md
```

---

For any issues, feel free to reach out.

Developed with ❤️ by [@alsaadii98](https://github.com/alsaadii98) at [eSITE Information Technology](https://esite-iq.com).
