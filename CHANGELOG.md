### 5.0.2 - 2022/07/19 18:02

[Full Changelog](https://github.com/conekta/ct-magento-2.4.4/compare/5.0.1..5.0.2)
* Fix:
  - Set 220 by default response in webhook by œelvisheredia [#3](https://github.com/conekta/ct-magento-2.4.4/pull/3)

### 5.0.1 - 2022/06/22 11:24

[Full Changelog](https://github.com/conekta/ct-magento-2.4.4/compare/5.0.0..5.0.1)
* Fix:
  - Fix error in Logger file by œelvisheredia [#2](https://github.com/conekta/ct-magento-2.4.4/pull/2)

### 5.0.0 - 2022/06/13 17:00

[Full Changelog](https://github.com/conekta/ct-magento-2.4.4/compare/main..5.0.0)
* Feature:
  - [MT-275] Update to php 8.1 by @elvisheredia in [#1](https://github.com/conekta/ct-magento-2.4.4/pull/1)
  - Apply PSR12 format rules
  - Upgrade libraries

### 4.1.0 - 2022/06/13 17:00
* Feature
  - Fix in OXXO order date format in magento admin.
  - Fix in purchases with free shipping.
  - Fixes for the marketplace store.
  - Fixes to pass the MFTF tests.
  - Listen to event notifications in the webhook saved in the store administration.
  - Authentication fix for magento 2.3
  - Fix of magneto status update in the Comments History section.
  - Fix for embedded checkout, only X amount of charges are added to the same order.
  - Added support for magento enterprise.
  - Added sanitization of special characters to checkout.
  - Fix to decimals.
  - Conekta plugin version added as metadata.
  - Sanitation of special fields also applied to SKU.